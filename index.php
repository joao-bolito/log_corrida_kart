<?php

// Caminho do arquivo
$arquivo = __DIR__ . "/corrida.txt";

// Função para converter tempo (mm:ss.xxx) em segundos float
function tempoParaSegundos($tempoStr) {
    list($min, $resto) = explode(":", $tempoStr);
    return ((int)$min * 60) + (float)str_replace(",", ".", $resto);
}

// Função para formatar segundos em mm:ss.xxx
function formatarTempo($segundos) {
    $min = floor($segundos / 60);
    $seg = $segundos - ($min * 60);
    return sprintf("%d:%06.3f", $min, $seg);
}

// Lê o arquivo e ignora cabeçalho
$linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
array_shift($linhas); // remove cabeçalho

$pilotos = [];
$voltasMax = 4;
$primeiroFim = null;
$melhorVoltaGeral = null;
$pilotoMelhorVolta = null;

// Processa cada linha do log
foreach ($linhas as $linha) {
    $partes = preg_split('/\s+/', $linha);

    $hora = $partes[0];                  
    $codigo = $partes[1];                
    $volta = (int)$partes[4];           
    $tempoVolta = $partes[5];            
    $velocidade = str_replace(",", ".", $partes[6]);

    // Junta nome completo (ex: "F.MASSA")
    if ($partes[2] === "-") {
        $nome = $partes[3];
    } else {
        $nome = $partes[2] . " " . $partes[3];
    }

    $tempoSegundos = tempoParaSegundos($tempoVolta);

    if (!isset($pilotos[$codigo])) {
        $pilotos[$codigo] = [
            "codigo" => $codigo,
            "nome" => $nome,
            "voltas" => 0,
            "tempoTotal" => 0.0,
            "melhorVolta" => null,
            "velocidades" => [],
            "ultimaHora" => $hora
        ];
    }

    $pilotos[$codigo]["voltas"] = $volta;
    $pilotos[$codigo]["tempoTotal"] += $tempoSegundos;
    $pilotos[$codigo]["ultimaHora"] = $hora;
    $pilotos[$codigo]["velocidades"][] = (float)$velocidade;

    if ($pilotos[$codigo]["melhorVolta"] === null || $tempoSegundos < $pilotos[$codigo]["melhorVolta"]) {
        $pilotos[$codigo]["melhorVolta"] = $tempoSegundos;
    }

    if ($melhorVoltaGeral === null || $tempoSegundos < $melhorVoltaGeral) {
        $melhorVoltaGeral = $tempoSegundos;
        $pilotoMelhorVolta = $nome;
    }

    if ($volta === $voltasMax && $primeiroFim === null) {
        $primeiroFim = $codigo;
    }
}

// Ordena pilotos
usort($pilotos, function ($a, $b) {
    if ($a["voltas"] !== $b["voltas"]) {
        return $b["voltas"] <=> $a["voltas"];
    }
    return $a["tempoTotal"] <=> $b["tempoTotal"];
});

// Tempo do vencedor
$tempoVencedor = $pilotos[0]["tempoTotal"];

// Cabeçalho
printf("%-8s %-8s %-18s %-8s %-12s %-12s %-16s %-12s\n",
    "Posição", "Código", "Piloto", "Voltas", "Tempo Total", "Melhor Volta", "Velocidade Média", "Diferença 1° colocado"
);
echo str_repeat("-", 110) . "\n";

// Exibe pilotos
$posicao = 1;
foreach ($pilotos as $p) {
    $mediaVelocidade = array_sum($p["velocidades"]) / count($p["velocidades"]);
    $diferenca = $p["tempoTotal"] - $tempoVencedor;

    printf("%-8d %-8s %-18s %-8d %-12s %-12s %-16.3f %-12s\n",
        $posicao,
        $p["codigo"],
        $p["nome"],
        $p["voltas"],
        formatarTempo($p["tempoTotal"]),
        formatarTempo($p["melhorVolta"]),
        $mediaVelocidade,
        $posicao == 1 ? "-" : formatarTempo($diferenca) // vencedor não tem diferença
    );
    $posicao++;
}

echo "\nMelhor volta da corrida: " . formatarTempo($melhorVoltaGeral) . " (" . $pilotoMelhorVolta . ")\n";
