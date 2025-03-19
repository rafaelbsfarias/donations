<?php
require_once 'create_customer.php'; // Garante que a classe Asaas_API esteja disponível

class UserService {
    private $asaasApi;

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
        $data = $user->toArray();
        return $this->asaasApi->create_customer($data);
    }
}
