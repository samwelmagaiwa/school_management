<?php

namespace App\Http\Controllers\Nexoryatech;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    /**
     * Display listing of all schools
     */
    public function index()
    {
        $schools = School::withCount('users')->latest()->paginate(20);
        return view('pages.nexoryatech.schools.index', compact('schools'));
    }

    /**
     * Show create school form
     */
    public function create()
    {
        return view('pages.nexoryatech.schools.create');
    }

    /**
     * Store new school
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_code' => 'required|string|max:50|unique:schools,school_code|regex:/^[A-Z0-9_]+$/',
            'status' => 'required|in:active,inactive',
            
            // SuperAdmin details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_username' => 'required|string|max:100|unique:users,username',
            'admin_password' => 'nullable|string|min:6',
        ]);

        // Create school
        $school = School::create([
            'name' => $validated['name'],
            'school_code' => strtoupper($validated['school_code']),
            'status' => $validated['status'],
            'settings' => [],
        ]);

        // Create first SuperAdmin for this school
        $password = $validated['admin_password'] ?? Str::random(10);
        
        $admin = User::withoutGlobalScope('current_school')->create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'username' => $validated['admin_username'],
            'password' => Hash::make($password),
            'user_type' => 'super_admin',
            'school_id' => $school->id,
            'code' => strtoupper(Str::random(10)),
        ]);

        return redirect()->route('nexoryatech.schools.index')
            ->with('flash_success', 'School created successfully! SuperAdmin: ' . $validated['admin_email'] . ' / Password: ' . $password);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $school = School::with('users')->findOrFail($id);
        return view('pages.nexoryatech.schools.edit', compact('school'));
    }

    /**
     * Update school
     */
    public function update(Request $request, $id)
    {
        $school = School::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_code' => 'required|string|max:50|unique:schools,school_code,' . $id . '|regex:/^[A-Z0-9_]+$/',
            'status' => 'required|in:active,inactive',
            // Settings
            'motto' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'theme_color' => 'nullable|string|max:20',
        ]);

        // Prepare settings array
        $settings = $school->settings ?? [];
        $settings['motto'] = $validated['motto'] ?? null;
        $settings['phone'] = $validated['phone'] ?? null;
        $settings['email'] = $validated['email'] ?? null;
        $settings['theme_color'] = $validated['theme_color'] ?? null;

        $school->update([
            'name' => $validated['name'],
            'school_code' => strtoupper($validated['school_code']),
            'status' => $validated['status'],
            'settings' => $settings,
        ]);

        return back()->with('flash_success', 'School updated successfully!');
    }

    /**
     * Toggle school status
     */
    public function toggleStatus($id)
    {
        $school = School::findOrFail($id);
        $school->status = $school->status === 'active' ? 'inactive' : 'active';
        $school->save();

        return back()->with('flash_success', 'School status updated to: ' . $school->status);
    }

    /**
     * View school details
     */
    public function show($id)
    {
        $school = School::with(['users' => function($q) {
            $q->latest()->limit(50);
        }])->withCount('users')->findOrFail($id);

        $super_admin = $school->users()->where('user_type', 'super_admin')->oldest()->first();
        
        return view('pages.nexoryatech.schools.show', compact('school', 'super_admin'));
    }
}
