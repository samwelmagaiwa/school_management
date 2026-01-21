<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\ExamRecord;
use App\Http\Controllers\Controller;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\MarkRepo;
use Illuminate\Http\Request;

class ExamStatsController extends Controller
{
    protected $exam, $my_class, $mark, $year;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class, MarkRepo $mark)
    {
        $this->middleware('teamSA');
        
        $this->exam = $exam;
        $this->my_class = $my_class;
        $this->mark = $mark;
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Display statistics dashboard
     */
    public function index()
    {
        $d['exams'] = $this->exam->getExam(['year' => $this->year]);
        $d['my_classes'] = $this->my_class->all();
        $d['year'] = $this->year;

        return view('pages.support_team.exam_stats.index', $d);
    }

    /**
     * Show detailed statistics for a specific exam
     */
    public function show($exam_id)
    {
        $exam = $this->exam->find($exam_id);
        
        if (!$exam) {
            return redirect()->route('exam_stats.index')->with('flash_danger', 'Exam not found');
        }

        $d['exam'] = $exam;
        $d['year'] = $exam->year;
        
        // Overall exam statistics
        $d['overall_stats'] = $this->calculateOverallStats($exam);
        
        // Subject-wise statistics
        $d['subject_stats'] = $this->getSubjectStats($exam);
        
        // Class-wise statistics
        $d['class_stats'] = $this->getClassStats($exam);
        
        // Grade distribution
        $d['grade_distribution'] = $this->getGradeDistribution($exam);

        return view('pages.support_team.exam_stats.show', $d);
    }

    /**
     * Show statistics for specific class/section
     */
    public function classStats($exam_id, $class_id, $section_id = null)
    {
        $exam = $this->exam->find($exam_id);
        $my_class = $this->my_class->find($class_id);
        
        $wh = [
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
            'year' => $exam->year
        ];
        
        if ($section_id) {
            $wh['section_id'] = $section_id;
        }

        $marks = Mark::where($wh)->where('is_absent', false)->get();
        $all_marks = Mark::where($wh)->get();
        
        $d['exam'] = $exam;
        $d['my_class'] = $my_class;
        $d['section_id'] = $section_id;
        $d['stats'] = [
            'total_students' => $all_marks->unique('student_id')->count(),
            'present_students' => $marks->unique('student_id')->count(),
            'absent_students' => $all_marks->where('is_absent', true)->unique('student_id')->count(),
            'avg_score' => round($marks->avg(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }), 2),
            'highest_score' => $marks->max(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }),
            'lowest_score' => $marks->min(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }),
            'distinctions' => Mk::countDistinctions($marks),
            'credits' => Mk::countCredits($marks),
            'passes' => Mk::countPasses($marks),
            'failures' => Mk::countFailures($marks),
        ];
        
        $d['subject_performance'] = $this->getSubjectPerformanceForClass($exam_id, $class_id, $section_id);

        return view('pages.support_team.exam_stats.class_stats', $d);
    }

    /**
     * Calculate overall exam statistics
     */
    private function calculateOverallStats($exam)
    {
        $all_marks = Mark::where('exam_id', $exam->id)
                        ->where('year', $exam->year)
                        ->get();
                        
        $marks = $all_marks->where('is_absent', false);

        $total_students = $all_marks->unique('student_id')->count();
        $present_students = $marks->unique('student_id')->count();
        
        return [
            'total_students' => $total_students,
            'present_students' => $present_students,
            'absent_students' => $all_marks->where('is_absent', true)->unique('student_id')->count(),
            'absence_rate' => $total_students > 0 ? round(($total_students - $present_students) / $total_students * 100, 1) : 0,
            'avg_score' => round($marks->avg(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }), 2),
            'highest_score' => $marks->max(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }),
            'lowest_score' => $marks->min(function($m) { 
                return ($m->tca ?? 0) + ($m->exm ?? 0); 
            }),
            'distinctions' => Mk::countDistinctions($marks),
            'credits' => Mk::countCredits($marks),
            'passes' => Mk::countPasses($marks),
            'failures' => Mk::countFailures($marks),
            'pass_rate' => $present_students > 0 ? round((Mk::countDistinctions($marks) + Mk::countCredits($marks) + Mk::countPasses($marks)) / $present_students * 100, 1) : 0,
        ];
    }

    /**
     * Get subject-wise statistics
     */
    private function getSubjectStats($exam)
    {
        $marks = Mark::where('exam_id', $exam->id)
                    ->where('year', $exam->year)
                    ->where('is_absent', false)
                    ->with('subject')
                    ->get();

        $stats = [];
        
        foreach ($marks->groupBy('subject_id') as $subject_id => $subject_marks) {
            $subject = $subject_marks->first()->subject;
            
            $stats[] = [
                'subject' => $subject,
                'total_entries' => $subject_marks->count(),
                'avg_score' => round($subject_marks->avg(function($m) { 
                    return ($m->tca ?? 0) + ($m->exm ?? 0); 
                }), 2),
                'highest' => $subject_marks->max(function($m) { 
                    return ($m->tca ?? 0) + ($m->exm ?? 0); 
                }),
                'lowest' => $subject_marks->min(function($m) { 
                    return ($m->tca ?? 0) + ($m->exm ?? 0); 
                }),
                'pass_count' => $subject_marks->filter(function($m) {
                    return (($m->tca ?? 0) + ($m->exm ?? 0)) >= 50;
                })->count(),
                'fail_count' => $subject_marks->filter(function($m) {
                    return (($m->tca ?? 0) + ($m->exm ?? 0)) < 50;
                })->count(),
            ];
        }

        return collect($stats)->sortByDesc('avg_score')->values();
    }

    /**
     * Get class-wise statistics
     */
    private function getClassStats($exam)
    {
        $exam_records = ExamRecord::where('exam_id', $exam->id)
                                  ->where('year', $exam->year)
                                  ->with('my_class', 'section')
                                  ->get();

        $stats = [];
        
        foreach ($exam_records->groupBy('my_class_id') as $class_id => $class_records) {
            $my_class = $class_records->first()->my_class;
            
            $stats[] = [
                'class' => $my_class,
                'student_count' => $class_records->unique('student_id')->count(),
                'avg_score' => round($class_records->avg('ave'), 2),
                'class_avg' => round($class_records->avg('class_ave'), 2),
                'highest_total' => $class_records->max('total'),
                'lowest_total' => $class_records->min('total'),
            ];
        }

        return collect($stats)->sortByDesc('avg_score')->values();
    }

    /**
     * Get grade distribution for charts
     */
    private function getGradeDistribution($exam)
    {
        $marks = Mark::where('exam_id', $exam->id)
                    ->where('year', $exam->year)
                    ->where('is_absent', false)
                    ->with('grade')
                    ->get();

        $distribution = [];
        
        foreach ($marks->groupBy('grade_id') as $grade_id => $grade_marks) {
            if ($grade_id && $grade_marks->first()->grade) {
                $grade = $grade_marks->first()->grade;
                $distribution[$grade->name] = $grade_marks->count();
            }
        }

        return $distribution;
    }

    /**
     * Get subject performance for a specific class
     */
    private function getSubjectPerformanceForClass($exam_id, $class_id, $section_id = null)
    {
        $wh = [
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
        ];
        
        if ($section_id) {
            $wh['section_id'] = $section_id;
        }

        $marks = Mark::where($wh)
                    ->where('is_absent', false)
                    ->with('subject')
                    ->get();

        $performance = [];
        
        foreach ($marks->groupBy('subject_id') as $subject_id => $subject_marks) {
            $subject = $subject_marks->first()->subject;
            
            $performance[] = [
                'subject' => $subject->name,
                'avg' => round($subject_marks->avg(function($m) { 
                    return ($m->tca ?? 0) + ($m->exm ?? 0); 
                }), 2),
                'pass_rate' => round($subject_marks->filter(function($m) {
                    return (($m->tca ?? 0) + ($m->exm ?? 0)) >= 50;
                })->count() / $subject_marks->count() * 100, 1),
            ];
        }

        return collect($performance);
    }

    /**
     * Export statistics to PDF
     */
    public function exportPDF($exam_id)
    {
        // TODO: Implement PDF export
        return back()->with('flash_info', 'PDF export coming soon');
    }
}
