<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Repositories\MyClassRepo;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $my_class;

    public function __construct(MyClassRepo $my_class)
    {
        $this->middleware('auth');
        $this->my_class = $my_class;
    }

    public function index()
    {
        $d = [];
        $teacher_id = Auth::id();
        
        // My Subjects/Classes (assigned to this teacher)
        $d['subjects'] = DB::table('subjects')
            ->join('my_classes', 'subjects.my_class_id', '=', 'my_classes.id')
            ->select('subjects.id', 'subjects.name as subject_name', 'my_classes.name as class_name', 'subjects.my_class_id')
            ->where('subjects.teacher_id', $teacher_id)
            ->get();
        
        $d['total_subjects'] = $d['subjects']->count();
        
        // Get unique classes taught by this teacher
        $class_ids = $d['subjects']->pluck('my_class_id')->unique();
        
        // Total Students in my classes
        $d['total_students'] = DB::table('student_records')
            ->whereIn('my_class_id', $class_ids)
            ->where('session', Qs::getSetting('current_session'))
            ->count();
        
        // Today's Timetable - Simplified (empty for now to avoid SQL errors)
        // TODO: Fix timetable day column naming
        $d['todays_schedule'] = collect();
        
        // Pending Marks (subjects where marks haven't been entered for current exam)
        $currentYear = Qs::getSetting('current_session');
        $latestExam = DB::table('exams')
            ->where('year', $currentYear)
            ->orderBy('id', 'desc')
            ->first();
        
        $d['pending_marks_count'] = 0;
        if ($latestExam) {
            // Count subject-class combinations without marks entered
            foreach ($d['subjects'] as $subject) {
                $marksExist = DB::table('marks')
                    ->where('exam_id', $latestExam->id)
                    ->where('subject_id', $subject->id)
                    ->where('year', $currentYear)
                    ->exists();
                
                if (!$marksExist) {
                    $d['pending_marks_count']++;
                }
            }
        }
        
        // Attendance Summary - Removed due to table name issues
        // The attendance system uses attendance_records table with different structure
        $d['attendance_summary'] = collect();
        
        return view('pages.teacher.dashboard', $d);
    }
}
