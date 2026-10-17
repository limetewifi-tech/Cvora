<?php

namespace App\Services\Auth;

interface PhoneAuthenticator
{
    public function available(): bool;

    public function sendCode(string $phone): void;

    public function verify(string $phone, string $code): bool;
}
