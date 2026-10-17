@extends('layouts.public')
@section('content')
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-100 blur-3xl"></div>
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 lg:grid-cols-[1.05fr_.95fr] lg:py-20">
            <div>
                <p class="mb-4 inline-flex rounded-full bg-brand-100 px-3 py-1 text-sm font-medium text-brand-700">L'intelligence au service de votre carrière.</p>
                <h1 class="text-4xl font-semibold leading-tight text-ink sm:text-5xl">Des candidatures qui ouvrent des portes.</h1>
                <p class="mt-5 max-w-xl text-lg text-slate-600">Créez, améliorez et actualisez votre CV avec l'IA. Rédigez des lettres de motivation personnalisées et préparez vos candidatures en quelques minutes.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a class="btn btn-primary" href="{{ route('register') }}">Créer mon CV gratuitement</a>
                    <a class="btn btn-secondary" href="{{ route('templates.index') }}">Découvrir les modèles</a>
                </div>
                <p class="mt-4 text-sm text-slate-500">« Votre talent mérite le meilleur CV. »</p>
            </div>
            <div class="card overflow-hidden">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-300"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                    <span class="ml-2 text-xs text-slate-500">Aperçu de mise en page</span>
                </div>
                <div class="grid gap-0 sm:grid-cols-[9rem_1fr]">
                    <div class="bg-brand-600 p-4 text-white">
                        <p class="font-display text-lg font-semibold">Prénom Nom</p>
                        <p class="mt-1 text-xs text-blue-100">Intitulé du poste</p>
                        <p class="mt-4 text-xs leading-5 text-blue-50">Kinshasa<br>prenom.nom@example.com</p>
                    </div>
                    <div class="space-y-3 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">Profil</p>
                        <div class="h-2 w-full rounded bg-slate-100"></div>
                        <div class="h-2 w-4/5 rounded bg-slate-100"></div>
                        <p class="pt-2 text-xs font-semibold uppercase tracking-wide text-brand-600">Expérience</p>
                        <p class="text-sm font-medium">Poste exemple</p>
                        <p class="text-xs text-slate-500">Organisation exemple · 2023 — 2024</p>
                        <div class="h-2 w-full rounded bg-slate-100"></div>
                    </div>
                </div>
                <p class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500">Exemple de mise en page, avec des données fictives.</p>
            </div>
        </div>
    </section>

    <section class="bg-mist py-16" id="fonctionnalites">
        <div class="mx-auto max-w-6xl px-4">
            <h2 class="text-3xl font-semibold">Ce que vous pouvez faire</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Création de CV professionnels', 'Un éditeur guidé, du résumé aux références, avec aperçu immédiat.'],
                    ['Importation et actualisation', 'Importez un PDF texte ou un Word, corrigez les champs détectés, puis enregistrez.'],
                    ['Lettres de motivation', 'Rédigez à partir de votre CV et de l\'offre, puis relisez avant d\'enregistrer.'],
                    ['Adaptation à une offre', 'Comparez les compétences demandées à votre profil, sans rien ajouter en silence.'],
                    ['Téléchargement PDF et Word', 'Exportez un fichier lisible, avec les accents français, selon votre offre.'],
                    ['Plusieurs versions', 'Dupliquez un CV et conservez une version de secours avant d\'appliquer une proposition.'],
                ] as [$title, $text])
                    <article class="card p-5">
                        <h3 class="text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex items-end justify-between gap-4">
                <h2 class="text-3xl font-semibold">Modèles</h2>
                <a class="text-sm font-semibold text-brand-700" href="{{ route('templates.index') }}">Voir la galerie</a>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($templates as $template)
                    <a href="{{ route('templates.example', $template) }}" class="card overflow-hidden transition hover:-translate-y-0.5">
                        <iframe title="Aperçu {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="pointer-events-none h-52 w-full bg-white" loading="lazy"></iframe>
                        <div class="border-t border-slate-100 p-4">
                            <h3 class="font-semibold">{{ $template->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $template->is_premium ? 'Modèle premium' : 'Inclus dans l\'offre gratuite' }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-600">Les modèles apparaissent après l'initialisation de la base.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto max-w-6xl px-4">
            <h2 class="text-3xl font-semibold">Trois étapes</h2>
            <ol class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['1', 'Créez votre compte', 'Choisissez un modèle. Votre profil préremplit les nouveaux CV.'],
                    ['2', 'Complétez ou importez', 'Saisissez votre parcours ou vérifiez les données extraites d\'un fichier.'],
                    ['3', 'Relisez et téléchargez', 'Ajustez les propositions, puis exportez selon les limites de votre offre.'],
                ] as [$n, $title, $text])
                    <li class="card p-5">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 font-semibold text-white">{{ $n }}</span>
                        <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="bg-mist py-16" id="tarifs">
        <div class="mx-auto max-w-6xl px-4">
            <h2 class="text-3xl font-semibold">Tarifs</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">Les prix et les quotas viennent de la base de données. Un administrateur peut les modifier. Aucun montant n'est figé dans le code.</p>
            @include('public.partials.plans', ['plans' => $plans])
        </div>
    </section>

    <section class="py-16">
        <div class="mx-auto max-w-3xl px-4">
            <h2 class="text-3xl font-semibold">Questions fréquentes</h2>
            <div class="mt-6 space-y-3">
                @foreach ($faqs as $faq)
                    <div class="card" data-accordion>
                        <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold" aria-expanded="false">
                            {{ $faq['q'] }}
                            <span aria-hidden="true">+</span>
                        </button>
                        <div class="px-5 pb-4 text-sm leading-6 text-slate-600" data-panel hidden>{{ $faq['a'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-4 pb-16">
        <div class="mx-auto max-w-6xl rounded-3xl bg-brand-600 px-6 py-10 text-white sm:px-10">
            <h2 class="text-3xl font-semibold">Préparez votre prochaine candidature.</h2>
            <p class="mt-3 max-w-2xl text-blue-100">Créez un compte, choisissez un modèle et gardez la main sur chaque formulation.</p>
            <a class="btn mt-6 bg-white text-brand-700 hover:bg-blue-50" href="{{ route('register') }}">Créer mon CV gratuitement</a>
        </div>
    </section>
@endsection
