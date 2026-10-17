@extends('layouts.public')
@section('document-title', 'Contact — CVora')
@section('content')
    <div class="container cvora-narrow py-5">
        <h1 class="display-5 fw-semibold">Contact</h1>
        <p class="text-secondary">Écrivez-nous pour une question sur le service, vos données ou la facturation.</p>
        <form method="POST" action="{{ route('contact.store') }}" class="card mt-4 needs-validation" novalidate>
            @csrf
            <div class="card-body vstack gap-3">
                @include('components.flash')
                <div class="cvora-honeypot" aria-hidden="true">
                    <label for="company_website">Site</label>
                    <input id="company_website" name="company_website" tabindex="-1" autocomplete="off">
                </div>
                <div>
                    <label class="form-label" for="name">Nom</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
                    <div class="invalid-feedback">Indiquez votre nom.</div>
                </div>
                <div>
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                    <div class="invalid-feedback">Indiquez une adresse e-mail valide.</div>
                </div>
                <div>
                    <label class="form-label" for="subject">Sujet</label>
                    <input class="form-control" id="subject" name="subject" value="{{ old('subject') }}" required>
                    <div class="invalid-feedback">Indiquez le sujet du message.</div>
                </div>
                <div>
                    <label class="form-label" for="message">Message</label>
                    <textarea class="form-control cvora-textarea" id="message" name="message" required>{{ old('message') }}</textarea>
                    <div class="invalid-feedback">Écrivez votre message.</div>
                </div>
                <button class="btn btn-primary" type="submit" data-loading>Envoyer</button>
            </div>
        </form>
    </div>
@endsection
