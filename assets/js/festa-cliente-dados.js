/* ═══════════════════════════════════════════════════
   EventsHouse — Dados da reserva na Confirmar reserva
   ═══════════════════════════════════════════════════ */
(function () {
    'use strict';

    var form = document.getElementById('order_review');
    var dados = document.getElementById('eh-pay-dados');
    if (!form || !dados) return;

    var ccFileInput = dados.querySelector('.eh-file-input');
    var ccFileList = dados.querySelector('.eh-file-list');
    var otherRadio = dados.querySelector('.eh-other-radio');
    var otherInput = dados.querySelector('.eh-other-input');
    var purposeRadios = dados.querySelectorAll('input[name="event_purpose"]');

    var CC_MAX_FILES = 5;
    var CC_MAX_SIZE = 10 * 1024 * 1024;

    function formatFileSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function ccFilesState() {
        var result = { valid: true, tooMany: false, tooLarge: [], count: 0 };
        if (!ccFileInput || !ccFileInput.files) return result;
        var files = ccFileInput.files;
        result.count = files.length;
        if (files.length > CC_MAX_FILES) {
            result.valid = false;
            result.tooMany = true;
        }
        for (var i = 0; i < files.length; i++) {
            if (files[i].size > CC_MAX_SIZE) {
                result.valid = false;
                result.tooLarge.push(files[i].name);
            }
        }
        return result;
    }

    function renderCcFileList() {
        if (!ccFileList || !ccFileInput || !ccFileInput.files) return;
        ccFileList.innerHTML = '';
        var files = ccFileInput.files;
        var tooMany = files.length > CC_MAX_FILES;
        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            var isInvalid = tooMany || f.size > CC_MAX_SIZE;
            var row = document.createElement('div');
            row.className = 'eh-file-list-item' + (isInvalid ? ' is-invalid' : '');
            var nameSpan = document.createElement('span');
            nameSpan.className = 'eh-file-name';
            nameSpan.textContent = (isInvalid ? '⚠️ ' : '📎 ') + f.name;
            var sizeSpan = document.createElement('span');
            sizeSpan.className = 'eh-file-size';
            sizeSpan.textContent = formatFileSize(f.size);
            row.appendChild(nameSpan);
            row.appendChild(sizeSpan);
            ccFileList.appendChild(row);
        }
    }

    function showError(msg) {
        window.alert(msg);
        dados.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    if (ccFileInput) {
        ccFileInput.addEventListener('change', function () {
            renderCcFileList();
            var state = ccFilesState();
            if (state.tooMany) {
                showError('Pode carregar no máximo ' + CC_MAX_FILES + ' ficheiros na Cópia do CC.');
            } else if (state.tooLarge.length) {
                showError('Cada ficheiro deve ter no máximo 10 MB. Verifique: ' + state.tooLarge.join(', '));
            }
        });
    }

    if (purposeRadios && purposeRadios.length) {
        purposeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (!otherInput) return;
                if (otherRadio && otherRadio.checked) {
                    otherInput.disabled = false;
                    otherInput.focus();
                } else {
                    otherInput.disabled = true;
                    otherInput.value = '';
                }
            });
        });
    }
    if (otherInput) {
        otherInput.addEventListener('input', function () {
            if (otherRadio && otherInput.value.trim() !== '') {
                otherRadio.checked = true;
            }
        });
        otherInput.addEventListener('focus', function () {
            if (otherRadio) otherRadio.checked = true;
        });
    }

    form.addEventListener('submit', function (e) {
        var ccState = ccFilesState();
        if (!ccFileInput || !ccFileInput.files || !ccFileInput.files.length) {
            e.preventDefault();
            showError('Por favor carregue a cópia do CC.');
            return;
        }
        if (ccState.tooMany) {
            e.preventDefault();
            showError('Pode carregar no máximo ' + CC_MAX_FILES + ' ficheiros na Cópia do CC.');
            return;
        }
        if (ccState.tooLarge.length) {
            e.preventDefault();
            showError('Cada ficheiro deve ter no máximo 10 MB. Verifique: ' + ccState.tooLarge.join(', '));
            return;
        }
        if (!dados.querySelector('[name="how_found"]:checked')) {
            e.preventDefault();
            showError('Por favor indique como chegou até nós.');
            return;
        }
        var purposeInput = dados.querySelector('[name="event_purpose"]:checked');
        if (!purposeInput) {
            e.preventDefault();
            showError('Por favor indique a finalidade do evento.');
            return;
        }
        if (purposeInput.value === 'outra' && (!otherInput || !otherInput.value.trim())) {
            e.preventDefault();
            showError('Por favor especifique a finalidade do evento.');
            return;
        }
        if (!dados.querySelector('[name="wants_nif"]:checked')) {
            e.preventDefault();
            showError('Por favor indique se deseja colocar NIF na fatura.');
        }
    });
})();
