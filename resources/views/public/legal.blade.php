@extends('layouts.public')
@section('document-title', $title.' — CVora')
@section('content')
    <article class="mx-auto max-w-3xl px-4 py-14 prose-content space-y-4 text-slate-700">
        <h1 class="text-4xl font-semibold text-ink">{{ $title }}</h1>
        @php
            $identity = $settings->get('legal_name');
            $address = $settings->get('legal_address');
            $email = $settings->get('legal_email', $settings->get('contact_email', 'contact@cvora.local'));
            $rccm = $settings->get('legal_rccm');
        @endphp
        @if ($kind === 'notice')
            <p>Éditeur du service : {{ $identity ?: 'à compléter avant la mise en production' }}.</p>
            <p>Adresse : {{ $address ?: 'à compléter avant la mise en production' }}.</p>
            <p>Contact : {{ $email }}.</p>
            <p>Immatriculation : {{ $rccm ?: 'à compléter. Aucun numéro fictif n\'est affiché.' }}</p>
            <p>Hébergement : renseigner l'hébergeur réel au moment du déploiement.</p>
        @elseif ($kind === 'privacy')
            <p>CVora traite les données nécessaires à votre compte, à vos CV, à vos lettres, à vos candidatures et, si vous souscrivez une offre payante, à vos transactions.</p>
            <p>Les documents importés sont stockés hors du dossier public, téléchargés via une route authentifiée, puis supprimés après confirmation de l'import. Les fichiers ne sont pas exécutés.</p>
            <p>Les textes envoyés à l'assistant IA, lorsqu'une clé est configurée, partent vers le fournisseur indiqué dans l'environnement. Les journaux techniques n'enregistrent pas le contenu des CV ni les clés API.</p>
            <p>L'administration consulte des compteurs et des métadonnées de compte. Elle n'a pas d'écran pour lire vos CV.</p>
            <p>Vous pouvez corriger votre profil et supprimer votre compte. La suppression retire les documents et anonymise le compte. Les transactions restent conservées sans le contenu des candidatures.</p>
            <p>Contact confidentialité : {{ $email }}.</p>
        @else
            <p>CVora est un outil de préparation de documents. Il ne garantit ni un entretien ni une embauche.</p>
            <p>Vous restez responsable des informations envoyées à un employeur. Les propositions de l'assistant doivent être relues. Elles ne sont appliquées qu'après votre confirmation.</p>
            <p>Les quotas, modèles et exports dépendent de l'offre active, configurable par l'administrateur.</p>
            <p>Un paiement n'active une offre payante qu'après vérification serveur de la notification du prestataire. Le mode bac à sable ne constitue pas un paiement.</p>
            <p>L'usage du service suppose l'acceptation de ces conditions lors de l'inscription.</p>
        @endif
    </article>
@endsection
