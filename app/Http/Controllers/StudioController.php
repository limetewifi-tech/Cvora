<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\MatchAnalysis;
use App\Models\Resume;
use App\Models\User;
use App\Services\Ai\AiCareerService;
use App\Services\Billing\QuotaService;
use App\Services\Matching\MatchScorer;
use App\Services\Resume\ResumeWriter;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    public function index(Request $request)
    {
        return view('studio.index', [
            'resumes' => $request->user()->resumes()->orderBy('title')->get(),
            'analyses' => $request->user()->matchAnalyses()->latest()->limit(20)->get(),
            'canUseAi' => app(QuotaService::class)->allows($request->user(), 'job_adaptation'),
        ]);
    }

    public function store(Request $request, MatchScorer $scorer, AiCareerService $ai, QuotaService $quota)
    {
        $data = $request->validate([
            'offer_title' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'offer_text' => ['required', 'string', 'max:12000'],
            'resume_id' => ['required', 'integer'],
            'use_ai' => ['nullable', 'boolean'],
            'authorize_resume' => ['accepted'],
        ], [
            'authorize_resume.accepted' => 'Autorisez l\'utilisation de ce CV pour lancer la comparaison.',
        ]);

        $resume = $this->resume($request, (int) $data['resume_id']);
        $local = $scorer->score($resume, $data['offer_text']);
        $offer = $request->user()->jobOffers()->create([
            'title' => $data['offer_title'],
            'company' => $data['company'] ?? null,
            'description' => $data['offer_text'],
        ]);

        [$status, $error, $suggestions] = $this->suggestions(
            $request->boolean('use_ai'),
            $quota,
            $ai,
            $request->user(),
            $offer,
            $resume,
        );

        if ($status === 'success') {
            $offer->forceFill(['analysis' => $suggestions])->save();
        }

        $analysis = $request->user()->matchAnalyses()->create([
            'resume_id' => $resume->id,
            'job_offer_id' => $offer->id,
            'offer_title' => $data['offer_title'],
            'company' => $data['company'] ?? null,
            'offer_text' => $data['offer_text'],
            'score' => $local['score'],
            'result' => $local,
            'ai_suggestions' => $suggestions,
            'ai_status' => $status,
            'ai_error' => $error,
        ]);

        $message = $status === 'failed'
            ? 'La comparaison locale est enregistrée. Le fournisseur d\'IA n\'a pas fourni de suggestion.'
            : 'Comparaison enregistrée. Le CV n\'a pas été modifié.';

        return redirect()->route('studio.show', $analysis)->with($status === 'failed' ? 'error' : 'success', $message);
    }

    public function show(Request $request, MatchAnalysis $analysis)
    {
        abort_unless($analysis->user_id === $request->user()->id, 404);
        $analysis->load('resume');

        return view('studio.show', ['analysis' => $analysis]);
    }

    public function apply(Request $request, MatchAnalysis $analysis, ResumeWriter $writer)
    {
        abort_unless($analysis->user_id === $request->user()->id, 404);
        $request->validate([
            'confirm' => ['accepted'],
            'apply_summary' => ['nullable', 'boolean'],
            'experience_indexes' => ['nullable', 'array'],
            'experience_indexes.*' => ['integer', 'min:0'],
        ], [
            'confirm.accepted' => 'Confirmez que vous avez relu les propositions avant de les appliquer.',
        ]);

        $suggestions = $analysis->ai_suggestions ?? [];
        $resume = Resume::query()->where('user_id', $request->user()->id)->findOrFail($analysis->resume_id);
        $applied = 0;

        if ($request->boolean('apply_summary') && filled($suggestions['summary_proposal'] ?? null)) {
            $writer->applyConfirmedValue($resume, 'summary', ['value' => $suggestions['summary_proposal']]);
            $resume->refresh();
            $applied++;
        }

        foreach ((array) $request->input('experience_indexes', []) as $index) {
            $proposal = collect($suggestions['experiences'] ?? [])->firstWhere('index', (int) $index);

            if (! is_array($proposal) || ! filled($proposal['description'] ?? null)) {
                continue;
            }

            $writer->applyConfirmedValue($resume, 'experience', [
                'index' => (int) $index,
                'value' => $proposal['description'],
            ]);
            $resume->refresh();
            $applied++;
        }

        if ($applied === 0) {
            return back()->with('error', 'Aucune proposition confirmée n\'a été appliquée. Le CV reste inchangé.');
        }

        return redirect()->route('cvs.edit', $resume)->with('success', 'Les propositions cochées ont été appliquées. Relisez le CV avant de l\'exporter.');
    }

    private function resume(Request $request, int $id): Resume
    {
        return Resume::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    /**
     * @return array{0: string, 1: string|null, 2: array<string, mixed>|null}
     */
    private function suggestions(bool $requested, QuotaService $quota, AiCareerService $ai, User $user, JobOffer $offer, Resume $resume): array
    {
        if (! $requested) {
            return ['skipped', null, null];
        }

        if (! $quota->allows($user, 'job_adaptation')) {
            return ['skipped', 'Les suggestions IA sont incluses dans l\'offre Pro. Le score local a été calculé sans fournisseur.', null];
        }

        $proposal = $ai->analyzeJob($user, [
            'offer' => $offer->description,
            'summary' => (string) $resume->summary,
            'skills' => $resume->content['skills'] ?? [],
            'experiences' => $resume->content['experiences'] ?? [],
        ]);

        if (! $proposal->successful) {
            return ['failed', $proposal->error ?: 'Le fournisseur d\'IA n\'a pas répondu. Aucune suggestion n\'a été fabriquée.', null];
        }

        return ['success', null, $this->limitToResume($proposal->data, $resume)];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function limitToResume(array $data, Resume $resume): array
    {
        $known = [];

        foreach ($resume->content['skills'] ?? [] as $skill) {
            $name = trim((string) ($skill['name'] ?? ''));

            if ($name !== '') {
                $known[mb_strtolower($name)] = $name;
            }
        }

        $matched = [];

        foreach ((array) ($data['matched_skills'] ?? []) as $skill) {
            $key = mb_strtolower(trim((string) $skill));

            if (isset($known[$key])) {
                $matched[] = $known[$key];
            }
        }

        $experiences = [];

        foreach ((array) ($data['experiences'] ?? []) as $item) {
            if (! is_array($item) || ! isset($resume->content['experiences'][(int) ($item['index'] ?? -1)])) {
                continue;
            }

            $description = trim((string) ($item['description'] ?? ''));

            if ($description === '') {
                continue;
            }

            $experiences[] = [
                'index' => (int) $item['index'],
                'description' => $description,
            ];
        }

        return [
            'matched_skills' => array_values(array_unique($matched)),
            'missing_skills' => array_values(array_filter(array_map(fn ($skill) => trim((string) $skill), (array) ($data['missing_skills'] ?? [])))),
            'suggestions' => array_values(array_filter(array_map(fn ($line) => trim((string) $line), (array) ($data['suggestions'] ?? [])))),
            'summary_proposal' => trim((string) ($data['summary_proposal'] ?? '')),
            'experiences' => $experiences,
        ];
    }
}
