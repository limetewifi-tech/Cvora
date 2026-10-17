<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    public function view(User $user, Resume $resume): bool
    {
        return $this->owns($user, $resume->user_id);
    }

    public function update(User $user, Resume $resume): bool
    {
        return $this->owns($user, $resume->user_id);
    }

    public function delete(User $user, Resume $resume): bool
    {
        return $this->owns($user, $resume->user_id);
    }

    private function owns(User $user, int $ownerId): bool
    {
        return $user->is_active && $user->id === $ownerId;
    }
}
