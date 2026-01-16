<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->touchUser();

        return $response;
    }

    protected function touchUser(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        Cache::put($this->cacheKey($user->id), now(), now()->addMinutes(5));

        if (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
            $user->forceFill(['last_seen_at' => now()])->saveQuietly();
        }
    }

    protected function cacheKey(int $userId): string
    {
        return sprintf('user-online-%d', $userId);
    }
}
