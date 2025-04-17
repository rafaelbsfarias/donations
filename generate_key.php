<?php
// generate_key.php

require_once 'encryptionHelper.php';

// Certifique-se de definir a constante no seu wp-config.php ou aqui para o teste
if (!defined('SECRET_ENCRYPTION_KEY')) {
    define('SECRET_ENCRYPTION_KEY', 'iGEn1y6EganOunlDk7iyw/ffCaoOHQHDYPIiHQHo68Y=');
}

// Insira aqui a sua API key em formato bruto
$raw_api_key = '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjM1MDQyZWVhLTUxNGMtNDVmYS04ZTNlLTI2NTQ3ZjMxZjQ4Mzo6JGFhY2hfZjc4OTYzNzktZjIzNi00ZGUzLWI1ZWEtYjY1YjQ4NjI1YzFj';

// Criptografa a API key usando a função encrypt da classe EncryptionHelper
$encrypted_key = EncryptionHelper::encrypt($raw_api_key, SECRET_ENCRYPTION_KEY);

echo "Valor criptografado: " . $encrypted_key;
