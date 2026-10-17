<?php

namespace App\Services\Resume;

class NullOcrExtractor implements OcrExtractor
{
    public function available(): bool
    {
        return false;
    }

    public function extract(string $absolutePath): ?string
    {
        return null;
    }
}
