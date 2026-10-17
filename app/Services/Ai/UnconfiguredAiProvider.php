<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class UnconfiguredAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'unconfigured';
    }

    public function configured(): bool
    {
        return false;
    }

    public function complete(string $system, string $userPayload): array
    {
        throw new AiException('Le fournisseur d\'IA n\'est pas configuré. Ajoutez la clé API et le modèle du fournisseur dans l\'environnement.');
    }
}
