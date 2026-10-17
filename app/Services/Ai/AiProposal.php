<?php

namespace App\Services\Ai;

class AiProposal
{
    public function __construct(
        public bool $successful,
        public string $feature,
        public array $data = [],
        public array $missing = [],
        public array $warnings = [],
        public ?string $error = null,
        public int $creditsRemaining = 0,
    ) {}
}
