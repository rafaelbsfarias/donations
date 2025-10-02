# Documentação: includes/ajax-handler.php

## Visão Geral
Gerencia requisições AJAX para processamento de doações, incluindo validações de segurança, reCAPTCHA e processamento via `Asaas_Form_Processor`.

## Estrutura do Arquivo
- **Registro de Handlers**: Registra `wp_ajax_process_donation`.
- **Função Principal**: `asaas_process_donation()` - valida e processa doações.
- **Validações**:
  - Método POST.
  - Nonce.
  - reCAPTCHA.
  - Anti-bot (honeypot, velocidade, IP).
- **Processamento**: Chama `Asaas_Form_Processor::process_form()`.

## Funcionalidades
- Logs extensivos para debugging.
- Tratamento de erros estruturado.
- Suporte a doações únicas e recorrentes.

## Dependências
- `class-form-processor.php`, `class-nonce-manager.php`, `donation-log-functions.php`.

## Notas de Segurança
- Verificações múltiplas contra bots.
- Sanitização de inputs.

## Considerações para Produção
- Reduzir logs em produção para performance.