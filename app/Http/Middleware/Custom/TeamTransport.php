<?php

namespace App\Http\Middleware\Custom;

use Closure;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;

class TeamTransport
{
    public function handle($request, Closure $next)
    {
        return (Auth::check() && Qs::userIsTeamTransport()) ? $next($request) : redirect()->back()->with('flash_danger', 'Access Denied');
    }
}
