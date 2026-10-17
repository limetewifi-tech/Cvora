<?php

namespace App\Services\Account;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountDeleter
{
    public function delete(User $user): void
    {
        if ($user->isAdmin() && User::query()->where('role', 'admin')->where('is_active', true)->count() <= 1) {
            throw new \RuntimeException('Le dernier administrateur actif ne peut pas être supprimé.');
        }

        DB::transaction(function () use ($user) {
            $user->imports()->each(function ($import) {
                if ($import->path) {
                    Storage::disk($import->disk)->delete($import->path);
                }
                $import->delete();
            });

            $user->resumes()->delete();
            $user->coverLetters()->delete();
            $user->jobOffers()->delete();
            $user->applications()->delete();
            $user->profile?->delete();
            $user->notifications()->delete();
            $user->subscription?->forceFill(['status' => SubscriptionStatus::Cancelled])->save();

            $user->forceFill([
                'name' => 'Compte supprimé',
                'email' => 'deleted-'.$user->id.'@invalid.local',
                'phone' => null,
                'phone_verified_at' => null,
                'email_verified_at' => null,
                'password' => Hash::make(Str::random(40)),
                'is_active' => false,
            ])->save();
        });
    }
}
