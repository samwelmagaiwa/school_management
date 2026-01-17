<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\FeeStructure;
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
        $data['structures'] = FeeStructure::with('academicPeriod')->orderByDesc('created_at')->get();
        $data['periods'] = AcademicPeriod::orderBy('ordering')->get();
        $data['classes'] = MyClass::orderBy('name')->get();

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
            'due_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
        ]);

        FeeStructure::create($data);

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
            'due_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
        ]);

        $structure->update($data);

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
