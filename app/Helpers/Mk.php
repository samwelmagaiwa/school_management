<?php

namespace App\Helpers;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use Illuminate\Database\Eloquent\Collection;

class Mk extends Qs
{
    public static function examIsLocked()
    {
        return self::getSetting('lock_exam');
    }

    public static function getRemarks()
    {
        return ['Average', 'Credit', 'Distinction', 'Excellent', 'Fail', 'Fair', 'Good', 'Pass', 'Poor', 'Very Good', 'Very Poor'];
    }

    /** ADD ORDINAL SUFFIX TO POSITION **/
    public static function getSuffix($number)
    {
        if($number < 1){ return NULL;}

        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13))
            return $number . '<sup>th</sup>';
        else
            return $number . '<sup>' . $ends[$number % 10] . '</sup>';
    }

    /*Get Subject Total Per Term*/
    public static function getSubTotalTerm($st_id, $sub_id, $term, $class_id, $year)
    {
        $d = ['student_id' => $st_id, 'subject_id' => $sub_id, 'my_class_id' => $class_id, 'year' => $year];

        $tex = 'tex'.$term;
        $sub_total = Mark::where($d)->select($tex)->get()->where($tex, '>', 0);
        return $sub_total->count() > 0 ? $sub_total->first()->$tex : '-';
    }

    public static function countDistinctions(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'A%')->orWhere('name', 'LIKE', 'B%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countPasses(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'D%')->orWhere('name', 'LIKE', 'E%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countCredits(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'C%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countFailures(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'F%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countStudents($exam_id, $class_id, $section_id, $year)
    {
        $d = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $year];
        return Mark::where($d)->select('student_id')->distinct()->get()->count();
    }

    protected static function markGradeFilter(Collection $marks, $gradeIDS)
    {
        return $marks->filter(function($mks) use ($gradeIDS){
            return in_array($mks->grade_id, $gradeIDS);
        })->count();
    }

    public static function countSubjectsOffered(Collection $mark)
    {
        return $mark->filter(function($mk) {
            return ($mk->tca + $mk->exm) > 0 ;
        })->count();
    }

    /*Get Exam Avg Per Term*/
    public static function getTermAverage($st_id, $term, $year)
    {
        $exam = self::getExamByTerm($term, $year);
        $d = ['exam_id' => $exam->id, 'student_id' => $st_id, 'year' => $year];

        if($term < 3){
            $exr = ExamRecord::where($d);
            $avg = $exr->first()->ave ?: NULL;
            return $avg > 0 ? round($avg, 1) : $avg;
        }

        $mk = Mark::where($d)->whereNotNull('tex3');
        $avg = $mk->select('tex3')->avg('tex3');
        return round($avg, 1);
    }

    public static function getTermTotal($st_id, $term, $year)
    {
        $exam = self::getExamByTerm($term, $year);
        $d = ['exam_id' => $exam->id, 'student_id' => $st_id, 'year' => $year];

        if($term < 3){
            return ExamRecord::where($d)->first()->total ?? NULL;
        }

        $mk = Mark::where($d)->whereNotNull('tex3');
        return $mk->select('tex3')->sum('tex3');
    }

    public static function getExamByTerm($term, $year)
    {
        $d = ['term' => $term, 'year' => $year];
        return Exam::where($d)->first();
    }

    public static function getGradeList($class_type_id)
    {
        $grades = Grade::where(['class_type_id' => $class_type_id])->orderBy('name')->get();

        if($grades->count() < 1){
            $grades = Grade::whereNull('class_type_id')->orderBy('name')->get();
        }
        return $grades;
    }

    /**
     * If Class/Section is Changed in Same Year,
     * Delete Marks/ExamRecord of Previous Class/Section
     *
     * @param int $st_id
     * @param int $class_id
     * @return bool
     * @static
     */
    public static function deleteOldRecord($st_id, $class_id)
    {
        $d = ['student_id' => $st_id, 'year' => self::getCurrentSession()];

        $marks = Mark::where('my_class_id', '<>', $class_id)->where($d);
        if($marks->get()->count() > 0){
            $exr = ExamRecord::where('my_class_id', '<>', $class_id)->where($d);
            $marks->delete();
            $exr->delete();
        }
        return true;
    }

    /**
     * Get points associated with a grade
     */
    public static function getGradePoints($grade_id)
    {
        $grade = Grade::find($grade_id);
        if (!$grade) return 0;
        
        // Use manual point if set, otherwise follow default NECTA (A=1, B=2, C=3, D=4, E=5, F=9)
        if (!is_null($grade->point)) return $grade->point;

        $name = strtoupper($grade->name);
        switch ($name) {
            case 'A': return 1;
            case 'B': return 2;
            case 'C': return 3;
            case 'D': return 4;
            case 'E': return 5;
            case 'S': return 6;
            case 'F': return 9;
            default: return 0;
        }
    }

    /**
     * Calculate Points and Division for a student's exam record
     */
    public static function calculateDivision($exr_id)
    {
        $exr = ExamRecord::find($exr_id);
        if (!$exr) return null;

        $marks = Mark::where(['exam_id' => $exr->exam_id, 'student_id' => $exr->student_id, 'year' => $exr->year])
                     ->where('is_absent', false)
                     ->get();

        $class_type = Qs::findClassType($exr->my_class_id);
        $points = 0;
        $division = '-';

        if ($class_type->id == 5) { // Junior Secondary (O-Level)
            // NECTA O-Level: Sum of points for best 7 subjects
            $mark_points = $marks->map(function($m) {
                return self::getGradePoints($m->grade_id);
            })->filter(fn($p) => $p > 0)->sort()->values();

            $best_7 = $mark_points->take(7);
            $points = $best_7->sum();
            
            if ($best_7->count() >= 7) {
                if ($points >= 7 && $points <= 17) $division = 'I';
                elseif ($points >= 18 && $points <= 24) $division = 'II';
                elseif ($points >= 25 && $points <= 31) $division = 'III';
                elseif ($points >= 32 && $points <= 34) $division = 'IV';
                else $division = '0';
            }
        } 
        elseif ($class_type->id == 6) { // Senior Secondary (A-Level)
            // NECTA A-Level: Sum of points for best 3 principal subjects
            // Assuming for now all are principal or we take best 3
            $mark_points = $marks->map(function($m) {
                return self::getGradePoints($m->grade_id);
            })->filter(fn($p) => $p > 0 && $p < 9)->sort()->values();

            $best_3 = $mark_points->take(3);
            $points = $best_3->sum();

            if ($best_3->count() >= 3) {
                if ($points >= 3 && $points <= 9) $division = 'I';
                elseif ($points >= 10 && $points <= 12) $division = 'II';
                elseif ($points >= 13 && $points <= 17) $division = 'III';
                elseif ($points >= 18 && $points <= 19) $division = 'IV';
                else $division = '0';
            }
        }
        elseif ($class_type->id == 4) { // Primary
            // Primary is usually average based, but we can set division based on average if needed
            $avg = $exr->ave;
            if ($avg >= 81) $division = 'A';
            elseif ($avg >= 61) $division = 'B';
            elseif ($avg >= 41) $division = 'C';
            elseif ($avg >= 21) $division = 'D';
            else $division = 'E';
            $points = $exr->total; // For Primary, points could be total marks
        }

        $exr->update(['points' => $points, 'division' => $division]);
        return ['points' => $points, 'division' => $division];
    }
}
