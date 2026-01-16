<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\MyClass;
use App\Models\Subject;
use App\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teamSA'); // admins and super_admins only
    }

    public function index()
    {
        $departments = Department::with(['head', 'classes', 'subjects'])->orderBy('name')->get();
        $teachers    = User::where('user_type', 'teacher')->orderBy('name')->get();

        return view('pages.support_team.departments.index', compact('departments', 'teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150|unique:departments,name',
            'head_id' => 'nullable|integer|exists:users,id',
        ]);

        Department::create($data);

        return redirect()->route('departments.index')->with('flash_success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150|unique:departments,name,' . $department->id,
            'head_id' => 'nullable|integer|exists:users,id',
        ]);

        $department->update($data);

        return redirect()->route('departments.index')->with('flash_success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if (MyClass::where('department_id', $department->id)->exists() || Subject::where('department_id', $department->id)->exists()) {
            return redirect()->route('departments.index')->with('flash_warning', 'Cannot delete department with assigned classes or subjects.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('flash_success', 'Department deleted successfully.');
    }
}
