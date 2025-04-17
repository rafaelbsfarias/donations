<?php
require_once 'create_customer.php'; // Garante que a classe Asaas_API esteja disponível

class UserService {
    private $asaasApi;
    private static $request_counter = 0;

    public function __construct(Asaas_API $asaasApi) {
        $this->asaasApi = $asaasApi;
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
        
        $data = $user->toArray();
        $result = $this->asaasApi->create_customer($data);
        
        return $result;
    }
}
