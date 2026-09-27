/* ═══════════════════════════════════════
   EventsHouse — Admin Options Page JS
   ═══════════════════════════════════════ */
(function ($) {
    'use strict';

    // ── TABS ────────────────────────────────
    $(document).on('click', '.eh-tab-btn', function () {
        var tab = $(this).data('tab');
        $('.eh-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.eh-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    // ── MEDIA UPLOADER (botões nos cards de galeria) ──
    $(document).on('click', '.eh-media-btn', function (e) {
        e.preventDefault();
        var btn = $(this);
        var targetName = btn.data('target');
        var targetInput = $('[name="' + targetName + '"]');
        var previewContainer = btn.closest('.gallery-admin-item').find('.gallery-admin-preview');
        if (!previewContainer.length) {
            previewContainer = btn.closest('.orcamento-image-wrapper').find('.orcamento-image-preview');
        }
        if (!previewContainer.length) {
            previewContainer = btn.closest('td').find('.eh-hero-slide-preview');
        }

        var frame = wp.media({
            title: 'Selecionar Imagem',
            button: { text: 'Usar esta imagem' },
            multiple: false,
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            targetInput.val(attachment.url);
            if (previewContainer.hasClass('eh-hero-slide-preview')) {
                previewContainer.html('<img src="' + attachment.url + '" alt="" style="max-width:220px;height:auto;border:1px solid #ccc;border-radius:4px;" />');
            } else {
                previewContainer.html('<img src="' + attachment.url + '" alt="" />');
            }
        });

        frame.open();
    });

    // ── REMOVER LINHA DE SERVIÇO ──
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        reindexServices();
    });

    // ── ADICIONAR LINHA DE SERVIÇO ──
    $('#add-service').on('click', function () {
        var idx = $('#services-table tbody tr').length;
        var row = '<tr class="service-row">'
            + '<td><span class="drag-handle">⠿</span></td>'
            + '<td><input type="text" name="services[' + idx + '][icon]" value="⭐" style="width:60px;font-size:20px;text-align:center;" /></td>'
            + '<td><input type="text" name="services[' + idx + '][name]" value="" placeholder="Nome do serviço" style="width:100%;" /></td>'
            + '<td><button type="button" class="button button-small remove-row">✕ Remover</button></td>'
            + '</tr>';
        $('#services-table tbody').append(row);
    });

    function reindexServices() {
        $('#services-table tbody tr').each(function (i) {
            $(this).find('input').each(function () {
                var name = $(this).attr('name').replace(/services\[\d+\]/, 'services[' + i + ']');
                $(this).attr('name', name);
            });
        });
    }

    // ── REMOVER ITEM DE GALERIA ──
    $(document).on('click', '.remove-gallery-item', function () {
        $(this).closest('.gallery-admin-item').remove();
        reindexGallery();
    });

    // ── ADICIONAR ITEM DE GALERIA ──
    $('#add-gallery-item').on('click', function () {
        var idx = $('#gallery-page-grid .gallery-admin-item').length;

        var html = '<div class="gallery-admin-item" data-index="' + idx + '">'
            + '<div class="gallery-admin-preview"><div class="no-image">Sem imagem</div></div>'
            + '<input type="hidden" name="gallery_page[' + idx + '][url]" value="" class="gallery-url-input" />'
            + '<div class="gallery-admin-fields">'
            + '<button type="button" class="button eh-media-btn" data-target="gallery_page[' + idx + '][url]">🖼 Escolher Imagem</button>'
            + '<input type="text" name="gallery_page[' + idx + '][alt]" value="" placeholder="Alt / Descrição" style="width:100%;margin-top:5px;" />'
            + '<select name="gallery_page[' + idx + '][category]" style="width:100%;margin-top:5px;">'
            + '<option value="espaco">Espaço</option>'
            + '<option value="festas">Festas</option>'
            + '<option value="decoracao">Decoração</option>'
            + '</select>'
            + '<button type="button" class="button button-small remove-gallery-item" style="margin-top:5px;">✕ Remover</button>'
            + '</div></div>';

        $('#gallery-page-grid').append(html);
    });

    function reindexGallery() {
        $('#gallery-page-grid .gallery-admin-item').each(function (i) {
            $(this).attr('data-index', i);
            $(this).find('input, select').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/gallery_page\[\d+\]/, 'gallery_page[' + i + ']'));
                }
            });
            $(this).find('.eh-media-btn').attr('data-target', 'gallery_page[' + i + '][url]');
        });
    }

    // ── ADICIONAR EXTRA (ORÇAMENTO) ──
    $('#add-extra-btn').on('click', function () {
        var idx = $('#orcamento-extras-container .orcamento-card-admin').length;
        var html = '<div class="orcamento-card-admin" data-index="' + idx + '" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:15px;">'
            + '<div style="display:flex; gap:10px; margin-bottom:10px;">'
            + '<div style="flex:1;">'
            + '<label><strong>Título</strong></label><br>'
            + '<input type="text" name="orcamento_extras[' + idx + '][title]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:1;">'
            + '<label><strong>Horário</strong></label><br>'
            + '<input type="text" name="orcamento_extras[' + idx + '][time]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:0.5;">'
            + '<label><strong>Preço (€)</strong></label><br>'
            + '<input type="text" name="orcamento_extras[' + idx + '][price]" value="" style="width:100%" />'
            + '</div>'
            + '</div>'
            + '<div>'
            + '<label><strong>Descrição</strong></label><br>'
            + '<textarea name="orcamento_extras[' + idx + '][desc]" rows="2" style="width:100%"></textarea>'
            + '</div>'
            + '<div style="margin-top:10px;" class="orcamento-image-wrapper">'
            + '<label><strong>Imagem</strong></label><br>'
            + '<div style="display:flex; gap:10px; align-items:center;">'
            + '<input type="text" name="orcamento_extras[' + idx + '][image]" value="" style="flex:1;" />'
            + '<button type="button" class="button eh-media-btn" data-target="orcamento_extras[' + idx + '][image]">🖼 Escolher Imagem</button>'
            + '</div>'
            + '<div class="orcamento-image-preview" style="margin-top:5px; max-width: 150px;"></div>'
            + '</div>'
            + '<p style="text-align:right;margin:10px 0 0 0;">'
            + '<button type="button" class="button button-small remove-orcamento-btn" data-section="extras" style="color:#b22;">✕ Remover</button>'
            + '</p>'
            + '</div>';
        
        $('#orcamento-extras-container').append(html);
    });

    // ── ADICIONAR CARD (ORÇAMENTO): Fim de Semana ──
    $('#add-fimsemana-btn').on('click', function () {
        var idx = $('#orcamento-fimsemana-container .orcamento-card-admin').length;
        var html = '<div class="orcamento-card-admin" data-index="' + idx + '" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:15px;">'
            + '<div style="display:flex; gap:10px; margin-bottom:10px;">'
            + '<div style="flex:1;">'
            + '<label><strong>Título</strong></label><br>'
            + '<input type="text" name="orcamento_fimsemana[' + idx + '][title]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:1;">'
            + '<label><strong>Horário</strong></label><br>'
            + '<input type="text" name="orcamento_fimsemana[' + idx + '][time]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:0.5;">'
            + '<label><strong>Preço (€)</strong></label><br>'
            + '<input type="text" name="orcamento_fimsemana[' + idx + '][price]" value="" style="width:100%" />'
            + '</div>'
            + '</div>'
            + '<div>'
            + '<label><strong>Descrição</strong></label><br>'
            + '<textarea name="orcamento_fimsemana[' + idx + '][desc]" rows="2" style="width:100%"></textarea>'
            + '</div>'
            + '<p style="text-align:right;margin:10px 0 0 0;">'
            + '<button type="button" class="button button-small remove-orcamento-btn" data-section="fimsemana" style="color:#b22;">✕ Remover</button>'
            + '</p>'
            + '</div>';

        $('#orcamento-fimsemana-container').append(html);
    });

    // ── ADICIONAR CARD (ORÇAMENTO): Dias Úteis ──
    $('#add-uteis-btn').on('click', function () {
        var idx = $('#orcamento-uteis-container .orcamento-card-admin').length;
        var html = '<div class="orcamento-card-admin" data-index="' + idx + '" style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:15px;">'
            + '<div style="display:flex; gap:10px; margin-bottom:10px;">'
            + '<div style="flex:1;">'
            + '<label><strong>Título</strong></label><br>'
            + '<input type="text" name="orcamento_uteis[' + idx + '][title]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:1;">'
            + '<label><strong>Horário</strong></label><br>'
            + '<input type="text" name="orcamento_uteis[' + idx + '][time]" value="" style="width:100%" />'
            + '</div>'
            + '<div style="flex:0.5;">'
            + '<label><strong>Preço (€)</strong></label><br>'
            + '<input type="text" name="orcamento_uteis[' + idx + '][price]" value="" style="width:100%" />'
            + '</div>'
            + '</div>'
            + '<div>'
            + '<label><strong>Descrição</strong></label><br>'
            + '<textarea name="orcamento_uteis[' + idx + '][desc]" rows="2" style="width:100%"></textarea>'
            + '</div>'
            + '<p style="text-align:right;margin:10px 0 0 0;">'
            + '<button type="button" class="button button-small remove-orcamento-btn" data-section="uteis" style="color:#b22;">✕ Remover</button>'
            + '</p>'
            + '</div>';

        $('#orcamento-uteis-container').append(html);
    });

    // ── REMOVER CARD (ORÇAMENTO) ──
    $(document).on('click', '.remove-orcamento-btn', function () {
        var sec = $(this).data('section');
        $(this).closest('.orcamento-card-admin').remove();
        if (sec) {
            reindexOrcamentoSection(sec);
        }
    });

    function reindexOrcamentoSection(sec) {
        var containerSel = '#orcamento-' + sec + '-container';
        $(containerSel + ' .orcamento-card-admin').each(function (i) {
            $(this).attr('data-index', i);
            $(this).find('input, textarea').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    var re = new RegExp('orcamento_' + sec + '\\[\\d+\\]');
                    $(this).attr('name', name.replace(re, 'orcamento_' + sec + '[' + i + ']'));
                }
            });
        });
    }

    // ── EMAIL TESTER (EventsThemes > Emails) ──
    $(document).on('click', '.eh-send-test-email', function () {
        var cfg = window.EHAdminEmailTest || {};
        var ajaxUrl = cfg.ajaxUrl || window.ajaxurl || '';
        var nonce = cfg.nonce || '';
        var btn = $(this);
        var tpl = btn.data('template');
        var to = ($('#eventshouse_test_email_to').val() || '').toString().trim();
        var feedback = $('#eventshouse_test_email_feedback');

        if (!to) {
            feedback.text('Indique o email de destino para teste.').css('color', '#b91c1c');
            return;
        }
        if (!ajaxUrl || !nonce) {
            feedback.text('Configuração AJAX indisponível.').css('color', '#b91c1c');
            return;
        }

        var payload = {
            action: 'eventshouse_send_test_email',
            nonce: nonce,
            to: to,
            template: tpl,
            brand_color: $('#eventshouse_email_brand_color').val() || '',
            footer_note: $('#eventshouse_email_footer_note').val() || '',
        };

        if (tpl === 'contact') {
            payload.subject = $('#eventshouse_email_contact_subject').val() || '';
            payload.title = $('#eventshouse_email_contact_title').val() || '';
            payload.body = $('#eventshouse_email_contact_body').val() || '';
        } else if (tpl === 'avail_festas') {
            payload.subject = $('#eventshouse_email_avail_subject_festas').val() || '';
            payload.title = $('#eventshouse_email_avail_title_festas').val() || '';
            payload.body = $('#eventshouse_email_avail_body_festas').val() || '';
        } else if (tpl === 'avail_ilha') {
            payload.subject = $('#eventshouse_email_avail_subject_ilha').val() || '';
            payload.title = $('#eventshouse_email_avail_title_ilha').val() || '';
            payload.body = $('#eventshouse_email_avail_body_ilha').val() || '';
        } else if (tpl === 'ilha_reservation') {
            payload.subject = $('#eventshouse_email_ilha_reservation_subject').val() || '';
            payload.title = $('#eventshouse_email_ilha_reservation_title').val() || '';
            payload.body = $('#eventshouse_email_ilha_reservation_body').val() || '';
        } else if (tpl === 'ilha_reservation_admin') {
            payload.subject = $('#eventshouse_email_ilha_reservation_admin_subject').val() || '';
            payload.title = $('#eventshouse_email_ilha_reservation_admin_title').val() || '';
            payload.body = $('#eventshouse_email_ilha_reservation_admin_body').val() || '';
        } else if (tpl === 'ilha_reservation_reminder') {
            payload.subject = $('#eventshouse_email_ilha_reservation_reminder_subject').val() || '';
            payload.title = $('#eventshouse_email_ilha_reservation_reminder_title').val() || '';
            payload.body = $('#eventshouse_email_ilha_reservation_reminder_body').val() || '';
        } else if (tpl === 'ilha_reservation_postvisit') {
            payload.subject = $('#eventshouse_email_ilha_reservation_postvisit_subject').val() || '';
            payload.title = $('#eventshouse_email_ilha_reservation_postvisit_title').val() || '';
            payload.body = $('#eventshouse_email_ilha_reservation_postvisit_body').val() || '';
        } else if (tpl === 'ilha_pack_purchase') {
            payload.subject = $('#eventshouse_email_ilha_pack_purchase_subject').val() || '';
            payload.title = $('#eventshouse_email_ilha_pack_purchase_title').val() || '';
            payload.body = $('#eventshouse_email_ilha_pack_purchase_body').val() || '';
        } else if (tpl === 'festa_order') {
            payload.subject = $('#eventshouse_festa_order_email_subject').val() || '';
            payload.title = $('#eventshouse_festa_order_email_title').val() || '';
            payload.body = $('#eventshouse_festa_order_email_body').val() || '';
        }

        var defaultText = btn.text();
        btn.prop('disabled', true).text('A enviar...');
        feedback.text('A enviar email teste...').css('color', '#475569');

        $.post(ajaxUrl, payload)
            .done(function (res) {
                if (res && res.success) {
                    feedback.text((res.data && res.data.message) ? res.data.message : 'Email teste enviado.').css('color', '#15803d');
                } else {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Falha ao enviar email teste.';
                    feedback.text(msg).css('color', '#b91c1c');
                }
            })
            .fail(function () {
                feedback.text('Erro de comunicação ao enviar email teste.').css('color', '#b91c1c');
            })
            .always(function () {
                btn.prop('disabled', false).text(defaultText);
            });
    });

})(jQuery);
