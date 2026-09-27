/**
 * A Ilha — painel da receção
 */
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.rececao-nav-item');
    const tabContents = document.querySelectorAll('.rececao-tab');

    navItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const target = this.getAttribute('data-tab');
            if (!target) {
                return;
            }

            navItems.forEach(function (n) {
                n.classList.remove('active');
            });
            this.classList.add('active');

            tabContents.forEach(function (t) {
                t.classList.remove('is-active');
            });
            const panel = document.getElementById('tab-' + target);
            if (panel) {
                panel.classList.add('is-active');
            }

            if (target === 'historico' && typeof carregarHistorico === 'function') {
                carregarHistorico();
            }
            if (target === 'reservas' && typeof carregarReservas === 'function') {
                carregarReservas();
            }
        });
    });

    const searchInput = document.getElementById('search-client-input');
    const searchResults = document.getElementById('search-results');
    const spinner = document.getElementById('search-spinner');
    let searchTimeout;
    let rececaoClienteAtivo = null;

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const termo = this.value.trim();

            if (termo.length < 2) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(function () {
                fetch(ilhaRececaoData.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ilha_search_client',
                        nonce: ilhaRececaoData.nonce,
                        term: termo,
                    }),
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (spinner) {
                            spinner.style.display = 'none';
                        }
                        searchResults.innerHTML = '';
                        if (data.success && data.data.length > 0) {
                            data.data.forEach(function (cliente) {
                                const li = document.createElement('li');
                                li.innerHTML =
                                    '<strong>' +
                                    cliente.nome +
                                    '</strong><span>' +
                                    (cliente.email || 'Sem email') +
                                    ' | ' +
                                    (cliente.telefone || 'Sem telefone') +
                                    '</span>';
                                li.addEventListener('click', function () {
                                    selecionarCliente(cliente);
                                });
                                searchResults.appendChild(li);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML =
                                '<li class="rececao-search-empty">Nenhum cliente encontrado.</li>';
                            searchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (
                e.target !== searchInput &&
                !searchResults.contains(e.target)
            ) {
                searchResults.style.display = 'none';
            }
        });
    }

    function escapeHtml(str) {
        if (!str) {
            return '';
        }
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function selecionarCliente(cliente) {
        rececaoClienteAtivo = cliente;
        if (!searchInput || !searchResults) {
            return;
        }
        searchInput.value = '';
        searchResults.style.display = 'none';

        document.getElementById('cp-nome').textContent = cliente.nome;
        document.getElementById('cp-email').textContent =
            cliente.email || cliente.telefone;

        const detalhesContainer = document.getElementById('client-pass-details');
        const cardPasse = document.getElementById('active-pass-card');

        detalhesContainer.style.display = 'block';
        cardPasse.innerHTML = '<p class="rececao-loading">A procurar passe ativo…</p>';

        fetch(ilhaRececaoData.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'ilha_get_active_pass',
                nonce: ilhaRececaoData.nonce,
                cliente_id: cliente.id,
            }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success && data.data) {
                    const passe = data.data;
                    const diasRestantes = passe.dias_total - passe.dias_usados;

                    let html = '';
                    if (passe.reserva_hoje) {
                        const hora = passe.reserva_hoje.event_time || '';
                        html +=
                            '<div class="rececao-reserva-banner" role="status">' +
                            '<strong>Reserva hoje</strong>' +
                            (hora ? ' · ' + hora : '') +
                            '</div>';
                    }

                    const extras = passe.extras || {};
                    if (extras.tem_extras) {
                        html +=
                            '<div class="rececao-extras-banner" role="status">' +
                            '<strong>Extras no passe</strong>' +
                            '<p class="rececao-extras-banner__text">' +
                            escapeHtml(extras.label_resumo || '') +
                            '</p></div>';

                        if (
                            extras.adultos_restantes > 0 ||
                            extras.criancas_restantes > 0
                        ) {
                            html +=
                                '<div class="rececao-extras-checkin" id="rececao-extras-checkin">' +
                                '<p class="rececao-extras-checkin__title">Validar extras nesta entrada</p>';
                            if (extras.adultos_restantes > 0) {
                                html +=
                                    '<label class="rececao-extras-field">' +
                                    '<span>Adultos extra</span>' +
                                    '<input type="number" id="extra-adultos-usar" min="0" max="' +
                                    extras.adultos_restantes +
                                    '" value="0" inputmode="numeric" />' +
                                    '<span class="rececao-extras-field__max">máx. ' +
                                    extras.adultos_restantes +
                                    '</span></label>';
                            }
                            if (extras.criancas_restantes > 0) {
                                html +=
                                    '<label class="rececao-extras-field">' +
                                    '<span>Crianças extra</span>' +
                                    '<input type="number" id="extra-criancas-usar" min="0" max="' +
                                    extras.criancas_restantes +
                                    '" value="0" inputmode="numeric" />' +
                                    '<span class="rececao-extras-field__max">máx. ' +
                                    extras.criancas_restantes +
                                    '</span></label>';
                            }
                            html += '</div>';
                        }
                    }

                    html +=
                        '<div class="pass-badge">' +
                        passe.tipo_formatado +
                        '</div>' +
                        '<div class="pass-stats">' +
                        '<div class="stat-item"><span class="stat-value">' +
                        passe.dias_total +
                        '</span><span class="stat-label">Total</span></div>' +
                        '<div class="stat-item"><span class="stat-value" style="color:' +
                        (diasRestantes > 0 ? 'var(--rec-accent)' : 'var(--rec-danger)') +
                        '">' +
                        diasRestantes +
                        '</span><span class="stat-label">Restantes</span></div>' +
                        '<div class="stat-item"><span class="stat-value">' +
                        passe.dias_usados +
                        '</span><span class="stat-label">Usados</span></div>' +
                        '</div>';

                    if (diasRestantes > 0) {
                        html +=
                            '<button type="button" class="btn-checkin" onclick="registarCheckin(' +
                            passe.id +
                            ', ' +
                            cliente.id +
                            ')">Registar entrada (−1 dia)</button>';
                    } else {
                        html +=
                            '<button type="button" class="btn-checkin" disabled>Passe esgotado</button>';
                    }

                    html +=
                        '<div id="checkin-msg" class="rececao-checkin-msg" style="display:none"></div>';

                    cardPasse.innerHTML = html;
                } else {
                    cardPasse.innerHTML =
                        '<div class="pass-empty"><p>' +
                        (data.data ||
                            'O cliente não tem nenhum passe ou pack ativo.') +
                        '</p></div>';
                }
            });
    }

    const meiasModal = document.getElementById('rececao-meias-modal');
    const meiasModalBody = document.getElementById('rececao-meias-modal-body');

    function closeMeiasModal() {
        if (!meiasModal) {
            return;
        }
        meiasModal.hidden = true;
        meiasModal.setAttribute('aria-hidden', 'true');
        if (meiasModalBody) {
            meiasModalBody.innerHTML = '';
        }
    }

    function openMeiasModal() {
        if (!meiasModal) {
            return;
        }
        meiasModal.hidden = false;
        meiasModal.setAttribute('aria-hidden', 'false');
    }

    function getCheckinExtras() {
        const extraAdultInput = document.getElementById('extra-adultos-usar');
        const extraChildInput = document.getElementById('extra-criancas-usar');
        return {
            extraAdults: extraAdultInput
                ? parseInt(extraAdultInput.value, 10) || 0
                : 0,
            extraChildren: extraChildInput
                ? parseInt(extraChildInput.value, 10) || 0
                : 0,
        };
    }

    function onCheckinSuccess(btn, msgDiv, data) {
        msgDiv.style.display = 'block';
        msgDiv.className = 'rececao-checkin-msg rececao-checkin-msg--ok';
        msgDiv.textContent =
            (data.data && data.data.mensagem) ||
            'Check-in registado com sucesso.';
        if (btn) {
            btn.style.display = 'none';
        }
        const extrasBox = document.getElementById('rececao-extras-checkin');
        if (extrasBox) {
            extrasBox.style.display = 'none';
        }
    }

    function executarCheckin(passe_id, cliente_id, meias_order_id, btn) {
        const msgDiv = document.getElementById('checkin-msg');
        if (!msgDiv) {
            return;
        }

        const extras = getCheckinExtras();

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'A processar…';
        }

        const params = {
            action: 'ilha_checkin',
            nonce: ilhaRececaoData.nonce,
            passe_id: passe_id,
            cliente_id: cliente_id,
            extra_adultos: extras.extraAdults,
            extra_criancas: extras.extraChildren,
        };
        if (meias_order_id) {
            params.meias_order_id = meias_order_id;
        }

        fetch(ilhaRececaoData.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(params),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    closeMeiasModal();
                    onCheckinSuccess(btn, msgDiv, data);
                } else {
                    msgDiv.style.display = 'block';
                    msgDiv.className = 'rececao-checkin-msg rececao-checkin-msg--err';
                    msgDiv.textContent = 'Erro: ' + data.data;
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'Registar entrada (−1 dia)';
                    }
                }
            });
    }

    function formatMeiasPreco(meias) {
        if (meias.preco_formatado) {
            return meias.preco_formatado;
        }
        if (meias.preco != null && meias.preco !== '') {
            return Number(meias.preco).toFixed(2).replace('.', ',') + ' €';
        }
        return '';
    }

    function renderMeiasAskStep(passe_id, cliente_id, meias, cliente) {
        if (!meiasModalBody) {
            return;
        }

        const preco = formatMeiasPreco(meias);

        meiasModalBody.innerHTML =
            '<h2 id="rececao-meias-modal-title" class="rececao-modal__title">Adicionar meias?</h2>' +
            '<p class="rececao-modal__product"><strong>' +
            escapeHtml(meias.nome) +
            '</strong>' +
            (preco ? ' · ' + preco : '') +
            '</p>' +
            '<p class="rececao-modal__desc">Deseja adicionar este produto antes de registar a entrada?</p>' +
            '<div class="rececao-modal__actions">' +
            '<button type="button" class="rececao-btn rececao-btn--ghost" id="meias-btn-no">Não, só check-in</button>' +
            '<button type="button" class="rececao-btn rececao-btn--primary" id="meias-btn-yes">Sim, adicionar</button>' +
            '</div>';

        document.getElementById('meias-btn-no').addEventListener('click', function () {
            closeMeiasModal();
            executarCheckin(passe_id, cliente_id, 0, document.querySelector('.btn-checkin'));
        });

        document.getElementById('meias-btn-yes').addEventListener('click', function () {
            renderMeiasFormStep(passe_id, cliente_id, meias, cliente);
        });
    }

    function renderMeiasFormStep(passe_id, cliente_id, meias, cliente) {
        if (!meiasModalBody) {
            return;
        }

        const preco = formatMeiasPreco(meias);
        const telefoneDefault = (cliente && cliente.telefone) ? cliente.telefone : '';

        meiasModalBody.innerHTML =
            '<h2 id="rececao-meias-modal-title" class="rececao-modal__title">Meias antiderrapantes</h2>' +
            '<p class="rececao-modal__product">' +
            escapeHtml(meias.nome) +
            (preco ? ' · ' + preco : '') +
            '</p>' +
            '<label class="rececao-extras-field rececao-meias-qty-field">' +
            '<span>Quantidade</span>' +
            '<input type="number" id="modal-meias-qty" min="1" max="20" value="1" inputmode="numeric" />' +
            '</label>' +
            '<label class="rececao-extras-field rececao-meias-phone-field">' +
            '<span>Telemóvel MB WAY</span>' +
            '<input type="tel" id="modal-meias-phone" value="' +
            escapeHtml(telefoneDefault) +
            '" placeholder="912345678" inputmode="tel" autocomplete="tel" />' +
            '</label>' +
            '<p class="rececao-modal__hint">Será enviado um pedido de pagamento para este número.</p>' +
            '<p id="meias-modal-error" class="rececao-modal__error" style="display:none" role="alert"></p>' +
            '<div class="rececao-modal__actions">' +
            '<button type="button" class="rececao-btn rececao-btn--ghost" id="meias-btn-cancel">Cancelar</button>' +
            '<button type="button" class="rececao-btn rececao-btn--primary" id="meias-btn-create">Enviar pedido MB WAY</button>' +
            '</div>';

        document.getElementById('meias-btn-cancel').addEventListener('click', closeMeiasModal);

        document.getElementById('meias-btn-create').addEventListener('click', function () {
            const btnCreate = this;
            const errEl = document.getElementById('meias-modal-error');
            const qtyInput = document.getElementById('modal-meias-qty');
            const phoneInput = document.getElementById('modal-meias-phone');
            const qty = qtyInput ? parseInt(qtyInput.value, 10) || 0 : 0;
            const phone = phoneInput ? phoneInput.value.trim() : '';

            if (qty < 1) {
                errEl.style.display = 'block';
                errEl.textContent = 'Indique a quantidade de meias.';
                return;
            }

            if (!phone) {
                errEl.style.display = 'block';
                errEl.textContent = 'Indique o telemóvel MB WAY do cliente.';
                return;
            }

            btnCreate.disabled = true;
            btnCreate.textContent = 'A enviar MB WAY…';
            if (errEl) {
                errEl.style.display = 'none';
            }

            fetch(ilhaRececaoData.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ilha_rececao_meias_create',
                    nonce: ilhaRececaoData.nonce,
                    cliente_id: cliente_id,
                    meias_qty: qty,
                    meias_payment: 'mbway',
                    mbway_phone: phone,
                }),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data.success && data.data) {
                        renderMeiasWaitStep(
                            passe_id,
                            cliente_id,
                            data.data
                        );
                    } else {
                        btnCreate.disabled = false;
                        btnCreate.textContent = 'Enviar pedido MB WAY';
                        errEl.style.display = 'block';
                        errEl.textContent = data.data || 'Erro ao criar encomenda.';
                    }
                });
        });
    }

    function renderMeiasWaitStep(passe_id, cliente_id, orderData) {
        if (!meiasModalBody) {
            return;
        }

        meiasModalBody.innerHTML =
            '<h2 id="rececao-meias-modal-title" class="rececao-modal__title">Aguardar pagamento</h2>' +
            '<p class="rececao-modal__desc">Encomenda <strong>#' +
            escapeHtml(String(orderData.order_id)) +
            '</strong> criada.</p>' +
            '<p class="rececao-modal__desc">Email de confirmação enviado para <strong>' +
            escapeHtml(orderData.email || '') +
            '</strong>.</p>' +
            '<p class="rececao-modal__total">Total: <strong>' +
            (orderData.total || '') +
            '</strong></p>' +
            (orderData.mbway_message
                ? '<p class="rececao-modal__desc rececao-modal__mbway-sent">' +
                  escapeHtml(orderData.mbway_message) +
                  '</p>'
                : '') +
            '<p class="rececao-modal__hint">O cliente deve aceitar o pedido na app MB WAY. Depois confirme abaixo para registar o check-in.</p>' +
            '<div class="rececao-modal__actions rececao-modal__actions--stack">' +
            '<button type="button" class="rececao-btn rececao-btn--primary" id="meias-btn-confirm">Pagamento recebido — Registar check-in</button>' +
            '</div>';

        document.getElementById('meias-btn-confirm').addEventListener('click', function () {
            const btnConfirm = this;
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'A registar check-in…';
            executarCheckin(
                passe_id,
                cliente_id,
                orderData.order_id,
                document.querySelector('.btn-checkin')
            );
        });
    }

    window.registarCheckin = function (passe_id, cliente_id) {
        const meias =
            (ilhaRececaoData && ilhaRececaoData.meias) || null;

        if (meias && meias.nome && meias.em_stock !== false) {
            renderMeiasAskStep(passe_id, cliente_id, meias, rececaoClienteAtivo);
            openMeiasModal();
            return;
        }

        executarCheckin(
            passe_id,
            cliente_id,
            0,
            document.querySelector('.btn-checkin')
        );
    };

    if (meiasModal) {
        meiasModal.addEventListener('click', function (e) {
            if (
                e.target.matches('[data-meias-close]') ||
                e.target.classList.contains('rececao-modal__backdrop')
            ) {
                closeMeiasModal();
            }
        });
    }

    const btnRefresh = document.getElementById('btn-refresh-historico');
    const tbodyHist = document.getElementById('historico-tbody');

    window.carregarHistorico = function () {
        if (!tbodyHist) {
            return;
        }
        tbodyHist.innerHTML =
            '<tr><td colspan="5" class="rececao-table-loading">A carregar…</td></tr>';

        fetch(ilhaRececaoData.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'ilha_get_historico',
                nonce: ilhaRececaoData.nonce,
            }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                tbodyHist.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(function (h) {
                        const tr = document.createElement('tr');
                        const extrasHist = h.extras_label
                            ? '<span class="rececao-pill rececao-pill--extras">' +
                              escapeHtml(h.extras_label) +
                              '</span>'
                            : '—';
                        tr.innerHTML =
                            '<td><strong>' +
                            h.hora +
                            '</strong></td><td>' +
                            h.nome +
                            '</td><td><span class="rececao-pill">' +
                            h.tipo_formatado +
                            '</span></td><td>' +
                            h.dias_usados +
                            ' / ' +
                            h.dias_total +
                            '</td><td>' +
                            extrasHist +
                            '</td>';
                        tbodyHist.appendChild(tr);
                    });
                } else {
                    tbodyHist.innerHTML =
                        '<tr><td colspan="5" class="rececao-table-empty">Nenhum check-in hoje.</td></tr>';
                }
            });
    };

    if (btnRefresh) {
        btnRefresh.addEventListener('click', carregarHistorico);
    }

    const btnRefreshReservas = document.getElementById('btn-refresh-reservas');
    const tbodyReservas = document.getElementById('reservas-tbody');
    const reservasDateInput = document.getElementById('rececao-reservas-date');
    const reservasSummary = document.getElementById('rececao-reservas-summary');

    function renderReservasSummary(day) {
        if (!reservasSummary || !day) {
            return;
        }
        const full = day.ocupadas >= day.capacidade;
        reservasSummary.innerHTML =
            '<div class="rececao-reservas-stats' +
            (full ? ' rececao-reservas-stats--full' : '') +
            '">' +
            '<span class="rececao-reservas-count"><strong>' +
            day.ocupadas +
            '</strong> / ' +
            day.capacidade +
            ' reservas</span>' +
            '<span class="rececao-reservas-disp">' +
            day.disponiveis +
            ' vagas livres</span>' +
            '</div>' +
            '<p class="rececao-reservas-date-label">' +
            escapeHtml(day.data_formatada) +
            '</p>';
    }

    function renderReservasRows(reservas) {
        if (!tbodyReservas) {
            return;
        }
        tbodyReservas.innerHTML = '';
        if (!reservas || reservas.length === 0) {
            tbodyReservas.innerHTML =
                '<tr><td colspan="7" class="rececao-table-empty">Nenhuma reserva neste dia.</td></tr>';
            return;
        }
        reservas.forEach(function (r) {
            const tr = document.createElement('tr');
            const contacto =
                (r.email ? escapeHtml(r.email) : '') +
                (r.email && r.telefone ? '<br>' : '') +
                escapeHtml(r.telefone || '—');
            const statusClass =
                'rececao-pill rececao-pill--' + (r.status || 'confirmada');
            const extrasCell =
                r.extras && r.extras.tem_extras
                    ? '<span class="rececao-pill rececao-pill--extras" title="' +
                      escapeHtml(r.extras.label_resumo || '') +
                      '">' +
                      escapeHtml(r.extras.label_comprados || '—') +
                      '</span>'
                    : '—';
            tr.innerHTML =
                '<td><strong>' +
                escapeHtml(r.event_time || '—') +
                '</strong></td>' +
                '<td>' +
                escapeHtml(r.nome) +
                '</td>' +
                '<td>' +
                escapeHtml(r.idade_crianca ? String(r.idade_crianca) : '—') +
                '</td>' +
                '<td>' +
                contacto +
                '</td>' +
                '<td><span class="rececao-pill">' +
                escapeHtml(r.tipo_formatado || '—') +
                '</span></td>' +
                '<td>' +
                extrasCell +
                '</td>' +
                '<td><span class="' +
                statusClass +
                '">' +
                escapeHtml(r.status_label || r.status) +
                '</span></td>';
            tbodyReservas.appendChild(tr);
        });
    }

    window.carregarReservas = function () {
        if (!tbodyReservas) {
            return;
        }
        const dateVal =
            reservasDateInput && reservasDateInput.value
                ? reservasDateInput.value
                : '';
        tbodyReservas.innerHTML =
            '<tr><td colspan="7" class="rececao-table-loading">A carregar…</td></tr>';

        fetch(ilhaRececaoData.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'ilha_get_reservas',
                nonce: ilhaRececaoData.nonce,
                date: dateVal,
            }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success && data.data) {
                    renderReservasSummary(data.data);
                    renderReservasRows(data.data.reservas);
                } else {
                    tbodyReservas.innerHTML =
                        '<tr><td colspan="7" class="rececao-table-empty">Erro ao carregar reservas.</td></tr>';
                }
            });
    };

    if (reservasDateInput) {
        reservasDateInput.addEventListener('change', carregarReservas);
    }
    if (btnRefreshReservas) {
        btnRefreshReservas.addEventListener('click', carregarReservas);
    }
});
