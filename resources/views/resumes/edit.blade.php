@extends('layouts.app')
@section('document-title', $resume->title)
@section('content')
    @php
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
            <h1 class="h2 mb-1">Éditeur</h1>
            <p class="small text-secondary mb-0" data-save-status>Les modifications de texte sont enregistrées automatiquement.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('cvs.versions', $resume) }}">Versions</a>
            <a class="btn btn-outline-danger" href="{{ route('cvs.delete', $resume) }}">Supprimer</a>
        </div>
    </div>
    @if (session('suggestion'))
        <div class="alert alert-warning">Proposition non appliquée : {{ session('suggestion') }}</div>
    @endif
    <div class="card mb-4">
        <div class="card-body">
            <p class="small mb-2" data-editor-progress-label>Progression du CV</p>
            <div class="progress" data-editor-progress role="progressbar" aria-label="Étapes renseignées" aria-valuemin="0" aria-valuemax="10" aria-valuenow="0">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-xl-8">
            <form id="resume-form" method="POST" action="{{ route('cvs.update', $resume) }}" data-autosave="{{ route('cvs.update', $resume) }}" class="vstack gap-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="lock_version" value="{{ $resume->lock_version }}">
                @foreach (\App\Support\ResumeContent::sections() as $section)
                    <input type="hidden" name="sections_present[]" value="{{ $section }}">
                @endforeach
                <div class="d-flex gap-2 overflow-auto pb-1">
                    @foreach ($steps as $id => $label)
                        <button type="button" class="btn btn-sm btn-outline-primary cvora-step" data-step="{{ $id }}">{{ $label }}</button>
                    @endforeach
                </div>
                <section id="identite" class="card">
                    <div class="card-body vstack gap-3">
                        <h2 class="h5">1. Informations personnelles</h2>
                        <div>
                            <label class="form-label" for="title">Titre du document</label>
                            <input class="form-control" id="title" name="title" value="{{ $form['title'] }}" required>
                        </div>
                        <div class="row g-3">
                            @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'headline' => 'Titre visé', 'email' => 'E-mail', 'phone' => 'Téléphone', 'city' => 'Ville', 'country' => 'Pays', 'address' => 'Adresse', 'linkedin' => 'LinkedIn', 'website' => 'Site'] as $key => $label)
                                <div class="{{ $key === 'address' ? 'col-12' : 'col-sm-6' }}">
                                    <label class="form-label" for="personal_{{ $key }}">{{ $label }}</label>
                                    <input class="form-control" id="personal_{{ $key }}" name="personal[{{ $key }}]" value="{{ $form['personal'][$key] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
                <section id="resume" class="card">
                    <div class="card-body">
                        <h2 class="h5">2. Résumé professionnel</h2>
                        <textarea class="form-control cvora-textarea mt-3" name="summary">{{ $form['summary'] }}</textarea>
                    </div>
                </section>
                @php $stepNo = 3; @endphp
                @foreach (\App\Support\ResumeContent::sections() as $section)
                    <section id="{{ $section }}" class="card">
                        <div class="card-body vstack gap-3">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <h2 class="h5 mb-0">{{ $stepNo }}. {{ \App\Support\ResumeContent::labels()[$section]['fr'] }}</h2>
                                @if (\App\Support\ResumeContent::labels()[$section]['optional'])
                                    <span class="badge text-bg-light">Facultatif</span>
                                @endif
                            </div>
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
                                                    <label class="form-label">{{ $field['label'] }}</label>
                                                    <textarea class="form-control cvora-textarea" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]">{{ $item[$field['name']] ?? '' }}</textarea>
                                                @else
                                                    <label class="form-label">{{ $field['label'] }}</label>
                                                    <input class="form-control" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="{{ $item[$field['name']] ?? '' }}">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <button class="btn btn-outline-primary" name="action" value="add:{{ $section }}" type="submit">Ajouter</button>
                        </div>
                    </section>
                    @php $stepNo++; @endphp
                @endforeach
                <section class="card">
                    <div class="card-body vstack gap-3">
                        <h2 class="h5">Modèle et couleurs</h2>
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
                                <input class="form-control" id="primary" name="theme[primary]" value="{{ $form['theme']['primary'] ?? '#2563EB' }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="accent">Couleur d'accent</label>
                                <input class="form-control" id="accent" name="theme[accent]" value="{{ $form['theme']['accent'] ?? '#1F2937' }}">
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
                    </div>
                </section>
            </form>
        </div>
        <aside class="col-xl-4">
            <div class="cvora-preview-sticky vstack gap-3">
                <div class="card overflow-hidden">
                    <p class="border-bottom mb-0 px-3 py-2 small fw-semibold">Aperçu</p>
                    <iframe data-preview title="Aperçu du CV" src="{{ route('cvs.preview', $resume) }}" class="w-100 bg-white"></iframe>
                </div>
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
            </div>
        </aside>
    </div>
@endsection
