<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Http\Requests\ResumeFormRequest;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Services\Billing\QuotaService;
use App\Services\Documents\ResumePresenter;
use App\Services\Resume\ResumeWriter;
use App\Support\ResumeContent;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function index(Request $request)
    {
        $resumes = $request->user()->resumes()->with('template')->latest('updated_at')->get();

        return view('resumes.index', [
            'resumes' => $resumes,
            'quota' => app(QuotaService::class),
        ]);
    }

    public function create(Request $request)
    {
        $quota = app(QuotaService::class);
        $user = $request->user();
        $user->loadMissing('profile');

        return view('resumes.create', [
            'templates' => ResumeTemplate::available(),
            'quota' => $quota,
            'plan' => $quota->plan($user),
            'resumeCount' => $user->resumes()->count(),
            'defaultTitle' => $user->profile?->first_name ? 'CV de '.$user->profile->first_name : 'Mon CV',
        ]);
    }

    public function store(Request $request, ResumeWriter $writer)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'template_id' => ['required', 'integer', 'exists:resume_templates,id'],
        ], [
            'title.required' => 'Donnez un titre au document.',
            'template_id.required' => 'Choisissez un modèle.',
            'template_id.exists' => 'Ce modèle n\'est plus disponible.',
        ]);

        $template = ResumeTemplate::query()->findOrFail($data['template_id']);

        try {
            $resume = $writer->createFromProfile($request->user(), $template, $data['title']);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception, route('cvs.create'));
        }

        return redirect()->route('cvs.edit', $resume)->with('success', 'Votre CV a été créé. Les informations du profil ont été reprises lorsqu\'elles existaient.');
    }

    public function edit(Request $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $resume->load('template');

        return view('resumes.edit', [
            'resume' => $resume,
            'templates' => ResumeTemplate::available(),
            'quota' => app(QuotaService::class),
            'form' => $this->formState($resume, $request),
            'versions' => $resume->versions()->latest()->limit(12)->get(),
        ]);
    }

    public function update(ResumeFormRequest $request, Resume $resume, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);

        try {
            $resume = $writer->update($resume, $request->validated());
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Enregistré',
                'lock_version' => $resume->lock_version,
                'updated_at' => $resume->updated_at?->timezone(config('app.timezone'))->format('H:i'),
            ]);
        }

        return redirect()->route('cvs.edit', $resume)->with('success', 'CV enregistré.');
    }

    public function delete(Resume $resume)
    {
        $this->authorize('delete', $resume);

        return view('resumes.delete', compact('resume'));
    }

    public function destroy(Request $request, Resume $resume)
    {
        $this->authorize('delete', $resume);
        $request->validate(['confirm' => ['accepted']]);
        $resume->delete();

        return redirect()->route('cvs.index')->with('success', 'Le CV a été supprimé.');
    }

    public function duplicate(Request $request, Resume $resume, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);
        $resume->loadMissing('user');

        try {
            $copy = $writer->duplicate($resume);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        return redirect()->route('cvs.edit', $copy)->with('success', 'Une copie du CV a été créée.');
    }

    public function preview(Resume $resume, ResumePresenter $presenter)
    {
        $this->authorize('view', $resume);

        return view('resumes.paper', [
            'document' => $presenter->present($resume),
            'title' => $resume->title,
        ]);
    }

    public function applyProposal(Request $request, Resume $resume, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);
        $data = $request->validate([
            'target' => ['required', 'in:summary,experience,skill,section_order'],
            'value' => ['nullable', 'string', 'max:4000'],
            'index' => ['nullable', 'integer', 'min:0'],
            'order' => ['nullable', 'array'],
            'order.*' => ['in:'.implode(',', ResumeContent::sections())],
        ]);

        try {
            $writer->applyConfirmedValue($resume, $data['target'], $data);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        return redirect()->route('cvs.edit', $resume)->with('success', 'La proposition confirmée a été appliquée. La version précédente est conservée.');
    }

    private function formState(Resume $resume, Request $request): array
    {
        $content = $resume->content ?? ResumeContent::emptyContent();
        $state = [
            'title' => old('title', $resume->title),
            'summary' => old('summary', $resume->summary),
            'template_id' => old('template_id', $resume->template_id),
            'personal' => old('personal', $resume->personal ?? ResumeContent::emptyPersonal()),
            'theme' => old('theme', $resume->theme ?? ['primary' => '#2563EB', 'accent' => '#1F2937']),
            'section_order' => old('section_order', $resume->section_order ?? ResumeContent::defaultOrder()),
            'status' => old('status', $resume->status),
        ];

        foreach (ResumeContent::sections() as $section) {
            $state[$section] = old($section, $content[$section] ?? []);
        }

        return $state;
    }
}
