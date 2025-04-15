<?php
require_once 'User.php';
require_once 'UserService.php';
require_once 'UserForm.php';

class UserController {
    private $userService;
    private $userForm;

    public function __construct(UserService $userService, UserForm $userForm) {
        $this->userService = $userService;
        $this->userForm = $userForm;
    }

    /**
     * Processa o envio do formulário: cria o cliente e, se criado com sucesso, cria a assinatura.
     *
     * @return string
     */
    public function handleRequest() {
        $output = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user_form'])) {
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
                    $output .= '<h3>Cliente Criado com Sucesso:</h3><pre>' . print_r($customerResult, true) . '</pre>';

                    // Coleta dados do cartão e do titular
                    $holderName = $name; // Assume que o titular é o mesmo que o usuário
                    $cardNumber = sanitize_text_field($_POST['user_cardNumber']);
                    $expiryMonth = sanitize_text_field($_POST['user_expiryMonth']);
                    $expiryYear = sanitize_text_field($_POST['user_expiryYear']);
                    $ccv = sanitize_text_field($_POST['user_ccv']);

                    $postalCode = sanitize_text_field($_POST['user_postalCode']);
                    $addressNumber = sanitize_text_field($_POST['user_addressNumber']);
                    $addressComplement = sanitize_text_field(isset($_POST['user_addressComplement']) ? $_POST['user_addressComplement'] : '');
                    $phone = sanitize_text_field($_POST['user_phone']);
                    $mobilePhone = sanitize_text_field(isset($_POST['user_mobilePhone']) ? $_POST['user_mobilePhone'] : '');

                    // Cria dados da assinatura
                    $subscriptionData = array(
                        "billingType" => "CREDIT_CARD",
                        "cycle"       => "MONTHLY",
                        "customer"    => $customerResult['id'],
                        "value"       => 100,
                        "nextDueDate" => date('Y-m-d'), // Define para hoje para cobrança imediata
                        "creditCard" => array(
                            "holderName" => $holderName,
                            "number" => $cardNumber,
                            "expiryMonth" => $expiryMonth,
                            "expiryYear" => $expiryYear,
                            "ccv" => $ccv
                        ),
                        "creditCardHolderInfo" => array(
                            "name" => $name,
                            "email" => $email,
                            "cpfCnpj" => $cpfCnpj,
                            "postalCode" => $postalCode,
                            "addressNumber" => $addressNumber,
                            "addressComplement" => $addressComplement,
                            "phone" => $phone,
                            "mobilePhone" => $mobilePhone
                        )
                    );

                    // Cria a assinatura usando SubscriptionService
                    require_once __DIR__ . '/SubscriptionService.php';
                    $subscriptionService = new SubscriptionService();
                    $subscriptionResult = $subscriptionService->create_subscription($subscriptionData);

                    if ($subscriptionResult && isset($subscriptionResult['id'])) {
                        $output .= '<h3>Assinatura Criada com Sucesso:</h3><pre>' . print_r($subscriptionResult, true) . '</pre>';
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
        // Renderiza o formulário
        $output .= $this->userForm->render();
        return $output;
    }
}