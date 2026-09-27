/**
 * Produto evento: quantidades na sidebar, total estimado e inscrição rápida (sem formulário).
 */
(function () {
    'use strict';

    function initEventQtySteppers(page) {
        var wrap = page.querySelector('.eh-event-pricing');
        if (!wrap) {
            return;
        }

        var base = parseFloat(wrap.getAttribute('data-base') || '0', 10);
        var hasBase = wrap.getAttribute('data-has-base-price') === '1';
        var adultExtra = parseFloat(wrap.getAttribute('data-adult-extra') || '0', 10);
        var childExtra = parseFloat(wrap.getAttribute('data-child-extra') || '0', 10);
        var max = parseInt(wrap.getAttribute('data-max') || '50', 10);
        var dec = parseInt(wrap.getAttribute('data-decimals') || '2', 10);
        var decSep = wrap.getAttribute('data-decimal-sep') || ',';
        var thouSep = wrap.getAttribute('data-thousand-sep') || '.';
        var sym = wrap.getAttribute('data-currency-symbol') || '€';
        var outTotal = page.querySelector('.eh-event-live-total');
        var form = page.querySelector('form.eh-event-quick-cart-form');

        function formatMoney(n) {
            var fixed = n.toFixed(dec);
            var parts = fixed.split('.');
            var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
            return intPart + decSep + parts[1] + ' ' + sym;
        }

        function getQty(key) {
            var input = page.querySelector('.eh-event-qty-input[data-qty-key="' + key + '"]');
            if (!input) {
                return 0;
            }
            var c = parseInt(input.value, 10);
            return isNaN(c) || c < 0 ? 0 : Math.min(max, c);
        }

        function setQty(key, val) {
            var input = page.querySelector('.eh-event-qty-input[data-qty-key="' + key + '"]');
            var display = page.querySelector('.eh-event-qty-value[data-qty-key="' + key + '"]');
            val = Math.max(0, Math.min(max, parseInt(val, 10) || 0));
            if (input) {
                input.value = String(val);
            }
            if (display) {
                display.textContent = String(val);
            }
            updateTotal();
        }

        function updateTotal() {
            var total = hasBase ? base : 0;
            total += getQty('adult_extra') * adultExtra;
            total += getQty('child_extra') * childExtra;
            if (outTotal) {
                outTotal.textContent = formatMoney(total);
            }
        }

        page.addEventListener('click', function (e) {
            var btn = e.target.closest('.eh-event-qty-stepper__btn');
            if (!btn || !page.contains(btn)) {
                return;
            }
            e.preventDefault();
            var key = btn.getAttribute('data-qty-key');
            if (!key) {
                return;
            }
            var cur = getQty(key);
            if (btn.classList.contains('eh-event-qty-stepper__btn--minus')) {
                setQty(key, cur - 1);
            } else {
                setQty(key, cur + 1);
            }
        });

        updateTotal();

        var enrollBtn = page.querySelector('#woo-event-enroll-submit');
        if (enrollBtn && form) {
            enrollBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var total = hasBase ? base : 0;
                total += getQty('adult_extra') * adultExtra;
                total += getQty('child_extra') * childExtra;
                var anyQty =
                    getQty('adult_extra') > 0 || getQty('child_extra') > 0 || getQty('child_free') > 0;
                if (!hasBase && !anyQty) {
                    window.alert(
                        (window.ehWcEventSingle && window.ehWcEventSingle.err_no_selection) ||
                            'Seleccione pelo menos um bilhete.'
                    );
                    return;
                }
                if (total <= 0 && !anyQty && !hasBase) {
                    window.alert(
                        (window.ehWcEventSingle && window.ehWcEventSingle.err_no_selection) ||
                            'Seleccione pelo menos um bilhete.'
                    );
                    return;
                }
                form.submit();
            });
        }
    }

    function initFieldConditions(page) {
        if (!page.querySelector('.eh-wc-event-custom-field[data-show-when]')) {
            return;
        }
        /* formulário desactivado — ignorar */
    }

    function boot() {
        var page = document.querySelector('.woo-single--event');
        if (!page) {
            return;
        }
        initFieldConditions(page);
        initEventQtySteppers(page);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
