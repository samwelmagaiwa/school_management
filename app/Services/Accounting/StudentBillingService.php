<?php

namespace App\Services\Accounting;

use App\Helpers\Qs;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeStructureItem;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\StudentFeeAssignment;
use App\Models\StudentRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentBillingService
{
    public function __construct(private readonly InstallmentScheduler $scheduler)
    {
    }

    public function generateForEnrollment(StudentRecord $student): array
    {
        $structures = $this->structuresForStudent($student);
        $invoices = [];

        foreach ($structures as $structure) {
            if ($invoice = $this->generateForStudent($student, $structure)) {
                $invoices[] = $invoice;
            }
        }

        return $invoices;
    }

    public function generateForStructure(FeeStructure $structure, array $filters = []): array
    {
        $structure->loadMissing('items');

        if ($structure->status !== 'published') {
            return [];
        }

        $students = $this->studentsForStructure($structure, $filters);
        $invoices = [];

        foreach ($students as $student) {
            if ($invoice = $this->generateForStudent($student, $structure)) {
                $invoices[] = $invoice;
            }
        }

        return $invoices;
    }

    public function generateForStudent(StudentRecord $student, FeeStructure $structure, bool $force = false): ?Invoice
    {
        if (! $force && $this->invoiceExists($student, $structure)) {
            return Invoice::where('student_record_id', $student->id)
                ->where('fee_structure_id', $structure->id)
                ->whereNull('parent_invoice_id')
                ->latest()
                ->first();
        }

        return DB::transaction(function () use ($student, $structure) {
            $assignment = $this->recordAssignment($student, $structure);
            $invoice = $this->createParentInvoice($student, $structure);
            $subtotal = $this->attachItems($invoice, $student, $structure);

            if ($subtotal <= 0) {
                $invoice->delete();
                $assignment->update(['status' => 'cancelled']);
                return null;
            }

            $this->scheduler->generateForInvoice($invoice);

            if (! $invoice->childInvoices()->exists()) {
                $invoice->update(['status' => 'issued']);
            }

            $assignment->update(['status' => 'applied']);

            return $invoice->fresh(['items', 'childInvoices']);
        });
    }

    protected function createParentInvoice(StudentRecord $student, FeeStructure $structure): Invoice
    {
        $periodId = $structure->academic_period_id;
        $period = $periodId ? AcademicPeriod::find($periodId) : null;

        return Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber($structure),
            'student_id' => $student->user_id,
            'student_record_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'academic_period_id' => $periodId,
            'status' => 'draft',
            'issued_by' => auth()->id(),
            'issued_at' => now(),
            'due_date' => $structure->due_date ?? $period?->due_date,
            'subtotal_amount' => 0,
            'discount_total' => 0,
            'penalty_total' => 0,
            'total_amount' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'currency' => 'TZS',
            'notes' => 'Auto-generated from fee structure',
        ]);
    }

    protected function attachItems(Invoice $invoice, StudentRecord $student, FeeStructure $structure): float
    {
        $items = FeeStructureItem::where('fee_structure_id', $structure->id)
            ->where(function ($q) use ($student) {
                $q->whereNull('my_class_id')
                    ->orWhere('my_class_id', $student->my_class_id);
            })
            ->where(function ($q) use ($student) {
                $q->whereNull('section_id')
                    ->orWhere('section_id', $student->section_id);
            })
            ->with('item')
            ->get();

        $subtotal = 0;

        foreach ($items as $structureItem) {
            $lineAmount = (float) $structureItem->amount;

            $invoice->items()->create([
                'fee_item_id' => $structureItem->fee_item_id,
                'description' => $structureItem->item?->name ?? 'Fee Item',
                'quantity' => 1,
                'unit_amount' => $lineAmount,
                'total_amount' => $lineAmount,
                'discount_amount' => 0,
                'waiver_amount' => 0,
                'is_optional' => $structureItem->is_optional,
            ]);

            $subtotal += $lineAmount;
        }

        if ($subtotal > 0) {
            $invoice->update([
                'subtotal_amount' => $subtotal,
                'total_amount' => $subtotal,
                'balance_due' => $subtotal,
            ]);
        }

        return $subtotal;
    }

    protected function invoiceExists(StudentRecord $student, FeeStructure $structure): bool
    {
        return Invoice::where('student_record_id', $student->id)
            ->where('fee_structure_id', $structure->id)
            ->whereNull('parent_invoice_id')
            ->exists();
    }

    protected function generateInvoiceNumber(FeeStructure $structure): string
    {
        $prefix = Str::slug($structure->name ?: 'fee');
        return strtoupper($prefix.'-'.now()->format('Ymd').'-'.Str::random(5));
    }

    public function recordAssignment(StudentRecord $student, FeeStructure $structure, string $scope = 'student'): StudentFeeAssignment
    {
        return StudentFeeAssignment::updateOrCreate(
            [
                'student_record_id' => $student->id,
                'fee_structure_id' => $structure->id,
            ],
            [
                'academic_period_id' => $structure->academic_period_id,
                'scope' => $scope,
                'my_class_id' => $student->my_class_id,
                'section_id' => $student->section_id,
                'status' => 'pending',
            ]
        );
    }

    protected function structuresForStudent(StudentRecord $student): Collection
    {
        $session = Qs::getCurrentSession();

        return FeeStructure::where('status', 'published')
            ->when($session, fn ($q) => $q->where('academic_year', $session))
            ->whereHas('items', function ($q) use ($student) {
                $q->whereNull('my_class_id')->orWhere('my_class_id', $student->my_class_id);
            })
            ->whereHas('items', function ($q) use ($student) {
                $q->whereNull('section_id')->orWhere('section_id', $student->section_id);
            })
            ->with('items')
            ->get();
    }

    protected function studentsForStructure(FeeStructure $structure, array $filters = []): Collection
    {
        $classIds = $structure->items->pluck('my_class_id')->filter()->unique()->all();
        $sectionIds = $structure->items->pluck('section_id')->filter()->unique()->all();

        $query = StudentRecord::query()->where('grad', 0);
        $session = Qs::getCurrentSession();

        if ($session) {
            $query->where('session', $session);
        }

        if ($filters['class_id'] ?? false) {
            $query->where('my_class_id', $filters['class_id']);
        } elseif (! empty($classIds)) {
            $query->whereIn('my_class_id', $classIds);
        }

        if ($filters['section_id'] ?? false) {
            $query->where('section_id', $filters['section_id']);
        } elseif (! empty($sectionIds)) {
            $query->whereIn('section_id', $sectionIds);
        }

        return $query->get();
    }
}
