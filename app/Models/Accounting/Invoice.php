<?php

namespace App\Models\Accounting;

use App\Models\StudentRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'parent_invoice_id',
        'is_installment',
        'installment_sequence',
        'installment_label',
        'student_id',
        'student_record_id',
        'fee_structure_id',
        'academic_period_id',
        'status',
        'issued_by',
        'issued_at',
        'due_date',
        'subtotal_amount',
        'discount_total',
        'penalty_total',
        'total_amount',
        'amount_paid',
        'balance_due',
        'currency',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'date',
        'is_installment' => 'boolean',
    ];

    public function parentInvoice()
    {
        return $this->belongsTo(self::class, 'parent_invoice_id');
    }

    public function childInvoices()
    {
        return $this->hasMany(self::class, 'parent_invoice_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function period()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function installments()
    {
        return $this->hasMany(StudentInstallment::class);
    }
}
