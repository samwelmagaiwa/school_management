<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Qs;

class Teacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if authenticated AND session is valid
        if (!Auth::check() || !$request->session()->has('_token')) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // Check Teacher role
        if (!Qs::userIsTeacher()) {
            return redirect()->route('home')
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
