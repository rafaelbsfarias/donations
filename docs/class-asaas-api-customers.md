# Documentação: api/class-asaas-api-customers.php

## Visão Geral
Esta classe gerencia operações relacionadas a clientes na API Asaas, incluindo busca por CPF/CNPJ e criação de novos clientes.

## Estrutura da Classe
- **Propriedade Privada**:
  - `$client`: Instância de `Asaas_API_Client`.
- **Construtor**: Recebe o cliente da API.
- **Métodos**:
  - `find_by_cpf_cnpj($cpf_cnpj)`: Busca cliente existente.
  - `create_customer($customer_data)`: Cria novo cliente.

## Funcionalidades
- Sanitização de CPF/CNPJ.
- Busca de clientes via GET com filtro.
- Criação de clientes via POST.
- Tratamento de erros e validações básicas.

## Dependências
- Requer `class-asaas-api-client.php`.
- Utiliza `includes/sanitization/class-data-sanitizer.php`.

## Notas de Segurança
- Sanitiza dados antes de enviar à API.
- Verificação de segurança no topo.

## Considerações para Produção
- Método `find_by_cpf_cnpj` assume que o primeiro resultado é o correto; pode precisar de paginação para múltiplos clientes.