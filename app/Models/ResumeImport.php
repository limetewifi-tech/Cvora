<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'resume_id', 'disk', 'path', 'original_name', 'mime', 'size', 'status', 'extracted_text', 'parsed', 'warnings'])]
class ResumeImport extends Model
{
    use OwnedByUser;

    protected function casts(): array
    {
        return [
            'parsed' => 'array',
            'warnings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
