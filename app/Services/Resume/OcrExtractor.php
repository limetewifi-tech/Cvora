<?php

namespace App\Services\Resume;

interface OcrExtractor
{
    public function available(): bool;

    public function extract(string $absolutePath): ?string;
}
