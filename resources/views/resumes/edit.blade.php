@extends('layouts.studio')
@section('document-title', $resume->title)
@section('content')
    @php
        $wizard = $wizard ?? false;
        $wizardStep = $wizard ? (int) ($wizardStep ?? 1) : 1;
        $wizardMeta = [
            1 => ['Profil', 'Indiquez vos informations personnelles.'],
            2 => ['Expériences', 'Ajoutez chaque poste, avec l\'employeur, les dates et ce que vous y avez fait.'],
            3 => ['Formations', 'Indiquez vos diplômes et vos formations.'],
            4 => ['Compétences', 'Listez les compétences que vous pouvez justifier.'],
            5 => ['Résumé professionnel', 'Résumez votre parcours à partir des faits déjà saisis.'],
            6 => ['Autres rubriques', 'Langues, certifications, projets, puis le modèle et les couleurs.'],
        ];
        $wizardSections = [
            'experiences' => 2,
            'education' => 3,
            'skills' => 4,
            'languages' => 6,
            'certifications' => 6,
            'projects' => 6,
            'interests' => 6,
            'references' => 6,
        ];
        $wizardUrl = fn (int $step) => route('cvs.edit', ['resume' => $resume, 'wizard' => 1, 'wizard_step' => $step]);
        $steps = [
            'identite' => 'Informations personnelles',
            'resume' => 'Résumé professionnel',
            'experiences' => 'Expériences',
            'education' => 'Formation',
            'skills' => 'Compétences',
            'languages' => 'Langues',
            'certifications' => 'Certifications',
            'projects' => 'Projets',
            'interests' => 'Centres d\'intérêt',
            'references' => 'Références',
        ];
    @endphp
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h1 class="h2 mb-1">@if ($wizard) Étape {{ $wizardStep }} sur 6 @else Éditeur @endif</h1>
            <p class="small text-secondary mb-0" data-save-status>@if ($wizard) {{ $wizardMeta[$wizardStep][0] }}. {{ $wizardMeta[$wizardStep][1] }} Les modifications de texte sont enregistrées automatiquement. @else Les modifications de texte sont enregistrées automatiquement. @endif</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($wizard)
                <a class="btn btn-outline-primary" href="{{ route('cvs.edit', $resume) }}">Éditeur complet</a>
            @else
                <a class="btn btn-outline-primary" href="{{ $wizardUrl(1) }}">Mode étape par étape</a>
            @endif
            <a class="btn btn-outline-primary" href="{{ route('assistant.index', ['cv' => $resume->id]) }}">Améliorer avec l'IA</a>
            <a class="btn btn-outline-primary" href="{{ route('cvs.preview', $resume) }}" target="_blank" rel="noopener">Format A4</a>
            @if ($quota->allows(auth()->user(), 'export_pdf'))
                <a class="btn btn-primary" href="{{ route('cvs.export', [$resume, 'pdf']) }}">Télécharger le PDF</a>
            @endif
            <a class="btn btn-outline-primary" href="{{ route('cvs.versions', $resume) }}">Versions</a>
            <a class="btn btn-outline-danger" href="{{ route('cvs.delete', $resume) }}">Supprimer</a>
        </div>
    </div>
    @if (request()->boolean('assistant'))
        <div class="alert alert-info" id="assistant-cv">
            L'assistant est prêt pour ce CV. Une proposition n'est appliquée que si vous la validez.
            <a href="{{ route('assistant.index', ['cv' => $resume->id]) }}">Ouvrir l'assistant</a>
        </div>
    @endif
    @if (session('suggestion'))
        <div class="alert alert-warning">Proposition non appliquée : {{ session('suggestion') }}</div>
    @endif
    @unless ($wizard)
        <div class="card mb-4">
            <div class="card-body">
                <p class="small mb-2" data-editor-progress-label>Progression du CV</p>
                <div class="progress" data-editor-progress role="progressbar" aria-label="Étapes renseignées" aria-valuemin="0" aria-valuemax="10" aria-valuenow="0">
                    <div class="progress-bar"></div>
                </div>
            </div>
        </div>
    @endunless
    @if ($wizard)
        <div class="d-xl-none d-flex gap-2 mb-3" role="group" aria-label="Affichage">
            <button class="btn btn-primary" type="button" data-wizard-pane-target="form" aria-pressed="true">Formulaire</button>
            <button class="btn btn-outline-primary" type="button" data-wizard-pane-target="preview" aria-pressed="false">Aperçu</button>
        </div>
    @endif
    <div class="row g-4" @if ($wizard) data-wizard data-wizard-pane="form" @endif>
        <div class="{{ $wizard ? 'col-xl-6' : 'col-xl-8' }}" @if ($wizard) data-wizard-form @endif>
            <form id="resume-form" method="POST" action="{{ route('cvs.update', $resume) }}" data-autosave="{{ route('cvs.update', $resume) }}" class="vstack gap-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="lock_version" value="{{ $resume->lock_version }}">
                @foreach (\App\Support\ResumeContent::sections() as $section)
                    <input type="hidden" name="sections_present[]" value="{{ $section }}">
                @endforeach
                @if ($wizard)
                    <input type="hidden" name="wizard" value="1">
                    <input type="hidden" name="wizard_step" value="{{ $wizardStep }}">
                @else
                    <input type="hidden" name="complet" value="1">
                @endif
                @if ($wizard)
                    <nav class="d-flex flex-wrap gap-2" aria-label="Étapes du CV">
                        @foreach ($wizardMeta as $number => $meta)
                            <a class="btn btn-sm {{ $number === $wizardStep ? 'btn-primary' : 'btn-outline-primary' }} cvora-step" href="{{ $wizardUrl($number) }}" data-wizard-go @if ($number === $wizardStep) aria-current="step" @endif>{{ $number }}. {{ $meta[0] }}</a>
                        @endforeach
                    </nav>
                    <div class="progress" role="progressbar" aria-label="Étape {{ $wizardStep }} sur 6" aria-valuemin="1" aria-valuemax="6" aria-valuenow="{{ $wizardStep }}">
                        <div class="progress-bar" style="width: {{ (int) round($wizardStep / 6 * 100) }}%"></div>
                    </div>
                    @if ($wizardStep === 1)
                        <section class="card border-primary" data-wizard-panel="1">
                            <div class="card-body vstack gap-2">
                                <h2 class="h5 mb-0">Importer mon CV</h2>
                                <p class="small text-secondary mb-0">Téléchargez un PDF ou un document Word. Les informations reconnues sont proposées pour un nouveau CV. Rien n'est enregistré tant que vous ne validez pas chaque champ.</p>
                                <div><a class="btn btn-outline-primary" href="{{ route('imports.create') }}">Importer mon CV</a></div>
                            </div>
                        </section>
                    @endif
                @else
                    <div class="d-flex gap-2 overflow-auto pb-1">
                        @foreach ($steps as $id => $label)
                            <button type="button" class="btn btn-sm btn-outline-primary cvora-step" data-step="{{ $id }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hide-done" data-hide-done checked>
                        <label class="form-check-label" for="hide-done">Afficher seulement ce qui reste</label>
                    </div>
                @endif
                <section id="identite" class="card" data-editor-section data-section-group="identite" data-wizard-step="1"@if ($wizard && $wizardStep !== 1) hidden @endif>
                    <div class="card-body vstack gap-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h2 class="h5 mb-0">1. Informations personnelles</h2>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-section-open hidden>Ouvrir</button>
                        </div>
                        <p class="small text-secondary mb-0" data-section-note hidden>Cette partie est déjà remplie.</p>
                        <div data-section-body class="vstack gap-3">
                        <div>
                            <label class="form-label" for="title">Titre du document</label>
                            <input class="form-control" id="title" name="title" value="{{ $form['title'] }}" required>
                        </div>
                        <div class="row g-3">
                            @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'headline' => 'Titre visé', 'email' => 'E-mail', 'phone' => 'Téléphone', 'city' => 'Ville', 'country' => 'Pays', 'address' => 'Adresse', 'linkedin' => 'LinkedIn', 'website' => 'Site'] as $key => $label)
                                <div class="{{ $key === 'address' ? 'col-12' : 'col-sm-6' }}">
                                    <label class="form-label" for="personal_{{ $key }}">{{ $label }}</label>
                                    <input class="form-control" id="personal_{{ $key }}" name="personal[{{ $key }}]" value="{{ $form['personal'][$key] ?? '' }}" @if ($key === 'headline') data-coach-field="headline" @endif>
                                </div>
                            @endforeach
                        </div>
                        <div class="border rounded p-3">
                            <p class="form-label mb-1">Photo</p>
                            <p class="small text-secondary">JPEG, PNG ou WebP, 5 Mo maximum. Le recadrage est centré : carré, rectangle ou cercle selon le modèle. Sans photo, les initiales restent affichées.</p>
                            @if ($resume->photo_path)
                                <p class="small mb-2">Photo enregistrée. Elle suit le CV dans l'aperçu, le PDF et le document Word.</p>
                            @else
                                <p class="small mb-2">Aucune photo pour le moment. L'aperçu affiche {{ $resume->displayName() === 'Prénom Nom' ? 'vos initiales' : 'les initiales' }}.</p>
                            @endif
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <input class="form-control" id="photo" form="photo-form" type="file" name="photo" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help">
                                <button class="btn btn-primary" form="photo-form" type="submit">Enregistrer la photo</button>
                                @if ($resume->photo_path)
                                    <button class="btn btn-outline-secondary" form="photo-remove" type="submit">Retirer la photo</button>
                                @endif
                            </div>
                            <p class="small text-secondary mb-0 mt-2" id="photo-help">La photo n'est pas envoyée avec l'enregistrement automatique du texte.</p>
                            @error('photo')
                                <p class="text-danger small mb-0 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        </div>
                    </div>
                </section>
                <section id="resume" class="card" data-editor-section data-section-group="resume" data-wizard-step="5"@if ($wizard && $wizardStep !== 5) hidden @endif>
                    <div class="card-body vstack gap-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h2 class="h5 mb-0">2. Résumé professionnel</h2>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-section-open hidden>Ouvrir</button>
                        </div>
                        <p class="small text-secondary mb-0" data-section-note hidden>Cette partie est déjà remplie.</p>
                        <div data-section-body class="vstack gap-3">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-suggest data-suggest-url="{{ route('assistant.summary') }}" data-suggest-field="summary">Améliorer ce texte</button>
                        </div>
                        <textarea class="form-control cvora-textarea" name="summary" data-coach-field="summary">{{ $form['summary'] }}</textarea>
                        <p class="small text-secondary mb-0">Trame à remplir avec vos faits : « [Métier] depuis [année], dans [domaine]. » L'amélioration reformule ce qui est déjà écrit.</p>
                        <div data-suggest-output hidden></div>
                        </div>
                    </div>
                </section>
                @php $stepNo = 3; @endphp
                @foreach (\App\Support\ResumeContent::sections() as $section)
                    <section id="{{ $section }}" class="card" data-editor-section @if (in_array($section, ['experiences', 'education', 'skills'], true)) data-section-group="{{ $section }}" @else data-section-mode="optional" @endif data-wizard-step="{{ $wizardSections[$section] }}"@if ($wizard && $wizardStep !== $wizardSections[$section]) hidden @endif>
                        <div class="card-body vstack gap-3">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <h2 class="h5 mb-0">{{ $stepNo }}. {{ \App\Support\ResumeContent::labels()[$section]['fr'] }}</h2>
                                <span class="d-flex align-items-center gap-2">
                                    @if (\App\Support\ResumeContent::labels()[$section]['optional'])
                                        <span class="badge text-bg-light">Facultatif</span>
                                    @endif
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-section-open hidden>Ouvrir</button>
                                </span>
                            </div>
                            <p class="small text-secondary mb-0" data-section-note hidden>Cette partie est déjà remplie.</p>
                            <div data-section-body class="vstack gap-3">
                            @if ($section === 'references')
                                <p class="small text-secondary mb-0">N'indiquez une référence qu'avec l'accord de la personne.</p>
                            @endif
                            @foreach ($form[$section] as $index => $item)
                                <div class="border rounded p-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
                                        @if ($index > 0)
                                            <button class="btn btn-sm btn-outline-secondary" name="action" value="move:{{ $section }}:{{ $index }}:up" type="submit">Monter</button>
                                        @endif
                                        @if ($index < count($form[$section]) - 1)
                                            <button class="btn btn-sm btn-outline-secondary" name="action" value="move:{{ $section }}:{{ $index }}:down" type="submit">Descendre</button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger" name="action" value="remove:{{ $section }}:{{ $index }}" type="submit">Supprimer</button>
                                    </div>
                                    <input type="hidden" name="{{ $section }}[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                                    <div class="row g-3">
                                        @foreach (\App\Support\ResumeContent::fields($section) as $field)
                                            <div class="{{ $field['type'] === 'textarea' ? 'col-12' : 'col-sm-6' }}">
                                                @if ($field['type'] === 'checkbox')
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" id="{{ $section }}_{{ $index }}_{{ $field['name'] }}" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="1" @checked($item[$field['name']] ?? false)>
                                                        <label class="form-check-label" for="{{ $section }}_{{ $index }}_{{ $field['name'] }}">{{ $field['label'] }}</label>
                                                    </div>
                                                @elseif ($field['type'] === 'textarea')
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                        <label class="form-label mb-0">{{ $field['label'] }}</label>
                                                        @if (in_array($section, ['experiences', 'education'], true) && $field['name'] === 'description')
                                                            <button class="btn btn-sm btn-outline-primary" type="button" data-suggest data-suggest-url="{{ route('assistant.grammar') }}" data-suggest-field="{{ $section }}[{{ $index }}][{{ $field['name'] }}]">Améliorer ce texte</button>
                                                        @endif
                                                    </div>
                                                    <textarea class="form-control cvora-textarea" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" @if ($field['name'] === 'description' && $section === 'experiences') data-coach-field="experience" data-coach-index="{{ $index }}" @elseif ($field['name'] === 'description' && $section === 'education') data-coach-field="education" data-coach-index="{{ $index }}" @endif>{{ $item[$field['name']] ?? '' }}</textarea>
                                                    @if (in_array($section, ['experiences', 'education'], true) && $field['name'] === 'description')
                                                        <div data-suggest-output hidden></div>
                                                    @endif
                                                @else
                                                    <label class="form-label">{{ $field['label'] }}</label>
                                                    <input class="form-control" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="{{ $item[$field['name']] ?? '' }}" @if ($section === 'skills' && $field['name'] === 'name') data-coach-field="skill" data-coach-index="{{ $index }}" @endif>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <button class="btn btn-outline-primary" name="action" value="add:{{ $section }}" type="submit">Ajouter</button>
                            </div>
                        </div>
                    </section>
                    @php $stepNo++; @endphp
                @endforeach
                <section class="card" data-wizard-step="6"@if ($wizard && $wizardStep !== 6) hidden @endif>
                    <div class="card-body vstack gap-3">
                        <h2 class="h5">Modèle et couleurs</h2>
                        @if ($wizard)
                            <p class="small text-secondary mb-0">Le modèle, la couleur, la police et la taille se règlent au-dessus de l'aperçu. Changer de modèle conserve les textes déjà saisis.</p>
                        @else
                            <div>
                                <label class="form-label" for="template_id">Modèle</label>
                                <select class="form-select" id="template_id" name="template_id">
                                    @foreach ($templates as $template)
                                        @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                                        <option value="{{ $template->id }}" @selected($form['template_id'] == $template->id) @disabled(! $allowed && $form['template_id'] != $template->id)>{{ $template->name }}{{ $template->is_premium ? ' · premium' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label" for="primary">Couleur principale</label>
                                    <input class="form-control" id="primary" name="theme[primary]" value="{{ $form['theme']['primary'] ?? '#0F766E' }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="accent">Couleur d'accent</label>
                                    <input class="form-control" id="accent" name="theme[accent]" value="{{ $form['theme']['accent'] ?? '#1C1917' }}">
                                </div>
                            </div>
                            <div>
                                <label class="form-label" for="status">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" @selected($form['status'] === 'draft')>Brouillon</option>
                                    <option value="ready" @selected($form['status'] === 'ready')>Prêt</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit" data-loading>Enregistrer</button>
                        @endif
                        <button class="btn btn-outline-primary" type="button" data-prefer-template data-prefer-url="{{ route('cvs.prefer-template', $resume) }}">Retenir ce modèle</button>
                        <p class="small text-secondary mb-0" data-prefer-status>Le modèle retenu est proposé à la création des prochains CV.</p>
                    </div>
                </section>
                @if ($wizard)
                    <div class="d-flex flex-wrap justify-content-between gap-2">
                        @if ($wizardStep > 1)
                            <a class="btn btn-outline-primary" href="{{ $wizardUrl($wizardStep - 1) }}" data-wizard-go>Précédent</a>
                        @else
                            <span></span>
                        @endif
                        @if ($wizardStep < 6)
                            <a class="btn btn-primary" href="{{ $wizardUrl($wizardStep + 1) }}" data-wizard-go>Suivant</a>
                        @else
                            <button class="btn btn-primary" type="submit" data-loading>Enregistrer le CV</button>
                        @endif
                    </div>
                @endif
            </form>
            <form id="photo-form" method="POST" action="{{ route('cvs.photo', $resume) }}" enctype="multipart/form-data" hidden>
                @csrf
                @if ($wizard)
                    <input type="hidden" name="wizard" value="1">
                    <input type="hidden" name="wizard_step" value="1">
                @else
                    <input type="hidden" name="complet" value="1">
                @endif
            </form>
            <form id="photo-remove" method="POST" action="{{ route('cvs.photo.destroy', $resume) }}" hidden>
                @csrf
                @method('DELETE')
                @if ($wizard)
                    <input type="hidden" name="wizard" value="1">
                    <input type="hidden" name="wizard_step" value="1">
                @else
                    <input type="hidden" name="complet" value="1">
                @endif
            </form>
        </div>
        <aside class="{{ $wizard ? 'col-xl-6' : 'col-xl-4' }}" @if ($wizard) data-wizard-preview @endif>
            <div class="cvora-preview-sticky vstack gap-3">
                @if ($wizard)
                    <div class="cvora-toolbar card">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3 py-3">
                            <div>
                                <label class="form-label mb-1" for="template_id">Modèle</label>
                                <select class="form-select form-select-sm" id="template_id" name="template_id" form="resume-form">
                                    @foreach ($templates as $template)
                                        @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                                        <option value="{{ $template->id }}" @selected($form['template_id'] == $template->id) @disabled(! $allowed && $form['template_id'] != $template->id)>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <p class="form-label mb-1" id="color-label">Couleur</p>
                                <div class="d-flex align-items-center gap-1" role="group" aria-labelledby="color-label">
                                    @foreach (['#0F766E', '#1C1917', '#9A3412', '#1E3A8A', '#44403C', '#B45309'] as $swatch)
                                        <button class="cvora-swatch" type="button" data-theme-swatch="{{ $swatch }}" style="background: {{ $swatch }}" aria-label="Couleur {{ $swatch }}"></button>
                                    @endforeach
                                    <input class="form-control form-control-color" id="primary" name="theme[primary]" form="resume-form" value="{{ $form['theme']['primary'] }}" aria-label="Couleur principale">
                                </div>
                            </div>
                            <div>
                                <label class="form-label mb-1" for="theme-font">Police</label>
                                <select class="form-select form-select-sm" id="theme-font" name="theme[font]" form="resume-form">
                                    <option value="sans" @selected(($form['theme']['font'] ?? 'sans') === 'sans')>Sans</option>
                                    <option value="serif" @selected(($form['theme']['font'] ?? '') === 'serif')>Serif</option>
                                    <option value="mono" @selected(($form['theme']['font'] ?? '') === 'mono')>Mono</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label mb-1" for="theme-size">Taille</label>
                                <select class="form-select form-select-sm" id="theme-size" name="theme[size]" form="resume-form">
                                    <option value="s" @selected(($form['theme']['size'] ?? 'm') === 's')>S</option>
                                    <option value="m" @selected(($form['theme']['size'] ?? 'm') === 'm')>M</option>
                                    <option value="l" @selected(($form['theme']['size'] ?? '') === 'l')>L</option>
                                </select>
                            </div>
                            <input type="hidden" name="theme[accent]" form="resume-form" value="{{ $form['theme']['accent'] }}">
                            <input type="hidden" name="status" form="resume-form" value="{{ $form['status'] }}">
                        </div>
                    </div>
                @endif
                <div class="card cvora-a4-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 border-bottom px-3 py-2">
                        <p class="mb-0 small fw-semibold">Aperçu A4 · <span data-template-name>{{ $resume->template?->name }}</span></p>
                        <a class="small" href="{{ route('cvs.preview', $resume) }}" target="_blank" rel="noopener">Ouvrir</a>
                    </div>
                    <div class="cvora-a4-frame" data-a4-frame>
                        <iframe data-preview title="Aperçu A4 du CV" src="{{ route('cvs.preview', $resume) }}?cadre=1"></iframe>
                    </div>
                    <p class="small text-secondary mb-0 px-3 py-2">Première page, 210 × 297 mm. L'aperçu se recharge après l'enregistrement automatique.</p>
                </div>
                @if ($wizard)
                    <details class="card">
                        <summary class="px-3 py-2">Aide, checklist et export</summary>
                        <div class="vstack gap-3 p-3 pt-0">
                @endif
                <section class="card" data-proofread data-proofread-url="{{ route('cvs.proofread', $resume) }}">
                    <div class="card-body vstack gap-2">
                        <h2 class="h6 mb-0">Français du CV</h2>
                        <p class="small text-secondary mb-0">Corrige l'orthographe ou clarifie le français des textes déjà écrits. Un appel réserve un crédit. Le CV change seulement si vous utilisez une proposition.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-proofread-mode="spelling">Corriger l'orthographe</button>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-proofread-mode="clarity">Rendre le français plus clair</button>
                        </div>
                        <div data-proofread-output></div>
                        <p class="small text-secondary mb-0" data-proofread-status></p>
                    </div>
                </section>
                @php $ready = collect($checks)->where('done', true)->count(); @endphp
                <div class="card">
                    <div class="card-body">
                        <p class="fw-semibold mb-2" data-readiness-label>{{ $ready }} élément{{ $ready > 1 ? 's' : '' }} prêt{{ $ready > 1 ? 's' : '' }} sur {{ count($checks) }}</p>
                        <ul class="list-unstyled mb-0 vstack gap-1">
                            @foreach ($checks as $check)
                                <li data-readiness-item data-readiness-group="{{ $check['jump'] }}" data-done="{{ $check['done'] ? '1' : '0' }}" data-readiness-mode="{{ $check['mode'] }}" data-readiness-prefix="{{ $check['prefix'] }}" data-readiness-fields='@json($check['fields'])'>
                                    <button class="btn btn-link btn-sm text-start p-0 text-decoration-none" type="button" data-jump="{{ $check['jump'] }}">
                                        <i class="bi {{ $check['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} me-1" data-readiness-icon aria-hidden="true"></i>{{ $check['label'] }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <section class="card" data-coach data-coach-url="{{ route('cvs.coach', $resume) }}">
                    <div class="card-body vstack gap-2">
                        <h2 class="h6 mb-0">Conversation</h2>
                        <p class="small text-secondary mb-0">Un message réserve un crédit ({{ $credits }} restant{{ $credits > 1 ? 's' : '' }}) et envoie le contenu professionnel du CV au fournisseur configuré. Le crédit est rendu si l'appel échoue. Le CV change seulement si vous utilisez une proposition.</p>
                        <div class="cvora-coach-log vstack gap-2" data-coach-log aria-live="polite"></div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-coach-prompt>Que manque-t-il pour un CV complet ?</button>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-coach-prompt>Propose une formulation du résumé à partir des faits déjà saisis.</button>
                        </div>
                        <label class="form-label mb-0" for="coach-message">Message</label>
                        <textarea class="form-control" id="coach-message" data-coach-input rows="3" maxlength="2000" placeholder="Décrivez ce que vous voulez reformuler"></textarea>
                        <button class="btn btn-primary" type="button" data-coach-send>Envoyer</button>
                        <p class="small text-secondary mb-0" data-coach-status></p>
                    </div>
                </section>
                <div class="card">
                    <div class="card-body">
                        <p class="fw-semibold">Export</p>
                        @if ($quota->allows(auth()->user(), 'export_pdf'))
                            <a class="d-block mt-2" href="{{ route('cvs.export', [$resume, 'pdf']) }}"><i class="bi bi-filetype-pdf me-1" aria-hidden="true"></i>Télécharger le PDF</a>
                        @else
                            <a class="d-block mt-2" href="{{ route('billing.index') }}">PDF disponible avec une offre supérieure</a>
                        @endif
                        @if ($quota->allows(auth()->user(), 'export_docx'))
                            <a class="d-block mt-2" href="{{ route('cvs.export', [$resume, 'docx']) }}"><i class="bi bi-file-earmark-word me-1" aria-hidden="true"></i>Télécharger le Word</a>
                        @else
                            <a class="d-block mt-2" href="{{ route('billing.index') }}">Word disponible avec une offre supérieure</a>
                        @endif
                    </div>
                </div>
                @if ($wizard)
                        </div>
                    </details>
                @endif
            </div>
        </aside>
    </div>
@endsection
