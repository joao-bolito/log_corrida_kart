<?php
require_once __DIR__ . '/../Domain/Piloto.php';
require_once __DIR__ . '/../Repositories/CorridaRepository.php';
require_once __DIR__ . '/../Services/CorridaService.php';

use Repositories\CorridaRepository;
use Services\CorridaService;

try {
    $arquivo = __DIR__ . "/corrida.txt";
    $repo = new CorridaRepository($arquivo);
    $linhas = $repo->lerLinhas();

    $service = new CorridaService(4);
    $service->processarCorrida($linhas);

    $pilotos = $service->getPilotosOrdenados();
    $tempoVencedor = $pilotos[0]->tempoTotal;

    // Cabeçalho
    printf("%-8s %-8s %-18s %-8s %-12s %-12s %-16s %-12s\n",
        "Posição", "Código", "Piloto", "Voltas", "Tempo Total", "Melhor Volta", "Velocidade Média", "Diferença 1° colocado"
    );
    echo str_repeat("-", 110) . "\n";

    $posicao = 1;
    foreach ($pilotos as $p) {
        $mediaVelocidade = $p->mediaVelocidade();
        $diferenca = $p->tempoTotal - $tempoVencedor;

        printf("%-8d %-8s %-18s %-8d %-12s %-12s %-16.3f %-12s\n",
            $posicao,
            $p->codigo,
            $p->nome,
            $p->voltas,
            $service->formatarTempoPublico($p->tempoTotal),
            $service->formatarTempoPublico($p->melhorVolta),
            $mediaVelocidade,
            $posicao == 1 ? "-" : $service->formatarTempoPublico($diferenca)
        );
        $posicao++;
    }

    echo "\nMelhor volta da corrida: " . $service->formatarTempoPublico($service->getMelhorVoltaGeral()) . " (" . $service->getPilotoMelhorVolta() . ")\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
