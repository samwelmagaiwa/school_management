<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Repositories\StudentRepo;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $student;

    public function __construct(StudentRepo $student)
    {
        $this->middleware('auth');
        $this->student = $student;
    }

    public function index()
    {
        $d = [];
        $student_id = Auth::id();
        
        // Get Student Record
        $d['sr'] = $sr = $this->student->getRecord(['user_id' => $student_id])->first();
        
        if (!$sr) {
            $d['attendance_stats'] = (object) ['total_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0];
            $d['attendance_percentage'] = 0;
            $d['fee_summary'] = (object) ['total_paid' => 0, 'total_outstanding' => 0];
            $d['latest_exam_record'] = null;
            $d['borrowed_books'] = collect();
            $d['upcoming_exams'] = collect();
            return view('pages.student.dashboard', $d);
        }
        
        $currentYear = Qs::getSetting('current_session');
        
        // Academic Performance - Latest Exam Result
        $d['latest_exam_record'] = DB::table('exam_records')
            ->join('exams', 'exam_records.exam_id', '=', 'exams.id')
            ->select('exams.name as exam_name', 'exam_records.ave', 'exam_records.pos', 'exam_records.total')
            ->where('exam_records.student_id', $student_id)
            ->where('exam_records.year', $currentYear)
            ->orderBy('exams.id', 'desc')
            ->first();
        
        // Attendance Summary - Current Year
        $d['attendance_stats'] = DB::table('attendance_records')
            ->select(
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(CASE WHEN attendance_type = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN attendance_type = "absent" THEN 1 ELSE 0 END) as absent'),
                DB::raw('SUM(CASE WHEN attendance_type = "late" THEN 1 ELSE 0 END) as late')
            )
            ->where('student_id', $student_id)
            ->whereYear('created_at', date('Y'))
            ->first();
        
        // Calculate attendance percentage
        $total = $d['attendance_stats']->total_days ?? 0;
        $present = $d['attendance_stats']->present ?? 0;
        $d['attendance_percentage'] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        
        // Fee Status
        $d['fee_summary'] = DB::table('payment_records')
            ->select(
                DB::raw('SUM(amt_paid) as total_paid'),
                DB::raw('SUM(balance) as total_outstanding')
            )
            ->where('student_id', $student_id)
            ->where('year', $currentYear)
            ->first();
        
        // Borrowed Books
        $d['borrowed_books'] = DB::table('book_loans')
            ->join('books', 'book_loans.book_id', '=', 'books.id')
            ->select('books.title', 'books.author', 'book_loans.issued_at', 'book_loans.due_date', 'book_loans.returned_at')
            ->where('book_loans.user_id', $student_id)
            ->whereNull('book_loans.returned_at')
            ->get();
        
        // Upcoming Exams (if any)
        $d['upcoming_exams'] = DB::table('exams')
            ->where('year', $currentYear)
            ->where('id', '>', function($query) use ($student_id, $currentYear) {
                $query->select(DB::raw('MAX(exam_id)'))
                    ->from('exam_records')
                    ->where('student_id', $student_id)
                    ->where('year', $currentYear);
            })
            ->limit(3)
            ->get();
        
        return view('pages.student.dashboard', $d);
    }
}
