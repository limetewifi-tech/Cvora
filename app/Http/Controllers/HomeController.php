<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Plan;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Support\Guides;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'documents' => $this->recentDocuments(),
        ]);
    }

    public function templates()
    {
        return view('public.templates', [
            'templates' => ResumeTemplate::available(),
        ]);
    }

    public function pricing()
    {
        return view('public.pricing', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function letters()
    {
        return view('public.letters');
    }

    public function resources()
    {
        return view('public.resources', ['guides' => Guides::all()]);
    }

    public function resource(string $slug)
    {
        $guide = Guides::find($slug);
        abort_unless($guide, 404);

        return view('public.resource', ['guide' => $guide]);
    }

    /**
     * @return Collection<int, array{kind: string, title: string, href: string, updated_at: mixed}>
     */
    private function recentDocuments(): Collection
    {
        $user = auth()->user();

        if ($user === null) {
            return collect();
        }

        return $user->resumes()->latest('updated_at')->limit(4)->get()
            ->map(fn (Resume $resume) => [
                'kind' => 'CV',
                'title' => $resume->title,
                'href' => route('cvs.edit', $resume),
                'updated_at' => $resume->updated_at,
            ])
            ->concat($user->coverLetters()->latest('updated_at')->limit(4)->get()->map(fn (CoverLetter $letter) => [
                'kind' => 'Lettre',
                'title' => $letter->title,
                'href' => route('letters.edit', $letter),
                'updated_at' => $letter->updated_at,
            ]))
            ->sortByDesc(fn (array $document) => $document['updated_at'])
            ->take(4)
            ->values();
    }
}
