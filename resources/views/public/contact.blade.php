@extends('layouts.public')
@section('document-title', 'Contact — CVora')
@section('content')
    <div class="mx-auto max-w-xl px-4 py-14">
        <h1 class="text-4xl font-semibold">Contact</h1>
        <p class="mt-3 text-slate-600">Écrivez-nous pour une question sur le service, vos données ou la facturation.</p>
        <form method="POST" action="{{ route('contact.store') }}" class="card mt-6 space-y-4 p-6">
            @csrf
            @include('components.flash')
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="company_website">Site</label>
                <input id="company_website" name="company_website" tabindex="-1" autocomplete="off">
            </div>
            <div><label class="label" for="name">Nom</label><input class="field" id="name" name="name" value="{{ old('name') }}" required></div>
            <div><label class="label" for="email">E-mail</label><input class="field" id="email" type="email" name="email" value="{{ old('email') }}" required></div>
            <div><label class="label" for="subject">Sujet</label><input class="field" id="subject" name="subject" value="{{ old('subject') }}" required></div>
            <div><label class="label" for="message">Message</label><textarea class="field min-h-32" id="message" name="message" required>{{ old('message') }}</textarea></div>
            <button class="btn btn-primary" type="submit">Envoyer</button>
        </form>
    </div>
@endsection
