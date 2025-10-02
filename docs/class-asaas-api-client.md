# Documentação: api/class-asaas-api-client.php

## Visão Geral
Esta classe implementa um cliente genérico para interagir com a API do Asaas. Ela abstrai as requisições HTTP (GET e POST) e gerencia autenticação via token de acesso.

## Estrutura da Classe
- **Propriedades Privadas**:
  - `$api_base_url`: URL base da API Asaas.
  - `$access_token`: Token de autenticação.
  - `$http_client`: Instância de cliente HTTP que implementa `Asaas_HTTP_Client_Interface`.
- **Construtor**: Inicializa as propriedades.
- **Métodos Públicos**:
  - `get($endpoint, $params)`: Realiza requisição GET.
  - `post($endpoint, $data)`: Realiza requisição POST.

## Funcionalidades
- Suporte a requisições GET com parâmetros de query.
- Suporte a requisições POST com dados JSON.
- Tratamento de erros via cliente HTTP.
- Autenticação automática com header `access_token`.

## Dependências
- Requer `api/interfaces/interface-http-client.php` para a interface do cliente HTTP.
- Utiliza `class-wordpress-http-client.php` ou similar para implementação concreta.

## Notas de Segurança
- Verificação de segurança no topo.
- Usa `sslverify => true` para conexões HTTPS seguras.

## Considerações para Produção
- Pode ser estendido para suportar outros métodos HTTP (PUT, DELETE).
- Logs de erro podem ser adicionados para debugging.