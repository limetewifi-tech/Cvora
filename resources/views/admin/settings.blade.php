@extends('layouts.admin')
@section('document-title', 'Configuration')
@section('content')
    <h1 class="text-3xl font-semibold">Configuration</h1>
    <p class="mt-2 text-sm text-slate-600">Paiement : {{ $paymentDriver }}. UniPay : {{ $unipayConfigured ? 'identifiants présents' : 'non configuré' }}. IA : {{ $aiConfigured ? 'clé présente' : 'clé absente' }}.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="card mt-6 grid gap-3 p-5 md:grid-cols-2">
        @csrf @method('PUT')
        @foreach (['feature_ai' => 'Assistant IA', 'feature_import' => 'Import', 'feature_letters' => 'Lettres', 'feature_payments' => 'Paiements', 'feature_exports' => 'Exports'] as $flag => $label)
            <label class="text-sm"><input type="checkbox" name="{{ $flag }}" value="1" @checked($settings->flag($flag))> {{ $label }}</label>
        @endforeach
        <input class="field" name="ai_model" value="{{ $settings->get('ai_model') }}" placeholder="Modèle IA (sans clé)">
        <input class="field" name="ai_base_url" value="{{ $settings->get('ai_base_url') }}" placeholder="URL de base IA">
        <input class="field" name="legal_name" value="{{ $settings->get('legal_name') }}" placeholder="Raison sociale">
        <input class="field" name="legal_email" value="{{ $settings->get('legal_email') }}" placeholder="E-mail légal">
        <input class="field" name="legal_address" value="{{ $settings->get('legal_address') }}" placeholder="Adresse">
        <input class="field" name="legal_rccm" value="{{ $settings->get('legal_rccm') }}" placeholder="RCCM, si vous l'avez">
        <input class="field md:col-span-2" name="contact_email" value="{{ $settings->get('contact_email') }}" placeholder="E-mail de contact">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </form>
@endsection
