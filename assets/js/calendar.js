/* ═══════════════════════════════════════════════════
   EventsHouse — Calendar JS (várias instâncias)
   Google Calendar via AJAX WordPress
   ═══════════════════════════════════════════════════ */
(function () {
    'use strict';

    var cfg = window.EHCalendar || {};
    var ajaxUrl = cfg.ajaxUrl || '';
    var nonce = cfg.nonce || '';

    var MONTH_NAMES = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    function normalizeLoose(str) {
        var s = (str || '').toString().toLowerCase().trim();
        if (typeof s.normalize === 'function') {
            s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } else {
            s = s
                .replace(/[áàâãä]/g, 'a')
                .replace(/[éèêë]/g, 'e')
                .replace(/[íìîï]/g, 'i')
                .replace(/[óòôõö]/g, 'o')
                .replace(/[úùûü]/g, 'u')
                .replace(/ç/g, 'c');
        }
        return s.replace(/\s+/g, ' ');
    }

    function initCalendar(calWrap) {
        if (!calWrap) return;

        var ctx = calWrap.getAttribute('data-cal-context') || 'default';
        var isIlha = (ctx === 'ilha');
        var configured = isIlha ? !!cfg.ilhaConfigured : !!cfg.mainConfigured;
        var slots = (isIlha && cfg.ilhaSlots && cfg.ilhaSlots.length) ? cfg.ilhaSlots : (cfg.slots || []);
        if (!slots || !slots.length) slots = ['10:00-12:30', '13:30-16:00', '17:00-19:30'];

        var root = calWrap.parentElement;
        var calLoading = root ? root.querySelector('.eh-cal-loading') : null;
        var calGrid = calWrap.querySelector('.eh-cal-grid');
        var calTitle = calWrap.querySelector('.eh-cal-title');
        var slotPicker = calWrap.querySelector('.eh-slot-picker');
        var bookPanel = calWrap.querySelector('.eh-booking-panel');
        var selDateBadge = calWrap.querySelector('.eh-selected-date-badge');
        var bForm = calWrap.querySelector('.eh-booking-form');
        var fMsg = calWrap.querySelector('.eh-booking-panel .eh-form-message');
        var ilhaDetail = calWrap.querySelector('.eh-event-detail');
        var ilhaDetailTitle = calWrap.querySelector('.eh-event-detail-title');
        var ilhaDetailTime = calWrap.querySelector('.eh-event-detail-time');
        var ilhaDetailDesc = calWrap.querySelector('.eh-event-detail-desc');
        var ilhaDetailLink = calWrap.querySelector('.eh-event-detail-link');
        var ilhaReserveToggle = calWrap.querySelector('.eh-ilha-reserve-toggle');
        var ilhaReserveArea = calWrap.querySelector('.eh-ilha-reserve-area');
        var ilhaEmailForm = calWrap.querySelector('.eh-ilha-email-form');
        var ilhaNoPassBlock = calWrap.querySelector('.eh-ilha-no-pass-block');
        var ilhaReserveForm = calWrap.querySelector('.eh-ilha-reserve-form');
        var ilhaVerifiedEmail = '';

        var prevBtn = calWrap.querySelector('.eh-cal-prev');
        var nextBtn = calWrap.querySelector('.eh-cal-next');

        var currentYear;
        var currentMonth;
        var occupiedDates = []; // default mode: occupied days
        var blockedSlotsByDate = {}; // default mode: blocked slots
        var ilhaEventDays = []; // ilha mode: days with events
        var ilhaEventsByDate = {}; // ilha mode: detailed events by day
        var selectedDate = null;
        var selectedSlot = null;
        var selectedEventIdx = -1;

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        currentYear = today.getFullYear();
        currentMonth = today.getMonth();

        var monthHorizon = parseInt(cfg.months, 10);
        if (!monthHorizon || monthHorizon < 1) monthHorizon = 6;
        function monthIdx(y, m) { return y * 12 + m; }
        var todayMonthIdx = monthIdx(today.getFullYear(), today.getMonth());
        var maxMonthIdx = todayMonthIdx + monthHorizon;

        function setLoadingDone() {
            if (calLoading) calLoading.style.display = 'none';
        }

        function loadEvents() {
            setLoadingDone();
            renderMonth();
            if (!configured) return;

            var xhr = new XMLHttpRequest();
            xhr.timeout = 15000;
            xhr.open('POST', ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success && data.data) {
                            if (isIlha) {
                                ilhaEventDays = Array.isArray(data.data.events) ? data.data.events : [];
                                ilhaEventsByDate = (data.data.eventItemsByDate && typeof data.data.eventItemsByDate === 'object') ? data.data.eventItemsByDate : {};
                            } else {
                                occupiedDates = Array.isArray(data.data.events) ? data.data.events : [];
                                blockedSlotsByDate = (data.data.blockedSlots && typeof data.data.blockedSlots === 'object') ? data.data.blockedSlots : {};
                            }
                        }
                    } catch (e) {}
                }
                renderMonth();
            };
            xhr.onerror = renderMonth;
            xhr.ontimeout = renderMonth;

            var body = 'action=EventsHouse_get_calendar&nonce=' + encodeURIComponent(nonce);
            if (isIlha) body += '&cal_context=ilha';
            xhr.send(body);
        }

        function renderMonth() {
            if (!calGrid || !calTitle) return;
            calTitle.textContent = MONTH_NAMES[currentMonth] + ' ' + currentYear;
            calGrid.innerHTML = '';

            var firstDay = new Date(currentYear, currentMonth, 1).getDay();
            var daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            for (var i = 0; i < firstDay; i++) {
                var empty = document.createElement('div');
                empty.className = 'eh-cal-day empty';
                calGrid.appendChild(empty);
            }

            for (var d = 1; d <= daysInMonth; d++) {
                var cell = document.createElement('div');
                var dateStr = pad(currentYear) + '-' + pad(currentMonth + 1) + '-' + pad(d);
                var cellDate = new Date(currentYear, currentMonth, d);
                cellDate.setHours(0, 0, 0, 0);

                var isToday = (cellDate.getTime() === today.getTime());
                var isPast = (cellDate < today);
                var isSelected = (selectedDate === dateStr);

                cell.className = 'eh-cal-day';
                cell.textContent = d;
                if (isToday) cell.classList.add('today');

                if (isIlha) {
                    var hasEvent = ilhaEventDays.indexOf(dateStr) !== -1;
                    if (isPast) {
                        cell.classList.add('past');
                    } else if (hasEvent) {
                        cell.classList.add('available', 'has-event');
                    } else {
                        cell.classList.add('occupied', 'no-event');
                    }

                    if (!isPast && hasEvent) {
                        (function (ds) {
                            cell.addEventListener('click', function () { selectDate(ds); });
                        })(dateStr);
                    }
                } else {
                    var isOccupied = occupiedDates.indexOf(dateStr) !== -1;
                    if (isPast) {
                        cell.classList.add('past');
                    } else if (isOccupied) {
                        cell.classList.add('occupied');
                    } else {
                        cell.classList.add('available');
                    }
                    if (!isPast && !isOccupied) {
                        (function (ds2) {
                            cell.addEventListener('click', function () { selectDate(ds2); });
                        })(dateStr);
                    }
                }

                if (isSelected) cell.classList.add('selected');
                calGrid.appendChild(cell);
            }
        }

        function selectDate(dateStr) {
            selectedDate = dateStr;
            selectedSlot = null;
            selectedEventIdx = -1;
            renderMonth();

            var parts = dateStr.split('-');
            var dObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            var displayStr = dObj.getDate() + ' de ' + MONTH_NAMES[dObj.getMonth()] + ' de ' + dObj.getFullYear();

            if (selDateBadge) selDateBadge.textContent = '📅 ' + displayStr;
            var inputDate = calWrap.querySelector('.eh-input-date');
            if (inputDate) inputDate.value = dateStr;

            if (bookPanel) {
                bookPanel.style.display = 'block';
                bookPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            if (isIlha) {
                renderIlhaEvents(dateStr);
            } else {
                renderSlots(dateStr);
            }
        }

        function renderIlhaEvents(dateStr) {
            if (!slotPicker) return;
            slotPicker.innerHTML = '';
            hideIlhaDetail();

            var list = (dateStr && ilhaEventsByDate[dateStr] && Array.isArray(ilhaEventsByDate[dateStr])) ? ilhaEventsByDate[dateStr] : [];
            if (!list.length) {
                var noEv = document.createElement('p');
                noEv.className = 'eh-ilha-no-events';
                noEv.textContent = 'Sem eventos publicados para esta data.';
                slotPicker.appendChild(noEv);
                return;
            }

            list.forEach(function (ev, idx) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'eh-slot-btn eh-event-btn';
                btn.textContent = (ev.time ? (ev.time + ' — ') : '') + (ev.title || 'Manhãs na Ilha');
                btn.addEventListener('click', function () {
                    selectedEventIdx = idx;
                    calWrap.querySelectorAll('.eh-event-btn').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    showIlhaDetail(ev);
                });
                slotPicker.appendChild(btn);
            });
        }

        function showIlhaDetail(ev) {
            if (!ilhaDetail) return;
            var eventTitle = ev.title || 'Manhãs na Ilha';
            if (ilhaDetailTitle) ilhaDetailTitle.textContent = ev.title || 'Manhãs na Ilha';
            if (ilhaDetailTime) ilhaDetailTime.textContent = ev.time || '';
            if (ilhaDetailDesc) {
                ilhaDetailDesc.innerHTML = '';
                var desc = (ev.description || '').trim();
                if (!desc) {
                    ilhaDetailDesc.textContent = 'Sem descrição disponível.';
                } else {
                    var paragraphs = desc.split(/\n\s*\n/);
                    paragraphs.forEach(function (para) {
                        var text = (para || '').trim();
                        if (!text) return;
                        var p = document.createElement('p');
                        p.textContent = text;
                        ilhaDetailDesc.appendChild(p);
                    });
                    if (!ilhaDetailDesc.childNodes.length) {
                        ilhaDetailDesc.textContent = desc;
                    }
                }
            }
            if (ilhaDetailLink) {
                if (ev.link) {
                    ilhaDetailLink.href = ev.link;
                    ilhaDetailLink.style.display = 'inline-flex';
                } else {
                    ilhaDetailLink.removeAttribute('href');
                    ilhaDetailLink.style.display = 'none';
                }
            }
            if (ilhaReserveForm) {
                var dateInput = ilhaReserveForm.querySelector('.eh-ilha-date');
                var titleInput = ilhaReserveForm.querySelector('.eh-ilha-title');
                var timeInput = ilhaReserveForm.querySelector('.eh-ilha-time');
                var linkInput = ilhaReserveForm.querySelector('.eh-ilha-link');
                if (dateInput) dateInput.value = selectedDate || '';
                if (titleInput) titleInput.value = eventTitle;
                if (timeInput) timeInput.value = ev.time || '';
                if (linkInput) linkInput.value = ev.link || '';
            }
            var normalizedTitle = normalizeLoose(eventTitle);
            var showReserve = normalizedTitle.indexOf('manhas na ilha') !== -1;
            if (ilhaReserveToggle) {
                ilhaReserveToggle.style.display = showReserve ? 'inline-flex' : 'none';
            }
            if (!showReserve) {
                hideIlhaReservePanels();
            }
            ilhaDetail.style.display = 'block';
        }

        function hideIlhaReservePanels() {
            if (ilhaReserveArea) ilhaReserveArea.style.display = 'none';
            if (ilhaEmailForm) {
                ilhaEmailForm.style.display = 'none';
                ilhaEmailForm.reset();
            }
            ilhaVerifiedEmail = '';
            if (ilhaNoPassBlock) ilhaNoPassBlock.style.display = 'none';
            if (ilhaReserveForm) {
                ilhaReserveForm.style.display = 'none';
                ilhaReserveForm.reset();
            }
        }

        function showIlhaEmailPanel() {
            hideIlhaReservePanels();
            if (ilhaReserveArea) ilhaReserveArea.style.display = 'block';
            if (ilhaEmailForm) ilhaEmailForm.style.display = 'block';
        }

        function showIlhaNoPassPanel(packsUrl) {
            hideIlhaReservePanels();
            if (ilhaReserveArea) ilhaReserveArea.style.display = 'block';
            if (ilhaNoPassBlock) {
                var cta = ilhaNoPassBlock.querySelector('.eh-ilha-packs-cta');
                var link = ilhaNoPassBlock.querySelector('.eh-ilha-packs-link-label a');
                var url = packsUrl || cfg.packsUrl || '';
                if (cta && url) cta.href = url;
                if (link && url) link.href = url;
                ilhaNoPassBlock.style.display = 'block';
            }
        }

        function showIlhaReservePanel(userData) {
            hideIlhaReservePanels();
            if (ilhaReserveArea) ilhaReserveArea.style.display = 'block';
            if (ilhaReserveForm) {
                var nameInput = ilhaReserveForm.querySelector('[name="name"]');
                var emailInput = ilhaReserveForm.querySelector('[name="email"]');
                if (userData) {
                    if (nameInput && userData.display_name) nameInput.value = userData.display_name;
                    if (emailInput && userData.email) {
                        emailInput.value = userData.email;
                        emailInput.readOnly = true;
                        ilhaVerifiedEmail = userData.email;
                    }
                }
                ilhaReserveForm.style.display = 'block';
            }
        }

        function checkIlhaPassByEmail(email) {
            if (ilhaReserveArea) ilhaReserveArea.style.display = 'block';
            var xhrCheck = new XMLHttpRequest();
            xhrCheck.open('POST', ajaxUrl, true);
            xhrCheck.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhrCheck.onload = function () {
                try {
                    var res = JSON.parse(xhrCheck.responseText);
                    if (res.success && res.data && res.data.has_pass) {
                        showIlhaReservePanel(res.data);
                    } else {
                        showIlhaNoPassPanel(res.data && res.data.packs_url);
                    }
                } catch (errCheck) {
                    showMsg('error', 'Não foi possível verificar o seu pacote. Tente novamente.');
                }
            };
            xhrCheck.onerror = function () {
                showMsg('error', 'Erro de rede. Verifique a sua ligação.');
            };
            xhrCheck.send(
                'action=ilha_reservation_check_pass&nonce=' + encodeURIComponent(nonce) +
                '&email=' + encodeURIComponent(email)
            );
        }

        function hideIlhaDetail() {
            if (!ilhaDetail) return;
            ilhaDetail.style.display = 'none';
            if (ilhaDetailTitle) ilhaDetailTitle.textContent = '';
            if (ilhaDetailTime) ilhaDetailTime.textContent = '';
            if (ilhaDetailDesc) ilhaDetailDesc.textContent = '';
            if (ilhaReserveToggle) ilhaReserveToggle.style.display = 'none';
            hideIlhaReservePanels();
        }

        function renderSlots(dateStr) {
            if (!slotPicker) return;
            slotPicker.innerHTML = '';
            var blocked = (dateStr && blockedSlotsByDate[dateStr]) ? blockedSlotsByDate[dateStr] : [];

            slots.forEach(function (slot) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'eh-slot-btn';
                btn.textContent = '🕐 ' + slot;

                var isBlocked = blocked.indexOf(slot) !== -1;
                if (isBlocked) {
                    btn.disabled = true;
                    btn.classList.add('is-blocked');
                    btn.title = 'Este horário está ocupado na agenda.';
                }
                if (selectedSlot === slot && !isBlocked) btn.classList.add('active');

                btn.addEventListener('click', function () {
                    if (btn.disabled) return;
                    selectedSlot = slot;
                    var inputSlot = calWrap.querySelector('.eh-input-slot');
                    if (inputSlot) inputSlot.value = slot;
                    calWrap.querySelectorAll('.eh-slot-btn').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                });
                slotPicker.appendChild(btn);
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                renderMonth();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                var ny = currentYear;
                var nm = currentMonth + 1;
                if (nm > 11) {
                    nm = 0;
                    ny++;
                }
                if (monthIdx(ny, nm) > maxMonthIdx) {
                    return;
                }
                currentMonth = nm;
                currentYear = ny;
                renderMonth();
            });
        }

        if (bForm && !isIlha) {
            var submitBtnEl = bForm.querySelector('.eh-submit-btn');
            var submitDefaultLabel = submitBtnEl ? submitBtnEl.textContent : '';

            bForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!selectedDate) {
                    showMsg('error', 'Por favor selecione uma data no calendário.');
                    return;
                }
                if (!selectedSlot) {
                    showMsg('error', 'Por favor escolha um horário.');
                    return;
                }
                var phoneInput = bForm.querySelector('[name="phone"]');
                if (!phoneInput || !phoneInput.value.trim()) {
                    showMsg('error', 'Por favor indique o número de telefone.');
                    return;
                }
                var guestsInput = bForm.querySelector('[name="guests"]');
                var guestsVal = guestsInput ? parseInt(guestsInput.value, 10) : 0;
                if (!guestsInput || !guestsInput.value.trim() || isNaN(guestsVal) || guestsVal < 1) {
                    showMsg('error', 'Por favor indique o número de convidados.');
                    return;
                }

                var submitBtn = bForm.querySelector('.eh-submit-btn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '⏳ A enviar...';
                }

                var data = new FormData(bForm);
                data.append('action', 'EventsHouse_availability');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxUrl, true);
                xhr.onload = function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitDefaultLabel;
                    }
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            showMsg('success', res.data.message);
                            bForm.reset();
                            selectedDate = null;
                            selectedSlot = null;
                            // Mantem o painel aberto para o utilizador ver a mensagem de sucesso.
                            renderMonth();
                        } else {
                            showMsg('error', res.data.message || 'Erro ao enviar. Tente novamente.');
                        }
                    } catch (err) {
                        showMsg('error', 'Erro de comunicação. Tente novamente.');
                    }
                };
                xhr.onerror = function () {
                    var sb = bForm.querySelector('.eh-submit-btn');
                    if (sb) {
                        sb.disabled = false;
                        sb.textContent = submitDefaultLabel;
                    }
                    showMsg('error', 'Erro de rede. Verifique a sua ligação.');
                };
                xhr.send(data);
            });
        }

        if (ilhaReserveToggle && isIlha) {
            ilhaReserveToggle.addEventListener('click', function () {
                var areaOpen = ilhaReserveArea && ilhaReserveArea.style.display === 'block';
                if (areaOpen) {
                    hideIlhaReservePanels();
                    return;
                }
                if (ilhaVerifiedEmail) {
                    checkIlhaPassByEmail(ilhaVerifiedEmail);
                } else {
                    showIlhaEmailPanel();
                }
            });
        }

        if (ilhaEmailForm && isIlha) {
            var ilhaEmailBtn = ilhaEmailForm.querySelector('.eh-submit-btn');
            var ilhaEmailDefaultLabel = ilhaEmailBtn ? ilhaEmailBtn.textContent : 'Continuar';

            ilhaEmailForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var emailInput = ilhaEmailForm.querySelector('[name="email"]');
                var emailVal = emailInput ? emailInput.value.trim() : '';
                if (!emailVal) {
                    showMsg('error', 'Indique o email usado na compra do pack.');
                    return;
                }
                if (ilhaEmailBtn) {
                    ilhaEmailBtn.disabled = true;
                    ilhaEmailBtn.textContent = '⏳ A verificar...';
                }
                var xhrEmail = new XMLHttpRequest();
                xhrEmail.open('POST', ajaxUrl, true);
                xhrEmail.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhrEmail.onload = function () {
                    if (ilhaEmailBtn) {
                        ilhaEmailBtn.disabled = false;
                        ilhaEmailBtn.textContent = ilhaEmailDefaultLabel;
                    }
                    try {
                        var emailRes = JSON.parse(xhrEmail.responseText);
                        if (emailRes.success && emailRes.data && emailRes.data.has_pass) {
                            ilhaVerifiedEmail = emailVal;
                            showIlhaReservePanel(emailRes.data);
                        } else {
                            showIlhaNoPassPanel(emailRes.data && emailRes.data.packs_url);
                        }
                    } catch (errEmail) {
                        showMsg('error', 'Erro de comunicação. Tente novamente.');
                    }
                };
                xhrEmail.onerror = function () {
                    if (ilhaEmailBtn) {
                        ilhaEmailBtn.disabled = false;
                        ilhaEmailBtn.textContent = ilhaEmailDefaultLabel;
                    }
                    showMsg('error', 'Erro de rede. Verifique a sua ligação.');
                };
                xhrEmail.send(
                    'action=ilha_reservation_check_pass&nonce=' + encodeURIComponent(nonce) +
                    '&email=' + encodeURIComponent(emailVal)
                );
            });
        }

        if (ilhaReserveForm && isIlha) {
            var ilhaSubmitBtn = ilhaReserveForm.querySelector('.eh-submit-btn');
            var ilhaDefaultLabel = ilhaSubmitBtn ? ilhaSubmitBtn.textContent : 'Confirmar reserva';

            ilhaReserveForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!selectedDate) {
                    showMsg('error', 'Escolha uma data antes de reservar.');
                    return;
                }
                if (ilhaSubmitBtn) {
                    ilhaSubmitBtn.disabled = true;
                    ilhaSubmitBtn.textContent = '⏳ A enviar...';
                }
                var data = new FormData(ilhaReserveForm);
                data.append('action', 'EventsHouse_ilha_reservation');

                var xhrIlha = new XMLHttpRequest();
                xhrIlha.open('POST', ajaxUrl, true);
                xhrIlha.onload = function () {
                    if (ilhaSubmitBtn) {
                        ilhaSubmitBtn.disabled = false;
                        ilhaSubmitBtn.textContent = ilhaDefaultLabel;
                    }
                    try {
                        var res = JSON.parse(xhrIlha.responseText);
                        if (res.success) {
                            showMsg('success', res.data.message);
                            ilhaReserveForm.reset();
                            hideIlhaReservePanels();
                        } else if (res.data && res.data.no_pass) {
                            showIlhaNoPassPanel(res.data.packs_url);
                        } else {
                            showMsg('error', (res.data && res.data.message) ? res.data.message : 'Erro ao reservar.');
                        }
                    } catch (err2) {
                        showMsg('error', 'Erro de comunicação. Tente novamente.');
                    }
                };
                xhrIlha.onerror = function () {
                    if (ilhaSubmitBtn) {
                        ilhaSubmitBtn.disabled = false;
                        ilhaSubmitBtn.textContent = ilhaDefaultLabel;
                    }
                    showMsg('error', 'Erro de rede. Verifique a sua ligação.');
                };
                xhrIlha.send(data);
            });
        }

        function showMsg(type, text) {
            if (!fMsg) {
                return;
            }
            fMsg.className = 'eh-form-message ' + type;
            fMsg.textContent = text;
            fMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(function () {
                fMsg.className = 'eh-form-message';
                fMsg.textContent = '';
            }, 8000);
        }

        loadEvents();
    }

    document.querySelectorAll('.eh-calendar-wrap').forEach(initCalendar);
})();
