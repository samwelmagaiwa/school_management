<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\MarkRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MarkExcelController extends Controller
{
    protected $exam, $my_class, $mark, $year;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class, MarkRepo $mark)
    {
        $this->middleware('teamSAT');
        $this->exam = $exam;
        $this->my_class = $my_class;
        $this->mark = $mark;
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Export marks template for a specific subject/class
     */
    public function export($exam_id, $class_id, $section_id, $subject_id)
    {
        $exam = $this->exam->find($exam_id);
        $my_class = $this->my_class->find($class_id);
        $section = $this->my_class->findSection($section_id);
        $subject = $this->my_class->findSubject($subject_id);

        $wh = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $this->year];
        $marks = $this->exam->getMark($wh)->sortBy('user.name');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['MARK ID', 'STUDENT NAME', 'ADM NO', '1ST CA (20)', '2ND CA (20)', 'EXAM (60)', 'ABSENT (Y/N)', 'REASON'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
        }

        // Freeze top row
        $sheet->freezePane('A2');

        // Data rows
        $row = 2;
        foreach ($marks as $mk) {
            $sheet->setCellValue('A' . $row, $mk->id);
            $sheet->setCellValue('B' . $row, $mk->user->name);
            $sheet->setCellValue('C' . $row, $mk->user->student_record->adm_no ?? '');
            $sheet->setCellValue('D' . $row, $mk->t1);
            $sheet->setCellValue('E' . $row, $mk->t2);
            $sheet->setCellValue('F' . $row, $mk->exm);
            $sheet->setCellValue('G' . $row, $mk->is_absent ? 'Y' : 'N');
            $sheet->setCellValue('H' . $row, $mk->exemption_reason);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "Marks_{$my_class->name}_{$subject->name}_{$exam->name}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Import marks from uploaded Excel file
     */
    public function import(Request $req, $exam_id, $class_id, $section_id, $subject_id)
    {
        $req->validate([
            'marks_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $req->file('marks_file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Check if file is empty or missing headers
        if (count($rows) < 2) {
            return back()->with('flash_danger', 'The uploaded file is empty or invalid.');
        }

        $exam = $this->exam->find($exam_id);
        $class_type = $this->my_class->findTypeByClass($class_id);
        $all_st_ids = [];

        // Loop through data rows (skip header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $mark_id = $row[0];
            $t1 = $row[3];
            $t2 = $row[4];
            $exm = $row[5];
            $absent = strtoupper($row[6]) == 'Y';
            $reason = $row[7];

            $mk = MarkRepo::find($mark_id); // Using the model directly via repo if possible or just find
            if (!$mk) continue;

            $all_st_ids[] = $mk->student_id;
            $d = [];

            if ($absent) {
                $d['t1'] = $d['t2'] = $d['tca'] = $d['exm'] = NULL;
                $d['is_absent'] = true;
                $d['exemption_reason'] = $reason ?: 'Absent from exam';
            } else {
                $d['t1'] = $t1 = is_numeric($t1) ? $t1 : NULL;
                $d['t2'] = $t2 = is_numeric($t2) ? $t2 : NULL;
                $d['tca'] = $tca = ($t1 ?? 0) + ($t2 ?? 0);
                $d['exm'] = $exm = is_numeric($exm) ? $exm : NULL;
                $d['is_absent'] = false;
                $d['exemption_reason'] = NULL;

                $d['tex' . $exam->term] = $total = $tca + ($exm ?? 0);

                if ($total > 100) {
                    $d['tex' . $exam->term] = $d['t1'] = $d['t2'] = $d['tca'] = $d['exm'] = NULL;
                }

                $grade = $this->mark->getGrade($total, $class_type->id);
                $d['grade_id'] = $grade ? $grade->id : NULL;
            }

            $d['modified_by'] = Auth::id();
            $this->exam->updateMark($mk->id, $d);
        }

        // Sub Position Calculation
        $marks = $this->exam->getMark(['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $this->year]);
        foreach ($marks as $mk) {
            $d2['sub_pos'] = $this->mark->getSubPos($mk->student_id, $exam, $class_id, $subject_id, $this->year);
            $this->exam->updateMark($mk->id, $d2);
        }

        // Exam Record Update
        $p = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $this->year];
        foreach (array_unique($all_st_ids) as $st_id) {
            $p['student_id'] = $st_id;
            $d3['total'] = $this->mark->getExamTotalTerm($exam, $st_id, $class_id, $this->year);
            $d3['ave'] = $this->mark->getExamAvgTerm($exam, $st_id, $class_id, $section_id, $this->year);
            $d3['class_ave'] = $this->mark->getClassAvg($exam, $class_id, $this->year);
            $d3['pos'] = $this->mark->getPos($st_id, $exam, $class_id, $section_id, $this->year);
            $d3['modified_by'] = Auth::id();

            $this->exam->updateRecord($p, $d3);

            // NECTA Compliance: Calculate Points and Division
            $exr = $this->exam->getRecord($p)->first();
            if ($exr) {
                Mk::calculateDivision($exr->id);
            }
        }

        return back()->with('flash_success', 'Marks imported successfully!');
    }
}
