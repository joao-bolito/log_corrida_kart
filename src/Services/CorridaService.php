<?php
namespace Services;

use Domain\Piloto;

class CorridaService {

    private array $pilotos = [];
    private ?float $melhorVoltaGeral = null;
    private ?string $pilotoMelhorVolta = null;
    private ?string $primeiroFim = null;

    private int $voltasMax;

    public function __construct(int $voltasMax = 4) {
        $this->voltasMax = $voltasMax;
    }

    // Converte tempo mm:ss.xxx para segundos float
    private function tempoParaSegundos(string $tempoStr): float {
        [$min, $resto] = explode(":", $tempoStr);
        return ((int)$min * 60) + (float)str_replace(",", ".", $resto);
    }

    // Formata segundos em mm:ss.xxx
    private function formatarTempo(float $segundos): string {
        $min = floor($segundos / 60);
        $seg = $segundos - ($min * 60);
        return sprintf("%d:%06.3f", $min, $seg);
    }

    // Processa todas as linhas do log
    public function processarCorrida(array $linhas): void {
        foreach ($linhas as $linha) {
            $partes = preg_split('/\s+/', $linha);
            if (count($partes) < 7) continue;

            $hora = $partes[0];
            $codigo = $partes[1];
            $volta = (int)$partes[4];
            $tempoVolta = $partes[5];
            $velocidade = (float)str_replace(",", ".", $partes[6]);

            $nome = $partes[2] === "-" ? $partes[3] : $partes[2] . " " . $partes[3];

            $tempoSegundos = $this->tempoParaSegundos($tempoVolta);

            if (!isset($this->pilotos[$codigo])) {
                $this->pilotos[$codigo] = new Piloto($codigo, $nome);
            }

            $this->pilotos[$codigo] = $this->pilotos[$codigo]->adicionarVolta($tempoSegundos, $velocidade);

            if ($this->melhorVoltaGeral === null || $tempoSegundos < $this->melhorVoltaGeral) {
                $this->melhorVoltaGeral = $tempoSegundos;
                $this->pilotoMelhorVolta = $nome;
            }

            if ($volta === $this->voltasMax && $this->primeiroFim === null) {
                $this->primeiroFim = $codigo;
            }
        }
    }

    // Retorna pilotos ordenados por voltas e tempo total
    public function getPilotosOrdenados(): array {
        $pilotos = $this->pilotos;
        usort($pilotos, function ($a, $b) {
            if ($a->voltas !== $b->voltas) return $b->voltas <=> $a->voltas;
            return $a->tempoTotal <=> $b->tempoTotal;
        });
        return $pilotos;
    }

    public function getMelhorVoltaGeral(): ?float {
        return $this->melhorVoltaGeral;
    }

    public function getPilotoMelhorVolta(): ?string {
        return $this->pilotoMelhorVolta;
    }

    public function formatarTempoPublico(float $segundos): string {
        return $this->formatarTempo($segundos);
    }
}
