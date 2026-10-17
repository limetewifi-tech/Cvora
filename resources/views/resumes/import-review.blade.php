@extends('layouts.app')
@section('document-title', 'Vérifier l\'import')
@section('content')
    <h1 class="h2">Vérifier les informations détectées</h1>
    @foreach ($import->warnings ?? [] as $warning)
        <div class="alert alert-warning mt-3">{{ $warning }}</div>
    @endforeach
    @if ($import->path)
        <p class="mt-3"><a class="fw-semibold" href="{{ route('imports.file', $import) }}">Télécharger le fichier original</a></p>
    @endif
    <p class="text-secondary">Champs repérés : {{ $detected ? implode(', ', $detected) : 'aucun champ fiable' }}.</p>
    <form method="POST" action="{{ route('imports.confirm', $import) }}" class="card mt-3 needs-validation" novalidate>
        @csrf
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label" for="title">Titre du nouveau document</label>
                <input class="form-control" id="title" name="title" value="{{ old('title', 'CV importé') }}" required>
                <div class="invalid-feedback">Donnez un titre au document.</div>
            </div>
            <div class="row g-3">
                @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'headline' => 'Titre', 'email' => 'E-mail', 'phone' => 'Téléphone', 'city' => 'Ville', 'country' => 'Pays'] as $key => $label)
                    <div class="col-sm-6">
                        <label class="form-label">{{ $label }}</label>
                        <input class="form-control" name="personal[{{ $key }}]" value="{{ $personal[$key] ?? '' }}">
                    </div>
                @endforeach
            </div>
            <div>
                <label class="form-label">Résumé</label>
                <textarea class="form-control cvora-textarea" name="summary">{{ $summary }}</textarea>
            </div>
            @foreach (\App\Support\ResumeContent::sections() as $section)
                <fieldset class="border rounded p-3">
                    <legend class="float-none w-auto px-2 fs-6 fw-semibold">{{ \App\Support\ResumeContent::labels()[$section]['fr'] }}</legend>
                    @forelse ($content[$section] ?? [] as $index => $item)
                        <div class="row g-2 mt-1">
                            <input type="hidden" name="{{ $section }}[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                            @foreach (\App\Support\ResumeContent::fields($section) as $field)
                                @if ($field['type'] !== 'checkbox')
                                    <div class="col-sm-6">
                                        <input class="form-control" name="{{ $section }}[{{ $index }}][{{ $field['name'] }}]" value="{{ $item[$field['name']] ?? '' }}" placeholder="{{ $field['label'] }}" aria-label="{{ $field['label'] }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @empty
                        <p class="small text-secondary mb-0">Rien de détecté. Vous pourrez compléter dans l'éditeur.</p>
                    @endforelse
                </fieldset>
            @endforeach
            <fieldset>
                <legend class="form-label mb-1">Modèle pour ce profil</legend>
                <p class="small text-secondary">{{ $suggestion['reason'] }}</p>
                @php
                    $selectedTemplate = old('template_id')
                        ? $templates->firstWhere('id', (int) old('template_id'))
                        : $templates->firstWhere('slug', $suggestion['slug']);
                @endphp
                <div class="vstack gap-2">
                    @foreach ($templates as $template)
                        @php $allowed = ! $template->is_premium || $premiumTemplates; @endphp
                        <label class="d-flex gap-3 border rounded p-3 {{ $allowed ? '' : 'opacity-75' }}">
                            <input class="form-check-input mt-1" type="radio" name="template_id" value="{{ $template->id }}" @checked(($selectedTemplate?->id ?? null) == $template->id) @disabled(! $allowed)>
                            <span>
                                <span class="fw-semibold">{{ $template->name }}</span>
                                @if ($template->slug === $suggestion['slug'])
                                    <span class="badge text-bg-primary ms-1">Proposé</span>
                                @endif
                                <span class="d-block small text-secondary">{{ $template->description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember_template" id="remember_template" value="1" @checked(old('remember_template'))>
                <label class="form-check-label" for="remember_template">Retenir ce modèle pour les prochains CV.</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="confirmed" id="confirmed" value="1" required>
                <label class="form-check-label" for="confirmed">Je confirme ou je corrige ces informations avant l'enregistrement.</label>
                <div class="invalid-feedback">Confirmez les informations avant l'enregistrement.</div>
            </div>
            <div>
                <label class="form-label" for="resume_id">Mettre à jour un CV existant (facultatif)</label>
                <select class="form-select" id="resume_id" name="resume_id">
                    <option value="">Créer un nouveau CV</option>
                    @foreach ($resumes as $resume)
                        <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="overwrite_confirmed" id="overwrite_confirmed" value="1">
                <label class="form-check-label" for="overwrite_confirmed">Je confirme le remplacement du CV sélectionné. Sans cette case, rien n'est écrasé.</label>
            </div>
            <button class="btn btn-primary" type="submit" data-loading>Enregistrer les données validées</button>
        </div>
    </form>
@endsection
