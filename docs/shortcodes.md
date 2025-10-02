# Documentação: includes/shortcodes.php

## Visão Geral
Define shortcodes para exibir formulários de doação.

## Shortcodes
- `[asaas_recurring_donation]`: Formulário de doação recorrente.
- `[asaas_single_donation]`: Formulário de doação única.

## Funcionalidades
- Carrega templates correspondentes.
- Passa dados de contexto (tipo, nonce).

## Dependências
- Templates em `templates/`, `class-nonce-manager.php`.

## Considerações
- Usa `ob_start()` para capturar output.