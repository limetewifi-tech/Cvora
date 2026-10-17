@extends('layouts.app')
@section('document-title', 'Assistant IA')
@section('content')
    <h1 class="h2">Assistant IA</h1>
    <p class="text-secondary">Décrivez le résultat voulu. L'assistant agit sur le CV choisi et vous montre un texte à accepter, modifier ou ignorer. Crédits restants : {{ $credits }}.</p>
    @if ($resumes->isEmpty())
        @include('components.empty', ['icon' => 'bi-stars', 'title' => 'Créez d\'abord un CV pour donner un contexte à l\'assistant.', 'action' => 'Commencer', 'href' => route('start.index')])
    @else
        <form method="POST" action="{{ route('assistant.ask') }}" class="card mt-3 needs-validation" novalidate data-ai-form>
            @csrf
            <div class="card-body vstack gap-3">
                <div>
                    <label class="form-label" for="ask_resume">CV concerné</label>
                    <select class="form-select" id="ask_resume" name="resume_id" required>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}" @selected((string) old('resume_id', $selectedResumeId) === (string) $resume->id)>{{ $resume->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="ask_message">Demande</label>
                    <textarea class="form-control cvora-textarea" id="ask_message" name="message" required maxlength="2000" placeholder="Exemples : Rends mon résumé plus professionnel. Corrige les fautes. Identifie les points faibles. Crée une lettre de motivation.">{{ old('message') }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="ask_position">Poste, pour une lettre</label>
                        <input class="form-control" id="ask_position" name="position" value="{{ old('position') }}" maxlength="160">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="ask_company">Organisation, pour une lettre</label>
                        <input class="form-control" id="ask_company" name="company" value="{{ old('company') }}" maxlength="160">
                    </div>
                </div>
                <div>
                    <label class="form-label" for="ask_offer">Texte de l'offre, si vous voulez adapter le CV</label>
                    <textarea class="form-control" id="ask_offer" name="offer" rows="4" maxlength="8000">{{ old('offer') }}</textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_ask" value="1" required @checked(old('authorize_resume'))>
                    <label class="form-check-label" for="authorize_ask">J'autorise l'utilisation de ce CV pour cette demande.</label>
                    <div class="invalid-feedback">Autorisez l'utilisation du CV.</div>
                </div>
                <button class="btn btn-primary align-self-start" type="submit">Lancer</button>
                <p class="small text-secondary mb-0">Une demande qui reformule ou corrige réserve un crédit. Repérer les manques n'en utilise pas. Le CV change seulement avec « Appliquer au CV ».</p>
            </div>
        </form>
    @endif
    <ul class="list-unstyled vstack gap-2 mt-3">
        @foreach ($providers as $name => $provider)
            <li class="card"><div class="card-body py-3">{{ $name }} · {{ ['configured' => 'configuré', 'incomplete' => 'incomplet', 'absent' => 'non configuré'][$provider['state']] }} · clé {{ $provider['key_present'] ? 'présente' : 'absente' }} · modèle {{ $provider['model'] !== '' ? $provider['model'] : 'absent' }}</div></li>
        @endforeach
    </ul>
    @if (! $driverSupported)
        <div class="alert alert-warning mt-3" role="status">Le fournisseur « {{ $driver }} » n'est pas pris en charge. Utilisez anthropic, gemini ou groq. Aucun autre fournisseur n'est appelé.</div>
    @elseif (! $configured)
        <div class="alert alert-warning mt-3" role="status">Le fournisseur sélectionné est incomplet ou non configuré. Une clé seule ne suffit pas : l'identifiant de modèle doit aussi être renseigné sur le serveur. Aucune clé n'est envoyée au navigateur.</div>
    @endif
    @if (session('proposal'))
        @include('assistant.result')
    @endif
    <details class="mt-4">
        <summary class="fw-semibold">Outils séparés</summary>
        <div class="row g-3 mt-2">
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.summary') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Résumé</h2>
                    <label class="form-label" for="summary_text">Texte actuel</label>
                    <textarea class="form-control cvora-textarea" id="summary_text" name="text" placeholder="Collez votre résumé actuel"></textarea>
                    <button class="btn btn-primary" type="submit">Proposer une reformulation</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.grammar') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Correction</h2>
                    <label class="form-label" for="grammar_text">Texte à corriger</label>
                    <textarea class="form-control cvora-textarea" id="grammar_text" name="text"></textarea>
                    <button class="btn btn-primary" type="submit">Corriger</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.tone') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Ton</h2>
                    <label class="form-label" for="tone">Ton souhaité</label>
                    <select class="form-select" id="tone" name="tone">
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
                    <label class="form-label" for="refresh_resume">CV</label>
                    <select class="form-select" id="refresh_resume" name="resume_id" required>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_refresh" value="1" required>
                        <label class="form-check-label" for="authorize_refresh">J'autorise l'utilisation de ce CV.</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Proposer une actualisation</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.experiences') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Expériences</h2>
                    <label class="form-label" for="experience_resume">CV</label>
                    <select class="form-select" id="experience_resume" name="resume_id" required>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_experiences" value="1" required>
                        <label class="form-check-label" for="authorize_experiences">J'autorise l'utilisation de ce CV.</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Reformuler les descriptions</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.skills') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Compétences</h2>
                    <label class="form-label" for="skill_resume">CV</label>
                    <select class="form-select" id="skill_resume" name="resume_id" required>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_skills" value="1" required>
                        <label class="form-check-label" for="authorize_skills">J'autorise l'utilisation de ce CV.</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Reformuler les intitulés</button>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <form method="POST" action="{{ route('assistant.letter') }}" class="card h-100" data-ai-form>
                @csrf
                <div class="card-body vstack gap-3">
                    <h2 class="h5">Lettre de motivation</h2>
                    <label class="form-label" for="letter_position">Poste</label>
                    <input class="form-control" id="letter_position" name="position" maxlength="160">
                    <label class="form-label" for="letter_company">Organisation</label>
                    <input class="form-control" id="letter_company" name="company" maxlength="160">
                    <label class="form-label" for="letter_resume">CV, facultatif</label>
                    <select class="form-select" id="letter_resume" name="resume_id">
                        <option value="">Ne pas utiliser de CV</option>
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="authorize_resume" id="authorize_letter" value="1">
                        <label class="form-check-label" for="authorize_letter">J'autorise l'utilisation du CV sélectionné.</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Proposer une lettre</button>
                </div>
            </form>
        </div>
    </div>
        <p class="mt-3"><a href="{{ route('studio.index') }}">Ouvrir le Studio CV pour comparer une offre</a></p>
    </details>
@endsection
