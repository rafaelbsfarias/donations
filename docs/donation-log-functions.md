# Documentação: includes/donation-log-functions.php

## Visão Geral
Conjunto de funções para logging de doações, payloads API, tentativas suspeitas, IPs bloqueados e scores reCAPTCHA.

## Funções Principais
- `asaas_log_donation`: Registra doações.
- `asaas_log_api_payload`: Registra payloads enviados à API.
- `asaas_log_suspicious_attempt`: Registra tentativas suspeitas.
- `asaas_log_blocked_ip`: Registra IPs bloqueados.
- `asaas_log_recaptcha_score`: Registra scores reCAPTCHA.

## Funcionalidades
- Criação automática de diretório de logs com proteção (.htaccess, index.php).
- Sanitização de dados.
- Offset de timezone do WordPress.

## Problemas Identificados
- Código duplicado: bloco de criação de diretório repetido em todas as funções.

## Dependências
- Funções WordPress (wp_upload_dir, etc.).

## Considerações para Produção
- Refatorar para eliminar duplicação.