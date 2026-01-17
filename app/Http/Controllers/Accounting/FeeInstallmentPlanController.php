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
        }]);

        $activePlan = $structure->installmentPlans->first();
        $periods = AcademicPeriod::orderBy('ordering')->get();

        return view('pages.accountant.fees.installments', [
            'structure' => $structure,
            'plan' => $activePlan,
            'installments' => $activePlan?->installments ?? collect(),
            'periods' => $periods,
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
            'due_date'          => 'nullable|date',
            'grace_days'        => 'nullable|integer|min:0',
            'late_penalty_type' => 'nullable|in:none,fixed,percentage',
            'late_penalty_value'=> 'nullable|numeric|min:0',
        ]);

        $plan->installments()->create($data);

        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function updateInstallment(Request $request, FeeInstallment $installment)
    {
        $data = $request->validate([
            'sequence'          => 'required|integer|min:1',
            'label'             => 'required|string|max:191',
            'percentage'        => 'nullable|numeric|min:0|max:100',
            'fixed_amount'      => 'nullable|numeric|min:0',
            'due_date'          => 'nullable|date',
            'grace_days'        => 'nullable|integer|min:0',
            'late_penalty_type' => 'nullable|in:none,fixed,percentage',
            'late_penalty_value'=> 'nullable|numeric|min:0',
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
