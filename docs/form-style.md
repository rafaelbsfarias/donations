# Documentação: assets/frontend/css/form-style.css

## Visão Geral
Este arquivo CSS contém estilos para os formulários de doação do plugin, incluindo layouts responsivos, cores, tipografia e interações.

## Estrutura do Arquivo
- **Estilos Gerais**: Layout do formulário principal (`.asaas-donation-form`), títulos, parágrafos.
- **Campos de Formulário**: Estilos para inputs, selects, labels, foco e validação.
- **Botões**: Estilos para botões de submit, incluindo estados hover e disabled.
- **Mensagens**: Estilos para mensagens de sucesso/erro (`.asaas-message`).
- **Campos Específicos**: Estilos para cartões de crédito, PIX, boletos (linhas 100+).
- **Responsividade**: Media queries para dispositivos móveis.

## Funcionalidades Principais
- Design moderno com bordas arredondadas e sombras.
- Tema azul (#0066cc) para botões e focos.
- Suporte a diferentes métodos de pagamento (cartão, PIX, boleto).
- Animações suaves (transitions).

## Dependências
- Nenhum arquivo específico; usado pelos templates PHP.

## Notas de Design
- Usa font-family do sistema para performance.
- Cores acessíveis (contraste adequado).

## Considerações para Produção
- Minificar para produção.
- Verificar compatibilidade cross-browser.