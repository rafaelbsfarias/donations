# Documentação: api/class-wordpress-http-client.php

## Visão Geral
Esta classe implementa a interface `Asaas_HTTP_Client_Interface` usando as funções HTTP nativas do WordPress (`wp_remote_get`, `wp_remote_post`).

## Estrutura da Classe
- Implementa `Asaas_HTTP_Client_Interface`.
- **Métodos**:
  - `get($url, $headers, $options)`: Requisição GET.
  - `post($url, $body, $headers, $options)`: Requisição POST.
  - `has_error($response)`: Verifica erros.
  - `get_body($response)`: Extrai corpo da resposta.

## Funcionalidades
- Abstrai chamadas HTTP do WordPress.
- Configurações padrão para timeout e SSL.
- Tratamento de erros via WP_Error.

## Dependências
- Requer `api/interfaces/interface-http-client.php`.

## Notas de Segurança
- Usa `sslverify => true` por padrão.
- Verificação de segurança no topo.

## Considerações para Produção
- Timeout padrão de 30s pode ser ajustado para APIs externas.