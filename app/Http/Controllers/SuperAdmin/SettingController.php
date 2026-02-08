<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;

class SettingController extends Controller
{
    protected $setting, $my_class;

    public function __construct(SettingRepo $setting, MyClassRepo $my_class)
    {
        $this->setting = $setting;
        $this->my_class = $my_class;
    }

    public function index()
    {
         $s = $this->setting->all();
         $d['class_types'] = $this->my_class->getTypes();
         $d['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });
        
        // Add current school information for SuperAdmin
        $d['school'] = auth()->user()->school;
        
        return view('pages.super_admin.settings', $d);
    }

    public function update(SettingUpdate $req)
    {
        // Handle school code update separately
        if ($req->has('school_code')) {
            $school = auth()->user()->school;
            $newSchoolCode = strtoupper(trim($req->school_code));
            
            // Validate school code uniqueness (excluding current school)
            $existingSchool = \App\Models\School::where('school_code', $newSchoolCode)
                ->where('id', '!=', $school->id)
                ->first();
            
            if ($existingSchool) {
                return back()->with('flash_danger', 'School code "' . $newSchoolCode . '" is already in use by another school.');
            }
            
            // Validate format (letters, numbers, underscores only)
            if (!preg_match('/^[A-Z0-9_]+$/', $newSchoolCode)) {
                return back()->with('flash_danger', 'School code must contain only uppercase letters, numbers, and underscores.');
            }
            
            // Update school code
            $school->school_code = $newSchoolCode;
            $school->save();
        }
        
        // Handle system settings updates
        $sets = $req->except('_token', '_method', 'logo', 'school_code');
        $sets['lock_exam'] = $sets['lock_exam'] == 1 ? 1 : 0;
        $keys = array_keys($sets);
        $values = array_values($sets);
        for($i=0; $i<count($sets); $i++){
            $this->setting->update($keys[$i], $values[$i]);
        }

        if($req->hasFile('logo')) {
            $logo = $req->file('logo');
            $f = Qs::getFileMetaData($logo);
            $f['name'] = 'logo.' . $f['ext'];
            $f['path'] = $logo->storeAs(Qs::getPublicUploadPath(), $f['name']);
            $logo_path = asset('storage/' . $f['path']);
            $this->setting->update('logo', $logo_path);
        }

        return back()->with('flash_success', __('msg.update_ok'));

    }
}
