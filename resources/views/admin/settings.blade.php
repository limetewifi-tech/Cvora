@extends('layouts.admin')
@section('document-title', 'Configuration')
@section('content')
    <h1 class="h2">Configuration</h1>
    <p class="small text-secondary">Paiement : {{ $paymentDriver }}. UniPay : {{ $unipayConfigured ? 'identifiants présents' : 'non configuré' }}. IA : {{ $aiConfigured ? 'clé présente' : 'clé absente' }}.</p>
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
                <div class="col-md-6"><label class="form-label">Modèle IA</label><input class="form-control" name="ai_model" value="{{ $settings->get('ai_model') }}" placeholder="Modèle IA (sans clé)"></div>
                <div class="col-md-6"><label class="form-label">URL de base IA</label><input class="form-control" name="ai_base_url" value="{{ $settings->get('ai_base_url') }}" placeholder="URL de base IA"></div>
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
