<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Http\Requests\Student\StudentRecordCreate;
use App\Http\Requests\Student\StudentRecordUpdate;
use App\Models\DormBed;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use App\Services\Accounting\StudentBillingService;
use App\Services\HostelService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentRecordController extends Controller
{
    protected $loc, $my_class, $user, $student;

   public function __construct(LocationRepo $loc, MyClassRepo $my_class, UserRepo $user, StudentRepo $student, protected HostelService $hostelService, protected StudentBillingService $billing)
   {
       $this->middleware('teamSA', ['only' => ['edit','update', 'reset_pass', 'create', 'store', 'graduated'] ]);
       $this->middleware('super_admin', ['only' => ['destroy',] ]);

       $this->middleware('can:student.admit')->only(['create', 'store', 'import']);
       $this->middleware('can:student.graduate')->only(['graduated', 'not_graduated']);

        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
        $this->billing = $billing;
   }

    public function reset_pass($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        $data['password'] = Hash::make('student');
        $this->user->update($st_id, $data);
        return back()->with('flash_success', __('msg.p_reset'));
    }

    public function create()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms(true);
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.students.add', $data);
    }

    public function store(StudentRecordCreate $req)
    {
       $data =  $req->only(Qs::getUserRecord());
       $sr =  $req->only(Qs::getStudentData());

        $ct = $this->my_class->findTypeByClass($req->my_class_id)->code;
       /* $ct = ($ct == 'J') ? 'JSS' : $ct;
        $ct = ($ct == 'S') ? 'SS' : $ct;*/

        $data['user_type'] = 'student';
        $data['name'] = ucwords($req->name);
        $data['address'] = $data['address'] ?? 'No Address';
        $data['code'] = strtoupper(Str::random(10));
        $data['password'] = Hash::make('student');
        $data['photo'] = null;  // Will fall back to default via accessor
        $adm_no = $req->adm_no;
        $data['username'] = strtoupper(Qs::getAppCode().'/'.$ct.'/'.$sr['year_admitted'].'/'.($adm_no ?: mt_rand(1000, 99999)));

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$data['code'], $f['name']);
            $data['photo'] = 'storage/' . $f['path'];
        }

        if($req->ward){
            $ward = is_numeric($req->ward) ? \App\Models\Ward::find($req->ward) : $this->loc->findOrCreateWard($req->lga_id, $req->ward);
            $data['ward'] = $ward ? $ward->name : $req->ward;

            if($req->street){
                $village = is_numeric($req->street) ? \App\Models\Village::find($req->street) : $this->loc->findOrCreateVillage($ward->id ?? null, $req->street);
                $data['street'] = $village ? $village->name : $req->street;
            }
        }

        $user = $this->user->create($data); // Create User

        $sr['adm_no'] = $data['username'];
        $sr['user_id'] = $user->id;
        $sr['session'] = Qs::getSetting('current_session');

        $studentRecord = $this->student->createRecord($sr); // Create Student

        if ($req->filled('dorm_bed_id')) {
            $bed = DormBed::find($req->dorm_bed_id);
            if ($bed) {
                $this->hostelService->assignBed($studentRecord, $bed);
            }
        }

        $this->billing->generateForEnrollment($studentRecord);

        return Qs::jsonStoreOk();
    }

    public function listByClass($class_id)
    {
        $data['my_class'] = $mc = $this->my_class->getMC(['id' => $class_id])->first();
        $data['students'] = $this->student->findStudentsByClass($class_id);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return is_null($mc) ? Qs::goWithDanger() : view('pages.support_team.students.list', $data);
    }

    public function graduated()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['students'] = $this->student->allGradStudents();

        return view('pages.support_team.students.graduated', $data);
    }

    public function not_graduated($sr_id)
    {
        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = Qs::getSetting('current_session');
        $this->student->updateRecord($sr_id, $d);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function show($sr_id)
    {
        $raw = $sr_id;
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger('dashboard', 'Error: Invalid Student Hash ('.$raw.')');}

        $sr = \App\Models\StudentRecord::with('user')->find($sr_id);
        if(!$sr){return Qs::goWithDanger('dashboard', 'Error: Student Record Not Found');}

        $data['sr'] = $sr;

        /* Prevent Other Students/Parents from viewing Profile of others */
        if(Auth::user()->id != $data['sr']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['sr']->user_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.students.show', $data);
    }

    public function edit($sr_id)
    {
        $raw = $sr_id;
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger('dashboard', 'Error: Invalid Student Hash ('.$raw.')');}

        $sr = \App\Models\StudentRecord::find($sr_id);
        if(!$sr){return Qs::goWithDanger('dashboard', 'Error: Student Record Not Found');}

        $data['sr'] = $sr;
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms(true);
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();

        // Get Ward and Village objects if they exist
        $user = $sr->user;
        $data['ward'] = $user->ward ? DB::table('wards')->where('name', $user->ward)->where('lga_id', $user->lga_id)->first() : null;
        $data['village'] = ($data['ward'] && $user->street) ? DB::table('villages')->where('name', $user->street)->where('ward_id', $data['ward']->id)->first() : null;

        return view('pages.support_team.students.edit', $data);
    }

    public function update(StudentRecordUpdate $req, $sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $sr = \App\Models\StudentRecord::find($sr_id);
        $d =  $req->only(Qs::getUserRecord());
        $d['name'] = ucwords($req->name);

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$sr->user->code, $f['name']);
            $d['photo'] = 'storage/' . $f['path'];
        }

        if($req->ward){
            $ward = is_numeric($req->ward) ? \App\Models\Ward::find($req->ward) : $this->loc->findOrCreateWard($req->lga_id, $req->ward);
            $d['ward'] = $ward ? $ward->name : $req->ward;

            if($req->street){
                $village = is_numeric($req->street) ? \App\Models\Village::find($req->street) : $this->loc->findOrCreateVillage($ward->id ?? null, $req->street);
                $d['street'] = $village ? $village->name : $req->street;
            }
        }

        $this->user->update($sr->user->id, $d); // Update User Details

        $srec = $req->only(Qs::getStudentData());

        $this->student->updateRecord($sr_id, $srec); // Update St Rec
        $sr->refresh();

        if ($req->boolean('vacate_bed')) {
            $this->hostelService->vacateBed($sr);
            $sr->refresh();
        }

        if ($req->filled('dorm_bed_id')) {
            $bed = DormBed::find($req->dorm_bed_id);
            if ($bed && $sr->dorm_bed_id !== $bed->id) {
                $this->hostelService->assignBed($sr, $bed);
            }
        }

        /*** If Class/Section is Changed in Same Year, Delete Marks/ExamRecord of Previous Class/Section ****/
        Mk::deleteOldRecord($sr->user->id, $srec['my_class_id']);

        return Qs::jsonUpdateOk();
    }

    public function destroy($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        if(!$st_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['user_id' => $st_id])->first();
        $path = Qs::getUploadPath('student').$sr->user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : false;
        $this->user->delete($sr->user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

}
