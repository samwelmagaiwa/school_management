<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AccountingSecurityLogger
{
    public static function log(string $event, ?object $model = null, array $context = []): void
    {
        $payload = [
            'event' => $event,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model->id ?? null,
            'old_values' => $context['old'] ?? null,
            'new_values' => $context['new'] ?? null,
            'description' => $context['description'] ?? null,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
        ];

        AccountingAuditLog::create($payload);
    }
}
