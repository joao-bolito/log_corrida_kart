<?php
namespace Domain;

class Piloto {
    public string $codigo;
    public string $nome;
    public int $voltas;
    public float $tempoTotal;
    public ?float $melhorVolta;
    public array $velocidades;

    public function __construct(string $codigo, string $nome) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->voltas = 0;
        $this->tempoTotal = 0.0;
        $this->melhorVolta = null;
        $this->velocidades = [];
    }

    // Adiciona uma volta de forma imutável (retorna novo objeto)
    public function adicionarVolta(float $tempoSegundos, float $velocidade): self {
        $novo = clone $this;
        $novo->voltas++;
        $novo->tempoTotal += $tempoSegundos;
        $novo->velocidades[] = $velocidade;
        $novo->melhorVolta = $novo->melhorVolta === null ? $tempoSegundos : min($novo->melhorVolta, $tempoSegundos);
        return $novo;
    }

    public function mediaVelocidade(): float {
        return array_sum($this->velocidades) / count($this->velocidades);
    }
}
