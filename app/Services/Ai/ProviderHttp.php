<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use Illuminate\Support\Facades\Log;

class ProviderHttp
{
    public static function fail(string $provider, int $status): never
    {
        Log::warning('Échec du fournisseur IA', [
            'provider' => $provider,
            'status' => $status,
        ]);

        $message = match (true) {
            $status === 429 => 'Le fournisseur d\'IA a atteint sa limite de débit. Réessayez dans un instant.',
            in_array($status, [402, 403], true) => 'Le quota du fournisseur d\'IA est épuisé ou l\'accès est refusé.',
            in_array($status, [408, 500, 502, 503, 504, 529], true) => 'Le fournisseur d\'IA n\'a pas répondu correctement. Réessayez dans un instant.',
            default => 'Le fournisseur d\'IA a renvoyé une erreur. Aucun crédit n\'est conservé.',
        };

        throw new AiException($message);
    }

    public static function timedOut(string $provider): never
    {
        Log::warning('Délai du fournisseur IA', ['provider' => $provider]);

        throw new AiException('Le fournisseur d\'IA n\'a pas répondu à temps. Réessayez dans un instant.');
    }
}
