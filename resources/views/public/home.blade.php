@extends('layouts.public')
@section('content')
    <section class="cvora-hero">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <p class="cvora-kicker mb-3"><i class="bi bi-stars" aria-hidden="true"></i> L'intelligence au service de votre carrière.</p>
                    <h1 class="display-5 fw-semibold">Des candidatures qui ouvrent des portes.</h1>
                    <p class="lead text-secondary mt-3">Créez, améliorez et actualisez votre CV avec l'IA. Rédigez des lettres de motivation personnalisées et préparez vos candidatures en quelques minutes.</p>
                    <div class="d-grid gap-2 d-sm-flex mt-4">
                        <a class="btn btn-primary" href="{{ route('register') }}">Créer mon CV gratuitement</a>
                        <a class="btn btn-outline-primary" href="{{ route('templates.index') }}">Découvrir les modèles</a>
                    </div>
                    <p class="mt-4 mb-0 text-secondary">« Votre talent mérite le meilleur CV. »</p>
                </div>
                <div class="col-lg-6">
                    <div class="card overflow-hidden">
                        <div class="d-flex align-items-center gap-2 border-bottom px-3 py-2">
                            <span class="cvora-dot cvora-dot-red rounded-circle"></span>
                            <span class="cvora-dot cvora-dot-amber rounded-circle"></span>
                            <span class="cvora-dot cvora-dot-green rounded-circle"></span>
                            <span class="small text-secondary ms-2">Aperçu de mise en page</span>
                        </div>
                        <div class="cvora-preview-frame">
                            <div class="cvora-preview-side">
                                <p class="cvora-heading h5 mb-1">Prénom Nom</p>
                                <p class="cvora-preview-muted small mb-3">Intitulé du poste</p>
                                <p class="small mb-0">Kinshasa<br>exemple@mail.fr</p>
                            </div>
                            <div class="p-3">
                                <p class="small fw-semibold text-uppercase text-primary mb-2">Profil</p>
                                <div class="cvora-skeleton mb-2"></div>
                                <div class="cvora-skeleton w-75 mb-3"></div>
                                <p class="small fw-semibold text-uppercase text-primary mb-2">Expérience</p>
                                <p class="fw-semibold mb-1">Poste exemple</p>
                                <p class="small text-secondary">Organisation exemple · 2023 — 2024</p>
                                <div class="cvora-skeleton"></div>
                            </div>
                        </div>
                        <p class="border-top mb-0 px-3 py-2 small text-secondary">Exemple de mise en page, avec des données fictives.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cvora-band py-5" id="fonctionnalites">
        <div class="container">
            <h2 class="h1">Ce que vous pouvez faire</h2>
            <div class="row g-3 mt-2">
                @foreach ([
                    ['bi-file-earmark-richtext', 'Création de CV professionnels', 'Un éditeur guidé, du résumé aux références, avec aperçu immédiat.'],
                    ['bi-upload', 'Importation et actualisation', 'Importez un PDF texte ou un Word, corrigez les champs détectés, puis enregistrez.'],
                    ['bi-envelope-paper', 'Lettres de motivation', 'Rédigez à partir de votre CV et de l\'offre, puis relisez avant d\'enregistrer.'],
                    ['bi-bullseye', 'Adaptation à une offre', 'Comparez les compétences demandées à votre profil, sans rien ajouter en silence.'],
                    ['bi-download', 'Téléchargement PDF et Word', 'Exportez un fichier lisible, avec les accents français, selon votre offre.'],
                    ['bi-copy', 'Plusieurs versions', 'Dupliquez un CV et conservez une version de secours avant d\'appliquer une proposition.'],
                ] as [$icon, $title, $text])
                    <div class="col-md-6 col-lg-4">
                        <article class="card h-100">
                            <div class="card-body">
                                <i class="bi {{ $icon }} fs-4 text-primary" aria-hidden="true"></i>
                                <h3 class="h5 mt-3">{{ $title }}</h3>
                                <p class="mb-0 text-secondary">{{ $text }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between gap-3">
                <h2 class="h1 mb-0">Modèles</h2>
                <a class="fw-semibold" href="{{ route('templates.index') }}">Voir la galerie</a>
            </div>
            <div class="row g-3 mt-2">
                @forelse ($templates as $template)
                    <div class="col-sm-6 col-lg-3">
                        <a href="{{ route('templates.example', $template) }}" class="card h-100 text-decoration-none text-body overflow-hidden">
                            <iframe title="Aperçu {{ $template->name }}" src="{{ route('templates.paper', $template) }}" class="cvora-frame-sm w-100 border-0 bg-white" loading="lazy"></iframe>
                            <div class="card-body border-top">
                                <h3 class="h6 mb-1">{{ $template->name }}</h3>
                                <p class="small text-secondary mb-0">{{ $template->is_premium ? 'Modèle premium' : 'Inclus dans l\'offre gratuite' }}</p>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-secondary">Les modèles apparaissent après l'initialisation de la base.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="h1">Trois étapes</h2>
            <ol class="row g-3 list-unstyled mt-2">
                @foreach ([
                    ['1', 'Créez votre compte', 'Choisissez un modèle. Votre profil préremplit les nouveaux CV.'],
                    ['2', 'Complétez ou importez', 'Saisissez votre parcours ou vérifiez les données extraites d\'un fichier.'],
                    ['3', 'Relisez et téléchargez', 'Ajustez les propositions, puis exportez selon les limites de votre offre.'],
                ] as [$n, $title, $text])
                    <li class="col-md-4">
                        <article class="card h-100">
                            <div class="card-body">
                                <span class="cvora-step-index d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-semibold">{{ $n }}</span>
                                <h3 class="h5 mt-3">{{ $title }}</h3>
                                <p class="mb-0 text-secondary">{{ $text }}</p>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="cvora-band py-5" id="tarifs">
        <div class="container">
            <h2 class="h1">Tarifs</h2>
            <p class="text-secondary">Les prix et les quotas viennent de la base de données. Un administrateur peut les modifier. Aucun montant n'est figé dans le code.</p>
            @include('public.partials.plans', ['plans' => $plans])
        </div>
    </section>

    <section class="py-5">
        <div class="container cvora-prose">
            <h2 class="h1">Questions fréquentes</h2>
            <div class="accordion mt-4" id="faq">
                @foreach ($faqs as $faq)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $loop->index }}" aria-expanded="false" aria-controls="faq-{{ $loop->index }}">
                                {{ $faq['q'] }}
                            </button>
                        </h3>
                        <div id="faq-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#faq">
                            <div class="accordion-body text-secondary">{{ $faq['a'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pb-5">
        <div class="container">
            <div class="cvora-cta px-4 py-5 px-sm-5">
                <h2 class="h1">Préparez votre prochaine candidature.</h2>
                <p class="cvora-cta-note mt-3 mb-0">Créez un compte, choisissez un modèle et gardez la main sur chaque formulation.</p>
                <a class="btn btn-light text-primary mt-4" href="{{ route('register') }}">Créer mon CV gratuitement</a>
            </div>
        </div>
    </section>
@endsection
