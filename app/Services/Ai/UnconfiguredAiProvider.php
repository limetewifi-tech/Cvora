<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class UnconfiguredAiProvider implements AiProvider
{
    public function configured(): bool
    {
        return false;
    }

    public function complete(string $system, string $userPayload): array
    {
        throw new AiException('Le fournisseur d\'IA n\'est pas configuré. Ajoutez AI_API_KEY dans l\'environnement.');
    }
}
