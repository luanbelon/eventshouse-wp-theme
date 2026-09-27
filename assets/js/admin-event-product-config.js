/**
 * Admin: builder de campos e sync do config JSON do produto evento.
 */
(function ($) {
    'use strict';

    var i18n = window.ehEventAdminConfig && window.ehEventAdminConfig.i18n ? window.ehEventAdminConfig.i18n : {};
    var builtinFields = window.ehEventAdminConfig && window.ehEventAdminConfig.builtinFields
        ? window.ehEventAdminConfig.builtinFields
        : [];

    function getBaseConfig() {
        var $root = $('#eh-event-fields-builder');
        var raw = $root.attr('data-config');
        if (!raw) {
            return { version: 1, event: {}, base: {}, pricing: {}, extras: {}, fields: [] };
        }
        try {
            return JSON.parse(raw);
        } catch (e) {
            return { version: 1, event: {}, base: {}, pricing: {}, extras: {}, fields: [] };
        }
    }

    function collectConfigFromDom() {
        var config = getBaseConfig();
        config.version = 1;
        config.event = {
            date: $('#eh_evt_admin_date').val() || '',
            day_label: $('#eh_evt_admin_day_label').val() || '',
            time_start: $('#eh_evt_admin_time_start').val() || '',
            time_end: $('#eh_evt_admin_time_end').val() || '',
            location: $('#eh_evt_admin_location').val() || '',
        };
        config.base = {
            adults: parseInt($('#eh_evt_admin_base_adults').val(), 10) || 0,
            children: parseInt($('#eh_evt_admin_base_children').val(), 10) || 0,
        };
        var paidMin = $('#eh_evt_admin_paid_min').val();
        var paidMax = $('#eh_evt_admin_paid_max').val();
        config.pricing = {
            has_base_price: $('#eh_evt_admin_has_base_price').is(':checked'),
            adult_extra: parseFloat($('#eh_evt_admin_adult_extra').val()) || 0,
            child_extra: parseFloat($('#eh_evt_admin_child_extra').val()) || 0,
            child_free_max_age: parseInt($('#eh_evt_admin_free_age').val(), 10) || 0,
            child_paid_age_min: paidMin === '' ? null : parseInt(paidMin, 10),
            child_paid_age_max: paidMax === '' ? null : parseInt(paidMax, 10),
        };
        config.extras = {
            enabled: $('#eh_evt_admin_extras_enabled').is(':checked'),
            max: parseInt($('#eh_evt_admin_extras_max').val(), 10) || 0,
        };
        config.fields = collectFieldsFromBuilder();
        return config;
    }

    function collectFieldsFromBuilder() {
        var fields = [];
        $('#eh-event-fields-builder .eh-field-row').each(function () {
            var $row = $(this);
            var id = $row.find('.eh-field-id').val();
            if (!id) {
                return;
            }
            var field = {
                id: id.replace(/[^a-z0-9_]/gi, '_').toLowerCase(),
                builtin: $row.find('.eh-field-builtin').val() === '1',
                type: $row.find('.eh-field-type').val(),
                label: $row.find('.eh-field-label').val(),
                hint: $row.find('.eh-field-hint').val(),
                required: $row.find('.eh-field-required').is(':checked'),
                enabled: $row.find('.eh-field-enabled').is(':checked'),
                options: [],
                show_when: null,
            };
            var optsRaw = $row.find('.eh-field-options').val();
            if (optsRaw) {
                optsRaw.split('\n').forEach(function (line) {
                    line = line.trim();
                    if (line) {
                        field.options.push({ value: line.toLowerCase().replace(/\s+/g, '_'), label: line });
                    }
                });
            }
            var condField = $row.find('.eh-field-cond-field').val();
            if (condField) {
                field.show_when = {
                    field: condField,
                    op: $row.find('.eh-field-cond-op').val(),
                    value: $row.find('.eh-field-cond-value').val(),
                };
            }
            fields.push(field);
        });
        return fields;
    }

    function allFieldIds() {
        var ids = [];
        $('#eh-event-fields-builder .eh-field-row').each(function () {
            var id = $(this).find('.eh-field-id').val();
            if (id) {
                ids.push(id);
            }
        });
        return ids;
    }

    function renderFieldRow(field, index) {
        var isBuiltin = field && field.builtin;
        var f = field || {
            id: 'custom_' + Date.now(),
            builtin: false,
            type: 'text',
            label: '',
            hint: '',
            required: false,
            enabled: true,
            options: [],
            show_when: null,
        };
        var optsText = (f.options || [])
            .map(function (o) {
                return o.label || o.value || '';
            })
            .join('\n');
        var cond = f.show_when || {};
        var fieldRefs = allFieldIds();
        var condOptions = '<option value="">' + (i18n.noCondition || 'Sempre visível') + '</option>';
        fieldRefs.forEach(function (fid) {
            if (fid !== f.id) {
                condOptions +=
                    '<option value="' +
                    fid +
                    '"' +
                    (cond.field === fid ? ' selected' : '') +
                    '>' +
                    fid +
                    '</option>';
            }
        });

        var $row = $(
            '<div class="eh-field-row">' +
                '<input type="hidden" class="eh-field-builtin" value="' +
                (isBuiltin ? '1' : '0') +
                '" />' +
                '<p><label>' +
                (i18n.label || 'Etiqueta') +
                ' <input type="text" class="eh-field-label widefat" value="' +
                (f.label || '').replace(/"/g, '&quot;') +
                '" /></label></p>' +
                '<p><label>' +
                (i18n.customId || 'ID') +
                ' <input type="text" class="eh-field-id" value="' +
                (f.id || '') +
                '" ' +
                (isBuiltin ? 'readonly' : '') +
                ' /></label></p>' +
                '<p><label>' +
                (i18n.type || 'Tipo') +
                ' <select class="eh-field-type">' +
                ['text', 'textarea', 'email', 'tel', 'date', 'select', 'radio', 'checkbox']
                    .map(function (t) {
                        return '<option value="' + t + '"' + (f.type === t ? ' selected' : '') + '>' + t + '</option>';
                    })
                    .join('') +
                '</select></label></p>' +
                '<p><label><input type="checkbox" class="eh-field-required" ' +
                (f.required ? 'checked' : '') +
                ' /> ' +
                (i18n.required || 'Obrigatório') +
                '</label> ' +
                '<label><input type="checkbox" class="eh-field-enabled" ' +
                (f.enabled !== false ? 'checked' : '') +
                ' /> ' +
                (i18n.enabled || 'Ativo') +
                '</label></p>' +
                '<p class="eh-field-options-wrap"><label>' +
                (i18n.options || 'Opções') +
                '<textarea class="eh-field-options" rows="2">' +
                optsText +
                '</textarea></label></p>' +
                '<p><label>' +
                (i18n.showWhen || 'Condição') +
                ' <select class="eh-field-cond-field">' +
                condOptions +
                '</select></label> ' +
                '<select class="eh-field-cond-op">' +
                ['equals', 'not_equals', 'not_empty', 'empty']
                    .map(function (o) {
                        return '<option value="' + o + '"' + (cond.op === o ? ' selected' : '') + '>' + o + '</option>';
                    })
                    .join('') +
                '</select> ' +
                '<input type="text" class="eh-field-cond-value" value="' +
                (cond.value || '').replace(/"/g, '&quot;') +
                '" placeholder="' +
                (i18n.value || 'Valor') +
                '" /></p>' +
                '<p><button type="button" class="button eh-field-remove">' +
                (i18n.remove || 'Remover') +
                '</button></p>' +
                '<hr /></div>'
        );

        if (isBuiltin) {
            $row.find('.eh-field-remove').hide();
            $row.find('.eh-field-id').prop('readonly', true);
        }

        return $row;
    }

    function initBuilder() {
        var $builder = $('#eh-event-fields-builder');
        if (!$builder.length) {
            return;
        }

        var config = getBaseConfig();
        var fields = config.fields && config.fields.length ? config.fields : builtinFields;

        $builder.empty();
        fields.forEach(function (field, idx) {
            $builder.append(renderFieldRow(field, idx));
        });

        if (!$builder.find('.eh-field-row').length) {
            builtinFields.forEach(function (field) {
                $builder.append(renderFieldRow(field, 0));
            });
        }

        $builder.after(
            '<p><button type="button" class="button" id="eh-event-add-field">' +
                (i18n.addField || 'Adicionar campo') +
                '</button></p>'
        );
    }

    function syncJson() {
        var config = collectConfigFromDom();
        $('#eh_event_config_json').val(JSON.stringify(config));
    }

    $(function () {
        initBuilder();

        $(document).on('click', '#eh-event-add-field', function (e) {
            e.preventDefault();
            $('#eh-event-fields-builder').append(
                renderFieldRow({ id: 'custom_' + Date.now(), builtin: false, type: 'text', enabled: true }, 0)
            );
        });

        $(document).on('click', '.eh-field-remove', function (e) {
            e.preventDefault();
            $(this).closest('.eh-field-row').remove();
        });

        $(document).on(
            'change input',
            '#eh_event_product_data input, #eh_event_product_data select, #eh_event_product_data textarea, #eh-event-fields-builder',
            syncJson
        );

        $('form#post').on('submit', function () {
            syncJson();
        });

        $('#product-type').on('change', function () {
            if ($(this).val() === 'event') {
                window.setTimeout(syncJson, 100);
            }
        });

        syncJson();
    });
})();
