@extends('layouts.app')
@section('document-title', 'Assistant IA')
@section('content')
    <h1 class="text-3xl font-semibold">Assistant IA</h1>
    <p class="mt-2 text-slate-600">Crédits restants : {{ $credits }}. Les propositions s'affichent ici et ne modifient rien tant que vous ne les appliquez pas dans l'éditeur.</p>
    @unless ($configured)
        <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Le fournisseur d'IA n'est pas configuré. Ajoutez AI_API_KEY sur le serveur. Aucune clé n'est envoyée au navigateur.</p>
    @endunless
    @if (session('proposal'))
        <section class="card mt-4 p-5 text-sm">
            <h2 class="font-semibold">Proposition</h2>
            @if (! session('proposal.successful'))<p class="mt-2">{{ session('proposal.error') }}</p>@endif
            <pre class="mt-2 whitespace-pre-wrap">{{ json_encode(session('proposal.data'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @foreach (session('proposal.missing') ?? [] as $missing)<p class="text-amber-800">Information manquante : {{ $missing }}</p>@endforeach
            @foreach (session('proposal.warnings') ?? [] as $warning)<p>{{ $warning }}</p>@endforeach
        </section>
    @endif
    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <form method="POST" action="{{ route('assistant.summary') }}" class="card space-y-3 p-5" data-ai-form>@csrf<h2 class="font-semibold">Résumé</h2><textarea class="field min-h-28" name="text" placeholder="Collez votre résumé actuel"></textarea><button class="btn btn-primary" type="submit">Proposer une reformulation</button></form>
        <form method="POST" action="{{ route('assistant.grammar') }}" class="card space-y-3 p-5" data-ai-form>@csrf<h2 class="font-semibold">Correction</h2><textarea class="field min-h-28" name="text"></textarea><button class="btn btn-primary" type="submit">Corriger</button></form>
        <form method="POST" action="{{ route('assistant.tone') }}" class="card space-y-3 p-5" data-ai-form>@csrf<h2 class="font-semibold">Ton</h2><select class="field" name="tone"><option value="professionnel">Professionnel</option><option value="classique">Classique</option><option value="direct">Direct</option><option value="debutant">Débutant</option></select><textarea class="field min-h-24" name="text"></textarea><button class="btn btn-primary" type="submit">Adapter le ton</button></form>
        <form method="POST" action="{{ route('assistant.refresh') }}" class="card space-y-3 p-5" data-ai-form>@csrf<h2 class="font-semibold">Actualiser un CV</h2><select class="field" name="resume_id">@foreach ($resumes as $resume)<option value="{{ $resume->id }}">{{ $resume->title }}</option>@endforeach</select><button class="btn btn-primary" type="submit">Proposer une actualisation</button></form>
    </div>
@endsection
