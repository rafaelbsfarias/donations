# Documentação: admin/class-admin-menu.php

## Visão Geral
Esta classe é responsável por adicionar e gerenciar o menu administrativo do plugin Asaas no painel do WordPress. Ela cria uma página de configurações onde os administradores podem ajustar as opções do plugin.

## Estrutura da Classe
- **Construtor**: Registra o hook `admin_menu` para adicionar o menu.
- **Métodos**:
  - `add_admin_menu()`: Adiciona a página de menu usando `add_menu_page()`.
  - `render_settings_page()`: Renderiza o HTML da página de configurações, incluindo formulário com campos de opções.

## Funcionalidades
- Adiciona um menu principal no admin com ícone e posição específica.
- Renderiza uma página de configurações básica com seções e campos gerenciados pelo WordPress Settings API.

## Hooks Utilizados
- `admin_menu`: Hook para adicionar itens de menu no admin.

## Dependências
- Utiliza funções do WordPress como `add_menu_page`, `settings_fields`, `do_settings_sections`.
- Depende de outras classes para registrar seções e campos de configurações (não incluído nesta classe).

## Notas de Segurança
- Inclui verificação de segurança no topo do arquivo.

## Considerações para Produção
- A página de configurações é básica; em versões futuras, pode ser expandida com validações e sanitizações adicionais.