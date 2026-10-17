<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Prepared = 'prepared';
    case Applied = 'applied';
    case Interview = 'interview';
    case Offer = 'offer';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Prepared => 'Préparé',
            self::Applied => 'Envoyée',
            self::Interview => 'Entretien',
            self::Offer => 'Offre reçue',
            self::Accepted => 'Acceptée',
            self::Rejected => 'Refusée',
        };
    }
}
