<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Support\Facades\Auth;

class HostelOfficerOrAdmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->user_type, ['hostel_officer', 'admin', 'super_admin'])) {
            abort(403, 'You are not allowed to manage hostel operations.');
        }

        return $next($request);
    }
}
