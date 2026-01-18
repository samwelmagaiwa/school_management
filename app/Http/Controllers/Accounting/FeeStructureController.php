<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\FeeItem;
use App\Models\Accounting\FeeCategory;
use App\Models\MyClass;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index()
    {
        $data['structures'] = FeeStructure::with(['academicPeriod', 'terms'])->orderByDesc('created_at')->get();
        $data['periods'] = AcademicPeriod::orderBy('ordering')->get();
        $data['classes'] = MyClass::orderBy('name')->get();
        $data['fee_items'] = FeeItem::orderBy('name')->get();
        $data['fee_categories'] = FeeCategory::orderBy('name')->get();

        return view('pages.accountant.fees.structures', $data);
    }

    public function store(Request $request)
    {
        // Only Admin / Super Admin may create fee structures
        abort_unless(Qs::userIsTeamSA(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'academic_year' => 'required|string|max:9',
            'academic_period_id' => 'nullable|exists:academic_periods,id',
            'due_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->academic_period_id && $value) {
                        $period = AcademicPeriod::find($request->academic_period_id);
                        if ($period) {
                            if ($value < $period->start_date->format('Y-m-d')) {
                                $fail('The due date cannot be earlier than the period start date (' . $period->start_date->format('d M Y') . ').');
                            }
                            $limit = $period->due_date ?? $period->end_date;
                            if ($value > $limit->format('Y-m-d')) {
                                $fail('The due date cannot be later than the period limit (' . $limit->format('d M Y') . ').');
                            }
                        }
                    }
                },
            ],
            'status' => 'required|in:draft,published,archived',
        ]);

        $fs = FeeStructure::create($data);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'id' => $fs->id, 'msg' => 'Fee structure created successfully. Next: Configure Terms.']);
        }

        return redirect()->route('accounting.fee-structures.index')
            ->with('flash_success', __('msg.store_ok'));
    }

    public function update(Request $request, FeeStructure $structure)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'academic_year' => 'required|string|max:9',
            'academic_period_id' => 'nullable|exists:academic_periods,id',
            'due_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->academic_period_id && $value) {
                        $period = AcademicPeriod::find($request->academic_period_id);
                        if ($period) {
                            if ($value < $period->start_date->format('Y-m-d')) {
                                $fail('The due date cannot be earlier than the period start date (' . $period->start_date->format('d M Y') . ').');
                            }
                            $limit = $period->due_date ?? $period->end_date;
                            if ($value > $limit->format('Y-m-d')) {
                                $fail('The due date cannot be later than the period limit (' . $limit->format('d M Y') . ').');
                            }
                        }
                    }
                },
            ],
            'status' => 'required|in:draft,published,archived',
        ]);

        $structure->update($data);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'msg' => 'Basic info saved successfully. Next: Define Terms.']);
        }

        return redirect()->route('accounting.fee-structures.index')
            ->with('flash_success', __('msg.update_ok'));
    }

    public function destroy(FeeStructure $structure)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $structure->delete();

        return redirect()->route('accounting.fee-structures.index')
            ->with('flash_success', __('msg.del_ok'));
    }
}
