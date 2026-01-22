<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\Department;
use App\Models\HumanResource\Designation;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teamSA');
        $this->middleware('can:dept.manage');
    }
    public function index()
    {
        $departments = Department::all();
        $designations = Designation::all();
        return view('pages.human_resource.departments.index', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:staff_departments,name',
            'slug' => 'required|unique:staff_departments,slug',
        ]);

        Department::create($validated);
        return back()->with('flash_success', 'Department Created');
    }

    public function storeDesignation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|unique:staff_designations,title',
        ]);

        Designation::create($validated);
        return back()->with('flash_success', 'Designation Created');
    }
}
