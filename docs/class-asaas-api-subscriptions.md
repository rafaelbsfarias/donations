# Documentação: api/class-asaas-api-subscriptions.php

## Visão Geral
Esta classe gerencia operações de assinaturas recorrentes na API Asaas, incluindo criação e cancelamento de assinaturas.

## Estrutura da Classe
- **Propriedade Privada**:
  - `$client`: Instância de `Asaas_API_Client`.
- **Construtor**: Recebe o cliente da API.
- **Métodos**:
  - `create_subscription($subscription_data)`: Cria assinatura recorrente.
  - `cancel_subscription($subscription_id)`: Cancela assinatura existente.

## Funcionalidades
- Validação de dados obrigatórios.
- Sanitização de valores.
- Suporte a cartões tokenizados.
- Tratamento de erros.

## Dependências
- Requer `class-asaas-api-client.php`.
- Utiliza `includes/sanitization/class-data-sanitizer.php`.

## Notas de Segurança
- Sanitiza dados antes de enviar.
- Verificação de segurança no topo.

## Considerações para Produção
- Cancelamento pode ser expandido com confirmações adicionais.