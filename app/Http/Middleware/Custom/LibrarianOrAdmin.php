<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Support\Facades\Auth;

class LibrarianOrAdmin
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->user_type, ['librarian', 'admin', 'super_admin'])) {
            abort(403, 'You are not allowed to manage the library.');
        }

        return $next($request);
    }
}
