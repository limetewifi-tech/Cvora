<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\JobOffer;
use App\Models\Resume;
use App\Services\Ai\AiCareerService;
use App\Services\Ai\AiProposal;
use App\Services\Ai\AiStatus;
use App\Services\Billing\QuotaService;
use App\Support\AssistantIntent;
use App\Support\ResumeContent;
use App\Support\ResumeReadiness;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function index(Request $request, QuotaService $quota, AiStatus $status)
    {
        $snapshot = $status->snapshot();
        $user = $request->user();
        $credits = $quota->creditsRemaining($user);
        $user->unsetRelation('subscription');
        $subscription = $user->subscription()->with('plan')->first();

        $resumes = $request->user()->resumes()->orderBy('title')->get();
        $selected = (string) $request->query('cv', '');

        return view('assistant.index', [
            'credits' => $credits,
            'creditAllowance' => (int) ($subscription?->plan?->ai_credits ?? 0),
            'creditsResetAt' => $subscription?->credits_reset_at,
            'resumes' => $resumes,
            'selectedResumeId' => $resumes->contains('id', (int) $selected)
                ? (int) $selected
                : $resumes->first()?->id,
            'configured' => app(AiCareerService::class)->configured(),
            'providers' => $snapshot['providers'],
            'driver' => $snapshot['driver'],
            'driverSupported' => $snapshot['driver_supported'],
        ]);
    }

    public function ask(Request $request, AiCareerService $ai, QuotaService $quota)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'resume_id' => ['required', 'integer'],
            'offer' => ['nullable', 'string', 'max:8000'],
            'position' => ['nullable', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'authorize_resume' => ['accepted'],
        ], [
            'message.required' => 'Écrivez ce que vous voulez obtenir.',
            'authorize_resume.accepted' => 'Autorisez l\'utilisation du CV avant cette demande.',
        ]);

        $resume = $this->resume($request, (int) $data['resume_id']);
        $intent = AssistantIntent::detect($data['message']);
        $extra = ['resume_id' => $resume->id, 'intent' => $intent];

        if ($intent === AssistantIntent::GAPS) {
            return $this->respond($request, $this->gaps($resume, $quota->creditsRemaining($request->user())), $extra);
        }

        if ($intent === AssistantIntent::OFFER && blank($data['offer'] ?? null)) {
            return back()->withInput()->with('error', 'Collez le texte de l\'offre pour adapter le CV. Aucun crédit n\'a été utilisé.');
        }

        if ($intent === AssistantIntent::OFFER && ! $quota->allows($request->user(), 'job_adaptation')) {
            return back()->withInput()->with('error', 'Le score local d\'une offre se lance depuis Répondre à une offre. Les suggestions du fournisseur sont incluses dans l\'offre Pro. Aucun crédit n\'a été utilisé.');
        }

        if ($intent === AssistantIntent::LETTER && (blank($data['position'] ?? null) || blank($data['company'] ?? null))) {
            return back()->withInput()->with('error', 'Indiquez le poste et l\'organisation pour la lettre. Aucun crédit n\'a été utilisé.');
        }

        if (in_array($intent, [AssistantIntent::SUMMARY, AssistantIntent::IMPROVE], true) && blank($resume->summary)) {
            return back()->withInput()->with('error', 'Le résumé est vide. Écrivez quelques faits dans le CV, puis relancez. Aucun crédit n\'a été utilisé.');
        }

        if ($intent === AssistantIntent::PROOFREAD && $this->prose($resume) === []) {
            return back()->withInput()->with('error', 'Aucun texte n\'est encore saisi. Écrivez un résumé, une expérience ou une formation avant la correction. Aucun crédit n\'a été utilisé.');
        }

        $proposal = match ($intent) {
            AssistantIntent::LETTER => $ai->coverLetter($request->user(), [
                'position' => $data['position'] ?? '',
                'company' => $data['company'] ?? '',
                'offer' => $data['offer'] ?? '',
                'summary' => trim((string) $resume->summary),
                'skills' => $resume->content['skills'] ?? [],
                'tone' => 'professionnel',
                'length' => 'standard',
            ]),
            AssistantIntent::OFFER => $ai->analyzeJob($request->user(), [
                'offer' => $data['offer'] ?? '',
                'summary' => (string) $resume->summary,
                'skills' => $resume->content['skills'] ?? [],
                'experiences' => $resume->content['experiences'] ?? [],
            ]),
            AssistantIntent::PROOFREAD => $ai->proofread($request->user(), [
                'mode' => 'spelling',
                'items' => $this->prose($resume),
            ]),
            default => $ai->improveSummary($request->user(), (string) $resume->summary, 'professionnel', $data['message']),
        };

        return $this->respond($request, $proposal, $extra);
    }

    public function summary(Request $request, AiCareerService $ai)
    {
        $data = $request->validate([
            'text' => ['nullable', 'string', 'max:4000'],
            'tone' => ['nullable', 'in:professionnel,classique,direct,debutant'],
        ]);

        return $this->respond($request, $ai->improveSummary($request->user(), (string) ($data['text'] ?? ''), $data['tone'] ?? 'professionnel'));
    }

    public function experiences(Request $request, AiCareerService $ai)
    {
        $data = $this->authorizedResume($request);
        $resume = $this->resume($request, (int) $data['resume_id']);

        return $this->respond($request, $ai->improveExperiences($request->user(), $resume->content['experiences'] ?? []));
    }

    public function skills(Request $request, AiCareerService $ai)
    {
        $data = $this->authorizedResume($request);
        $resume = $this->resume($request, (int) $data['resume_id']);

        return $this->respond($request, $ai->reformulateSkills($request->user(), $resume->content['skills'] ?? []));
    }

    public function grammar(Request $request, AiCareerService $ai)
    {
        $data = $request->validate(['text' => ['nullable', 'string', 'max:4000']]);

        return $this->respond($request, $ai->correct($request->user(), (string) ($data['text'] ?? '')));
    }

    public function tone(Request $request, AiCareerService $ai)
    {
        $data = $request->validate([
            'text' => ['nullable', 'string', 'max:4000'],
            'tone' => ['required', 'in:professionnel,classique,direct,debutant'],
        ]);

        return $this->respond($request, $ai->adaptTone($request->user(), (string) ($data['text'] ?? ''), $data['tone']));
    }

    public function letter(Request $request, AiCareerService $ai)
    {
        $data = $request->validate([
            'position' => ['nullable', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'offer' => ['nullable', 'string', 'max:8000'],
            'resume_id' => ['nullable', 'integer'],
            'tone' => ['nullable', 'in:professionnel,classique,direct,debutant'],
            'length' => ['nullable', 'in:courte,standard,detaillee'],
        ]);

        $summary = '';
        $skills = [];

        if (! empty($data['resume_id'])) {
            $request->validate([
                'authorize_resume' => ['accepted'],
            ], [
                'authorize_resume.accepted' => 'Autorisez l\'utilisation du CV pour rédiger la lettre.',
            ]);
            $resume = $this->resume($request, (int) $data['resume_id']);
            $summary = trim((string) $resume->summary."\n".collect($resume->content['experiences'] ?? [])->pluck('description')->implode("\n"));
            $skills = $resume->content['skills'] ?? [];
        }

        return $this->respond($request, $ai->coverLetter($request->user(), [
            'position' => $data['position'] ?? '',
            'company' => $data['company'] ?? '',
            'offer' => $data['offer'] ?? '',
            'summary' => $summary,
            'skills' => $skills,
            'tone' => $data['tone'] ?? 'professionnel',
            'length' => $data['length'] ?? 'standard',
        ]));
    }

    public function analyze(Request $request, AiCareerService $ai)
    {
        $data = $request->validate([
            'job_offer_id' => ['required', 'integer'],
            'resume_id' => ['required', 'integer'],
            'authorize_resume' => ['accepted'],
        ], [
            'authorize_resume.accepted' => 'Autorisez l\'utilisation du CV pour analyser l\'offre.',
        ]);
        $offer = JobOffer::query()->where('user_id', $request->user()->id)->findOrFail($data['job_offer_id']);
        $resume = $this->resume($request, (int) $data['resume_id']);
        $proposal = $ai->analyzeJob($request->user(), [
            'offer' => $offer->description,
            'summary' => (string) $resume->summary,
            'skills' => $resume->content['skills'] ?? [],
            'experiences' => $resume->content['experiences'] ?? [],
        ]);

        if ($proposal->successful) {
            $offer->forceFill(['analysis' => [
                'required_skills' => $proposal->data['required_skills'] ?? [],
                'matched_skills' => $proposal->data['matched_skills'] ?? [],
                'missing_skills' => $proposal->data['missing_skills'] ?? [],
                'suggestions' => $proposal->data['suggestions'] ?? [],
                'summary_proposal' => $proposal->data['summary_proposal'] ?? '',
                'missing_information' => $proposal->missing,
                'warnings' => $proposal->warnings,
                'resume_id' => $resume->id,
            ]])->save();
        }

        return $this->respond($request, $proposal);
    }

    public function refresh(Request $request, AiCareerService $ai)
    {
        $data = $this->authorizedResume($request);
        $resume = $this->resume($request, (int) $data['resume_id']);

        return $this->respond($request, $ai->refreshResume($request->user(), [
            'summary' => (string) $resume->summary,
            'experiences' => $resume->content['experiences'] ?? [],
            'section_order' => $resume->section_order ?? [],
        ]));
    }

    private function authorizedResume(Request $request): array
    {
        return $request->validate([
            'resume_id' => ['required', 'integer'],
            'authorize_resume' => ['accepted'],
        ], [
            'authorize_resume.accepted' => 'Autorisez l\'utilisation du CV avant cette action.',
        ]);
    }

    private function resume(Request $request, int $id): Resume
    {
        return Resume::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    private function respond(Request $request, AiProposal $proposal, array $extra = [])
    {
        $payload = [
            'successful' => $proposal->successful,
            'feature' => $proposal->feature,
            'data' => $proposal->data,
            'missing' => $proposal->missing,
            'warnings' => $proposal->warnings,
            'error' => $proposal->error,
            'credits_remaining' => $proposal->creditsRemaining,
            'resume_id' => $extra['resume_id'] ?? null,
            'intent' => $extra['intent'] ?? null,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, $proposal->successful ? 200 : 422);
        }

        return back()->withInput()->with('proposal', $payload);
    }

    private function gaps(Resume $resume, int $credits): AiProposal
    {
        $content = is_array($resume->content) ? $resume->content : [];
        $checks = ResumeReadiness::checks([
            'personal' => $resume->personal ?? [],
            'summary' => $resume->summary,
            'experiences' => $content['experiences'] ?? [],
            'education' => $content['education'] ?? [],
            'skills' => $content['skills'] ?? [],
        ]);
        $missing = array_values(array_map(
            fn (array $check) => $check['label'],
            array_filter($checks, fn (array $check) => ! $check['done']),
        ));

        return new AiProposal(
            successful: true,
            feature: 'gaps',
            data: [
                'reply' => $missing === []
                    ? 'Les sections principales sont présentes. Je peux reformuler un texte déjà saisi, sans ajouter de fait.'
                    : 'Il reste à compléter : '.implode(', ', $missing).'.',
                'missing_labels' => $missing,
            ],
            creditsRemaining: $credits,
        );
    }

    /**
     * @return array<int, array{target: string, index: int|null, text: string}>
     */
    private function prose(Resume $resume): array
    {
        $content = is_array($resume->content) ? $resume->content : ResumeContent::emptyContent();
        $items = [];
        $summary = trim((string) $resume->summary);

        if ($summary !== '') {
            $items[] = ['target' => 'summary', 'index' => null, 'text' => $summary];
        }

        foreach (['experiences' => 'experience', 'education' => 'education'] as $section => $target) {
            foreach (array_values($content[$section] ?? []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $text = trim((string) ($row['description'] ?? ''));

                if ($text !== '') {
                    $items[] = ['target' => $target, 'index' => $index, 'text' => $text];
                }
            }
        }

        return $items;
    }
}
