<?php

namespace App\Services\Ai;

interface AiProvider
{
    public function name(): string;

    public function configured(): bool;

    /**
     * @return array<string, mixed>
     */
    public function complete(string $system, string $userPayload): array;
}
