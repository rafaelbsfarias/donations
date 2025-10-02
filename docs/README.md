# Índice de Documentação do Plugin Asaas Easy Subscription

Esta pasta contém documentação detalhada de todos os arquivos do plugin.

## Arquivos Principais
- [asaas-easy-subscription-plugin.php](asaas-easy-subscription-plugin.md) - Arquivo principal do plugin.

## Admin
- [class-admin-menu.php](class-admin-menu.md) - Menu administrativo.
- [class-admin-settings.php](class-admin-settings.md) - Configurações admin.

## API
- [class-asaas-api-client.php](class-asaas-api-client.md) - Cliente genérico da API.
- [class-asaas-api-credit-cards.php](class-asaas-api-credit-cards.md) - Operações com cartões.
- [class-asaas-api-customers.php](class-asaas-api-customers.md) - Operações com clientes.
- [class-asaas-api-payments.php](class-asaas-api-payments.md) - Operações com pagamentos.
- [class-asaas-api-subscriptions.php](class-asaas-api-subscriptions.md) - Operações com assinaturas.
- [class-wordpress-http-client.php](class-wordpress-http-client.md) - Cliente HTTP WordPress.
- [interface-http-client.php](interface-http-client.md) - Interface para clientes HTTP.

## Assets (Frontend)
- [form-style.css](form-style.md) - Estilos CSS.
- [form-ajax.js](form-ajax.md) - AJAX para formulários.
- [form-masks.js](form-masks.md) - Máscaras de input.
- [form-script.js](form-script.md) - Inicialização.
- [form-ui.js](form-ui.md) - Interface do usuário.
- [form-utils.js](form-utils.md) - Utilitários JS.

## Includes
- [ajax-handler.php](ajax-handler.md) - Handler AJAX.
- [class-asaas-api.php](class-asaas-api.md) - Facade da API.
- [class-form-processor.php](class-form-processor.md) - Processador de formulários.
- [class-plugin-loader.php](class-plugin-loader.md) - Loader do plugin.
- [donation-log-functions.php](donation-log-functions.md) - Funções de log.
- [enqueue-scripts.php](enqueue-scripts.md) - Enfileiramento de scripts.
- [shortcodes.php](shortcodes.md) - Shortcodes.

## Sanitization
- [class-data-sanitizer.php](class-data-sanitizer.md) - Sanitização de dados.
- [class-form-sanitizer.php](class-form-sanitizer.md) - Sanitização de formulários.

## Security
- [class-nonce-manager.php](class-nonce-manager.md) - Gerenciamento de nonces.

## Templates
- [form-recurring-donation.php](form-recurring-donation.md) - Template doação recorrente.
- [form-single-donation.php](form-single-donation.md) - Template doação única.

## Roadmap de Melhorias
- [roadmap/](roadmap/README.md) - Plano gradual de melhorias, organizado em fases seguras.

## Notas Gerais
- Plugin para integrações com Asaas (pagamentos únicos e recorrentes).
- Usa WordPress Settings API, nonces, sanitização.
- Principais violações: duplicação em logs, script inline em template.