<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        
        // Academic Stats
        $d['total_classes'] = DB::table('my_classes')->count();
        $d['total_sections'] = DB::table('sections')->count();
        $d['total_subjects'] = DB::table('subjects')->count();
        $d['total_students'] = DB::table('student_records')->where('session', Qs::getSetting('current_session'))->count();
        $d['total_exams'] = DB::table('exams')->where('year', Qs::getSetting('current_session'))->count();

        // Recent Exam Records
        $d['recent_exams'] = DB::table('exams')
            ->where('year', Qs::getSetting('current_session'))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.academic.dashboard', $d);
    }
}
