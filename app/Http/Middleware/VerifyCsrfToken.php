<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Allow logout without requiring a CSRF token to avoid 419 errors
        // if the token is missing or stale.
        'logout',
        // You can add more URIs here if needed, e.g. webhook endpoints.
        // 'webhook/*',
    ];
}
