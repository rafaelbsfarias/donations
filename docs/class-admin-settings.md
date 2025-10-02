# Documentação: admin/class-admin-settings.php

## Visão Geral
Esta classe gerencia as configurações administrativas do plugin Asaas, utilizando o WordPress Settings API para registrar opções, seções e campos de configuração. Permite aos administradores configurar chaves de API, ambiente, reCAPTCHA e logs.

## Estrutura da Classe
- **Construtor**: Registra o hook `admin_init` para inicializar as configurações.
- **Métodos de Registro**:
  - `register_settings()`: Registra opções, seções e campos usando `register_setting`, `add_settings_section` e `add_settings_field`.
- **Métodos de Renderização**: Renderizam os campos de formulário (input, select, checkbox).
- **Método Utilitário**:
  - `get_api_base_url()`: Retorna a URL base da API Asaas com base no ambiente selecionado.

## Opções Registradas
- `asaas_api_key`: Chave da API Asaas.
- `asaas_environment`: Ambiente (sandbox ou production).
- `asaas_recaptcha_site_key`: Chave do site reCAPTCHA.
- `asaas_recaptcha_secret_key`: Chave secreta reCAPTCHA.
- `asaas_enable_donation_logs`: Habilita/desabilita logs de doações.

## Hooks Utilizados
- `admin_init`: Hook para registrar configurações no admin.

## Dependências
- Utiliza funções do WordPress Settings API.
- Depende de `class-admin-menu.php` para exibir a página de configurações.

## Notas de Segurança
- Campos de input usam `esc_attr` para escapar valores.
- Verificação de segurança no topo do arquivo.

## Considerações para Produção
- As chaves de API e reCAPTCHA devem ser tratadas com cuidado; considere criptografia para armazenamento.
- O método `get_api_base_url` é estático e pode ser usado por outras classes.