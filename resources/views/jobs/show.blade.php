@extends('layouts.app')
@section('document-title', $offer->title)
@section('content')
    <h1 class="h2">{{ $offer->title }}</h1>
    <p class="text-secondary">{{ $offer->company }}</p>
    <form method="POST" action="{{ route('jobs.update', $offer) }}" class="card mt-3 needs-validation" novalidate>
        @csrf @method('PUT')
        <div class="card-body vstack gap-3">
            <div><label class="form-label">Intitulé</label><input class="form-control" name="title" value="{{ $offer->title }}" required></div>
            <div><label class="form-label">Organisation</label><input class="form-control" name="company" value="{{ $offer->company }}"></div>
            <div><label class="form-label">Description</label><textarea class="form-control cvora-textarea-lg" name="description" required>{{ $offer->description }}</textarea></div>
            <button class="btn btn-outline-primary" type="submit" data-loading>Mettre à jour le texte</button>
        </div>
    </form>
    <form method="POST" action="{{ route('assistant.analyze') }}" class="card mt-3" data-ai-form>
        @csrf
        <div class="card-body">
            <input type="hidden" name="job_offer_id" value="{{ $offer->id }}">
            <label class="form-label" for="resume_id">Comparer avec un CV</label>
            <select class="form-select" id="resume_id" name="resume_id" required>
                @foreach ($resumes as $resume)
                    <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                @endforeach
            </select>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_resume" value="1" required>
                <label class="form-check-label" for="authorize_resume">J'autorise l'utilisation de ce CV.</label>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Analyser l'offre</button>
        </div>
    </form>
    @if ($offer->analysis)
        <section class="card mt-3">
            <div class="card-body">
                <h2 class="h5">Analyse enregistrée</h2>
                <p>Cette analyse ne modifie pas le CV.</p>
                <p class="mb-1">Compétences repérées : {{ implode(', ', $offer->analysis['required_skills'] ?? []) ?: '—' }}</p>
                <p class="mb-1">Déjà présentes : {{ implode(', ', $offer->analysis['matched_skills'] ?? []) ?: '—' }}</p>
                <p class="mb-1">Absentes du CV : {{ implode(', ', $offer->analysis['missing_skills'] ?? []) ?: '—' }}</p>
                @foreach ($offer->analysis['suggestions'] ?? [] as $suggestion)<p class="mb-1">{{ $suggestion }}</p>@endforeach
                @foreach ($offer->analysis['missing_information'] ?? [] as $missing)<p class="text-warning-emphasis mb-1">Information manquante : {{ $missing }}</p>@endforeach
                <form method="POST" action="{{ route('jobs.adapt', $offer) }}" class="mt-3">
                    @csrf
                    <label class="form-label" for="adapt_resume">Créer une copie à partir de</label>
                    <select class="form-select" id="adapt_resume" name="resume_id">
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-primary mt-2" type="submit">Créer une copie à adapter</button>
                </form>
                <form method="POST" action="{{ route('jobs.analysis.clear', $offer) }}" class="mt-3">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Retirer l'analyse</button>
                </form>
            </div>
        </section>
    @endif
    <div class="mt-4">
        <x-confirm-form id="delete-offer" title="Supprimer cette offre" :action="route('jobs.destroy', $offer)" trigger="Supprimer l'offre" submit="Supprimer l'offre">
            <p class="mb-0">L'offre et son analyse seront retirées de votre compte.</p>
        </x-confirm-form>
    </div>
@endsection
