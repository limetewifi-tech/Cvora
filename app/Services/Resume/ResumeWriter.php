<?php

namespace App\Services\Resume;

use App\Exceptions\PlanLimitException;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\ResumeVersion;
use App\Models\User;
use App\Services\Billing\QuotaService;
use App\Support\ResumeContent;
use Illuminate\Support\Facades\DB;

class ResumeWriter
{
    public function __construct(private QuotaService $quota) {}

    public function createFromProfile(User $user, ResumeTemplate $template, string $title): Resume
    {
        $this->quota->assertCanCreateResume($user);
        $this->guardTemplate($user, $template);

        $profile = $user->profile;
        $personal = ResumeContent::emptyPersonal();
        $personal['first_name'] = $profile?->first_name ?? '';
        $personal['last_name'] = $profile?->last_name ?? '';
        $personal['headline'] = $profile?->headline ?? '';
        $personal['email'] = $user->email;
        $personal['phone'] = $profile?->phone ?: ($user->phone ?? '');
        $personal['city'] = $profile?->city ?? '';
        $personal['country'] = $profile?->country ?? '';
        $personal['address'] = $profile?->address ?? '';
        $personal['linkedin'] = $profile?->linkedin ?? '';
        $personal['website'] = $profile?->website ?? '';

        return $user->resumes()->create([
            'template_id' => $template->id,
            'title' => $title,
            'status' => 'draft',
            'source' => 'editor',
            'summary' => $profile?->summary,
            'personal' => ResumeContent::normalizePersonal($personal),
            'content' => ResumeContent::emptyContent(),
            'section_order' => ResumeContent::normalizeOrder($template->default_section_order),
            'theme' => ResumeContent::normalizeTheme($template->default_theme),
            'lock_version' => 0,
        ]);
    }

    public function update(Resume $resume, array $input): Resume
    {
        $this->assertLock($resume, $input);
        $input = ResumeContent::applyAction($input);
        $template = $this->templateFromInput($resume->user, $input, $resume);

        $present = $input['sections_present'] ?? ResumeContent::sections();
        $existing = $resume->content ?? ResumeContent::emptyContent();
        $raw = [];

        foreach (ResumeContent::sections() as $section) {
            if (is_array($present) && ! in_array($section, $present, true)) {
                $raw[$section] = $existing[$section] ?? [];
                continue;
            }

            $raw[$section] = $input[$section] ?? [];
        }

        $resume->fill([
            'title' => ResumeContent::clean($input['title'] ?? $resume->title, 160),
            'summary' => ResumeContent::clean($input['summary'] ?? '', 4000, true),
            'template_id' => $template->id,
            'personal' => ResumeContent::normalizePersonal($input['personal'] ?? []),
            'content' => ResumeContent::normalizeContent($raw),
            'section_order' => ResumeContent::normalizeOrder($input['section_order'] ?? $resume->section_order, $template->default_section_order),
            'theme' => ResumeContent::normalizeTheme($input['theme'] ?? [], $template->default_theme ?? []),
            'status' => ($input['status'] ?? $resume->status) === 'ready' ? 'ready' : 'draft',
            'lock_version' => $resume->lock_version + 1,
        ]);

        if ($resume->title === '') {
            $resume->title = 'Mon CV';
        }

        $resume->save();

        return $resume->refresh();
    }

    public function createFromConfirmed(User $user, array $input): Resume
    {
        $this->quota->assertCanCreateResume($user);
        $template = ResumeTemplate::query()->active()->where('is_premium', false)->orderBy('sort_order')->first()
            ?? ResumeTemplate::query()->active()->orderBy('sort_order')->first();

        if (! $template) {
            throw new PlanLimitException('Aucun modèle n\'est disponible.');
        }

        $this->guardTemplate($user, $template);

        return $user->resumes()->create([
            'template_id' => $template->id,
            'title' => ResumeContent::clean($input['title'] ?? 'CV importé', 160) ?: 'CV importé',
            'status' => 'draft',
            'source' => 'import',
            'summary' => ResumeContent::clean($input['summary'] ?? '', 4000, true),
            'personal' => ResumeContent::normalizePersonal($input['personal'] ?? []),
            'content' => ResumeContent::normalizeContent($input),
            'section_order' => ResumeContent::normalizeOrder(null, $template->default_section_order),
            'theme' => ResumeContent::normalizeTheme(null, $template->default_theme ?? []),
        ]);
    }

    public function overwriteFromConfirmed(Resume $resume, array $input): Resume
    {
        $this->snapshot($resume, 'safety', 'Avant importation confirmée');

        return $this->update($resume, array_merge($input, [
            'lock_version' => $resume->fresh()->lock_version,
            'template_id' => $resume->template_id,
            'sections_present' => ResumeContent::sections(),
            'title' => $input['title'] ?? $resume->title,
        ]));
    }

    public function duplicate(Resume $resume): Resume
    {
        $this->quota->assertCanCreateResume($resume->user);
        $template = $resume->template;

        if ($template) {
            $this->guardTemplate($resume->user, $template);
        }

        return $resume->user->resumes()->create([
            'template_id' => $resume->template_id,
            'title' => ResumeContent::clean('Copie de '.$resume->title, 160),
            'status' => 'draft',
            'source' => $resume->source,
            'summary' => $resume->summary,
            'personal' => $resume->personal,
            'content' => $resume->content,
            'section_order' => $resume->section_order,
            'theme' => $resume->theme,
            'lock_version' => 0,
        ]);
    }

    public function snapshot(Resume $resume, string $kind, ?string $note = null): ResumeVersion
    {
        if ($kind === 'manual') {
            $this->quota->assertFeature(
                $resume->user,
                'version_history',
                'L\'historique complet des versions est inclus dans l\'offre Pro.',
            );
        }

        return DB::transaction(function () use ($resume, $kind, $note) {
            $number = (int) $resume->versions()->max('version_number') + 1;

            $version = $resume->versions()->create([
                'user_id' => $resume->user_id,
                'version_number' => $number,
                'kind' => $kind,
                'note' => $note ? ResumeContent::clean($note, 160) : null,
                'snapshot' => $this->snapshotData($resume),
            ]);

            if ($kind === 'safety' && ! $this->quota->allows($resume->user, 'version_history')) {
                $keep = $resume->versions()->where('kind', 'safety')->orderByDesc('id')->limit(3)->pluck('id');
                $resume->versions()->where('kind', 'safety')->whereNotIn('id', $keep)->delete();
            }

            return $version;
        });
    }

    public function restore(Resume $resume, ResumeVersion $version): Resume
    {
        abort_unless($version->resume_id === $resume->id, 404);

        $this->snapshot($resume, 'safety', 'Avant restauration');
        $snapshot = $version->snapshot;

        $resume->fill([
            'title' => $snapshot['title'] ?? $resume->title,
            'summary' => $snapshot['summary'] ?? null,
            'personal' => $snapshot['personal'] ?? ResumeContent::emptyPersonal(),
            'content' => $snapshot['content'] ?? ResumeContent::emptyContent(),
            'section_order' => $snapshot['section_order'] ?? ResumeContent::defaultOrder(),
            'theme' => $snapshot['theme'] ?? $resume->theme,
            'template_id' => $snapshot['template_id'] ?? $resume->template_id,
            'lock_version' => $resume->lock_version + 1,
        ])->save();

        return $resume->refresh();
    }

    public function applyConfirmedValue(Resume $resume, string $target, array $payload): Resume
    {
        $this->snapshot($resume, 'safety', 'Avant proposition confirmée');
        $content = $resume->content ?? ResumeContent::emptyContent();

        if ($target === 'summary') {
            $resume->summary = ResumeContent::clean($payload['value'] ?? '', 4000, true);
        } elseif ($target === 'experience' && isset($content['experiences'][(int) ($payload['index'] ?? -1)])) {
            $content['experiences'][(int) $payload['index']]['description'] = ResumeContent::clean($payload['value'] ?? '', 4000, true);
            $resume->content = $content;
        } elseif ($target === 'skill' && isset($content['skills'][(int) ($payload['index'] ?? -1)])) {
            $content['skills'][(int) $payload['index']]['name'] = ResumeContent::clean($payload['value'] ?? '', 200);
            $resume->content = $content;
        } elseif ($target === 'section_order') {
            $resume->section_order = ResumeContent::normalizeOrder($payload['order'] ?? []);
        } else {
            throw new PlanLimitException('Cette proposition ne correspond à aucun champ du CV.');
        }

        $resume->lock_version++;
        $resume->save();

        return $resume->refresh();
    }

    public function snapshotData(Resume $resume): array
    {
        return [
            'title' => $resume->title,
            'summary' => $resume->summary,
            'personal' => $resume->personal,
            'content' => $resume->content,
            'section_order' => $resume->section_order,
            'theme' => $resume->theme,
            'template_id' => $resume->template_id,
        ];
    }

    private function assertLock(Resume $resume, array $input): void
    {
        if (! array_key_exists('lock_version', $input) || (int) $input['lock_version'] !== (int) $resume->lock_version) {
            throw new PlanLimitException('Ce CV a été modifié entre-temps. Rechargez la page avant d\'enregistrer.');
        }
    }

    private function templateFromInput(User $user, array $input, Resume $resume): ResumeTemplate
    {
        $template = ResumeTemplate::query()->where('is_active', true)->find($input['template_id'] ?? $resume->template_id);

        if (! $template) {
            throw new PlanLimitException('Ce modèle n\'est pas disponible.');
        }

        $this->guardTemplate($user, $template);

        return $template;
    }

    private function guardTemplate(User $user, ResumeTemplate $template): void
    {
        if (! $template->is_active) {
            throw new PlanLimitException('Ce modèle n\'est pas disponible.');
        }

        $this->quota->assertCanUseTemplate($user, $template->is_premium);
    }
}
