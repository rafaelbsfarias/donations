# Documentação: asaas-easy-subscription-plugin.php

## Visão Geral
Este é o arquivo principal do plugin WordPress "Asaas Easy Subscription Plugin". Ele serve como ponto de entrada para a inicialização do plugin, definindo constantes essenciais, carregando dependências e registrando hooks necessários para o funcionamento do plugin.

## Estrutura do Arquivo
- **Cabeçalho do Plugin**: Contém metadados como nome, descrição, versão, autor e domínio de texto.
- **Definições de Segurança**: Verificação para impedir acesso direto ao arquivo.
- **Constantes do Plugin**: Definição de constantes para versão, diretório e URL do plugin.
- **Carregamento de Dependências**: Inclusão do arquivo de carregamento principal.
- **Inicialização**: Função de inicialização que instancia e inicia o loader do plugin.

## Funções e Hooks
- `asaas_easy_subscription_plugin_init()`: Função de inicialização chamada no hook `plugins_loaded`. Instancia `Asaas_Plugin_Loader` e chama seu método `init()`.

## Constantes Definidas
- `ASAAS_PLUGIN_VERSION`: Versão atual do plugin (1.1.0).
- `ASAAS_PLUGIN_DIR`: Caminho absoluto do diretório do plugin.
- `ASAAS_PLUGIN_URL`: URL do diretório do plugin.

## Dependências
- Requer `includes/class-plugin-loader.php` para gerenciar a inicialização de componentes do plugin.

## Notas de Segurança
- Inclui verificação `if (!defined('ABSPATH')) { exit; }` para prevenir execução direta.

## Considerações para Produção
- Este arquivo é crítico e deve ser mantido simples, delegando lógica complexa para classes especializadas.