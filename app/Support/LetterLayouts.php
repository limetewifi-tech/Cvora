<?php

namespace App\Support;

class LetterLayouts
{
    public static function all(): array
    {
        return [
            'classique' => [
                'name' => 'Classique',
                'description' => 'Expéditeur à gauche, destinataire à droite, objet puis signature.',
            ],
            'moderne' => [
                'name' => 'Moderne',
                'description' => 'Bandeau avec le nom, puis les deux adresses sur une ligne.',
            ],
            'bandeau' => [
                'name' => 'Bandeau',
                'description' => 'Titre coloré en tête, le poste visé juste en dessous.',
            ],
            'elegant' => [
                'name' => 'Élégant',
                'description' => 'Nom en grand, filet fin, destinataire et texte aérés.',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function has(string $layout): bool
    {
        return isset(self::all()[$layout]);
    }
}
