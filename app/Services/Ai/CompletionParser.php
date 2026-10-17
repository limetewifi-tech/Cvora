<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class CompletionParser
{
    /**
     * @return array<string, mixed>
     */
    public static function decode(mixed $content): array
    {
        if (! is_string($content)) {
            throw new AiException('La réponse du fournisseur IA est illisible.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) && preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
        }

        if (! is_array($decoded)) {
            throw new AiException('La réponse du fournisseur IA n\'est pas un JSON exploitable.');
        }

        return $decoded;
    }
}
