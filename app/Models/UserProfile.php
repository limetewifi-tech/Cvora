<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'first_name', 'last_name', 'headline', 'phone', 'city', 'country', 'address', 'linkedin', 'website', 'summary', 'preferences'])]
class UserProfile extends Model
{
    protected function casts(): array
    {
        return [
            'preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preference(string $key, mixed $default = null): mixed
    {
        return $this->preferences[$key] ?? $default;
    }

    public function completion(): array
    {
        $checks = [
            'Prénom et nom' => filled($this->first_name) && filled($this->last_name),
            'Titre professionnel' => filled($this->headline),
            'Ville' => filled($this->city),
            'Téléphone' => filled($this->phone),
            'Résumé' => filled($this->summary),
        ];

        $done = count(array_filter($checks));

        return [
            'checks' => $checks,
            'percent' => (int) round($done / max(count($checks), 1) * 100),
        ];
    }
}
