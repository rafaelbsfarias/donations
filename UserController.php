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

    public function handleRequest() {
        $output = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user_form'])) {
            if (!isset($_POST['create_user_nonce']) || !wp_verify_nonce($_POST['create_user_nonce'], 'create_user_action')) {
                $output .= '<p>Falha na verificação de segurança.</p>';
            } else {
                $name    = sanitize_text_field($_POST['user_name']);
                $email   = sanitize_email($_POST['user_email']);
                $cpfCnpj = sanitize_text_field($_POST['user_cpfcnpj']);

                $user = new User($name, $email, $cpfCnpj);
                $customerResult = $this->userService->createUser($user);

                if ($customerResult && isset($customerResult['id'])) {
                    $output .= '<h3>Cliente Criado com Sucesso:</h3><pre>' . print_r($customerResult, true) . '</pre>';

                    require_once __DIR__ . '/SubscriptionService.php';
                    $subscriptionService = new SubscriptionService();

                    $subscriptionData = array(
                        "billingType" => "CREDIT_CARD",
                        "cycle"       => "MONTHLY",
                        "customer"    => $customerResult['id'],
                        "value"       => 100,
                        "nextDueDate" => date('Y-m-d', strtotime('+1 month')),
                        "callback"    => array(
                            "successUrl" => home_url('/sucesso/', 'https')
                        )
                    );

                    $subscriptionResult = $subscriptionService->create_subscription($subscriptionData);
                    if ($subscriptionResult && isset($subscriptionResult['paymentLink']) && !empty($subscriptionResult['paymentLink'])) {
                        $paymentLink = $subscriptionResult['paymentLink'];
                        $output .= '<h3>Assinatura Criada com Sucesso:</h3><pre>' . print_r($subscriptionResult, true) . '</pre>';
                        $output .= '<p><a href="' . esc_url($paymentLink) . '" target="_blank" rel="noopener noreferrer">Clique aqui para realizar o pagamento</a></p>';
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
        $output .= $this->userForm->render();
        return $output;
    }
}
