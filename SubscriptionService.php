<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Settings')) {
    require_once __DIR__ . '/settings.php';
    $settings = new Settings();
} else {
    global $settings;
    if (!isset($settings)) {
        $settings = new Settings();
    }
}

require_once __DIR__ . '/EncryptionHelper.php';

class SubscriptionService {
    private $api_url;
    private $api_key;

    public function __construct() {
        global $settings;
        $base_url = $settings->get('BASE_URL');
        $this->api_url = rtrim($base_url, '/') . '/subscriptions';
        $encryptedKey = get_option('donations_api_key_encrypted');
        if ($encryptedKey && defined('SECRET_ENCRYPTION_KEY')) {
            $this->api_key = EncryptionHelper::decrypt($encryptedKey, SECRET_ENCRYPTION_KEY);
        } else {
            $this->api_key = '';
        }
    }

    public function create_subscription($subscriptionData) {
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'access_token' => $this->api_key,
                'accept'       => 'application/json',
                'User-Agent'   => 'teste'
            ),
            'body'    => wp_json_encode($subscriptionData),
            'timeout' => 60,
        );

        $response = wp_remote_post($this->api_url, $args);
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
