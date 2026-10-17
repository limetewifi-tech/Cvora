<?php

namespace App\Http\Controllers;

use App\Services\Billing\QuotaService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, QuotaService $quota)
    {
        $user = $request->user();
        $user->load(['profile', 'subscription.plan']);

        $resumes = $user->resumes()->latest('updated_at')->limit(5)->get();
        $letters = $user->coverLetters()->latest('updated_at')->limit(5)->get();
        $recent = $resumes->map(fn ($resume) => [
            'type' => 'CV',
            'title' => $resume->title,
            'updated_at' => $resume->updated_at,
            'url' => route('cvs.edit', $resume),
        ])->concat($letters->map(fn ($letter) => [
            'type' => 'Lettre',
            'title' => $letter->title,
            'updated_at' => $letter->updated_at,
            'url' => route('letters.edit', $letter),
        ]))->sortByDesc('updated_at')->take(5)->values();

        return view('dashboard.index', [
            'resumeCount' => $user->resumes()->count(),
            'letterCount' => $user->coverLetters()->count(),
            'applicationCount' => $user->applications()->count(),
            'recent' => $recent,
            'subscription' => $user->subscription,
            'credits' => $quota->creditsRemaining($user),
            'completion' => $user->profile?->completion() ?? ['checks' => [], 'percent' => 0],
            'notifications' => $user->unreadNotifications()->limit(5)->get(),
        ]);
    }
}
