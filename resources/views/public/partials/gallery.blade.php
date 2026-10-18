@php
    $catalog = collect(\App\Support\ResumeTemplateCatalog::definitions())->keyBy('slug');
    $sectors = \App\Support\ResumeTemplateCatalog::sectors();
    $filters = [
        'tous' => 'Tous les styles',
        'classique' => 'CV classiques',
        'moderne' => 'CV modernes',
        'design' => 'CV designs',
        'original' => 'CV originaux',
    ];
    $candidatures = [
        'tous' => 'Toutes les candidatures',
        'emploi' => 'Emploi',
        'stage' => 'Stage',
        'reconversion' => 'Reconversion',
    ];
@endphp
<div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Métiers">
    <button class="btn btn-sm btn-primary rounded-pill" type="button" data-sector-filter="tous" aria-pressed="true">Tous les métiers</button>
    @foreach ($sectors as $key => $label)
        <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-sector-filter="{{ $key }}" aria-pressed="false">{{ $label }}</button>
    @endforeach
</div>
<div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Types de candidature">
    @foreach ($candidatures as $key => $label)
        <button class="btn btn-sm {{ $key === 'tous' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill" type="button" data-candidature-filter="{{ $key }}" aria-pressed="{{ $key === 'tous' ? 'true' : 'false' }}">{{ $label }}</button>
    @endforeach
</div>
<div class="d-flex flex-wrap gap-2 mb-4" role="group" aria-label="Filtres de modèles">
    @foreach ($filters as $key => $label)
        <button class="btn btn-sm {{ $key === 'tous' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill" type="button" data-gallery-filter="{{ $key }}" aria-pressed="{{ $key === 'tous' ? 'true' : 'false' }}">{{ $label }}</button>
    @endforeach
</div>
<div class="row g-4" data-gallery>
    @forelse ($templates as $template)
        @php $meta = $catalog->get($template->slug, []); @endphp
        <div class="col-sm-6 col-lg-3" data-category="{{ $meta['category'] ?? 'moderne' }}" data-sector="{{ $meta['sector'] ?? 'informatique' }}" data-candidature="{{ $meta['candidature'] ?? 'emploi' }}">
            <article class="card h-100 overflow-hidden">
                <a class="cvora-thumb" href="{{ route('templates.example', $template) }}" aria-label="Aperçu du modèle {{ $template->name }}" style="background-image: url('{{ asset('images/cv-apercu.svg') }}'); background-size: cover;">
                    <iframe title="" data-src="{{ route('templates.paper', $template) }}?miniature=1" loading="lazy" tabindex="-1"></iframe>
                </a>
                <div class="card-body d-flex flex-column">
                    <p class="small text-secondary mb-1">{{ $sectors[$meta['sector'] ?? 'informatique'] ?? 'Métier' }}</p>
                    <h2 class="h6 mb-1">{{ $template->name }}</h2>
                    <p class="small text-secondary">{{ $template->description }}</p>
                    <p class="small mb-3">{{ $template->is_premium ? 'Premium' : 'Inclus' }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('templates.example', $template) }}">Voir le modèle</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('cvs.create', ['modele' => $template->slug]) }}">Utiliser ce modèle</a>
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('cvs.create', ['modele' => $template->slug, 'ia' => 1]) }}">Créer un CV avec l'IA</a>
                    </div>
                </div>
            </article>
        </div>
    @empty
        <p class="text-secondary">Les modèles apparaissent après l'initialisation de la base.</p>
    @endforelse
</div>
<p class="small text-secondary mt-3" data-gallery-count></p>
<script>
    (function () {
        const state = { sector: 'tous', style: 'tous', candidature: 'tous' };
        const cards = () => Array.from(document.querySelectorAll('[data-gallery] [data-category]'));
        const arm = (card) => {
            const frame = card.querySelector('iframe[data-src]');
            if (frame && !frame.getAttribute('src') && !card.hidden) {
                frame.setAttribute('src', frame.getAttribute('data-src'));
            }
        };
        const paint = () => {
            let visible = 0;
            cards().forEach((card) => {
                const sectorOk = state.sector === 'tous' || card.dataset.sector === state.sector;
                const styleOk = state.style === 'tous' || card.dataset.category === state.style;
                const candidatureOk = state.candidature === 'tous' || card.dataset.candidature === state.candidature;
                card.hidden = !(sectorOk && styleOk && candidatureOk);
                if (!card.hidden) {
                    visible += 1;
                    if (visible <= 12) arm(card);
                }
            });
            const count = document.querySelector('[data-gallery-count]');
            if (count) count.textContent = visible + ' modèle' + (visible > 1 ? 's' : '') + ' affiché' + (visible > 1 ? 's' : '') + '.';
        };
        const group = (selector, key) => {
            document.querySelectorAll(selector).forEach((button) => {
                button.addEventListener('click', () => {
                    state[key] = button.dataset.sectorFilter || button.dataset.galleryFilter || button.dataset.candidatureFilter;
                    document.querySelectorAll(selector).forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('btn-primary', active);
                        item.classList.toggle('btn-outline-primary', !active);
                        item.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    paint();
                });
            });
        };
        group('[data-sector-filter]', 'sector');
        group('[data-gallery-filter]', 'style');
        group('[data-candidature-filter]', 'candidature');
        paint();
    }());
</script>
