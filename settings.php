<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Settings {
    private $data = [];

    /**
     * Construtor que lê o arquivo .env.
     * @param string $envFilePath Caminho para o arquivo .env. Por padrão, usa o mesmo diretório deste arquivo.
     */
    public function __construct($envFilePath = __DIR__ . '/.env') {
        if (file_exists($envFilePath)) {
            $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $this->data[$key] = $value;
                }
            }
        }
        // Não lançar exceção, apenas continuar com valores padrão ou vazios
    }

    /**
     * Retorna o valor de uma chave ou um valor padrão se não existir.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Retorna todos os dados lidos do arquivo.
     *
     * @return array
     */
    public function all() {
        return $this->data;
    }
}
