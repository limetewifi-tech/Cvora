@extends('layouts.app')
@section('document-title', $analysis->offer_title)
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="h2">{{ $analysis->offer_title }}</h1>
            <p class="text-secondary mb-0">{{ $analysis->company }} · CV : {{ $analysis->resume?->title ?? 'CV retiré' }}</p>
        </div>
        <button class="btn btn-outline-primary no-print" type="button" data-print>Imprimer le résultat</button>
    </div>
    <section class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <p class="cvora-score mb-0" aria-label="{{ $analysis->score === null ? 'Score non calculé' : 'Score '.$analysis->score.' sur 100' }}">{{ $analysis->score === null ? '—' : $analysis->score }}</p>
                <div>
                    <h2 class="h5">Score local</h2>
                    <p class="mb-0">{{ $analysis->result['explanation'] ?? '' }}</p>
                </div>
            </div>
            <p class="small text-secondary mt-3 mb-0">{{ $analysis->result['method'] ?? '' }}</p>
        </div>
    </section>
    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Déjà dans le CV</h2>
                    @forelse ($analysis->result['matched'] ?? [] as $item)
                        <p class="mb-1">{{ $item }}</p>
                    @empty
                        <p class="text-secondary mb-0">Aucune exigence de l'offre n'est écrite dans le CV.</p>
                    @endforelse
                </div>
            </section>
        </div>
        <div class="col-md-6">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Absentes du CV</h2>
                    @forelse ($analysis->result['missing'] ?? [] as $item)
                        <p class="mb-1">{{ $item }}</p>
                    @empty
                        <p class="text-secondary mb-0">Aucune exigence identifiable ne manque, ou l'offre n'a pas fourni d'exigence.</p>
                    @endforelse
                    <p class="small text-secondary mt-3 mb-0">Ces éléments ne sont pas ajoutés au CV.</p>
                </div>
            </section>
        </div>
    </div>
    <section class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Expériences en lien</h2>
            @forelse ($analysis->result['relevant_experiences'] ?? [] as $experience)
                <p class="mb-1"><span class="fw-semibold">{{ $experience['title'] }}</span> {{ $experience['company'] ? '· '.$experience['company'] : '' }} — {{ $experience['reason'] }}</p>
            @empty
                <p class="text-secondary mb-0">Aucune expérience enregistrée ne reprend les exigences présentes.</p>
            @endforelse
        </div>
    </section>
    <section class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Suggestions du fournisseur</h2>
            @if ($analysis->ai_status === 'success' && $analysis->ai_suggestions)
                @foreach ($analysis->ai_suggestions['suggestions'] ?? [] as $suggestion)
                    <p class="mb-1">{{ $suggestion }}</p>
                @endforeach
                <form method="POST" action="{{ route('studio.apply', $analysis) }}" class="vstack gap-3 mt-3 no-print needs-validation" novalidate>
                    @csrf
                    @if (filled($analysis->ai_suggestions['summary_proposal'] ?? null))
                        <div>
                            <label class="form-label" for="summary_proposal">Proposition de résumé</label>
                            <textarea class="form-control cvora-textarea" id="summary_proposal" readonly>{{ $analysis->ai_suggestions['summary_proposal'] }}</textarea>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="apply_summary" id="apply_summary" value="1">
                                <label class="form-check-label" for="apply_summary">Appliquer ce résumé</label>
                            </div>
                        </div>
                    @endif
                    @foreach ($analysis->ai_suggestions['experiences'] ?? [] as $experience)
                        <div>
                            <label class="form-label" for="experience-{{ $experience['index'] }}">Proposition pour l'expérience {{ $experience['index'] + 1 }}</label>
                            <textarea class="form-control cvora-textarea" id="experience-{{ $experience['index'] }}" readonly>{{ $experience['description'] }}</textarea>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="experience_indexes[]" id="apply-experience-{{ $experience['index'] }}" value="{{ $experience['index'] }}">
                                <label class="form-check-label" for="apply-experience-{{ $experience['index'] }}">Appliquer cette description</label>
                            </div>
                        </div>
                    @endforeach
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm" id="confirm" value="1" required>
                        <label class="form-check-label" for="confirm">J'ai relu ces propositions et je confirme leur application.</label>
                    </div>
                    <button class="btn btn-primary" type="submit" data-loading>Appliquer la sélection</button>
                </form>
            @elseif ($analysis->ai_status === 'failed')
                <div class="alert alert-warning mb-0">{{ $analysis->ai_error }} Aucun score ni aucune suggestion n'a été inventé à la place de cette réponse.</div>
            @else
                <p class="text-secondary mb-0">{{ $analysis->ai_error ?: 'Aucune suggestion IA n\'a été demandée. Seule la comparaison locale est affichée.' }}</p>
            @endif
        </div>
    </section>
    <p class="mt-3 no-print"><a href="{{ route('studio.index') }}">Retour au studio</a></p>
@endsection
