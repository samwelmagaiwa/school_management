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
        if(Qs::userIsTeamSA() && Auth::user()->user_type == 'hr'){
            return redirect()->route('hr.reports.summary');
        }
        if(Auth::user()->user_type == 'accountant'){
            return redirect()->route('accounting.reports.summary');
        }
        if(Auth::user()->user_type == 'teacher'){
            return redirect()->route('teacher.dashboard');
        }
        if(Auth::user()->user_type == 'student'){
            return redirect()->route('student.dashboard');
        }
        if(Auth::user()->user_type == 'parent'){
            return redirect()->route('parent.dashboard');
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
        if(Qs::userIsTeamSA() && Auth::user()->user_type == 'hr'){
             return redirect()->route('hr.reports.summary');
        }
        if(Auth::user()->user_type == 'accountant'){
            return redirect()->route('accounting.reports.summary');
        }
        if(Auth::user()->user_type == 'teacher'){
            return redirect()->route('teacher.dashboard');
        }
        if(Auth::user()->user_type == 'student'){
            return redirect()->route('student.dashboard');
        }
        if(Auth::user()->user_type == 'parent'){
            return redirect()->route('parent.dashboard');
        }

        $d=[];
        if(Qs::userIsTeamSAT() || Qs::userIsTeamSA()){
            $d['users'] = $this->user->getAll();
        }

        return view('pages.support_team.dashboard', $d);
    }
}
