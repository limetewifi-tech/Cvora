@extends('layouts.app')
@section('document-title', $letter->exists ? $letter->title : 'Nouvelle lettre')
@section('content')
    <h1 class="text-3xl font-semibold">{{ $letter->exists ? 'Modifier la lettre' : 'Nouvelle lettre' }}</h1>
    @if (session('proposal'))
        <div class="card mt-4 p-4 text-sm">
            @if (session('proposal.successful'))
                <p class="font-semibold">Proposition à relire</p>
                <pre class="mt-2 whitespace-pre-wrap">{{ session('proposal.data.body') ?? session('proposal.data.proposal') }}</pre>
                @if ($letter->exists && session('proposal.data.body'))
                    <form method="POST" action="{{ route('letters.apply', $letter) }}" class="mt-3">@csrf<textarea class="field min-h-40" name="body">{{ session('proposal.data.body') }}</textarea><button class="btn btn-primary mt-3" type="submit">Appliquer ce texte confirmé</button></form>
                @endif
            @else
                <p>{{ session('proposal.error') }}</p>
            @endif
            @foreach (session('proposal.missing') ?? [] as $missing)<p class="mt-1 text-amber-800">Information manquante : {{ $missing }}</p>@endforeach
        </div>
    @endif
    <form method="POST" action="{{ $letter->exists ? route('letters.update', $letter) : route('letters.store') }}" class="card mt-6 space-y-4 p-6">
        @csrf
        @if ($letter->exists) @method('PUT') <input type="hidden" name="lock_version" value="{{ $letter->lock_version }}"> @endif
        <div><label class="label">Titre</label><input class="field" name="title" value="{{ old('title', $letter->title) }}" required></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="label">Poste</label><input class="field" name="position" value="{{ old('position', $letter->position) }}"></div>
            <div><label class="label">Organisation</label><input class="field" name="company" value="{{ old('company', $letter->company) }}"></div>
            <div><label class="label">Ton</label><select class="field" name="tone">@foreach (['classique' => 'Classique', 'professionnel' => 'Professionnel', 'direct' => 'Direct', 'debutant' => 'Débutant'] as $value => $label)<option value="{{ $value }}" @selected(old('tone', $letter->tone) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label class="label">Longueur</label><select class="field" name="length">@foreach (['courte' => 'Courte', 'standard' => 'Standard', 'detaillee' => 'Détaillée'] as $value => $label)<option value="{{ $value }}" @selected(old('length', $letter->length) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label class="label">CV source</label><select class="field" name="resume_id"><option value="">Aucun</option>@foreach ($resumes as $resume)<option value="{{ $resume->id }}" @selected(old('resume_id', $letter->resume_id) == $resume->id)>{{ $resume->title }}</option>@endforeach</select></div>
            <div><label class="label">Offre liée</label><select class="field" name="job_offer_id"><option value="">Aucune</option>@foreach ($offers as $offer)<option value="{{ $offer->id }}" @selected(old('job_offer_id', $letter->job_offer_id) == $offer->id)>{{ $offer->title }}</option>@endforeach</select></div>
        </div>
        <div><label class="label">Texte</label><textarea class="field min-h-56" name="body">{{ old('body', $letter->body) }}</textarea></div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </form>
    @if ($letter->exists)
        <form method="POST" action="{{ route('assistant.letter') }}" class="mt-4" data-ai-form>@csrf<input type="hidden" name="position" value="{{ $letter->position }}"><input type="hidden" name="company" value="{{ $letter->company }}"><input type="hidden" name="resume_id" value="{{ $letter->resume_id }}"><input type="hidden" name="tone" value="{{ $letter->tone }}"><input type="hidden" name="length" value="{{ $letter->length }}"><button class="btn btn-secondary" type="submit">Proposer une lettre avec l'IA</button></form>
        <div class="mt-4 flex gap-3 text-sm">
            @if ($canExportPdf)<a href="{{ route('letters.export', [$letter, 'pdf']) }}">PDF</a>@endif
            @if ($canExportDocx)<a href="{{ route('letters.export', [$letter, 'docx']) }}">Word</a>@endif
        </div>
        <form method="POST" action="{{ route('letters.destroy', $letter) }}" class="mt-6">@csrf @method('DELETE')<label class="text-sm"><input type="checkbox" name="confirm" value="1" required> Confirmer la suppression</label><button class="btn btn-ghost" type="submit">Supprimer</button></form>
    @endif
@endsection
