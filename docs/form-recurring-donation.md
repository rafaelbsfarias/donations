# Documentação: templates/form-recurring-donation.php

## Visão Geral
Template HTML para formulário de doação mensal (assinatura).

## Estrutura
- Campos: nome, email, CPF/CNPJ, valor, cartão (número, validade, CCV, CEP, endereço, telefone).
- Hidden: action, donation_type, nonce.
- Botão submit.

## Funcionalidades
- Campos obrigatórios marcados.
- Labels em português.

## Dependências
- `Asaas_Nonce_Manager` para nonce.

## Considerações
- Sem script inline; lógica em JS separado.