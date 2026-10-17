<?php

namespace App\Services\Auth;

class UnconfiguredPhoneAuthenticator implements PhoneAuthenticator
{
    public function available(): bool
    {
        return false;
    }

    public function sendCode(string $phone): void
    {
        // Branchement prévu pour un fournisseur SMS. Aucun code n'est envoyé.
    }

    public function verify(string $phone, string $code): bool
    {
        return false;
    }
}
