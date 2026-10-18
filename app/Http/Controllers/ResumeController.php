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
use App\Support\ResumeReadiness;
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
            'preferredSlug' => $user->profile?->preference('default_template'),
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

        return redirect()->route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => 1])->with('success', 'Votre CV est créé. Complétez-le étape par étape. L\'aperçu A4 se met à jour après l\'enregistrement.');
    }

    public function preferTemplate(Request $request, Resume $resume)
    {
        $this->authorize('update', $resume);

        $data = $request->validate([
            'template_id' => ['nullable', 'integer'],
        ]);

        $template = filled($data['template_id'] ?? null)
            ? ResumeTemplate::query()->where('is_active', true)->find($data['template_id'])
            : $resume->template;

        if (! $template) {
            return $this->preferResponse($request, false, 'Ce modèle n\'est pas disponible.');
        }

        try {
            app(QuotaService::class)->assertCanUseTemplate($request->user(), $template->is_premium);
        } catch (PlanLimitException $exception) {
            return $this->preferResponse($request, false, $exception->getMessage());
        }

        $profile = $request->user()->profile()->firstOrCreate(['user_id' => $request->user()->id]);
        $preferences = $profile->preferences ?? [];
        $preferences['default_template'] = $template->slug;
        $profile->forceFill(['preferences' => $preferences])->save();

        return $this->preferResponse($request, true, 'Ce modèle sera proposé pour vos prochains CV.');
    }

    private function preferResponse(Request $request, bool $successful, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'successful' => $successful,
                'message' => $message,
            ], $successful ? 200 : 422);
        }

        return back()->with($successful ? 'success' : 'error', $message);
    }

    public function edit(Request $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $resume->load('template');

        $form = $this->formState($resume, $request);

        return view('resumes.edit', [
            'resume' => $resume,
            'templates' => ResumeTemplate::available(),
            'quota' => app(QuotaService::class),
            'form' => $form,
            'checks' => ResumeReadiness::checks($form),
            'credits' => app(QuotaService::class)->creditsRemaining($request->user()),
            'versions' => $resume->versions()->latest()->limit(12)->get(),
            'wizard' => $request->boolean('wizard'),
            'wizardStep' => max(1, min(6, (int) $request->query('wizard_step', 1))),
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

        return $this->backToEditor($request, $resume)->with('success', 'CV enregistré.');
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

    public function preview(Request $request, Resume $resume, ResumePresenter $presenter)
    {
        $this->authorize('view', $resume);

        return view('resumes.paper', [
            'document' => $presenter->present($resume),
            'title' => $resume->title,
            'screen' => true,
            'embedded' => $request->boolean('cadre'),
        ]);
    }

    public function applyProposal(Request $request, Resume $resume, ResumeWriter $writer)
    {
        $this->authorize('update', $resume);
        $data = $request->validate([
            'target' => ['required', 'in:summary,headline,experience,education,skill,section_order'],
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

    private function backToEditor(Request $request, Resume $resume)
    {
        $parameters = ['resume' => $resume];

        if ($request->boolean('wizard')) {
            $parameters['wizard'] = 1;
            $parameters['wizard_step'] = max(1, min(6, (int) $request->input('wizard_step', 1)));
        }

        return redirect()->route('cvs.edit', $parameters);
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
