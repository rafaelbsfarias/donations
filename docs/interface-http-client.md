# Documentação: api/interfaces/interface-http-client.php

## Visão Geral
Esta interface define o contrato para clientes HTTP no plugin, permitindo abstração e testabilidade de requisições.

## Estrutura da Interface
- **Métodos Abstratos**:
  - `get($url, $headers, $options)`: Requisição GET.
  - `post($url, $body, $headers, $options)`: Requisição POST.
  - `has_error($response)`: Verifica erros.
  - `get_body($response)`: Extrai corpo.

## Funcionalidades
- Padroniza interações HTTP.
- Facilita mocking para testes.

## Dependências
- Nenhuma.

## Notas de Segurança
- Verificação de segurança no topo.

## Considerações para Produção
- Pode ser estendida para outros métodos HTTP.