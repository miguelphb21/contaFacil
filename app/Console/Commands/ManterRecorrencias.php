<?php

namespace App\Console\Commands;

use App\Services\RecorrenciaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lancamentos:manter-recorrencias {--meses=24 : Número de meses à frente}')]
#[Description('Gera os lançamentos pendentes das recorrências ativas até o horizonte definido')]
class ManterRecorrencias extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(RecorrenciaService $recorrenciaService): int
    {
        $gerados = $recorrenciaService->manterHorizonte((int) $this->option('meses'));

        $this->info("Horizonte mantido: {$gerados} lançamentos gerados.");

        return self::SUCCESS;
    }
}
