<div class="row g-3 mt-2">
    @forelse ($plans as $plan)
        <div class="col-lg-4">
            <article class="card h-100 {{ $plan->slug === 'premium' ? 'border-primary border-2' : '' }}">
                <div class="card-body d-flex flex-column">
                    <h3 class="h4">{{ $plan->name }}</h3>
                    <p class="cvora-heading display-6 fw-semibold mt-3">
                        @if ($plan->isFree())
                            Gratuit
                        @else
                            {{ number_format((float) $plan->price, 0, ',', ' ') }} {{ $plan->currency }}
                            <span class="fs-6 fw-medium text-secondary">/ {{ $plan->interval === 'year' ? 'an' : 'mois' }}</span>
                        @endif
                    </p>
                    <p class="text-secondary">{{ $plan->description }}</p>
                    <ul class="list-unstyled small mb-4">
                        <li class="mb-2"><i class="bi bi-check2 text-success me-1" aria-hidden="true"></i>{{ $plan->resume_limit === null ? 'CV illimités' : $plan->resume_limit.' CV' }}</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-1" aria-hidden="true"></i>{{ $plan->allows('cover_letters') ? ($plan->letter_limit === null ? 'Lettres illimitées' : $plan->letter_limit.' lettres') : 'Lettres non incluses' }}</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-1" aria-hidden="true"></i>{{ $plan->ai_credits }} crédits IA par période</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-1" aria-hidden="true"></i>{{ $plan->allows('export_pdf') ? 'Export PDF' : 'Aperçu sans export PDF' }}</li>
                        <li><i class="bi bi-check2 text-success me-1" aria-hidden="true"></i>{{ $plan->allows('export_docx') ? 'Export Word' : 'Export Word non inclus' }}</li>
                    </ul>
                    <a class="btn btn-primary mt-auto" href="{{ auth()->check() ? route('billing.index') : route('register') }}">{{ $plan->isFree() ? 'Commencer' : 'Choisir cette offre' }}</a>
                </div>
            </article>
        </div>
    @empty
        <p class="text-secondary">Les offres seront affichées après l'initialisation.</p>
    @endforelse
</div>
