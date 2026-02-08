<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\SchoolContext;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        // Check if it's a nexoryatech login attempt
        $isNexoryatech = $this->isNexoryatechLogin($request);

        // Only require school_code if NOT nexoryatech
        if (!$isNexoryatech) {
            $rules['school_code'] = 'required|string';
        }

        $request->validate($rules);
    }

    /**
     * Check if the login attempt is for a nexoryatech user
     */
    protected function isNexoryatechLogin(Request $request)
    {
        $identity = $request->input($this->username());
        $field = $this->username();
        
        // We need to bypass the global scope to find the user
        $user = \App\User::withoutGlobalScope('current_school')
            ->where($field, $identity)
            ->first();
            
        return $user && $user->user_type === 'nexoryatech';
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // For nexoryatech, we don't need school_code in credentials
        if ($this->isNexoryatechLogin($request)) {
            $credentials = $this->credentials($request);
            unset($credentials['school_code']); // Ensure school_code isn't passed to Auth::attempt for system admin
            
            $attempt = Auth::attempt($credentials, $request->filled('remember'));
            
            if ($attempt) {
                $request->session()->regenerate();
                // No SchoolContext needed for nexoryatech
                
                // Store security metadata
                $request->session()->put('last_activity', time());
                $request->session()->put('user_ip', $request->ip());
                $request->session()->put('user_agent', $request->userAgent());
            }
            
            return $attempt;
        }

        // Standard login for other users
        $schoolCode = strtoupper($request->school_code);
        $school = School::where('school_code', $schoolCode)->first();

        if (!$school) {
            return false;
        }

        if (!$school->isActive()) {
            return false;
        }

        // Add school_id to credentials for non-system admins
        $credentials = $request->only($this->username(), 'password');
        $credentials['school_id'] = $school->id;

        $attempt = Auth::attempt($credentials, $request->filled('remember'));

        if ($attempt) {
            $request->session()->regenerate();
            SchoolContext::set($school);

            $request->session()->put('last_activity', time());
            $request->session()->put('user_ip', $request->ip());
            $request->session()->put('user_agent', $request->userAgent());
        }

        return $attempt;
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'identity' => [__('These credentials do not match our records for this school.')],
        ]);
    }

    /**
     * Logout the user with complete session cleanup
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Clear school context
        SchoolContext::forget();
        
        // Logout user
        Auth::logout();
        
        // Invalidate the session
        $request->session()->invalidate();
        
        // Regenerate CSRF token to prevent reuse
        $request->session()->regenerateToken();
        
        // Clear all session data
        $request->session()->flush();
        
        // Redirect to login with cache prevention headers
        return redirect()->route('login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
            ->with('status', 'You have been logged out successfully.');
    }

    /**
     * Login with Username or Email
     */
    public function username()
    {
        $identity = request()->identity;
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $identity]);
        return $field;
    }
}
