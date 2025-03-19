<?php
class User {
    private $name;
    private $email;
    private $cpfCnpj;

    public function __construct($name, $email, $cpfCnpj) {
        $this->name = $name;
        $this->email = $email;
        $this->cpfCnpj = $cpfCnpj;
    }

    public function getName() {
        return $this->name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getCpfCnpj() {
        return $this->cpfCnpj;
    }
    
    public function toArray() {
        return [
            'name'    => $this->name,
            'email'   => $this->email,
            'cpfCnpj' => $this->cpfCnpj
        ];
    }
}
