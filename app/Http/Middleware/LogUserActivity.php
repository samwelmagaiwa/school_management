<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogUserActivity
{
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        '_token',
        '_method',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->logRequest($request, $response);

        return $response;
    }

    protected function logRequest(Request $request, $response): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $method = strtoupper($request->getMethod());

        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $action = $routeName ? Str::title(str_replace(['.', '_'], ' ', $routeName)) : $method.' '.$request->path();
        $description = $request->attributes->get('activity_description')
            ?? $request->input('activity_description')
            ?? null;

        $payload = $this->filterPayload($request->all());

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'route' => $routeName,
            'method' => $method,
            'url' => $request->fullUrl(),
            'description' => $description,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'changes' => empty($payload) ? null : $payload,
        ]);
    }

    protected function filterPayload(array $payload): array
    {
        return collect($payload)
            ->reject(fn ($value, $key) => $this->shouldSkipKey($key, $value))
            ->map(fn ($value) => $this->normalizeValue($value))
            ->toArray();
    }

    protected function normalizeValue($value)
    {
        if ($value instanceof UploadedFile) {
            return 'uploaded_file';
        }

        if (is_array($value)) {
            return collect($value)
                ->reject(fn ($nested, $nestedKey) => $this->shouldSkipKey($nestedKey, $nested))
                ->map(fn ($nested) => $this->normalizeValue($nested))
                ->toArray();
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return $value;
    }

    protected function shouldSkipKey($key, $value): bool
    {
        if (in_array($key, $this->sensitiveKeys, true)) {
            return true;
        }

        if ($value instanceof UploadedFile) {
            return true;
        }

        return false;
    }
}
