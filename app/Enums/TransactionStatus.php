<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Succeeded => 'Confirmée',
            self::Failed => 'Échouée',
            self::Cancelled => 'Annulée',
        };
    }
}
