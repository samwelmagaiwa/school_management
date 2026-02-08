<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ValidateSessionIP
{
    /**
     * Handle an incoming request.
     * Validates that user's IP hasn't changed during session (prevents session hijacking)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $sessionIP = $request->session()->get('user_ip');
            $currentIP = $request->ip();
            
            // If IP address has changed, logout user
            if ($sessionIP && $sessionIP !== $currentIP) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Suspicious activity detected. Your session has been terminated for security reasons.');
            }
        }
        
        return $next($request);
    }
}
