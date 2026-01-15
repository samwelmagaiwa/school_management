<?php

use Illuminate\Http\Request;

/*
||--------------------------------------------------------------------------
|| API Routes
||--------------------------------------------------------------------------
||
|| Here is where you can register API routes for your application. These
|| routes are loaded by the RouteServiceProvider within a group which
|| is assigned the "api" middleware group. Enjoy building your API!
||
*/

// Sanctum-authenticated endpoint to fetch the authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Explicit API auth endpoints using Sanctum (JSON-based, no redirects).
// These are separate from the web login routes provided by Auth::routes().
Route::post('auth/login', 'Api\\AuthController@login')->name('api.auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', 'Api\\AuthController@me')->name('api.auth.me');
    Route::post('auth/logout', 'Api\\AuthController@logout')->name('api.auth.logout');
});

// -------------------------------------------------------------------------
// API-only routes live here (JSON/Sanctum). Web routes are defined
// in routes/web.php using the standard Laravel pattern.
// -------------------------------------------------------------------------
