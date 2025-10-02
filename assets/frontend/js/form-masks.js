/* 
 * form-masks.js
 *
 * Máscaras e formatação de campos:
 *  - Máscara monetária (R$ 1.234,56)
 *  - Máscara dinâmica de CPF/CNPJ
 *  - Máscara de número de cartão (xxxx xxxx xxxx xxxx)
 *  - Máscara de telefone brasileiro ((xx) 9 xxxx-xxxx ou (xx) xxxx-xxxx)
 *  - Máscara de CEP (xx.xxx-xxx)
 */
(function() {
  'use strict';

  /**
   * Formata um valor como moeda (R$ 1.234,56)
   * @param {string|number} value
   * @returns {string}
   */
  function formatCurrency(value) {
    const num = parseFloat(value);
    if (isNaN(num)) {
      return '';
    }
    let formatted = num.toFixed(2).replace('.', ',');
    const parts = formatted.split(',');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
  }

  // --- Helper Functions ---

  /**
   * Configura atributos comuns para campos de entrada numérica.
   * @param {HTMLInputElement} field O elemento do campo.
   * @param {object} options Opções como maxLength.
   */
  function configureNumericInput(field, options = {}) {
    field.setAttribute('inputmode', 'numeric');
    if (options.maxLength) {
      field.setAttribute('maxlength', options.maxLength);
    }
  }

  /**
   * Manipula o evento keydown para restringir a entrada a dígitos e teclas permitidas.
   * @param {KeyboardEvent} event O evento de teclado.
   * @param {object} options Opções como allowPaste e allowCopyCutSelectAll.
   */
  function handleNumericKeyDown(event, options = { allowPaste: false, allowCopyCutSelectAll: true }) {
    const key = event.key;
    const isCtrlOrMeta = event.ctrlKey || event.metaKey;

    if (options.allowPaste && isCtrlOrMeta && key.toLowerCase() === 'v') {
      return; // Permitir colar
    }
    if (options.allowCopyCutSelectAll && isCtrlOrMeta && ['c', 'x', 'a'].includes(key.toLowerCase())) {
      return; // Permitir copiar, recortar, selecionar tudo
    }

    const allowedNavigationKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
    if (allowedNavigationKeys.includes(key)) {
      return; // Permitir teclas de navegação/edição
    }

    // Bloquear se não for um dígito (considerando teclas como Shift, Alt, etc., que não são dígitos mas têm length > 1 ou são especiais)
    if (!/\d/.test(key) || key.length > 1) {
        // Permitir teclas modificadoras sozinhas (Ctrl, Alt, Shift, Meta)
        if (!['Control', 'Alt', 'Shift', 'Meta'].includes(key)) {
             event.preventDefault();
        }
    }
  }

  /**
   * Adiciona um listener ao evento de submit do formulário para limpar o valor do campo.
   * @param {HTMLInputElement} field O elemento do campo.
   * @param {RegExp} cleaningRegex A expressão regular para limpar o valor.
   */
  function addSubmitCleanup(field, cleaningRegex) {
    if (field.form) {
      field.form.addEventListener('submit', function() {
        field.value = field.value.replace(cleaningRegex, '');
      });
    }
  }
  
  /**
   * Formata o valor do campo monetário nos eventos focus e blur.
   * @param {HTMLInputElement} field O elemento do campo.
   * @param {boolean} isBlurEvent Indica se o evento é blur (para limpar o campo se inválido).
   */
  function _formatMoneyFieldOnFocusBlur(field, isBlurEvent) {
    if (field.value) {
      let v = field.value.replace(/\D/g, '');
      if (v) {
        v = (parseInt(v, 10) / 100).toFixed(2);
        field.value = formatCurrency(v);
      } else if (isBlurEvent) {
        field.value = '';
      }
      // Se não for blur e v for vazio, o valor original (que não tinha dígitos) permanece.
    } else if (isBlurEvent) {
      field.value = ''; // Se já estiver vazio no blur, garante que permaneça vazio.
    }
  }

  // --- Mask Application Functions ---

  /**
   * Aplica máscara monetária aos campos de valor de doação
   */
  function applyMoneyMask() {
    const fields = document.querySelectorAll('input[name="donation_value"]');
    fields.forEach(field => {
      // A lógica de 'input' é complexa devido ao gerenciamento da posição do cursor e é mantida.
      field.addEventListener('input', function(e) {
        const start = this.selectionStart;
        const length = this.value.length;
        let v = this.value.replace(/\D/g, '');
        // Se v for '', parseInt(v) é NaN. (NaN/100).toFixed(2) é "NaN". formatCurrency("NaN") retorna ''.
        v = (parseInt(v, 10) / 100).toFixed(2);
        this.value = formatCurrency(v);
        const newLength = this.value.length;
        // Ajusta a posição do cursor
        const pos = start + (newLength - length);
        this.setSelectionRange(pos, pos);
        
        // Armazena o valor formatado no campo oculto
        const formattedField = document.getElementById('formatted-donation-value');
        if (formattedField) {
          formattedField.value = this.value;
        }
      });

      field.addEventListener('focus', function() {
        _formatMoneyFieldOnFocusBlur(this, false);
      });
      field.addEventListener('blur', function() {
        _formatMoneyFieldOnFocusBlur(this, true);
        
        // Atualiza o valor formatado no campo oculto ao sair do campo
        const formattedField = document.getElementById('formatted-donation-value');
        if (formattedField) {
          formattedField.value = this.value;
        }
      });
      
      // Garantir que o valor formatado seja capturado no envio do formulário
      if (field.form) {
        field.form.addEventListener('submit', function() {
          const formattedField = document.getElementById('formatted-donation-value');
          if (formattedField) {
            formattedField.value = field.value;
          }
        });
      }
    });
  }

  /**
   * Aplica máscara dinâmica de CPF ou CNPJ
   * Submete apenas dígitos (remove pontuações no submit)
   */
  function applyDocumentMask() {
    const fields = document.querySelectorAll('input[name="cpf_cnpj"], input.cpf-cnpj');
    fields.forEach(field => {
      configureNumericInput(field);
      
      field.addEventListener('keydown', function(e) {
        handleNumericKeyDown(e, { allowPaste: true }); // Permitir colar, mantendo outros bloqueios
      });
      
      field.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        if (v.length <= 11) { // CPF
          v = v
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else { // CNPJ
          v = v.substr(0, 14) // Limita ao tamanho do CNPJ
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2');
        }
        this.value = v;
      });
      
      addSubmitCleanup(field, /\D/g);
    });
  }

  /**
   * Aplica máscara de número de cartão (xxxx xxxx xxxx xxxx)
   * Submete apenas dígitos (remove espaços no submit)
   */
  function applyCardNumberMask() {
    const fields = document.querySelectorAll('input[name="card_number"], input.cardNumber');
    fields.forEach(field => {
      configureNumericInput(field, { maxLength: '19' }); // 16 dígitos + 3 espaços
      
      field.addEventListener('keydown', function(e) {
        handleNumericKeyDown(e, { allowPaste: true });
      });
      
      field.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').substr(0, 16); // Pega até 16 dígitos
        v = v.match(/.{1,4}/g)?.join(' ') || v; // Adiciona espaços a cada 4 dígitos
        this.value = v;
      });
      
      addSubmitCleanup(field, /\s+/g); // Remove apenas espaços no submit
    });
  }

  /**
   * Aplica máscara de telefone brasileiro (xx) 9 xxxx-xxxx ou (xx) xxxx-xxxx
   * Submete apenas dígitos (remove formatação no submit)
   */
  function applyPhoneMask() {
    const fields = document.querySelectorAll('input[name="phone"], input.phone');
    fields.forEach(field => {
      configureNumericInput(field, { maxLength: '16' }); // (xx) 9 xxxx-xxxx = 16 caracteres
      
      field.addEventListener('keydown', function(e) {
        handleNumericKeyDown(e, { allowPaste: true });
      });
      
      field.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        v = v.substr(0, 11); // Limita a 11 dígitos (com o 9)
        
        // Aplica a formatação conforme digita
        if (v.length > 10) { // Celular com 9 dígitos
          v = v.replace(/^(\d{2})(\d{1})(\d{4})(\d{4})$/, '($1) $2 $3-$4');
        } else if (v.length > 6) { // Telefone sem o 9
          v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
        } else if (v.length > 2) { // Apenas DDD digitado
          v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
        }
        
        this.value = v;
      });
      
      addSubmitCleanup(field, /\D/g);
    });
  }

  /**
   * Aplica máscara de CEP no formato xx.xxx-xxx
   * Submete apenas dígitos (remove formatação no submit)
   */
  function applyCepMask() {
    const fields = document.querySelectorAll('input[name="cep"], input.cep, input#cep');
    fields.forEach(field => {
      configureNumericInput(field, { maxLength: '10' }); // xx.xxx-xxx = 10 caracteres
      
      field.addEventListener('keydown', function(e) {
        handleNumericKeyDown(e, { allowPaste: true });
      });
      
      field.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '');
        v = v.substr(0, 8); // Limita a 8 dígitos
        
        // Aplica a formatação xx.xxx-xxx
        if (v.length > 5) {
          v = v.replace(/^(\d{2})(\d{3})(\d{0,3})$/, '$1.$2-$3');
        } else if (v.length > 2) {
          v = v.replace(/^(\d{2})(\d{0,3})$/, '$1.$2');
        }
        
        this.value = v;
      });
      
      addSubmitCleanup(field, /\D/g);
    });
  }

  // Expor para uso global
  window.AsaasFormMasks = window.AsaasFormMasks || {};
  window.AsaasFormMasks.applyMoneyMask = applyMoneyMask;
  window.AsaasFormMasks.formatCurrency = formatCurrency;
  window.AsaasFormMasks.applyDocumentMask = applyDocumentMask;
  window.AsaasFormMasks.applyCardNumberMask = applyCardNumberMask;
  window.AsaasFormMasks.applyPhoneMask = applyPhoneMask;
  window.AsaasFormMasks.applyCepMask = applyCepMask; // Adicione esta linha

  // Inicialização automática
  document.addEventListener('DOMContentLoaded', function() {
    applyMoneyMask();
    applyDocumentMask();
    applyCardNumberMask();
    applyPhoneMask();
    applyCepMask(); // Adicione esta linha
  });
})();