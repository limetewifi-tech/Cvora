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
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Éditeur</h1>
            <p class="text-sm text-slate-500" data-save-status>Les modifications de texte sont enregistrées automatiquement.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn btn-secondary" href="{{ route('cvs.versions', $resume) }}">Versions</a>
            <a class="btn btn-secondary" href="{{ route('cvs.delete', $resume) }}">Supprimer</a>
        </div>
    </div>
    @if (session('suggestion'))
        <p class="mb-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Proposition non appliquée : {{ session('suggestion') }}</p>
    @endif
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <form id="resume-form" method="POST" action="{{ route('cvs.update', $resume) }}" data-autosave="{{ route('cvs.update', $resume) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="lock_version" value="{{ $resume->lock_version }}">
            @foreach (\App\Support\ResumeContent::sections() as $section)
                <input type="hidden" name="sections_present[]" value="{{ $section }}">
            @endforeach
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach ($steps as $id => $label)
                    <button type="button" class="shrink-0 rounded-full bg-white px-3 py-1 text-sm ring-1 ring-slate-200" data-step="{{ $id }}">{{ $label }}</button>
                @endforeach
            </div>
            <section id="identite" class="card space-y-4 p-5">
                <h2 class="text-lg font-semibold">1. Informations personnelles</h2>
                <div><label class="label" for="title">Titre du document</label><input class="field" id="title" name="title" value="{{ $form['title'] }}" required></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'headline' => 'Titre visé', 'email' => 'E-mail', 'phone' => 'Téléphone', 'city' => 'Ville', 'country' => 'Pays', 'address' => 'Adresse', 'linkedin' => 'LinkedIn', 'website' => 'Site'] as $key => $label)
                        <div class="{{ $key === 'address' ? 'sm:col-span-2' : '' }}"><label class="label" for="personal_{{ $key }}">{{ $label }}</label><input class="field" id="personal_{{ $key }}" name="personal[{{ $key }}]" value="{{ $form['personal'][$key] ?? '' }}"></div>
                    @endforeach
                </div>
            </section>
            <section id="resume" class="card space-y-3 p-5">
                <h2 class="text-lg font-semibold">2. Résumé professionnel</h2>
                <textarea class="field min-h-32" name="summary">{{ $form['summary'] }}</textarea>
            </section>
            @php $stepNo = 3; @endphp
            @foreach (\App\Support\ResumeContent::sections() as $section)
                <section id="{{ $section }}" class="card space-y-4 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold">{{ $stepNo }}. {{ \App\Support\ResumeContent::labels()[$section]['fr'] }}</h2>
                        @if (\App\Support\ResumeContent::labels()[$section]['optional'])
                            <span class="text-xs text-slate-500">Facultatif</span>
                        @endif
                    </div>
                    @if ($section === 'references')
                        <p class="text-sm text-slate-600">N'indiquez une référence qu'avec l'accord de la personne.</p>
                    @endif
                    @foreach ($form[$section] as $index => $item)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="mb-3 flex flex-wrap justify-end gap-2">
                                @if ($index > 0)
                                    <button class="btn btn-ghost" name="action" value="move:{{ $section }}:{{ $index }}:up" type="submit">Monter</button>
                                @endif
                                @if ($index < count($form[$section]) - 1)
                                    <button class="btn btn-ghost" name="action" value="move:{{ $section }}:{{ $index }}:down" type="submit">Descendre</button>
                                @endif
                                <button class="btn btn-ghost" name="action" value="remove:{{ $section }}:{{ $index }}" type="submit">Supprimer</button>
                            </div>
                            <input type="hidden" name="{{ $section }}[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach (\App\Support\ResumeContent::fields($section) as $field)
                                    <div class="{{ $field['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                                        @if ($field['type'] === 'checkbox')
                                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="1" @checked($item[$field['name']] ?? false)> {{ $field['label'] }}</label>
                                        @elseif ($field['type'] === 'textarea')
                                            <label class="label">{{ $field['label'] }}</label>
                                            <textarea class="field min-h-24" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]">{{ $item[$field['name']] ?? '' }}</textarea>
                                        @else
                                            <label class="label">{{ $field['label'] }}</label>
                                            <input class="field" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="{{ $item[$field['name']] ?? '' }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <button class="btn btn-secondary" name="action" value="add:{{ $section }}" type="submit">Ajouter</button>
                </section>
                @php $stepNo++; @endphp
            @endforeach
            <section class="card space-y-4 p-5">
                <h2 class="text-lg font-semibold">Modèle et couleurs</h2>
                <label class="label" for="template_id">Modèle</label>
                <select class="field" id="template_id" name="template_id">
                    @foreach ($templates as $template)
                        @php $allowed = ! $template->is_premium || $quota->allows(auth()->user(), 'premium_templates'); @endphp
                        <option value="{{ $template->id }}" @selected($form['template_id'] == $template->id) @disabled(! $allowed && $form['template_id'] != $template->id)>{{ $template->name }}{{ $template->is_premium ? ' · premium' : '' }}</option>
                    @endforeach
                </select>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label" for="primary">Couleur principale</label><input class="field" id="primary" name="theme[primary]" value="{{ $form['theme']['primary'] ?? '#2563EB' }}"></div>
                    <div><label class="label" for="accent">Couleur d'accent</label><input class="field" id="accent" name="theme[accent]" value="{{ $form['theme']['accent'] ?? '#1F2937' }}"></div>
                </div>
                <label class="label" for="status">Statut</label>
                <select class="field" id="status" name="status"><option value="draft" @selected($form['status'] === 'draft')>Brouillon</option><option value="ready" @selected($form['status'] === 'ready')>Prêt</option></select>
                <button class="btn btn-primary" type="submit">Enregistrer</button>
            </section>
        </form>
        <aside class="space-y-4 xl:sticky xl:top-20 xl:self-start">
            <div class="card overflow-hidden">
                <p class="border-b border-slate-100 px-4 py-2 text-sm font-medium">Aperçu</p>
                <iframe data-preview title="Aperçu du CV" src="{{ route('cvs.preview', $resume) }}" class="h-[36rem] w-full bg-white"></iframe>
            </div>
            <div class="card p-4 text-sm">
                <p class="font-semibold">Export</p>
                @if ($quota->allows(auth()->user(), 'export_pdf'))
                    <a class="mt-2 block text-brand-700" href="{{ route('cvs.export', [$resume, 'pdf']) }}">Télécharger le PDF</a>
                @else
                    <a class="mt-2 block text-brand-700" href="{{ route('billing.index') }}">PDF disponible avec une offre supérieure</a>
                @endif
                @if ($quota->allows(auth()->user(), 'export_docx'))
                    <a class="mt-1 block text-brand-700" href="{{ route('cvs.export', [$resume, 'docx']) }}">Télécharger le Word</a>
                @else
                    <a class="mt-1 block text-brand-700" href="{{ route('billing.index') }}">Word disponible avec une offre supérieure</a>
                @endif
            </div>
        </aside>
    </div>
@endsection
