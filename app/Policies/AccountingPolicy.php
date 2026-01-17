<?php

namespace App\Policies;

use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\PaymentLedger;
use App\User;
use App\Services\Accounting\AccountingPermissionService;

class AccountingPolicy
{
    public function __construct(protected AccountingPermissionService $permissions)
    {
    }

    public function createInvoice(User $user): bool
    {
        return $this->permissions->userCan('invoice.create');
    }

    public function recordPayment(User $user, ?PaymentLedger $payment = null): bool
    {
        return $this->permissions->userCan('payments.record', $payment);
    }

    public function reversePayment(User $user, PaymentLedger $payment): bool
    {
        return $this->permissions->userCan('payments.reverse', $payment);
    }

    public function approveWaiver(User $user, Invoice $invoice): bool
    {
        return $this->permissions->userCan('payments.waive', $invoice);
    }

    public function manageLocks(User $user, AcademicPeriod $period): bool
    {
        return $this->permissions->userCan('locks.manage') && ! $period->is_locked;
    }

    public function unlockPeriod(User $user, AcademicPeriod $period): bool
    {
        return $this->permissions->userCan('periods.unlock');
    }
}
