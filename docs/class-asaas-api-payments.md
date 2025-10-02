# Documentação: api/class-asaas-api-payments.php

## Visão Geral
Esta classe gerencia operações de pagamentos na API Asaas, incluindo criação de pagamentos únicos e obtenção de QR Code PIX.

## Estrutura da Classe
- **Propriedade Privada**:
  - `$client`: Instância de `Asaas_API_Client`.
- **Construtor**: Recebe o cliente da API.
- **Métodos**:
  - `create_payment($payment_data)`: Cria pagamento único.
  - `get_pix_qrcode($payment_id)`: Obtém QR Code PIX para pagamento.

## Funcionalidades
- Validação de dados obrigatórios para pagamentos.
- Sanitização de valores monetários.
- Suporte a diferentes tipos de cobrança (cartão, PIX, etc.).
- Tratamento de erros da API.

## Dependências
- Requer `class-asaas-api-client.php`.
- Utiliza `includes/sanitization/class-data-sanitizer.php`.

## Notas de Segurança
- Sanitiza valores antes de enviar.
- Verificação de segurança no topo.

## Considerações para Produção
- Método `create_payment` pode ser expandido para assinaturas.
- QR Code PIX é gerado dinamicamente.