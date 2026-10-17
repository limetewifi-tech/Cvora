<?php

namespace App\Models;

use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'resume_id', 'job_offer_id', 'offer_title', 'company', 'offer_text', 'score', 'result', 'ai_suggestions', 'ai_status', 'ai_error'])]
class MatchAnalysis extends Model
{
    use OwnedByUser;

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'ai_suggestions' => 'array',
            'score' => 'integer',
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

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
