<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;

class SandboxGateway implements PaymentGateway
{
    public function driver(): string
    {
        return 'sandbox';
    }

    public function configured(): bool
    {
        return true;
    }

    public function start(User $user, Plan $plan, Transaction $transaction): array
    {
        return [
            'mode' => 'sandbox',
            'redirect_url' => route('billing.sandbox', $transaction),
        ];
    }
}
