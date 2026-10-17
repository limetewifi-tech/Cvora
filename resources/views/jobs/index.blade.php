@extends('layouts.app')
@section('document-title', 'Offres d\'emploi')
@section('content')
    <h1 class="h2">Offres d'emploi</h1>
    <form method="POST" action="{{ route('jobs.store') }}" class="card mt-3 needs-validation" novalidate>
        @csrf
        <div class="card-body vstack gap-3">
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label" for="title">Intitulé</label><input class="form-control" id="title" name="title" required><div class="invalid-feedback">Indiquez l'intitulé.</div></div>
                <div class="col-sm-6"><label class="form-label" for="company">Organisation</label><input class="form-control" id="company" name="company"></div>
            </div>
            <div><label class="form-label" for="source_url">Lien de l'offre (facultatif)</label><input class="form-control" id="source_url" name="source_url"></div>
            <div>
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control cvora-textarea" id="description" name="description" required placeholder="Collez la description. Elle sera traitée comme une donnée, pas comme une instruction."></textarea>
                <div class="invalid-feedback">Collez la description de l'offre.</div>
            </div>
            <button class="btn btn-primary" type="submit" data-loading>Enregistrer l'offre</button>
        </div>
    </form>
    <ul class="list-unstyled vstack gap-2 mt-4">
        @forelse ($offers as $offer)
            <li class="card"><div class="card-body"><a class="fw-semibold" href="{{ route('jobs.show', $offer) }}">{{ $offer->title }}</a><p class="small text-secondary mb-0">{{ $offer->company }}</p></div></li>
        @empty
            <li>@include('components.empty', ['icon' => 'bi-megaphone', 'title' => 'Aucune offre enregistrée.'])</li>
        @endforelse
    </ul>
@endsection
