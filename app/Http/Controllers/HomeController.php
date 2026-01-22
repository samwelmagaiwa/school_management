<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $user;
    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }


    public function index()
    {
        $user_type = Auth::user()->user_type;

        if($user_type == 'hr'){
            return redirect()->route('hr.reports.summary');
        }
        if($user_type == 'accountant'){
            return redirect()->route('accounting.reports.summary');
        }
        if($user_type == 'teacher'){
            return redirect()->route('teacher.dashboard');
        }
        if($user_type == 'student'){
            return redirect()->route('student.dashboard');
        }
        if($user_type == 'parent'){
            return redirect()->route('parent.dashboard');
        }
        if($user_type == 'librarian'){
            return redirect()->route('librarian.dashboard');
        }
        if($user_type == 'hostel_officer'){
            return redirect()->route('hostel.dashboard');
        }
        if($user_type == 'storekeeper'){
            return redirect()->route('inventory.dashboard');
        }
        if($user_type == 'transport_officer'){
            return redirect()->route('transport.dashboard');
        }
        if($user_type == 'auditor'){
            return redirect()->route('audit.dashboard');
        }
        if($user_type == 'academic'){
            return redirect()->route('academic.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard()
    {
        $user_type = Auth::user()->user_type;

        if($user_type == 'hr'){
             return redirect()->route('hr.reports.summary');
        }
        if($user_type == 'accountant'){
            return redirect()->route('accounting.reports.summary');
        }
        if($user_type == 'teacher'){
            return redirect()->route('teacher.dashboard');
        }
        if($user_type == 'student'){
            return redirect()->route('student.dashboard');
        }
        if($user_type == 'parent'){
            return redirect()->route('parent.dashboard');
        }
        if($user_type == 'librarian'){
            return redirect()->route('librarian.dashboard');
        }
        if($user_type == 'hostel_officer'){
            return redirect()->route('hostel.dashboard');
        }
        if($user_type == 'storekeeper'){
            return redirect()->route('inventory.dashboard');
        }
        if($user_type == 'transport_officer'){
            return redirect()->route('transport.dashboard');
        }
        if($user_type == 'auditor'){
            return redirect()->route('audit.dashboard');
        }
        if($user_type == 'academic'){
            return redirect()->route('academic.dashboard');
        }

        $d=[];
        if(Qs::userIsTeamSAT() || Qs::userIsTeamSA()){
            $d['users'] = $this->user->getAll();
        }

        return view('pages.support_team.dashboard', $d);
    }
}
