@extends('layouts.app')
@section('document-title', 'Studio CV')
@section('content')
    <h1 class="h2">Studio CV</h1>
    <p class="text-secondary">Comparez un CV déjà enregistré au texte d'une offre. Le score est calculé localement. Le CV ne change qu'après votre confirmation.</p>
    @if ($resumes->isEmpty())
        @include('components.empty', ['icon' => 'bi-file-earmark-text', 'title' => 'Enregistrez d\'abord un CV.', 'action' => 'Créer un CV', 'href' => route('cvs.create')])
    @else
        <form method="POST" action="{{ route('studio.store') }}" class="card mt-3 needs-validation" novalidate data-ai-form>
            @csrf
            <div class="card-body vstack gap-3">
                <div>
                    <label class="form-label" for="resume_id">CV à comparer</label>
                    <select class="form-select" id="resume_id" name="resume_id" required>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}" @selected((string) old('resume_id') === (string) $resume->id)>{{ $resume->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="offer_title">Intitulé de l'offre</label>
                    <input class="form-control" id="offer_title" name="offer_title" value="{{ old('offer_title') }}" maxlength="160" required>
                </div>
                <div>
                    <label class="form-label" for="company">Organisation</label>
                    <input class="form-control" id="company" name="company" value="{{ old('company') }}" maxlength="160">
                </div>
                <div>
                    <label class="form-label" for="offer_text">Texte de l'offre</label>
                    <textarea class="form-control cvora-textarea-lg" id="offer_text" name="offer_text" required>{{ old('offer_text') }}</textarea>
                    <p class="form-text">Écrivez les compétences demandées une par ligne, ou séparez-les par des virgules.</p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_resume" value="1" @checked(old('authorize_resume')) required>
                    <label class="form-check-label" for="authorize_resume">J'autorise l'utilisation de ce CV pour cette comparaison.</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="use_ai" id="use_ai" value="1" @checked(old('use_ai')) @disabled(! $canUseAi)>
                    <label class="form-check-label" for="use_ai">Demander aussi des suggestions au fournisseur d'IA{{ $canUseAi ? '' : ' (offre Pro)' }}</label>
                </div>
                <button class="btn btn-primary" type="submit" data-loading>Comparer</button>
            </div>
        </form>
    @endif
    <h2 class="h5 mt-4">Analyses enregistrées</h2>
    <ul class="list-unstyled vstack gap-2 mt-3">
        @forelse ($analyses as $analysis)
            <li class="card">
                <div class="card-body d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <a class="fw-semibold" href="{{ route('studio.show', $analysis) }}">{{ $analysis->offer_title }}</a>
                        <p class="small text-secondary mb-0">{{ $analysis->company }} · {{ $analysis->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="mb-0 fw-semibold">{{ $analysis->score === null ? 'Sans score' : $analysis->score.' / 100' }}</p>
                </div>
            </li>
        @empty
            <li>@include('components.empty', ['icon' => 'bi-bullseye', 'title' => 'Aucune comparaison pour le moment.'])</li>
        @endforelse
    </ul>
@endsection
