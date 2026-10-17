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

        $this->line('Fournisseur sélectionné : '.$snapshot['driver']);
        $this->line('Repli : '.($snapshot['fallback'] !== '' ? $snapshot['fallback'] : 'aucun'));

        foreach ($snapshot['providers'] as $name => $provider) {
            $key = $provider['key_present'] ? 'présente' : 'absente';
            $model = $provider['model'] !== '' ? $provider['model'] : 'absent';
            $this->line($name.' : clé '.$key.', modèle '.$model);
        }

        $this->line('Aucun appel distant n\'a été effectué.');

        return self::SUCCESS;
    }
}
