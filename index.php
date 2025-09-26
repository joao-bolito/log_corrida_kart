<?php

// Caminho do arquivo
$arquivo = __DIR__ . "/corrida.txt";

// Função para converter tempo (mm:ss.xxx) em segundos float
function tempoParaSegundos($tempoStr) {
    list($min, $resto) = explode(":", $tempoStr);
    return ((int)$min * 60) + (float)str_replace(",", ".", $resto);
}

// Lê o arquivo e ignora cabeçalho
$linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
array_shift($linhas); // remove a primeira linha

$pilotos = [];
$voltasMax = 4; // corrida acaba em 4 voltas
$primeiroFim = null;

// Processa cada linha do log
foreach ($linhas as $linha) {
    $partes = preg_split('/\s+/', $linha);

    $hora = $partes[0];                   // Hora da volta
    $codigo = $partes[1];                 // Código do piloto
    $volta = (int)$partes[4];             // Nº da volta
    $tempoVolta = $partes[5];             // Tempo da volta

    // Junta nome completo (ex: "F.MASSA")
    if ($partes[2] === "–") {
        $nome = $partes[3];
    } else {
        $nome = $partes[2] . " " . $partes[3];
    }

    // Converte tempo da volta em segundos
    $tempoSegundos = tempoParaSegundos($tempoVolta);

    // Inicializa dados do piloto se não existir
    if (!isset($pilotos[$codigo])) {
        $pilotos[$codigo] = [
            "codigo" => $codigo,
            "nome" => $nome,
            "voltas" => 0,
            "tempoTotal" => 0.0,
            "ultimaHora" => $hora
        ];
    }

    // Atualiza dados do piloto
    $pilotos[$codigo]["voltas"] = $volta;
    $pilotos[$codigo]["tempoTotal"] += $tempoSegundos;
    $pilotos[$codigo]["ultimaHora"] = $hora;

    // Marca quando o primeiro piloto completou a volta final
    if ($volta === $voltasMax && $primeiroFim === null) {
        $primeiroFim = $codigo;
    }
}

// Agora precisamos ordenar os pilotos
usort($pilotos, function ($a, $b) {
    // Ordena por voltas desc
    if ($a["voltas"] !== $b["voltas"]) {
        return $b["voltas"] <=> $a["voltas"];
    }
    // Empate → menor tempo total vence
    return $a["tempoTotal"] <=> $b["tempoTotal"];
});

// Cabeçalho
echo sprintf("%-8s %-8s %-15s %-8s %-12s\n", "Posição", "Código", "Piloto", "Voltas", "Tempo Total");
echo str_repeat("-", 55) . "\n";

$posicao = 1;
foreach ($pilotos as $p) {
    // Formata tempo total em mm:ss.xxx
    $min = floor($p["tempoTotal"] / 60);
    $seg = $p["tempoTotal"] - ($min * 60);
    $tempoFormatado = sprintf("%d:%06.3f", $min, $seg);

    echo sprintf(
        "%-8d %-8s %-15s %-8d %-12s\n",
        $posicao,
        $p["codigo"],
        $p["nome"],
        $p["voltas"],
        $tempoFormatado
    );
    $posicao++;
}
