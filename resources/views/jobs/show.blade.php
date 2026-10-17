@extends('layouts.app')
@section('document-title', $offer->title)
@section('content')
    <h1 class="text-3xl font-semibold">{{ $offer->title }}</h1>
    <p class="text-slate-600">{{ $offer->company }}</p>
    <form method="POST" action="{{ route('jobs.update', $offer) }}" class="card mt-4 space-y-3 p-5">@csrf @method('PUT')<input class="field" name="title" value="{{ $offer->title }}" required><input class="field" name="company" value="{{ $offer->company }}"><textarea class="field min-h-40" name="description" required>{{ $offer->description }}</textarea><button class="btn btn-secondary" type="submit">Mettre à jour le texte</button></form>
    <form method="POST" action="{{ route('assistant.analyze') }}" class="mt-4" data-ai-form>@csrf<input type="hidden" name="job_offer_id" value="{{ $offer->id }}"><label class="label">Comparer avec un CV</label><select class="field" name="resume_id">@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select><button class="btn btn-primary mt-3" type="submit">Analyser l'offre</button></form>
    @if ($offer->analysis)
        <section class="card mt-4 p-5 text-sm">
            <h2 class="text-lg font-semibold">Analyse enregistrée</h2>
            <p class="mt-2">Cette analyse ne modifie pas le CV.</p>
            <p class="mt-2">Compétences repérées : {{ implode(', ', $offer->analysis['required_skills'] ?? []) ?: '—' }}</p>
            <p>Déjà présentes : {{ implode(', ', $offer->analysis['matched_skills'] ?? []) ?: '—' }}</p>
            <p>Absentes du CV : {{ implode(', ', $offer->analysis['missing_skills'] ?? []) ?: '—' }}</p>
            @foreach ($offer->analysis['suggestions'] ?? [] as $suggestion)<p class="mt-1">{{ $suggestion }}</p>@endforeach
            @foreach ($offer->analysis['missing_information'] ?? [] as $missing)<p class="text-amber-800">Information manquante : {{ $missing }}</p>@endforeach
            <form method="POST" action="{{ route('jobs.adapt', $offer) }}" class="mt-3">@csrf<select class="field" name="resume_id">@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select><button class="btn btn-secondary mt-2" type="submit">Créer une copie à adapter</button></form>
            <form method="POST" action="{{ route('jobs.analysis.clear', $offer) }}" class="mt-2">@csrf @method('DELETE')<button class="text-sm text-slate-600" type="submit">Retirer l'analyse</button></form>
        </section>
    @endif
    <form method="POST" action="{{ route('jobs.destroy', $offer) }}" class="mt-6">@csrf @method('DELETE')<button class="btn btn-ghost" type="submit">Supprimer l'offre</button></form>
@endsection
