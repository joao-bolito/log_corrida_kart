<?php
namespace Repositories;

use RuntimeException;

class CorridaRepository {

    private string $arquivo;

    public function __construct(string $arquivo) {
        if (!file_exists($arquivo)) {
            throw new RuntimeException("Arquivo não encontrado: $arquivo");
        }
        $this->arquivo = $arquivo;
    }

    /**
     * Retorna as linhas do arquivo ignorando cabeçalho
     */
    public function lerLinhas(): array {
        $linhas = file($this->arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($linhas) <= 1) {
            throw new RuntimeException("Arquivo vazio ou sem dados.");
        }
        array_shift($linhas); // remove cabeçalho
        return $linhas;
    }
}
