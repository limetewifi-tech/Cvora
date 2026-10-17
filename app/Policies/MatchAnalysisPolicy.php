<?php

namespace App\Policies;

use App\Models\MatchAnalysis;
use App\Models\User;

class MatchAnalysisPolicy
{
    public function view(User $user, MatchAnalysis $analysis): bool
    {
        return $user->is_active && $user->id === $analysis->user_id;
    }

    public function update(User $user, MatchAnalysis $analysis): bool
    {
        return $this->view($user, $analysis);
    }

    public function delete(User $user, MatchAnalysis $analysis): bool
    {
        return $this->view($user, $analysis);
    }
}
