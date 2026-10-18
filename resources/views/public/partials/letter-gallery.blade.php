@php
    $layouts = $layouts ?? \App\Support\LetterLayouts::all();
    $categories = \App\Support\LetterLayouts::categories();
@endphp
<div class="d-flex flex-wrap gap-2 mb-4" role="group" aria-label="Catégories de lettres">
    <button class="btn btn-sm btn-primary rounded-pill" type="button" data-letter-filter="tous" aria-pressed="true">Toutes</button>
    @foreach ($categories as $key => $label)
        <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-letter-filter="{{ $key }}" aria-pressed="false">{{ $label }}</button>
    @endforeach
</div>
<div class="row g-4" data-letter-gallery>
    @foreach ($layouts as $slug => $layout)
        <div class="col-sm-6 col-lg-3" data-letter-category="{{ $layout['category'] }}">
            <article class="card h-100 overflow-hidden">
                <a class="cvora-thumb" href="{{ route('letters.create', ['modele' => $slug]) }}" style="background-image: url('{{ asset('images/cv-apercu.svg') }}'); background-size: cover;">
                    <iframe title="" data-src="{{ route('templates.letters.paper', $slug) }}" loading="lazy" tabindex="-1"></iframe>
                </a>
                <div class="card-body d-flex flex-column">
                    <p class="small text-secondary mb-1">{{ $categories[$layout['category']] ?? 'Lettre' }}</p>
                    <h2 class="h6 mb-1">{{ $layout['name'] }}</h2>
                    <p class="small text-secondary">{{ $layout['description'] }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('templates.letters.paper', $slug) }}">Voir le modèle</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('letters.create', ['modele' => $slug]) }}">Utiliser ce modèle</a>
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('letters.create', ['modele' => $slug, 'ia' => 1]) }}">Créer une lettre avec l'IA</a>
                    </div>
                </div>
            </article>
        </div>
    @endforeach
</div>
<script>
    (function () {
        const cards = Array.from(document.querySelectorAll('[data-letter-gallery] [data-letter-category]'));
        const arm = (card) => {
            const frame = card.querySelector('iframe[data-src]');
            if (frame && !frame.getAttribute('src') && !card.hidden) frame.setAttribute('src', frame.getAttribute('data-src'));
        };
        const paint = (category) => {
            let visible = 0;
            cards.forEach((card) => {
                card.hidden = category !== 'tous' && card.dataset.letterCategory !== category;
                if (!card.hidden) {
                    visible += 1;
                    if (visible <= 10) arm(card);
                }
            });
        };
        document.querySelectorAll('[data-letter-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-letter-filter]').forEach((item) => {
                    const active = item === button;
                    item.classList.toggle('btn-primary', active);
                    item.classList.toggle('btn-outline-primary', !active);
                    item.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                paint(button.dataset.letterFilter);
            });
        });
        paint('tous');
    }());
</script>
