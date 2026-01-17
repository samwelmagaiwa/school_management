<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Accounting\FeeStructure;
use App\Services\Accounting\StudentBillingService;
use Illuminate\Http\Request;

class StudentBillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function generateForStructure(Request $request, FeeStructure $structure, StudentBillingService $billing)
    {
        if (! Qs::userIsTeamSA()) {
            abort(403, 'Only admin / super admin may trigger bulk billing.');
        }

        if ($structure->status !== 'published') {
            return back()->with('flash_danger', 'Only published fee structures can be billed.');
        }

        $filters = [];
        if ($request->filled('class_id')) {
            $filters['class_id'] = $request->input('class_id');
        }
        if ($request->filled('section_id')) {
            $filters['section_id'] = $request->input('section_id');
        }

        $invoices = $billing->generateForStructure($structure, $filters);

        $count = count($invoices);

        return back()->with('flash_success', "Generated {$count} invoices for {$structure->name}.");
    }
}
