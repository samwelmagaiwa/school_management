<?php

namespace App\Http\Middleware\Custom;

use Closure;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;

class TeamInventory
{
    public function handle($request, Closure $next)
    {
        return (Auth::check() && Qs::userIsTeamInventory()) ? $next($request) : redirect()->back()->with('flash_danger', 'Access Denied');
    }
}
