<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cover_letter_id', 'user_id', 'version_number', 'kind', 'note', 'snapshot'])]
class CoverLetterVersion extends Model
{
    use OwnedByUser;

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function coverLetter(): BelongsTo
    {
        return $this->belongsTo(CoverLetter::class);
    }
}
