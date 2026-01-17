<?php

namespace App\Services\Accounting;

use App\Helpers\Qs;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\PaymentLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountingPermissionService
{
    /** @var array<string, string[]> */
    protected array $roleAbilities = [
        'accountant' => [
            'invoice.create',
            'invoice.view',
            'payments.record',
            'payments.view',
            'expenses.view',
            'reports.view',
        ],
        'admin' => [
            'invoice.view',
            'invoice.approve',
            'payments.view',
            'payments.reverse',
            'payments.waive',
            'expenses.view',
            'expenses.approve',
            'reports.view',
            'locks.manage',
        ],
        'super_admin' => [
            'invoice.view',
            'invoice.manage',
            'payments.view',
            'payments.manage',
            'expenses.manage',
            'reports.view',
            'locks.manage',
            'periods.unlock',
            'periods.edit_historical',
        ],
    ];

    public function userCan(string $ability, ?object $subject = null): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $role = $user->user_type;
        $abilities = $this->roleAbilities[$role] ?? [];

        if (! in_array($ability, $abilities) && ! in_array(Str::before($ability, '.'), $abilities)) {
            return false;
        }

        // Locking rules enforcement
        if ($subject instanceof Invoice && $this->invoiceIsLocked($subject)) {
            return false;
        }

        if ($subject instanceof PaymentLedger && $this->paymentIsLocked($subject)) {
            return false;
        }

        return true;
    }

    public function ensure(string $ability, ?object $subject = null): void
    {
        if (! $this->userCan($ability, $subject)) {
            abort(403, 'Unauthorized action for accounting control.');
        }
    }

    public function invoiceIsLocked(Invoice $invoice): bool
    {
        if (! $invoice->academic_period_id) {
            return false;
        }

        $period = AcademicPeriod::find($invoice->academic_period_id);
        return $period?->is_locked ?? false;
    }

    public function paymentIsLocked(PaymentLedger $payment): bool
    {
        if (! $payment->received_at) {
            return false;
        }

        $period = AcademicPeriod::where('start_date', '<=', $payment->received_at)
            ->where('end_date', '>=', $payment->received_at)
            ->first();

        return $period?->is_locked ?? false;
    }

    public function lockPeriod(AcademicPeriod $period, string $reason = null): void
    {
        $period->update(['is_locked' => true]);
        AccountingSecurityLogger::log('period.locked', $period, ['reason' => $reason]);
    }

    public function unlockPeriod(AcademicPeriod $period, string $reason = null): void
    {
        if (! Qs::userIsSuperAdmin()) {
            abort(403, 'Only super admins may unlock financial periods.');
        }

        $period->update(['is_locked' => false]);
        AccountingSecurityLogger::log('period.unlocked', $period, ['reason' => $reason]);
    }

    public function ensureNotLocked(?AcademicPeriod $period): void
    {
        if ($period && $period->is_locked) {
            abort(423, 'The selected period is locked. Create reversals instead.');
        }
    }
}
