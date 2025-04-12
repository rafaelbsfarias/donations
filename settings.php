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
        // Primeiro tenta obter as configurações do WordPress
        $api_key = get_option('asaas_api_key', '');
        $base_url = get_option('asaas_base_url', '');

        // Se existirem opções no WordPress, use-as
        if (!empty($api_key)) {
            $this->data['API_KEY'] = $api_key;
        }
        
        if (!empty($base_url)) {
            $this->data['BASE_URL'] = $base_url;
        }
        
        // Se algum dado estiver faltando, tente carregar do arquivo .env
        if (empty($this->data['API_KEY']) || empty($this->data['BASE_URL'])) {
            if (file_exists($envFilePath)) {
                $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0 || strpos(trim($line), '//') === 0) {
                        continue;
                    }
                    $parts = explode('=', $line, 2);
                    if (count($parts) == 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        
                        // Só preenche se ainda não tiver sido definido pelo WordPress
                        if (!isset($this->data[$key])) {
                            $this->data[$key] = $value;
                        }
                    }
                }
            }
        }
        
        // Se for a primeira execução, salva as configurações no WordPress
        if (!empty($this->data['API_KEY']) && empty(get_option('asaas_api_key'))) {
            update_option('asaas_api_key', $this->data['API_KEY']);
        }
        
        if (!empty($this->data['BASE_URL']) && empty(get_option('asaas_base_url'))) {
            update_option('asaas_base_url', $this->data['BASE_URL']);
        }
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
