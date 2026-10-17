<div class="mt-8 grid gap-4 lg:grid-cols-3">
    @forelse ($plans as $plan)
        <article class="card flex flex-col p-6 {{ $plan->slug === 'premium' ? 'ring-2 ring-brand-600' : '' }}">
            <h3 class="text-xl font-semibold">{{ $plan->name }}</h3>
            <p class="mt-3 font-display text-3xl font-semibold">
                @if ($plan->isFree())
                    Gratuit
                @else
                    {{ number_format((float) $plan->price, 0, ',', ' ') }} {{ $plan->currency }}
                    <span class="text-base font-medium text-slate-500">/ {{ $plan->interval === 'year' ? 'an' : 'mois' }}</span>
                @endif
            </p>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $plan->description }}</p>
            <ul class="mt-4 space-y-2 text-sm text-slate-700">
                <li>{{ $plan->resume_limit === null ? 'CV illimités' : $plan->resume_limit.' CV' }}</li>
                <li>{{ $plan->allows('cover_letters') ? ($plan->letter_limit === null ? 'Lettres illimitées' : $plan->letter_limit.' lettres') : 'Lettres non incluses' }}</li>
                <li>{{ $plan->ai_credits }} crédits IA par période</li>
                <li>{{ $plan->allows('export_pdf') ? 'Export PDF' : 'Aperçu sans export PDF' }}</li>
                <li>{{ $plan->allows('export_docx') ? 'Export Word' : 'Export Word non inclus' }}</li>
            </ul>
            <a class="btn btn-primary mt-6" href="{{ auth()->check() ? route('billing.index') : route('register') }}">{{ $plan->isFree() ? 'Commencer' : 'Choisir cette offre' }}</a>
        </article>
    @empty
        <p class="text-sm text-slate-600">Les offres seront affichées après l'initialisation.</p>
    @endforelse
</div>
