# Documentação: includes/security/class-nonce-manager.php

## Visão Geral
Classe para gerenciamento de nonces WordPress, prevenindo ataques CSRF.

## Constantes
- `NONCE_PREFIX`: Prefixo para ações.
- `ACTION_SINGLE_DONATION`, `ACTION_RECURRING_DONATION`: Ações específicas.

## Métodos
- `generate_nonce_field($action)`: Gera campo hidden com nonce.
- `verify_nonce($data, $action)`: Verifica nonce.

## Funcionalidades
- Usa `wp_nonce_field` e `wp_verify_nonce`.

## Dependências
- Funções WordPress.

## Considerações
- Essencial para segurança de formulários.