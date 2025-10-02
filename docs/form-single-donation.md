# Documentação: templates/form-single-donation.php

## Visão Geral
Template HTML para formulário de doação única, com seleção de método de pagamento (PIX, boleto, cartão).

## Estrutura
- Campos comuns: nome, email, CPF/CNPJ, valor.
- Select para payment_method.
- Campos condicionais para cartão.
- Honeypot e reCAPTCHA.
- Script inline para reCAPTCHA.

## Funcionalidades
- Campos dinâmicos por método.
- Validação client-side.

## Problemas
- Script inline; deveria ser movido para JS separado.

## Dependências
- `Asaas_Nonce_Manager`.

## Considerações
- Mais complexo que o recorrente devido a múltiplos métodos.