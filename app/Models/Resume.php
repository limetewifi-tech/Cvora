<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use App\Services\Documents\ResumePhoto;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'template_id', 'title', 'status', 'source', 'summary', 'personal', 'content', 'section_order', 'theme', 'photo_path', 'lock_version'])]
class Resume extends Model
{
    use OwnedByUser;

    protected static function booted(): void
    {
        static::deleting(function (Resume $resume): void {
            app(ResumePhoto::class)->delete($resume->photo_path);
        });
    }

    protected function casts(): array
    {
        return [
            'personal' => 'array',
            'content' => 'array',
            'section_order' => 'array',
            'theme' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResumeTemplate::class, 'template_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function displayName(): string
    {
        $personal = $this->personal ?? [];
        $name = trim(($personal['first_name'] ?? '').' '.($personal['last_name'] ?? ''));

        return $name !== '' ? $name : 'Prénom Nom';
    }
}
