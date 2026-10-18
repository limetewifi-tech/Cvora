@php
    $proposal = session('proposal');
    $data = $proposal['data'] ?? [];
    $resumeId = $proposal['resume_id'] ?? null;
@endphp
<section class="card mt-3" aria-live="polite">
    <div class="card-body vstack gap-3">
        <h2 class="h5 mb-0">Résultat</h2>
        @if (! $proposal['successful'])
            <p class="mb-0">{{ $proposal['error'] }}</p>
        @elseif (($proposal['feature'] ?? '') === 'gaps')
            <p class="mb-0">{{ $data['reply'] ?? '' }}</p>
            @if ($resumeId)
                <a class="btn btn-outline-primary align-self-start" href="{{ route('cvs.edit', $resumeId) }}">Ouvrir le CV</a>
            @endif
        @elseif (filled($data['proposal'] ?? null))
            <p class="small text-secondary mb-0">Reformulation du texte déjà saisi. Modifiez-la si besoin, puis appliquez-la. Refuser consiste à ne pas appliquer.</p>
            @if ($resumeId)
                <form method="POST" action="{{ route('cvs.proposal', $resumeId) }}" class="vstack gap-2">
                    @csrf
                    <input type="hidden" name="target" value="summary">
                    <textarea class="form-control cvora-textarea" name="value">{{ $data['proposal'] }}</textarea>
                    <button class="btn btn-primary align-self-start" type="submit">Appliquer au CV</button>
                </form>
            @else
                <p class="mb-0">{{ $data['proposal'] }}</p>
            @endif
        @elseif (filled($data['body'] ?? null))
            <p class="small text-secondary mb-0">Lettre rédigée à partir des faits fournis. Elle n'est enregistrée que si vous le demandez.</p>
            <form method="POST" action="{{ route('letters.store') }}" class="vstack gap-2">
                @csrf
                <input type="hidden" name="tone" value="professionnel">
                <input type="hidden" name="length" value="standard">
                @if ($resumeId)
                    <input type="hidden" name="resume_id" value="{{ $resumeId }}">
                @endif
                <label class="form-label" for="letter_title">Titre</label>
                <input class="form-control" id="letter_title" name="title" value="{{ old('position') ? 'Lettre — '.old('position') : 'Lettre de motivation' }}" required>
                <label class="form-label" for="saved_company">Organisation</label>
                <input class="form-control" id="saved_company" name="company" value="{{ old('company') }}">
                <label class="form-label" for="saved_position">Poste</label>
                <input class="form-control" id="saved_position" name="position" value="{{ old('position') }}">
                <label class="form-label" for="saved_body">Texte</label>
                <textarea class="form-control cvora-textarea" id="saved_body" name="body">{{ $data['body'] }}</textarea>
                <button class="btn btn-primary align-self-start" type="submit">Enregistrer cette lettre</button>
            </form>
        @elseif (isset($data['items']) && is_array($data['items']) && $resumeId)
            <p class="small text-secondary mb-0">Chaque texte reprend un passage déjà écrit. Appliquez seulement ceux que vous validez.</p>
            @foreach ($data['items'] as $item)
                @if (is_array($item) && filled($item['text'] ?? null) && in_array($item['target'] ?? '', ['summary', 'experience', 'education'], true))
                    <form method="POST" action="{{ route('cvs.proposal', $resumeId) }}" class="border rounded p-3 vstack gap-2">
                        @csrf
                        <input type="hidden" name="target" value="{{ $item['target'] }}">
                        @if (is_numeric($item['index'] ?? null))
                            <input type="hidden" name="index" value="{{ (int) $item['index'] }}">
                        @endif
                        <label class="form-label mb-0">{{ ['summary' => 'Résumé', 'experience' => 'Expérience', 'education' => 'Formation'][$item['target']] }}</label>
                        <textarea class="form-control" name="value" rows="4">{{ $item['text'] }}</textarea>
                        <button class="btn btn-sm btn-primary align-self-start" type="submit">Appliquer au CV</button>
                    </form>
                @endif
            @endforeach
        @elseif (isset($data['matched_skills']) || isset($data['missing_skills']))
            <p class="mb-0">Comparaison terminée. Le CV n'a pas été modifié.</p>
            @if (! empty($data['matched_skills']))
                <p class="mb-0">Déjà présentes : {{ implode(', ', $data['matched_skills']) }}</p>
            @endif
            @if (! empty($data['missing_skills']))
                <p class="mb-0">Absentes du CV : {{ implode(', ', $data['missing_skills']) }}. Ne les ajoutez que si vous les possédez.</p>
            @endif
            @if (filled($data['summary_proposal'] ?? null) && $resumeId)
                <form method="POST" action="{{ route('cvs.proposal', $resumeId) }}" class="vstack gap-2">
                    @csrf
                    <input type="hidden" name="target" value="summary">
                    <textarea class="form-control cvora-textarea" name="value">{{ $data['summary_proposal'] }}</textarea>
                    <button class="btn btn-primary align-self-start" type="submit">Appliquer ce résumé</button>
                </form>
            @endif
        @elseif (filled($data))
            <pre class="mb-0">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
        @foreach ($proposal['missing'] ?? [] as $missing)
            <p class="text-warning-emphasis mb-0">Information manquante : {{ $missing }}</p>
        @endforeach
        @foreach ($proposal['warnings'] ?? [] as $warning)
            <p class="mb-0">{{ $warning }}</p>
        @endforeach
        @if (isset($proposal['credits_remaining']))
            <p class="small text-secondary mb-0">Crédits restants : {{ $proposal['credits_remaining'] }}.</p>
        @endif
    </div>
</section>
