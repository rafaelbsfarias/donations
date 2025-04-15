<?php
require_once 'create_customer.php'; // Garante que a classe Asaas_API esteja disponível

class UserService {
    private $asaasApi;
    private static $request_counter = 0;

    public function __construct(Asaas_API $asaasApi) {
        $this->asaasApi = $asaasApi;
        error_log("[DEBUG] UserService::__construct - Nova instância criada");
    }

    /**
     * Cria um novo cliente na API do Asaas a partir de um objeto User.
     *
     * @param User $user
     * @return array|false
     */
    public function createUser(User $user) {
        // Incrementar contador para ver quantas vezes a função é chamada
        self::$request_counter++;
        $request_id = uniqid() . "-" . self::$request_counter;
        
        error_log("[DEBUG] UserService::createUser #{$request_id} - Início. Contador de chamadas: " . self::$request_counter);
        error_log("[DEBUG] UserService::createUser #{$request_id} - Usuário: " . $user->getName() . ", " . $user->getEmail() . ", " . $user->getCpfCnpj());
        
        // Obter backtrace para ver de onde a função está sendo chamada
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($backtrace as $index => $call) {
            error_log("[DEBUG] UserService::createUser #{$request_id} - Backtrace #{$index}: " 
                . (isset($call['class']) ? $call['class'] . '::' : '') 
                . $call['function'] . ' - File: ' 
                . (isset($call['file']) ? $call['file'] : 'unknown') . ' Line: ' 
                . (isset($call['line']) ? $call['line'] : 'unknown'));
        }
        
        $data = $user->toArray();
        
        error_log("[DEBUG] UserService::createUser #{$request_id} - Chamando API com dados: " . json_encode($data));
        
        $result = $this->asaasApi->create_customer($data);
        
        error_log("[DEBUG] UserService::createUser #{$request_id} - Resultado da API: " . (isset($result['id']) ? "Sucesso ID: " . $result['id'] : "Falha"));
        
        return $result;
    }
}
