<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\Department;
use App\Models\HumanResource\Designation;
use App\Models\StaffRecord;
use App\User;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{

    public function index()
    {
        $staff = User::where('user_type', '!=', 'student')
                    ->where('user_type', '!=', 'parent')
                    ->with(['staff_record.department', 'staff_record.designation'])
                    ->get();
        return view('pages.human_resource.staff.index', compact('staff'));
    }

    public function create()
    {
        $departments = Department::all();
        $designations = Designation::all();
        return view('pages.human_resource.staff.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $this->validateStaffRequest($request);

        // Create User
        $user = new User;
        $user->code = strtoupper(Str::random(10)); // Consider moving this to a robust generator if collision is a concern
        $user->password = Hash::make('password'); // Default password
        $user->user_type = 'teacher'; // Defaulting to teacher, assuming staff roles are managed via Designation or secondary roles
        $this->saveUserAttributes($user, $request);

        // Create/Update Staff Record
        $this->saveStaffRecord($user, $request);

        return redirect()->route('hr.staff.index')->with('flash_success', 'Staff Member Created Successfully');
    }

    public function edit($id)
    {
        $staff = User::findOrFail($id);
        $departments = Department::all();
        $designations = Designation::all();
        return view('pages.human_resource.staff.edit', compact('staff', 'departments', 'designations'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $this->validateStaffRequest($request, $user->id);

        $this->saveUserAttributes($user, $request);
        $this->saveStaffRecord($user, $request);

        return redirect()->route('hr.staff.index')->with('flash_success', 'Staff Record Updated');
    }

    protected function validateStaffRequest(Request $request, $userId = null)
    {
        $emailRule = 'required|string|email|max:255|unique:users';
        if ($userId) {
            $emailRule .= ',email,' . $userId;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'required|string',
            'address' => 'nullable|string',
            'department_id' => 'required|exists:staff_departments,id',
            'designation_id' => 'required|exists:staff_designations,id',
            'employment_type' => 'required|string',
            'basic_salary' => 'nullable|numeric',
            'date_of_hire' => 'required|date',
            'photo' => 'nullable|image|max:2048',
        ]);
    }

    protected function saveUserAttributes(User $user, Request $request)
    {
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->dob = $request->dob;
        $user->gender = $request->gender;
        $user->address = $request->address;
        // user_type and code are set on creation only or managed separately

        if($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('staff').$user->code, $f['name']);
            $user->photo = asset('storage/' . $f['path']);
        }

        $user->save();
    }

    protected function saveStaffRecord(User $user, Request $request)
    {
        // Use updateOrCreate or similar logic? 
        // Since it is a hasOne, we can access via relationship.
        $staff_record = $user->staff_record ?? new StaffRecord();
        
        // If new, ensure keys are set
        if(!$staff_record->exists) {
            $staff_record->user_id = $user->id;
            $staff_record->code = $user->code;
            $staff_record->status = 'active'; // Default status
        }

        // Common updates
        $staff_record->emp_date = $request->date_of_hire; 
        $staff_record->date_of_hire = $request->date_of_hire;
        $staff_record->department_id = $request->department_id;
        $staff_record->designation_id = $request->designation_id;
        $staff_record->employment_type = $request->employment_type;
        $staff_record->basic_salary = $request->basic_salary;
        
        $staff_record->save();
    }
}
