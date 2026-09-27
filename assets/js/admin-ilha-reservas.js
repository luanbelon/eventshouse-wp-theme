/**
 * Admin — Reservas Manhãs na Ilha
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ehIlhaReservasAdmin === 'undefined') {
        return;
    }

    const syncBtn = document.getElementById('eh-sync-gcal-btn');
    const syncResult = document.getElementById('eh-sync-gcal-result');

    if (syncBtn) {
        syncBtn.addEventListener('click', function () {
            if (syncBtn.disabled) {
                return;
            }
            syncBtn.disabled = true;
            if (syncResult) {
                syncResult.style.display = 'block';
                syncResult.className = 'eh-sync-gcal-result eh-sync-gcal-result--loading';
                syncResult.textContent = ehIlhaReservasAdmin.i18n.sync_running;
            }

            fetch(ehIlhaReservasAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ilha_sync_reservas_gcal',
                    nonce: ehIlhaReservasAdmin.nonce,
                    days_past: '60',
                    days_future: '90',
                }),
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (data) {
                    syncBtn.disabled = false;
                    if (!syncResult) {
                        if (data.success) {
                            window.location.reload();
                        }
                        return;
                    }
                    if (data.success && data.data) {
                        const s = data.data;
                        syncResult.className = 'eh-sync-gcal-result eh-sync-gcal-result--ok';
                        syncResult.innerHTML =
                            '<strong>' +
                            (s.message || ehIlhaReservasAdmin.i18n.sync_done) +
                            '</strong><br>' +
                            'Importadas: ' +
                            (s.imported || 0) +
                            ' · Já existiam: ' +
                            (s.skipped_exists || 0) +
                            ' · Slots ignorados: ' +
                            (s.skipped_slot || 0) +
                            ' · Sem dados de cliente: ' +
                            (s.skipped_no_data || 0);
                        setTimeout(function () {
                            window.location.reload();
                        }, 2500);
                    } else {
                        syncResult.className = 'eh-sync-gcal-result eh-sync-gcal-result--err';
                        syncResult.textContent =
                            (data.data && data.data.message) ||
                            ehIlhaReservasAdmin.i18n.sync_fail;
                    }
                })
                .catch(function () {
                    syncBtn.disabled = false;
                    if (syncResult) {
                        syncResult.className = 'eh-sync-gcal-result eh-sync-gcal-result--err';
                        syncResult.textContent = ehIlhaReservasAdmin.i18n.sync_fail;
                    }
                });
        });
    }

    const tbody = document.getElementById('eh-reservas-tbody');
    if (!tbody) {
        return;
    }

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.eh-cancel-reserva');
        if (!btn) {
            return;
        }
        e.preventDefault();
        const id = btn.getAttribute('data-id');
        const date = btn.getAttribute('data-date') || '';
        if (!id) {
            return;
        }
        if (!window.confirm(ehIlhaReservasAdmin.i18n.confirm_cancel)) {
            return;
        }

        btn.disabled = true;

        fetch(ehIlhaReservasAdmin.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'ilha_cancel_reserva',
                nonce: ehIlhaReservasAdmin.nonce,
                reserva_id: id,
                date: date,
            }),
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(ehIlhaReservasAdmin.i18n.error);
                    btn.disabled = false;
                }
            })
            .catch(function () {
                alert(ehIlhaReservasAdmin.i18n.error);
                btn.disabled = false;
            });
    });
});
