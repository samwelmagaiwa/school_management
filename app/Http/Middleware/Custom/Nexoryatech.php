<?php

namespace App\Http\Middleware\Custom;

use Closure;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;

class Nexoryatech
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

        // Check Nexoryatech role
        if (!Qs::userIsNexoryatech()) {
            return redirect()->route('home')
                ->with('error', 'Unauthorized access. System Administrator privileges required.');
        }

        return $next($request);
    }
}
