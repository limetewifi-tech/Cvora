<?php

namespace App\Http\Controllers;

use App\Exceptions\PlanLimitException;
use App\Models\ResumeTemplate;
use App\Services\Billing\QuotaService;
use App\Services\Resume\ResumeWriter;
use App\Support\ResumeContent;
use Illuminate\Http\Request;

class StartController extends Controller
{
    public function index(Request $request, QuotaService $quota)
    {
        $goal = $request->query('objectif');

        if (! in_array($goal, ['creer', 'ameliorer', 'offre'], true)) {
            $goal = null;
        }

        $user = $request->user();
        $user->loadMissing('profile');

        return view('start.index', [
            'goal' => $goal,
            'resumes' => $user->resumes()->with('template')->orderBy('title')->get(),
            'canAdapt' => $quota->allows($user, 'job_adaptation'),
            'defaultTitle' => $user->profile?->first_name ? 'CV de '.$user->profile->first_name : 'Mon CV',
            'defaultHeadline' => (string) ($user->profile?->headline ?? ''),
        ]);
    }

    public function store(Request $request, ResumeWriter $writer, QuotaService $quota)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'headline' => ['nullable', 'string', 'max:160'],
        ], [
            'title.required' => 'Donnez un titre au document.',
        ]);

        $user = $request->user();
        $user->loadMissing('profile');
        $template = $this->template($user, $quota);

        if (! $template) {
            return back()->withInput()->with('error', 'Aucun modèle n\'est disponible.');
        }

        try {
            $resume = $writer->createFromProfile($user, $template, $data['title']);
        } catch (PlanLimitException $exception) {
            return $this->fail($request, $exception, route('start.index', ['objectif' => 'creer']));
        }

        if (filled($data['headline'] ?? null)) {
            $personal = $resume->personal ?? ResumeContent::emptyPersonal();
            $personal['headline'] = ResumeContent::clean($data['headline'], 160);
            $resume->forceFill(['personal' => ResumeContent::normalizePersonal($personal)])->save();
        }

        return redirect()->route('cvs.edit', $resume)->with('success', 'Le CV est créé avec le modèle '.$template->name.'. L\'aperçu A4 est à côté du formulaire. Complétez les parties encore ouvertes, ou demandez une reformulation à l\'assistant.');
    }

    private function template($user, QuotaService $quota): ?ResumeTemplate
    {
        $templates = ResumeTemplate::available();
        $preferred = $user->profile?->preference('default_template');
        $match = $templates->first(fn (ResumeTemplate $template) => $template->slug === $preferred && (! $template->is_premium || $quota->allows($user, 'premium_templates')));

        return $match ?? $templates->first(fn (ResumeTemplate $template) => ! $template->is_premium || $quota->allows($user, 'premium_templates'));
    }
}
