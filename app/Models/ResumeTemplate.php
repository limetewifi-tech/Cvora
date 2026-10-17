<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'description', 'audience', 'is_premium', 'is_active', 'sort_order', 'default_section_order', 'default_theme'])]
class ResumeTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'default_section_order' => 'array',
            'default_theme' => 'array',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class, 'template_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
