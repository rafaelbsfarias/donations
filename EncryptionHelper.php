<?php
class EncryptionHelper {
    const CIPHER = 'aes-256-ctr';
    const IV_LENGTH = 16; // Tamanho do IV para AES-256-CTR
    
    /**
     * Criptografa um valor usando AES-256-CTR.
     *
     * @param string $plaintext Valor a ser criptografado.
     * @param string $key Chave mestra para criptografia (opcional se SECRET_ENCRYPTION_KEY estiver definida).
     * @return string Valor criptografado (IV e texto concatenados e codificados em base64).
     */
    public static function encrypt($plaintext, $key = null) {
        if ($key === null) {
            if (!defined('SECRET_ENCRYPTION_KEY')) {
                throw new Exception('Chave de criptografia não definida. Defina SECRET_ENCRYPTION_KEY no wp-config.php');
            }
            $key = SECRET_ENCRYPTION_KEY;
        }
        
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt($plaintext, self::CIPHER, $key, 0, $iv);
        return base64_encode($iv . ':' . $encrypted);
    }

    /**
     * Descriptografa um valor encriptado.
     *
     * @param string $encryptedValue Valor criptografado (em base64).
     * @param string $key Chave mestra utilizada na criptografia (opcional se SECRET_ENCRYPTION_KEY estiver definida).
     * @return string|false Valor original descriptografado ou false se houver erro.
     */
    public static function decrypt($encryptedValue, $key = null) {
        if ($key === null) {
            if (!defined('SECRET_ENCRYPTION_KEY')) {
                throw new Exception('Chave de criptografia não definida. Defina SECRET_ENCRYPTION_KEY no wp-config.php');
            }
            $key = SECRET_ENCRYPTION_KEY;
        }
        
        $decoded = base64_decode($encryptedValue);
        $parts = explode(':', $decoded, 2);
        if (count($parts) !== 2) {
            return false;
        }
        list($iv, $encrypted) = $parts;
        return openssl_decrypt($encrypted, self::CIPHER, $key, 0, $iv);
    }
}
