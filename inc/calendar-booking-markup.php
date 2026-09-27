<?php
/**
 * Bloco HTML: calendário de disponibilidade + formulário (Disponibilidade / Manhãs na Ilha).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Imprime o bloco de calendário e pedido de reserva.
 *
 * @param array $args {
 *     @type string $context          'default' (festas/disponibilidade) ou 'ilha'.
 *     @type string $section_title    Título H2 da secção.
 *     @type string $section_intro    Texto introdutório.
 *     @type array  $extras           Lista de extras (orçamento), cada item title/price.
 *     @type bool   $show_extras      Mostrar checkboxes de extras.
 *     @type bool   $gcal_configured  API + ID de calendário válidos para este contexto.
 *     @type string $submit_label     Texto do botão de envio.
 * }
 */
function EventsHouse_print_calendar_booking_block(array $args)
{
    $defaults = array(
        'context'          => 'default',
        'section_title'    => __('Verificar Disponibilidade', 'EventsHouse'),
        'section_intro'    => '',
        'extras'           => array(),
        'show_extras'      => true,
        'gcal_configured'  => false,
        'submit_label'     => __('📨 Verificar Disponibilidade', 'EventsHouse'),
    );
    $a = wp_parse_args($args, $defaults);

    $ctx = ($a['context'] === 'ilha') ? 'ilha' : 'default';
    $uid = ($ctx === 'ilha') ? 'ilha' : 'main';

    $extras = is_array($a['extras']) ? $a['extras'] : array();
    $show_extras = $a['show_extras'] && !empty($extras);
    ?>
    <div class="eh-cal-root eh-cal-root--<?php echo esc_attr($uid); ?>">
        <div class="eh-cal-loading" aria-live="polite">
            <span class="spin">⏳</span> <?php esc_html_e('A carregar calendário...', 'EventsHouse'); ?>
        </div>

        <div class="eh-calendar-wrap" data-cal-context="<?php echo esc_attr($ctx); ?>">
            <div class="eh-calendar">
                <div class="eh-cal-header">
                    <button type="button" class="eh-cal-nav eh-cal-prev" aria-label="<?php esc_attr_e('Mês anterior', 'EventsHouse'); ?>">‹</button>
                    <h3 class="eh-cal-title"></h3>
                    <button type="button" class="eh-cal-nav eh-cal-next" aria-label="<?php esc_attr_e('Próximo mês', 'EventsHouse'); ?>">›</button>
                </div>
                <div class="eh-cal-days-header">
                    <span><?php esc_html_e('Dom', 'EventsHouse'); ?></span><span><?php esc_html_e('Seg', 'EventsHouse'); ?></span><span><?php esc_html_e('Ter', 'EventsHouse'); ?></span><span><?php esc_html_e('Qua', 'EventsHouse'); ?></span><span><?php esc_html_e('Qui', 'EventsHouse'); ?></span><span><?php esc_html_e('Sex', 'EventsHouse'); ?></span><span><?php esc_html_e('Sáb', 'EventsHouse'); ?></span>
                </div>
                <div class="eh-cal-grid"></div>
                <div class="eh-cal-legend">
                    <div class="eh-legend-item">
                        <span class="eh-legend-dot available-dot"></span>
                        <?php echo $ctx === 'ilha' ? esc_html__('Com eventos', 'EventsHouse') : esc_html__('Disponível', 'EventsHouse'); ?>
                    </div>
                    <div class="eh-legend-item">
                        <span class="eh-legend-dot occupied-dot"></span>
                        <?php echo $ctx === 'ilha' ? esc_html__('Sem eventos', 'EventsHouse') : esc_html__('Ocupado', 'EventsHouse'); ?>
                    </div>
                </div>
            </div>

            <div class="eh-booking-panel<?php echo $ctx === 'ilha' ? ' eh-booking-panel--ilha' : ''; ?>" style="display:none;">
                <div class="eh-form-message" aria-live="polite"></div>
                <h3><?php echo $ctx === 'ilha' ? esc_html__('📅 Eventos do dia', 'EventsHouse') : esc_html__('🎉 Pedido de Reserva', 'EventsHouse'); ?></h3>

                <div class="eh-selected-date-badge">
                    <?php esc_html_e('Selecione uma data', 'EventsHouse'); ?>
                </div>

                <?php if ($ctx === 'ilha') : ?>
                    <p class="eh-slot-label">🗓️ <?php esc_html_e('Escolha um evento neste dia:', 'EventsHouse'); ?></p>
                    <div class="eh-slot-picker eh-event-picker"></div>
                    <div class="eh-event-detail" style="display:none;">
                        <h4 class="eh-event-detail-title"></h4>
                        <p class="eh-event-detail-time"></p>
                        <p class="eh-event-detail-desc"></p>
                        <a class="eh-event-detail-link" href="#" target="_blank" rel="noopener noreferrer" style="display:none;">
                            <?php esc_html_e('Inscrever / Saber mais →', 'EventsHouse'); ?>
                        </a>
                        <button type="button" class="eh-ilha-reserve-toggle" style="display:none;">
                            <?php esc_html_e('Reservar', 'EventsHouse'); ?>
                        </button>
                        <div class="eh-ilha-reserve-area" style="display:none;">
                        <form class="eh-ilha-email-form eh-bform" method="post" style="display:none;" autocomplete="on">
                            <p class="eh-ilha-email-intro"><?php esc_html_e('Introduza o email usado na compra do seu pack para continuar.', 'EventsHouse'); ?></p>
                            <div class="form-group">
                                <label for="eh-ilha-verify-email-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Email *', 'EventsHouse'); ?></label>
                                <input type="email" id="eh-ilha-verify-email-<?php echo esc_attr($uid); ?>" name="email" required autocomplete="email" placeholder="email@exemplo.pt">
                            </div>
                            <button type="submit" class="eh-submit-btn"><?php esc_html_e('Continuar', 'EventsHouse'); ?></button>
                        </form>
                        <div class="eh-ilha-no-pass-block" style="display:none;" role="status">
                            <p class="eh-ilha-no-pass-msg"><?php esc_html_e('Reservas permitidas apenas para utilizadores com pacote ativo.', 'EventsHouse'); ?></p>
                            <a class="eh-ilha-packs-cta eh-submit-btn" href="<?php echo esc_url(function_exists('EventsHouse_ilha_passes_page_url') ? EventsHouse_ilha_passes_page_url('#ilha-packs') : home_url('/manhas-na-ilha/#ilha-packs')); ?>">
                                <?php esc_html_e('Compre o seu pacote aqui', 'EventsHouse'); ?>
                            </a>
                            <p class="eh-ilha-packs-link-label">
                                <a href="<?php echo esc_url(function_exists('EventsHouse_ilha_passes_page_url') ? EventsHouse_ilha_passes_page_url('#ilha-packs') : home_url('/manhas-na-ilha/#ilha-packs')); ?>">
                                    <?php esc_html_e('Packs de Manhãs na Ilha', 'EventsHouse'); ?>
                                </a>
                            </p>
                        </div>
                        <form class="eh-ilha-reserve-form eh-bform" method="post" style="display:none;">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('EventsHouse_nonce')); ?>">
                            <input type="hidden" name="event_date" class="eh-ilha-date" value="">
                            <input type="hidden" name="event_title" class="eh-ilha-title" value="">
                            <input type="hidden" name="event_time" class="eh-ilha-time" value="">
                            <input type="hidden" name="event_link" class="eh-ilha-link" value="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="eh-ilha-name-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Nome *', 'EventsHouse'); ?></label>
                                    <input type="text" id="eh-ilha-name-<?php echo esc_attr($uid); ?>" name="name" required placeholder="<?php esc_attr_e('O seu nome', 'EventsHouse'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="eh-ilha-email-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Email *', 'EventsHouse'); ?></label>
                                    <input type="email" id="eh-ilha-email-<?php echo esc_attr($uid); ?>" name="email" required placeholder="email@exemplo.pt">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="eh-ilha-phone-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Telefone', 'EventsHouse'); ?></label>
                                    <input type="tel" id="eh-ilha-phone-<?php echo esc_attr($uid); ?>" name="phone" placeholder="+351 900 000 000">
                                </div>
                                <div class="form-group">
                                    <label for="eh-ilha-child-age-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Idade da criança', 'EventsHouse'); ?></label>
                                    <input type="number" id="eh-ilha-child-age-<?php echo esc_attr($uid); ?>" name="child_age" required min="1" max="18" step="1" inputmode="numeric" placeholder="<?php esc_attr_e('Ex.: 4', 'EventsHouse'); ?>">
                                </div>
                            </div>
                            <button type="submit" class="eh-submit-btn"><?php esc_html_e('Confirmar reserva', 'EventsHouse'); ?></button>
                        </form>
                        </div>
                    </div>
                <?php else : ?>
                    <form class="eh-booking-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('EventsHouse_nonce')); ?>">
                        <input type="hidden" name="cal_context" value="<?php echo esc_attr($ctx); ?>">
                        <input type="hidden" name="event_date" class="eh-input-date" value="">
                        <input type="hidden" name="time_slot" class="eh-input-slot" value="">

                        <p class="eh-slot-label">⏰ <?php esc_html_e('Escolha o horário: *', 'EventsHouse'); ?></p>
                        <div class="eh-slot-picker"></div>

                        <?php if ($show_extras) : ?>
                        <div class="form-group eh-extras-group">
                            <label><?php esc_html_e('Extras', 'EventsHouse'); ?></label>
                            <div class="eh-extras-list">
                                <?php foreach ($extras as $extra) : ?>
                                    <label class="eh-extra-checkbox">
                                        <input type="checkbox" name="extras[]" value="<?php echo esc_attr(sanitize_title($extra['title'] ?? '')); ?>">
                                        <span>
                                            <?php echo esc_html($extra['title'] ?? ''); ?>
                                            <?php if (!empty($extra['price'])) : ?>
                                                <small class="eh-extra-price">(+<?php echo esc_html($extra['price']); ?>€)</small>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="eh-bform">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="eh-avail-name-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Nome *', 'EventsHouse'); ?></label>
                                    <input type="text" id="eh-avail-name-<?php echo esc_attr($uid); ?>" name="name" required placeholder="<?php esc_attr_e('O seu nome', 'EventsHouse'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="eh-avail-email-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Email *', 'EventsHouse'); ?></label>
                                    <input type="email" id="eh-avail-email-<?php echo esc_attr($uid); ?>" name="email" required placeholder="email@exemplo.pt">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="eh-avail-phone-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Telefone *', 'EventsHouse'); ?></label>
                                    <input type="tel" id="eh-avail-phone-<?php echo esc_attr($uid); ?>" name="phone" required placeholder="+351 900 000 000">
                                </div>
                                <div class="form-group">
                                    <label for="eh-avail-guests-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Nº de Convidados *', 'EventsHouse'); ?></label>
                                    <input type="number" id="eh-avail-guests-<?php echo esc_attr($uid); ?>" name="guests" required placeholder="<?php esc_attr_e('Ex: 4', 'EventsHouse'); ?>" min="1" max="100">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="eh-avail-message-<?php echo esc_attr($uid); ?>"><?php esc_html_e('Mensagem (opcional)', 'EventsHouse'); ?></label>
                                <textarea id="eh-avail-message-<?php echo esc_attr($uid); ?>" name="message" placeholder="<?php esc_attr_e('Informações adicionais…', 'EventsHouse'); ?>"></textarea>
                            </div>
                            <button type="submit" class="eh-submit-btn"><?php echo esc_html($a['submit_label']); ?></button>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (!$a['gcal_configured']) : ?>
                <div class="eh-cal-not-configured">
                    <?php esc_html_e('O calendário de disponibilidade ainda não está configurado.', 'EventsHouse'); ?>
                    <a href="<?php echo esc_url(admin_url('themes.php?page=eventshouse-options')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Configurar agora →', 'EventsHouse'); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
