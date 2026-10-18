@extends('layouts.app')
@section('document-title', $letter->exists ? $letter->title : 'Nouvelle lettre')
@section('content')
    <h1 class="h2">{{ $letter->exists ? 'Modifier la lettre' : 'Nouvelle lettre' }}</h1>
    @if (session('proposal'))
        <div class="card mt-3">
            <div class="card-body">
                @if (session('proposal.successful'))
                    <p class="fw-semibold">Proposition à relire</p>
                    <pre class="mb-0">{{ session('proposal.data.body') ?? session('proposal.data.proposal') }}</pre>
                    @if ($letter->exists && session('proposal.data.body'))
                        <form method="POST" action="{{ route('letters.apply', $letter) }}" class="mt-3">
                            @csrf
                            <textarea class="form-control cvora-textarea-lg" name="body">{{ session('proposal.data.body') }}</textarea>
                            <button class="btn btn-primary mt-3" type="submit" data-loading>Appliquer ce texte confirmé</button>
                        </form>
                    @endif
                @else
                    <p class="mb-0">{{ session('proposal.error') }}</p>
                @endif
                @foreach (session('proposal.missing') ?? [] as $missing)
                    <p class="text-warning-emphasis mb-0 mt-2">Information manquante : {{ $missing }}</p>
                @endforeach
            </div>
        </div>
    @endif
    <form method="POST" action="{{ $letter->exists ? route('letters.update', $letter) : route('letters.store') }}" class="card mt-4 needs-validation" novalidate>
        @csrf
        @if ($letter->exists) @method('PUT') <input type="hidden" name="lock_version" value="{{ $letter->lock_version }}"> @endif
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label">Titre</label>
                <input class="form-control" name="title" value="{{ old('title', $letter->title) }}" required>
                <div class="invalid-feedback">Donnez un titre à la lettre.</div>
            </div>
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label">Poste</label><input class="form-control" name="position" value="{{ old('position', $letter->position) }}"></div>
                <div class="col-sm-6"><label class="form-label">Organisation</label><input class="form-control" name="company" value="{{ old('company', $letter->company) }}"></div>
                <div class="col-sm-6">
                    <label class="form-label">Ton</label>
                    <select class="form-select" name="tone">
                        @foreach (['classique' => 'Classique', 'professionnel' => 'Professionnel', 'direct' => 'Direct', 'debutant' => 'Débutant'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('tone', $letter->tone) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Modèle A4</label>
                    <select class="form-select" name="layout">
                        @foreach (\App\Support\LetterLayouts::all() as $value => $layout)
                            <option value="{{ $value }}" @selected(old('layout', $letter->layout ?: 'classique') === $value)>{{ $layout['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Longueur</label>
                    <select class="form-select" name="length">
                        @foreach (['courte' => 'Courte', 'standard' => 'Standard', 'detaillee' => 'Détaillée'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('length', $letter->length) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">CV source</label>
                    <select class="form-select" name="resume_id">
                        <option value="">Aucun</option>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}" @selected(old('resume_id', $letter->resume_id) == $resume->id)>{{ $resume->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Offre liée</label>
                    <select class="form-select" name="job_offer_id">
                        <option value="">Aucune</option>
                        @foreach ($offers as $offer)
                            <option value="{{ $offer->id }}" @selected(old('job_offer_id', $letter->job_offer_id) == $offer->id)>{{ $offer->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Texte</label>
                <textarea class="form-control cvora-textarea-lg" name="body">{{ old('body', $letter->body) }}</textarea>
            </div>
            <button class="btn btn-primary" type="submit" data-loading>Enregistrer</button>
        </div>
    </form>
    @if ($letter->exists)
        <section class="card mt-4 overflow-hidden">
            <div class="d-flex align-items-center justify-content-between gap-2 border-bottom px-3 py-2">
                <p class="mb-0 small fw-semibold">Aperçu A4</p>
                <a class="small" href="{{ route('letters.preview', $letter) }}" target="_blank" rel="noopener">Ouvrir</a>
            </div>
            <div class="cvora-a4-frame">
                <iframe title="Aperçu A4 de la lettre" src="{{ route('letters.preview', $letter) }}?cadre=1"></iframe>
            </div>
            <p class="small text-secondary mb-0 px-3 py-2">Page A4, 210 × 297 mm. L'aperçu se met à jour après l'enregistrement.</p>
        </section>
    @endif
    @if ($letter->exists)
        <form method="POST" action="{{ route('assistant.letter') }}" class="mt-3" data-ai-form>
            @csrf
            <input type="hidden" name="position" value="{{ $letter->position }}">
            <input type="hidden" name="company" value="{{ $letter->company }}">
            <input type="hidden" name="resume_id" value="{{ $letter->resume_id }}">
            <input type="hidden" name="tone" value="{{ $letter->tone }}">
            <input type="hidden" name="length" value="{{ $letter->length }}">
            <button class="btn btn-outline-primary" type="submit">Proposer une lettre avec l'IA</button>
        </form>
        <div class="d-flex gap-3 mt-3">
            @if ($canExportPdf)<a class="btn btn-outline-primary" href="{{ route('letters.export', [$letter, 'pdf']) }}">PDF</a>@endif
            @if ($canExportDocx)<a class="btn btn-outline-primary" href="{{ route('letters.export', [$letter, 'docx']) }}">Word</a>@endif
        </div>
        <div class="mt-4">
            <x-confirm-form id="delete-letter" title="Supprimer cette lettre" :action="route('letters.destroy', $letter)" trigger="Supprimer" submit="Supprimer la lettre">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="confirm" id="confirm-letter" value="1" required>
                    <label class="form-check-label" for="confirm-letter">Confirmer la suppression</label>
                    <div class="invalid-feedback">Cochez la confirmation pour supprimer la lettre.</div>
                </div>
            </x-confirm-form>
        </div>
    @endif
@endsection
