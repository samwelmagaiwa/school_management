<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Qs;

class Student
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

        // Check Student role
        if (!Qs::userIsStudent()) {
            return redirect()->route('home')
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
