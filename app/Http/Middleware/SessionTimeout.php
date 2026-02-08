<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * Session timeout in seconds (30 minutes)
     *
     * @var int
     */
    protected $timeout = 1800;

    /**
     * Handle an incoming request.
     * Auto-logout users after inactivity period
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity');
            
            // Check if session has expired
            if ($lastActivity && (time() - $lastActivity > $this->timeout)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Your session has expired due to inactivity. Please login again.');
            }
            
            // Update last activity timestamp
            $request->session()->put('last_activity', time());
        }
        
        return $next($request);
    }
}
