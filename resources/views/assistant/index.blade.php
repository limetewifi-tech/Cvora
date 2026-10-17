@extends('layouts.app')
@section('document-title', 'Assistant IA')
@section('content')
    <h1 class="h2">Assistant IA</h1>
    <p class="text-secondary">Crédits restants : {{ $credits }}. Les propositions s'affichent ici et ne modifient rien tant que vous ne les appliquez pas dans l'éditeur.</p>
    @unless ($configured)
        <div class="alert alert-warning mt-3">Le fournisseur d'IA n'est pas configuré. Ajoutez ANTHROPIC_API_KEY, GEMINI_API_KEY ou GROQ_API_KEY sur le serveur, avec l'identifiant de modèle correspondant. Aucune clé n'est envoyée au navigateur.</div>
    @endunless
    @if (session('proposal'))
        <section class="card mt-3">
            <div class="card-body">
                <h2 class="h5">Proposition</h2>
                @if (! session('proposal.successful'))<p>{{ session('proposal.error') }}</p>@endif
                <pre class="mb-0">{{ json_encode(session('proposal.data'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @foreach (session('proposal.missing') ?? [] as $missing)<p class="text-warning-emphasis mb-0">Information manquante : {{ $missing }}</p>@endforeach
                @foreach (session('proposal.warnings') ?? [] as $warning)<p class="mb-0">{{ $warning }}</p>@endforeach
            </div>
        </section>
    @endif
    <div class="row g-3 mt-2">
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.summary') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Résumé</h2>
                    <textarea class="form-control cvora-textarea" name="text" placeholder="Collez votre résumé actuel"></textarea>
                    <button class="btn btn-primary" type="submit">Proposer une reformulation</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.grammar') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Correction</h2>
                    <textarea class="form-control cvora-textarea" name="text"></textarea>
                    <button class="btn btn-primary" type="submit">Corriger</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.tone') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Ton</h2>
                    <select class="form-select" name="tone">
                        <option value="professionnel">Professionnel</option>
                        <option value="classique">Classique</option>
                        <option value="direct">Direct</option>
                        <option value="debutant">Débutant</option>
                    </select>
                    <textarea class="form-control cvora-textarea" name="text"></textarea>
                    <button class="btn btn-primary" type="submit">Adapter le ton</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.refresh') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Actualiser un CV</h2>
                    <select class="form-select" name="resume_id">
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit">Proposer une actualisation</button>
                </div>
            </form>
        </div>
    </div>
@endsection
