<?php

namespace App\Console\Commands;

use App\Services\Ai\AiStatus;
use Illuminate\Console\Command;

class AiStatusCommand extends Command
{
    protected $signature = 'cvora:ai-status';

    protected $description = 'Affiche la configuration IA sans révéler les clés et sans appel distant';

    public function handle(AiStatus $status): int
    {
        $snapshot = $status->snapshot();

        $this->line('Fournisseur sélectionné : '.($snapshot['driver'] !== '' ? $snapshot['driver'] : 'absent'));

        if (! $snapshot['driver_supported']) {
            $this->line('Ce fournisseur n\'est pas pris en charge. Valeurs acceptées : anthropic, gemini, groq. Aucun autre fournisseur n\'est appelé.');
        }

        $this->line('Repli : '.($snapshot['fallback'] !== '' ? $snapshot['fallback'] : 'aucun'));

        if (! $snapshot['fallback_supported']) {
            $this->line('Le repli n\'est pas pris en charge et ne sera pas appelé.');
        }

        foreach ($snapshot['providers'] as $name => $provider) {
            $key = $provider['key_present'] ? 'présente' : 'absente';
            $model = $provider['model'] !== '' ? $provider['model'] : 'absent';
            $this->line($name.' : clé '.$key.', modèle '.$model);
        }

        $this->line('Aucun appel distant n\'a été effectué.');

        return $snapshot['driver_supported'] ? self::SUCCESS : self::FAILURE;
    }
}
