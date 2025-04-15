<?php
require_once 'User.php';
require_once 'UserService.php';
require_once 'UserForm.php';

class UserController {
    private $userService;
    private $userForm;
    private static $request_id; // Para rastrear instâncias únicas

    public function __construct(UserService $userService, UserForm $userForm) {
        $this->userService = $userService;
        $this->userForm = $userForm;
        
        // Gerar um ID único para esta instância
        if (empty(self::$request_id)) {
            self::$request_id = uniqid();
        }
        error_log("[DEBUG] UserController::__construct - Nova instância criada. Request ID: " . self::$request_id);
    }

    /**
     * Processa o envio do formulário: cria o cliente e, se criado com sucesso, cria a assinatura.
     *
     * @return string
     */
    public function handleRequest() {
        $output = '';
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user_form'])) {
            // Verifica o nonce para segurança
            if (!isset($_POST['create_user_nonce']) || !wp_verify_nonce($_POST['create_user_nonce'], 'create_user_action')) {
                $output .= '<p>Falha na verificação de segurança.</p>';
            } else {
                // Sanitiza os dados do formulário
                $name    = sanitize_text_field($_POST['user_name']);
                $email   = sanitize_email($_POST['user_email']);
                $cpfCnpj = sanitize_text_field($_POST['user_cpfcnpj']);
    
                // Cria o objeto User
                $user = new User($name, $email, $cpfCnpj);
    
                // Cria o cliente via UserService
                $customerResult = $this->userService->createUser($user);
    
                if ($customerResult && isset($customerResult['id'])) {
                    // Registro do cliente bem-sucedido
                    // Aqui você pode adicionar logs e feedback, se desejar
    
                    // Processa os dados adicionais para a assinatura (dados do cartão, etc.)
                    $holderName = $name; // Supomos que o titular é o usuário
                    $cardNumber = sanitize_text_field($_POST['user_cardNumber']);
                    $expiryMonth = sanitize_text_field($_POST['user_expiryMonth']);
                    $expiryYear = sanitize_text_field($_POST['user_expiryYear']);
                    $ccv = sanitize_text_field($_POST['user_ccv']);
                    $postalCode = sanitize_text_field($_POST['user_postalCode']);
                    $addressNumber = sanitize_text_field($_POST['user_addressNumber']);
                    $addressComplement = sanitize_text_field(isset($_POST['user_addressComplement']) ? $_POST['user_addressComplement'] : '');
                    $phone = sanitize_text_field($_POST['user_phone']);
                    $mobilePhone = sanitize_text_field(isset($_POST['user_mobilePhone']) ? $_POST['user_mobilePhone'] : '');
    
                    // Prepara os dados para a assinatura
                    $subscriptionData = array(
                        "billingType" => "CREDIT_CARD",
                        "cycle"       => "MONTHLY",
                        "customer"    => $customerResult['id'],
                        "value"       => 100,
                        "nextDueDate" => date('Y-m-d', strtotime('+1 month')),
                        "creditCard"  => array(
                            "holderName" => $holderName,
                            "number"     => $cardNumber,
                            "expiryMonth"=> $expiryMonth,
                            "expiryYear" => $expiryYear,
                            "ccv"        => $ccv
                        ),
                        "creditCardHolderInfo" => array(
                            "name"           => $name,
                            "email"          => $email,
                            "cpfCnpj"        => $cpfCnpj,
                            "postalCode"     => $postalCode,
                            "addressNumber"  => $addressNumber,
                            "addressComplement" => $addressComplement,
                            "phone"          => $phone,
                            "mobilePhone"    => $mobilePhone
                        )
                    );
    
                    // Cria a assinatura usando SubscriptionService
                    require_once __DIR__ . '/SubscriptionService.php';
                    $subscriptionService = new SubscriptionService();
                    $subscriptionResult = $subscriptionService->create_subscription($subscriptionData);
    
                    if ($subscriptionResult && isset($subscriptionResult['id'])) {
                        // Se tudo ocorrer bem, redirecione para evitar reenvio (PRG)
                        wp_redirect(add_query_arg('donationsaas_success', 'true', home_url('/sucesso/')));
                        exit;
                    } else {
                        $output .= '<p>Erro ao criar assinatura.</p>';
                        if (isset($subscriptionResult['errors'])) {
                            $output .= '<pre>' . print_r($subscriptionResult['errors'], true) . '</pre>';
                        }
                    }
                } else {
                    $output .= '<p>Erro ao criar cliente.</p>';
                }
            }
        }
        // Renderiza o formulário se não for POST ou se houver algum erro
        $output .= $this->userForm->render();
        return $output;
    }
}