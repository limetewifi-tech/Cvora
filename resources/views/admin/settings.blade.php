@extends('layouts.admin')
@section('document-title', 'Configuration')
@section('content')
    <h1 class="h2">Configuration</h1>
    <p class="small text-secondary">Paiement : {{ $paymentDriver }}. iKeePay : {{ $ikeepayConfigured ? 'identifiants présents' : 'non configuré' }}. IA : {{ $aiStatus['driver'] }}, repli {{ $aiStatus['fallback'] !== '' ? $aiStatus['fallback'] : 'aucun' }}.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="card mt-3">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                @foreach (['feature_ai' => 'Assistant IA', 'feature_import' => 'Import', 'feature_letters' => 'Lettres', 'feature_payments' => 'Paiements', 'feature_exports' => 'Exports'] as $flag => $label)
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="{{ $flag }}" id="{{ $flag }}" value="1" @checked($settings->flag($flag))>
                            <label class="form-check-label" for="{{ $flag }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
                <div class="col-md-6">
                    <label class="form-label" for="ai_driver">Fournisseur IA</label>
                    <select class="form-select" id="ai_driver" name="ai_driver">
                        <option value="" @selected(($settings->get('ai_driver', '') ?? '') === '')>Défini par l'environnement</option>
                        @foreach (['anthropic' => 'Anthropic Claude', 'gemini' => 'Google Gemini', 'groq' => 'Groq'] as $value => $label)
                            <option value="{{ $value }}" @selected($settings->get('ai_driver', '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ai_fallback_driver">Repli facultatif</label>
                    <select class="form-select" id="ai_fallback_driver" name="ai_fallback_driver">
                        <option value="" @selected(($settings->get('ai_fallback_driver', '') ?? '') === '')>Aucun repli</option>
                        @foreach (['anthropic' => 'Anthropic Claude', 'gemini' => 'Google Gemini', 'groq' => 'Groq'] as $value => $label)
                            <option value="{{ $value }}" @selected($settings->get('ai_fallback_driver', '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label" for="anthropic_model">Modèle Anthropic</label><input class="form-control" id="anthropic_model" name="anthropic_model" value="{{ $settings->get('anthropic_model', '') }}" maxlength="80" autocomplete="off" placeholder="Identifiant du modèle"></div>
                <div class="col-md-4"><label class="form-label" for="gemini_model">Modèle Gemini</label><input class="form-control" id="gemini_model" name="gemini_model" value="{{ $settings->get('gemini_model', '') }}" maxlength="80" autocomplete="off" placeholder="Identifiant du modèle"></div>
                <div class="col-md-4"><label class="form-label" for="groq_model">Modèle Groq</label><input class="form-control" id="groq_model" name="groq_model" value="{{ $settings->get('groq_model', '') }}" maxlength="80" autocomplete="off" placeholder="Identifiant du modèle"></div>
                <div class="col-md-6"><label class="form-label">Raison sociale</label><input class="form-control" name="legal_name" value="{{ $settings->get('legal_name') }}" placeholder="Raison sociale"></div>
                <div class="col-md-6"><label class="form-label">E-mail légal</label><input class="form-control" name="legal_email" value="{{ $settings->get('legal_email') }}" placeholder="E-mail légal"></div>
                <div class="col-md-6"><label class="form-label">Adresse</label><input class="form-control" name="legal_address" value="{{ $settings->get('legal_address') }}" placeholder="Adresse"></div>
                <div class="col-md-6"><label class="form-label">RCCM</label><input class="form-control" name="legal_rccm" value="{{ $settings->get('legal_rccm') }}" placeholder="RCCM, si vous l'avez"></div>
                <div class="col-12"><label class="form-label">E-mail de contact</label><input class="form-control" name="contact_email" value="{{ $settings->get('contact_email') }}" placeholder="E-mail de contact"></div>
                <div class="col-12"><button class="btn btn-primary" type="submit">Enregistrer</button></div>
            </div>
        </div>
    </form>
@endsection
