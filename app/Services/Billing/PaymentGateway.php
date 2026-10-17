<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;

interface PaymentGateway
{
    public function driver(): string;

    public function configured(): bool;

    /**
     * @return array{redirect_url?: string, mode: string}
     */
    public function start(User $user, Plan $plan, Transaction $transaction): array;
}
