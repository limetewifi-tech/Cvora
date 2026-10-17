<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\JobOffer;
use App\Models\Resume;
use App\Services\Ai\AiCareerService;
use App\Services\Ai\AiProposal;
use App\Services\Billing\QuotaService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function index(Request $request, QuotaService $quota)
    {
        return view('assistant.index', [
            'credits' => $quota->creditsRemaining($request->user()),
            'resumes' => $request->user()->resumes()->orderBy('title')->get(),
            'configured' => app(AiCareerService::class)->configured(),
        ]);
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
        $data = $request->validate([
            'resume_id' => ['required', 'integer'],
        ]);
        $resume = $this->resume($request, (int) $data['resume_id']);

        return $this->respond($request, $ai->improveExperiences($request->user(), $resume->content['experiences'] ?? []));
    }

    public function skills(Request $request, AiCareerService $ai)
    {
        $data = $request->validate(['resume_id' => ['required', 'integer']]);
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
        $data = $request->validate(['resume_id' => ['required', 'integer']]);
        $resume = $this->resume($request, (int) $data['resume_id']);

        return $this->respond($request, $ai->refreshResume($request->user(), [
            'summary' => (string) $resume->summary,
            'experiences' => $resume->content['experiences'] ?? [],
            'section_order' => $resume->section_order ?? [],
        ]));
    }

    private function resume(Request $request, int $id): Resume
    {
        return Resume::query()->where('user_id', $request->user()->id)->findOrFail($id);
    }

    private function respond(Request $request, AiProposal $proposal)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'successful' => $proposal->successful,
                'feature' => $proposal->feature,
                'data' => $proposal->data,
                'missing' => $proposal->missing,
                'warnings' => $proposal->warnings,
                'error' => $proposal->error,
                'credits_remaining' => $proposal->creditsRemaining,
            ], $proposal->successful ? 200 : 422);
        }

        return back()->withInput()->with('proposal', [
            'successful' => $proposal->successful,
            'feature' => $proposal->feature,
            'data' => $proposal->data,
            'missing' => $proposal->missing,
            'warnings' => $proposal->warnings,
            'error' => $proposal->error,
            'credits_remaining' => $proposal->creditsRemaining,
        ]);
    }
}
