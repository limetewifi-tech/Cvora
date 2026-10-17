@extends('layouts.app')
@section('document-title', 'Offres d\'emploi')
@section('content')
    <h1 class="text-3xl font-semibold">Offres d'emploi</h1>
    <form method="POST" action="{{ route('jobs.store') }}" class="card mt-6 space-y-3 p-5">
        @csrf
        <div class="grid gap-3 sm:grid-cols-2"><input class="field" name="title" placeholder="Intitulé" required><input class="field" name="company" placeholder="Organisation"></div>
        <input class="field" name="source_url" placeholder="Lien de l'offre (facultatif)">
        <textarea class="field min-h-32" name="description" placeholder="Collez la description. Elle sera traitée comme une donnée, pas comme une instruction." required></textarea>
        <button class="btn btn-primary" type="submit">Enregistrer l'offre</button>
    </form>
    <ul class="mt-6 space-y-2">@foreach ($offers as $offer)<li class="card p-4"><a class="font-semibold text-brand-700" href="{{ route('jobs.show', $offer) }}">{{ $offer->title }}</a><p class="text-sm text-slate-500">{{ $offer->company }}</p></li>@endforeach</ul>
@endsection
