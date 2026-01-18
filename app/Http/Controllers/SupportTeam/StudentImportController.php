<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Models\User;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\Services\Accounting\StudentBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentImportController extends Controller
{
    public function __construct(
        protected MyClassRepo $my_class,
        protected UserRepo $user,
        protected StudentBillingService $billing
    ) {
        $this->middleware('teamSA');
    }

    public function index()
    {
        return view('pages.support_team.students.import');
    }

    public function download_template()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=student_import_template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Name', 'Gender', 'Phone', 'Email', 'Address', 'Class_ID', 'Year_Admitted'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            // Example row
            fputcsv($file, ['John Doe', 'Male', '0712345678', 'john@example.com', 'Dar es Salaam', '1', date('Y')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $req)
    {
        $req->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $req->file('csv_file');
        $handle = fopen($file->getRealPath(), "r");
        
        // Skip header
        fgetcsv($handle);

        $imported = 0;
        $errors = [];
        $row_count = 1;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row_count++;
            if (count($data) < 6) continue;

            try {
                $name = $data[0];
                $gender = $data[1];
                $phone = $data[2];
                $email = $data[3];
                $address = $data[4];
                $class_id = $data[5];
                $year_admitted = $data[6] ?? date('Y');

                // Basic validation
                if (empty($name) || empty($gender) || empty($class_id)) {
                    $errors[] = "Row $row_count: Name, Gender, and Class_ID are required.";
                    continue;
                }

                // Create User
                $user_data = [
                    'name' => ucwords($name),
                    'user_type' => 'student',
                    'gender' => $gender,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'address' => $address ?: 'No Address',
                    'password' => Hash::make('student'),
                    'username' => strtoupper(Qs::getAppCode().'/ST/'.Str::random(5)),
                    'code' => strtoupper(Str::random(10)),
                ];

                $user = $this->user->create($user_data);

                // Create Student Record
                $sr_data = [
                    'user_id' => $user->id,
                    'my_class_id' => $class_id,
                    'year_admitted' => $year_admitted,
                    'session' => Qs::getSetting('current_session'),
                    'adm_no' => $user_data['username'],
                    'section_id' => $this->my_class->getClassSections($class_id)->first()->id ?? null,
                ];

                $sr = StudentRecord::create($sr_data);

                // Generate Billing
                $this->billing->generateForEnrollment($sr);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $row_count: " . $e->getMessage();
            }
        }

        fclose($handle);

        $msg = "$imported students successfully imported.";
        if (count($errors) > 0) {
            return redirect()->route('students.import')->with('flash_success', $msg)->with('import_errors', $errors);
        }

        return redirect()->route('students.import')->with('flash_success', $msg);
    }
}
