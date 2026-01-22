<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\StaffRecord;
use App\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teamSA');
        $this->middleware('can:payroll.manage');
    }
    public function index()
    {
        $staffs = StaffRecord::with('user')->where('status', 'active')->get();
        return view('pages.human_resource.payroll.index', compact('staffs'));
    }

    public function export()
    {
        // Simple CSV Export logic
        $staffs = StaffRecord::with('user')->where('status', 'active')->get();
        $filename = "payroll_export_" . date('Y-m-d') . ".csv";
        
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Employee ID', 'Name', 'Department', 'Designation', 'Basic Salary', 'Employment Type');

        $callback = function() use($staffs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($staffs as $staff) {
                $row['Employee ID']  = $staff->code;
                $row['Name']    = $staff->user->name;
                $row['Department'] = $staff->department->name ?? '-';
                $row['Designation'] = $staff->designation->name ?? '-';
                $row['Basic Salary'] = $staff->basic_salary;
                $row['Employment Type'] = $staff->employment_type;

                fputcsv($file, array($row['Employee ID'], $row['Name'], $row['Department'], $row['Designation'], $row['Basic Salary'], $row['Employment Type']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
