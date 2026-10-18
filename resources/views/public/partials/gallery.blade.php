@php
    $catalog = collect(\App\Support\ResumeTemplateCatalog::definitions())->keyBy('slug');
    $filters = [
        'tous' => 'Tous les CV',
        'classique' => 'CV classiques',
        'moderne' => 'CV modernes',
        'design' => 'CV designs',
        'original' => 'CV originaux',
    ];
@endphp
<div class="d-flex flex-wrap gap-2 mb-4" role="group" aria-label="Filtres de modèles">
    @foreach ($filters as $key => $label)
        <button class="btn btn-sm {{ $key === 'tous' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill" type="button" data-gallery-filter="{{ $key }}" aria-pressed="{{ $key === 'tous' ? 'true' : 'false' }}">{{ $label }}</button>
    @endforeach
</div>
<div class="row g-4" data-gallery>
    @forelse ($templates as $template)
        @php $meta = $catalog->get($template->slug, []); @endphp
        <div class="col-sm-6 col-lg-3" data-category="{{ $meta['category'] ?? 'moderne' }}">
            <article class="card h-100 overflow-hidden">
                <a class="cvora-thumb" href="{{ route('templates.example', $template) }}" aria-label="Aperçu du modèle {{ $template->name }}">
                    <iframe title="" src="{{ route('templates.paper', $template) }}?miniature=1" loading="lazy" tabindex="-1"></iframe>
                </a>
                <div class="card-body d-flex flex-column">
                    <h2 class="h6 mb-1">{{ $template->name }}</h2>
                    <p class="small text-secondary">{{ $template->description }}</p>
                    <p class="small mb-3">{{ $template->is_premium ? 'Premium' : 'Inclus' }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        @auth
                            <a class="btn btn-primary btn-sm" href="{{ route('cvs.create', ['modele' => $template->slug]) }}">Choisir ce modèle</a>
                        @else
                            <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Choisir ce modèle</a>
                        @endauth
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('templates.example', $template) }}">Aperçu détaillé</a>
                    </div>
                </div>
            </article>
        </div>
    @empty
        <p class="text-secondary">Les modèles apparaissent après l'initialisation de la base.</p>
    @endforelse
</div>
<script>
    document.querySelectorAll('[data-gallery-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = button.dataset.galleryFilter;
            document.querySelectorAll('[data-gallery-filter]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('btn-primary', active);
                item.classList.toggle('btn-outline-primary', !active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            document.querySelectorAll('[data-gallery] [data-category]').forEach((card) => {
                card.hidden = value !== 'tous' && card.dataset.category !== value;
            });
        });
    });
</script>
