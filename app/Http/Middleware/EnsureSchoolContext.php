<?php

namespace App\Http\Middleware;

use App\Support\SchoolContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class EnsureSchoolContext
{
    public function handle(Request $request, Closure $next)
    {
        // Guest users: no context required
        if (!Auth::check()) {
            SchoolContext::forget();
            return $next($request);
        }

        // Nexoryatech users don't need school context - they operate across all schools
        if (auth()->user()->user_type === 'nexoryatech') {
            SchoolContext::forget();
            return $next($request);
        }

        $user = Auth::user();
        $school = $user->school;

        if (!$school) {
            Auth::logout();
            SchoolContext::forget();

            return $this->logoutResponse($request, __('auth.school_required'));
        }

        if (!$school->isActive()) {
            Auth::logout();
            SchoolContext::forget();

            return $this->logoutResponse($request, __('auth.school_inactive'));
        }

        SchoolContext::set($school);
        View::share('currentSchool', $school);
        $request->attributes->set('currentSchool', $school);

        return $next($request);
    }

    protected function logoutResponse(Request $request, string $message): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'school_code' => $message,
        ]);
    }
}
