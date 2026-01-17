<?php

namespace App\Providers;

use App\Services\Accounting\AccountingPermissionService;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AccountingPermissionService::class, fn () => new AccountingPermissionService());
    }

    public function boot(): void
    {
        //
    }
}
