# Documentação: api/class-asaas-api-credit-cards.php

## Visão Geral
Esta classe gerencia operações relacionadas a cartões de crédito na API Asaas, especificamente a tokenização de cartões para pagamentos seguros.

## Estrutura da Classe
- **Propriedade Privada**:
  - `$client`: Instância de `Asaas_API_Client` para requisições.
- **Construtor**: Recebe o cliente da API.
- **Método Principal**:
  - `tokenize_credit_card($card_data, $holder_info, $customer_id)`: Tokeniza um cartão de crédito.

## Funcionalidades
- Validação básica de dados obrigatórios.
- Sanitização de dados sensíveis (número do cartão, CPF/CNPJ, etc.) via `Asaas_Data_Sanitizer`.
- Chamada à API para tokenização.
- Tratamento de erros e retorno estruturado.

## Dependências
- Requer `class-asaas-api-client.php` para comunicação com a API.
- Utiliza `includes/sanitization/class-data-sanitizer.php` para limpeza de dados.

## Notas de Segurança
- Sanitiza dados sensíveis antes de enviar à API.
- Verificação de segurança no topo.

## Considerações para Produção
- Dados de cartão são enviados de forma segura via tokenização; evite logs desnecessários.
- Pode ser expandido para deletar tokens ou outras operações.