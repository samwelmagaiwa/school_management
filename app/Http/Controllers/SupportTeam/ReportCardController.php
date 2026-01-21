<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\MarkRepo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    protected $exam, $my_class, $student, $mark, $year;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class, StudentRepo $student, MarkRepo $mark)
    {
        $this->middleware('teamSA');
        $this->exam = $exam;
        $this->my_class = $my_class;
        $this->student = $student;
        $this->mark = $mark;
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Bulk generate report cards for a class/section
     */
    public function bulk(Request $req)
    {
        $exam_id = $req->exam_id;
        $class_id = $req->my_class_id;
        $section_id = $req->section_id;

        $exam = $this->exam->find($exam_id);
        $my_class = $this->my_class->find($class_id);
        $section = $this->my_class->findSection($section_id);

        if (!$exam || !$my_class || !$section) {
            return back()->with('flash_danger', 'Invalid Selection');
        }

        $wh = ['my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $this->year];
        $students = $this->student->getRecord($wh)->get()->sortBy('user.name');

        if ($students->count() < 1) {
            return back()->with('flash_danger', 'No students found in the selected class/section');
        }

        $mark_wh = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $this->year];
        $marks = $this->exam->getMark($mark_wh);
        $exam_records = $this->exam->getRecord($mark_wh)->get();

        // Fetch previous term records for annual result aggregation
        $prev_exam_records = null;
        if ($exam->term == 2) {
            $prev_exam = $this->exam->getExam(['term' => 1, 'year' => $this->year])->first();
            if ($prev_exam) {
                $prev_exam_records = $this->exam->getRecord(['exam_id' => $prev_exam->id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $this->year])->get();
            }
        }

        $data = [
            'ex' => $exam,
            'my_class' => $my_class,
            'section' => $section,
            'students' => $students,
            'marks' => $marks,
            'exam_records' => $exam_records,
            'prev_exam_records' => $prev_exam_records,
            'year' => $this->year,
            'class_type' => $this->my_class->findTypeByClass($class_id),
            'subjects' => $this->my_class->findSubjectByClass($class_id),
            'skills' => $this->exam->getSkillByClassType() ?: NULL,
            'tex' => 'tex' . $exam->term,
            's' => \App\Models\Setting::all()->flatMap(function($s){
                return [$s->type => $s->description];
            })
        ];

        $pdf = Pdf::loadView('pages.support_team.marks.report_cards_bulk', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = "Report_Cards_{$my_class->name}_{$section->name}_{$exam->name}.pdf";
        return $pdf->download($filename);
    }
}
