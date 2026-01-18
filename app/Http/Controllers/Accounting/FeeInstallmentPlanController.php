<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\FeeInstallment;
use App\Models\Accounting\FeeInstallmentPlan;
use App\Models\Accounting\FeeStructure;
use Illuminate\Http\Request;

class FeeInstallmentPlanController extends Controller
{
    public function __construct()
    {
        // Admin / Super Admin only
        $this->middleware('teamSA');
    }

    public function index(FeeStructure $structure)
    {
        $structure->load(['academicPeriod', 'installmentPlans.installments' => function ($q) {
            $q->orderBy('sequence');
        }, 'items']); // Load items to sum totals

        $totalAmount = $structure->items->sum('amount');
        $activePlan = $structure->installmentPlans->first();
        $periods = AcademicPeriod::orderBy('ordering')->get();

        return view('pages.accountant.fees.installments', [
            'structure' => $structure,
            'plan' => $activePlan,
            'installments' => $activePlan?->installments ?? collect(),
            'periods' => $periods,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function storePlan(Request $request, FeeStructure $structure)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        // For now keep a single active plan per structure
        $structure->installmentPlans()->update(['is_active' => false]);

        $structure->installmentPlans()->create([
            'name' => $data['name'],
            'is_active' => true,
        ]);

        return redirect()->route('accounting.installments.index', $structure->id)
            ->with('flash_success', __('msg.store_ok'));
    }

    public function storeInstallment(Request $request, FeeStructure $structure, FeeInstallmentPlan $plan)
    {
        abort_unless($plan->fee_structure_id === $structure->id, 404);

        $data = $request->validate([
            'sequence'          => 'required|integer|min:1',
            'label'             => 'required|string|max:191',
            'percentage'        => 'nullable|numeric|min:0|max:100',
            'fixed_amount'      => 'nullable|numeric|min:0',
            'fixed_amount'      => 'nullable|numeric|min:0',
            'due_date'          => [
                'nullable', 
                'date',
                function ($attribute, $value, $fail) use ($structure) {
                    if ($value && $structure->due_date && $value > $structure->due_date->format('Y-m-d')) {
                         $fail('The installment due date cannot be later than the fee structure due date (' . $structure->due_date->format('d M Y') . ').');
                    }
                }
            ],

        ]);

        $plan->installments()->create($data);

        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function updateInstallment(Request $request, FeeInstallment $installment)
    {
        // We need the structure to validate the date
        $structure = $installment->feeStructure;

        $data = $request->validate([
            'sequence'          => 'required|integer|min:1',
            'label'             => 'required|string|max:191',
            'percentage'        => 'nullable|numeric|min:0|max:100',
            'fixed_amount'      => 'nullable|numeric|min:0',
            'due_date'          => [
                'nullable', 
                'date',
                function ($attribute, $value, $fail) use ($structure) {
                    if ($value && $structure->due_date && $value > $structure->due_date->format('Y-m-d')) {
                         $fail('The installment due date cannot be later than the fee structure due date (' . $structure->due_date->format('d M Y') . ').');
                    }
                }
            ],

        ]);

        $installment->update($data);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function destroyInstallment(FeeInstallment $installment)
    {
        $installment->delete();
        return back()->with('flash_success', __('msg.del_ok'));
    }
}
