<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Models\Resume;
use App\Models\ResumeImport;
use App\Models\ResumeTemplate;
use App\Services\Billing\QuotaService;
use App\Services\Resume\ResumeImporter;
use App\Services\Resume\ResumeWriter;
use App\Support\ResumeContent;
use App\Support\TemplateMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeImportController extends Controller
{
    public function create(Request $request, QuotaService $quota)
    {
        $enabled = true;
        $disabledMessage = null;

        try {
            $quota->assertModule('feature_import', 'L\'importation est temporairement désactivée.');
        } catch (PlanLimitException $exception) {
            $enabled = false;
            $disabledMessage = $exception->getMessage();
        }

        return view('resumes.import', [
            'imports' => $request->user()->imports()->latest()->limit(8)->get(),
            'resumes' => $request->user()->resumes()->orderBy('title')->get(['id', 'title']),
            'maxKb' => (int) config('cvora.import.max_kb'),
            'enabled' => $enabled,
            'disabledMessage' => $disabledMessage,
        ]);
    }

    public function store(Request $request, ResumeImporter $importer, QuotaService $quota)
    {
        try {
            $quota->assertModule('feature_import', 'L\'importation est temporairement désactivée.');
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:'.(int) config('cvora.import.max_kb'), 'mimes:pdf,docx'],
        ]);

        $import = $importer->store($request->user(), $request->file('file'));

        return redirect()->route('imports.show', $import);
    }

    public function show(Request $request, ResumeImport $import)
    {
        $this->authorize('view', $import);
        $parsed = $import->parsed ?? [];
        $user = $request->user();
        $user->loadMissing('profile');
        $quota = app(QuotaService::class);
        $premium = $quota->allows($user, 'premium_templates');
        $content = is_array($parsed['content'] ?? null) ? $parsed['content'] : [];

        return view('resumes.import-review', [
            'import' => $import,
            'personal' => old('personal', $parsed['personal'] ?? ResumeContent::emptyPersonal()),
            'summary' => old('summary', $parsed['summary'] ?? ''),
            'content' => old('content', $parsed['content'] ?? ResumeContent::emptyContent()),
            'detected' => $parsed['detected'] ?? [],
            'resumes' => $user->resumes()->orderBy('title')->get(['id', 'title']),
            'templates' => ResumeTemplate::available(),
            'premiumTemplates' => $premium,
            'suggestion' => TemplateMatcher::suggest($content, $premium, $user->profile?->preference('default_template')),
        ]);
    }

    public function confirm(Request $request, ResumeImport $import, ResumeWriter $writer)
    {
        $this->authorize('view', $import);

        if ($import->status === 'confirmed') {
            return back()->with('error', 'Cette importation a déjà été confirmée.');
        }

        $data = $request->validate([
            'confirmed' => ['accepted'],
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'personal' => ['nullable', 'array'],
            'resume_id' => ['nullable', 'integer'],
            'overwrite_confirmed' => ['nullable', 'boolean'],
            'template_id' => ['nullable', 'integer', 'exists:resume_templates,id'],
            'remember_template' => ['nullable', 'boolean'],
        ]);

        foreach (ResumeContent::sections() as $section) {
            $data[$section] = $request->input($section, $import->parsed['content'][$section] ?? []);
        }

        $data['personal'] = $request->input('personal', []);

        try {
            if (filled($data['resume_id'] ?? null)) {
                if (! $request->boolean('overwrite_confirmed')) {
                    return back()->withInput()->with('error', 'Cochez la confirmation pour mettre à jour un CV existant. Rien n\'a été écrasé.');
                }

                $resume = Resume::query()->with('user')->where('user_id', $request->user()->id)->find($data['resume_id']);

                if (! $resume) {
                    return back()->withInput()->with('error', 'Le CV à mettre à jour est introuvable.');
                }

                $resume = $writer->overwriteFromConfirmed($resume, $data);
            } else {
                $resume = $writer->createFromConfirmed($request->user(), $data);
            }
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception);
        }

        if ($request->boolean('remember_template')) {
            $resume->loadMissing('template');
            $slug = $resume->template?->slug;

            if ($slug) {
                $profile = $request->user()->profile()->firstOrCreate(['user_id' => $request->user()->id]);
                $preferences = $profile->preferences ?? [];
                $preferences['default_template'] = $slug;
                $profile->forceFill(['preferences' => $preferences])->save();
            }
        }

        $import->forceFill([
            'status' => 'confirmed',
            'resume_id' => $resume->id,
            'parsed' => null,
        ])->save();
        app(ResumeImporter::class)->forgetFile($import);

        return redirect()->route('cvs.edit', $resume)->with('success', 'Les informations confirmées ont été enregistrées dans un CV.');
    }

    public function file(ResumeImport $import)
    {
        $this->authorize('view', $import);
        abort_unless($import->path && Storage::disk($import->disk)->exists($import->path), 404);

        return Storage::disk($import->disk)->download($import->path, $import->original_name);
    }
}
