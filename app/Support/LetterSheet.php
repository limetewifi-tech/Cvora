<?php

namespace App\Support;

use App\Models\CoverLetter;
use App\Models\User;
use Illuminate\Support\Carbon;

class LetterSheet
{
    public static function from(CoverLetter $letter): array
    {
        $letter->loadMissing('user.profile');
        $layout = LetterLayouts::has((string) $letter->layout) ? $letter->layout : 'classique';

        return self::compose(
            $layout,
            $letter->user,
            (string) $letter->company,
            (string) $letter->position,
            (string) $letter->body,
            $letter->updated_at ?? now(),
        );
    }

    public static function example(string $layout): array
    {
        return self::compose(
            LetterLayouts::has($layout) ? $layout : 'classique',
            null,
            'Nom de l\'organisation',
            'Intitulé du poste',
            'Le texte de votre lettre apparaît ici. Aucune phrase n\'est ajoutée à votre place.',
            now(),
            true,
        );
    }

    private static function compose(string $layout, ?User $user, string $company, string $position, string $body, Carbon $date, bool $example = false): array
    {
        $profile = $user?->profile;
        $name = trim((string) ($profile?->first_name.' '.$profile?->last_name));

        if ($name === '') {
            $name = $example ? 'Prénom Nom' : (string) ($user?->name ?? '');
        }

        $sender = array_values(array_filter([
            $name,
            $profile?->address,
            trim((string) ($profile?->city.' '.$profile?->country)),
            $user?->email ?: ($example ? 'prenom.nom@example.com' : ''),
            $profile?->phone ?: ($user?->phone ?? ''),
        ], fn ($line) => filled($line)));

        if ($example && $sender === ['Prénom Nom']) {
            $sender = ['Prénom Nom', 'Ville', 'prenom.nom@example.com'];
        }

        $recipient = array_values(array_filter([
            $company,
            $position !== '' ? 'Poste : '.$position : '',
        ]));

        $city = trim((string) ($profile?->city ?? ''));
        $when = $date->timezone(config('app.timezone'))->translatedFormat('j F Y');
        $place = $city !== '' ? $city.', le '.$when : 'Le '.$when;

        $meta = LetterLayouts::all()[$layout] ?? ['family' => $layout, 'accent' => '#0F766E'];

        return [
            'layout' => $layout,
            'family' => $meta['family'] ?? $layout,
            'accent' => $meta['accent'] ?? '#0F766E',
            'name' => $name !== '' ? $name : 'Prénom Nom',
            'sender' => $sender,
            'recipient' => $recipient,
            'place' => $place,
            'object' => $position !== '' ? 'Objet : candidature au poste de '.$position : 'Objet : candidature',
            'body' => $body,
            'example' => $example,
        ];
    }
}
