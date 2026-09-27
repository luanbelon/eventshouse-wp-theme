<?php
/**
 * Admin: configuração modular do produto tipo event.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Guarda config JSON a partir do POST do produto.
 *
 * @param int $post_id ID.
 */
function EventsHouse_event_save_config_from_post($post_id)
{
    $decoded = null;
    if (isset($_POST['eh_event_config_json']) && $_POST['eh_event_config_json'] !== '') {
        $json = wp_unslash($_POST['eh_event_config_json']);
        $decoded = json_decode($json, true);
    }
    if (!is_array($decoded)) {
        $decoded = EventsHouse_event_build_config_from_admin_post($post_id);
    }
    if (!is_array($decoded)) {
        return;
    }
    $config = EventsHouse_event_sanitize_config($decoded);
    update_post_meta($post_id, EventsHouse_event_config_meta_key(), wp_json_encode($config));
    EventsHouse_event_sync_legacy_meta($post_id, $config);
}

/**
 * @param int $post_id ID do produto.
 * @return array<string, mixed>|null
 */
function EventsHouse_event_build_config_from_admin_post($post_id = 0)
{
    if (!isset($_POST['eh_evt_admin_date'])) {
        return null;
    }
    $existing = array();
    $raw = $post_id ? get_post_meta($post_id, EventsHouse_event_config_meta_key(), true) : '';
    if (is_string($raw)) {
        $existing = json_decode($raw, true);
    }
    if (!is_array($existing)) {
        $existing = EventsHouse_event_default_config();
    }

    $paid_min = isset($_POST['eh_evt_admin_paid_min']) ? sanitize_text_field(wp_unslash($_POST['eh_evt_admin_paid_min'])) : '';
    $paid_max = isset($_POST['eh_evt_admin_paid_max']) ? sanitize_text_field(wp_unslash($_POST['eh_evt_admin_paid_max'])) : '';

    $existing['event'] = array(
        'date'       => sanitize_text_field(wp_unslash($_POST['eh_evt_admin_date'] ?? '')),
        'day_label'  => sanitize_text_field(wp_unslash($_POST['eh_evt_admin_day_label'] ?? '')),
        'time_start' => sanitize_text_field(wp_unslash($_POST['eh_evt_admin_time_start'] ?? '')),
        'time_end'   => sanitize_text_field(wp_unslash($_POST['eh_evt_admin_time_end'] ?? '')),
        'location'   => sanitize_text_field(wp_unslash($_POST['eh_evt_admin_location'] ?? '')),
    );
    $existing['base'] = array(
        'adults'   => absint($_POST['eh_evt_admin_base_adults'] ?? 1),
        'children' => absint($_POST['eh_evt_admin_base_children'] ?? 1),
    );
    $existing['pricing'] = array(
        'has_base_price'     => !empty($_POST['eh_evt_admin_has_base_price']),
        'adult_extra'        => (float) wc_format_decimal(wp_unslash($_POST['eh_evt_admin_adult_extra'] ?? '7')),
        'child_extra'        => (float) wc_format_decimal(wp_unslash($_POST['eh_evt_admin_child_extra'] ?? '7')),
        'child_free_max_age' => absint($_POST['eh_evt_admin_free_age'] ?? 3),
        'child_paid_age_min' => $paid_min === '' ? null : absint($paid_min),
        'child_paid_age_max' => $paid_max === '' ? null : absint($paid_max),
    );
    $existing['extras'] = array(
        'enabled' => !empty($_POST['eh_evt_admin_extras_enabled']),
        'max'     => absint($_POST['eh_evt_admin_extras_max'] ?? 10),
    );

    return $existing;
}

/**
 * Painel «Dados do evento» no metabox do produto.
 */
function EventsHouse_wc_event_product_data_panel()
{
    global $post;
    if (!$post) {
        return;
    }

    $product = wc_get_product($post->ID);
    if ($product && $product->get_type() === 'event') {
        $config  = EventsHouse_event_get_config($product);
        $regular = $product->get_regular_price('edit');
        $sale    = $product->get_sale_price('edit');
    } else {
        $config  = EventsHouse_event_default_config();
        $regular = $product ? $product->get_regular_price('edit') : '';
        $sale    = $product ? $product->get_sale_price('edit') : '';
    }

    $event   = $config['event'] ?? array();
    $base    = $config['base'] ?? array();
    $pricing = $config['pricing'] ?? array();
    $extras  = $config['extras'] ?? array();

    echo '<div id="eh_event_product_data" class="panel woocommerce_options_panel hidden">';

    echo '<div class="options_group">';
    echo '<p class="form-field"><strong>' . esc_html__('Preço da inscrição base', 'EventsHouse') . '</strong></p>';
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_has_base_price">';
    echo '<input type="checkbox" id="eh_evt_admin_has_base_price" name="eh_evt_admin_has_base_price" value="1" ' . checked(!empty($pricing['has_base_price']), true, false) . ' /> ';
    echo esc_html__('Este evento tem preço base (inscrição fixa)', 'EventsHouse');
    echo '</label>';
    echo '<span class="description">' . esc_html__('Se desmarcar, o total inicial na ficha é 0 € e só se pagam os bilhetes extra que o cliente escolher.', 'EventsHouse') . '</span>';
    echo '</p>';
    echo '<div id="eh_evt_admin_base_price_fields">';
    woocommerce_wp_text_input(
        array(
            'id'                => '_regular_price',
            'name'              => '_regular_price',
            'value'             => esc_attr((string) $regular),
            'label'             => __('Preço base (€)', 'EventsHouse'),
            'description'       => __('Valor da inscrição conforme o pacote base definido abaixo.', 'EventsHouse'),
            'type'              => 'text',
            'data_type'         => 'price',
            'custom_attributes' => array('step' => '0.01', 'min' => '0'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'                => '_sale_price',
            'name'              => '_sale_price',
            'value'             => esc_attr((string) $sale),
            'label'             => __('Preço promocional (€)', 'EventsHouse'),
            'type'              => 'text',
            'data_type'         => 'price',
            'custom_attributes' => array('step' => '0.01', 'min' => '0'),
        )
    );
    echo '</div>';
    echo '</div>';

    echo '<div class="options_group eh-event-admin-config">';
    echo '<p class="form-field"><strong>' . esc_html__('Informação do evento (sempre visível na ficha)', 'EventsHouse') . '</strong></p>';

    woocommerce_wp_text_input(
        array(
            'id'          => 'eh_evt_admin_date',
            'name'        => 'eh_evt_admin_date',
            'label'       => __('Data do evento', 'EventsHouse'),
            'value'       => esc_attr($event['date'] ?? ''),
            'type'        => 'date',
            'description' => __('Aparece na ficha, carrinho e encomenda.', 'EventsHouse'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'          => 'eh_evt_admin_day_label',
            'name'        => 'eh_evt_admin_day_label',
            'label'       => __('Rótulo do dia (opcional)', 'EventsHouse'),
            'value'       => esc_attr($event['day_label'] ?? ''),
            'description' => __('Ex.: Dia 4 — substitui a data formatada se preenchido.', 'EventsHouse'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'    => 'eh_evt_admin_time_start',
            'name'  => 'eh_evt_admin_time_start',
            'label' => __('Hora início', 'EventsHouse'),
            'value' => esc_attr($event['time_start'] ?? ''),
            'type'  => 'time',
            'custom_attributes' => array('step' => '60'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'    => 'eh_evt_admin_time_end',
            'name'  => 'eh_evt_admin_time_end',
            'label' => __('Hora fim', 'EventsHouse'),
            'value' => esc_attr($event['time_end'] ?? ''),
            'type'  => 'time',
            'custom_attributes' => array('step' => '60'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'          => 'eh_evt_admin_location',
            'name'        => 'eh_evt_admin_location',
            'label'       => __('Local', 'EventsHouse'),
            'value'       => esc_attr($event['location'] ?? ''),
            'description' => __('Ex.: A Ilha — Carnaxide', 'EventsHouse'),
        )
    );

    echo '<p class="form-field"><strong>' . esc_html__('Pacote base incluído na inscrição', 'EventsHouse') . '</strong></p>';
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_base_adults">' . esc_html__('Adultos incluídos', 'EventsHouse') . '</label>';
    echo '<input type="number" class="short" id="eh_evt_admin_base_adults" name="eh_evt_admin_base_adults" min="0" max="5" step="1" value="' . esc_attr((string) ($base['adults'] ?? 1)) . '" />';
    echo '</p>';
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_base_children">' . esc_html__('Crianças incluídas', 'EventsHouse') . '</label>';
    echo '<input type="number" class="short" id="eh_evt_admin_base_children" name="eh_evt_admin_base_children" min="0" max="5" step="1" value="' . esc_attr((string) ($base['children'] ?? 1)) . '" />';
    echo '</p>';

    echo '<p class="form-field"><strong>' . esc_html__('Preços dos participantes extra', 'EventsHouse') . '</strong></p>';
    woocommerce_wp_text_input(
        array(
            'id'                => 'eh_evt_admin_adult_extra',
            'name'              => 'eh_evt_admin_adult_extra',
            'label'             => __('Adulto extra (€)', 'EventsHouse'),
            'value'             => esc_attr((string) ($pricing['adult_extra'] ?? 7)),
            'type'              => 'number',
            'custom_attributes' => array('step' => '0.01', 'min' => '0'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id'                => 'eh_evt_admin_child_extra',
            'name'              => 'eh_evt_admin_child_extra',
            'label'             => __('Criança extra (€)', 'EventsHouse'),
            'value'             => esc_attr((string) ($pricing['child_extra'] ?? 7)),
            'type'              => 'number',
            'custom_attributes' => array('step' => '0.01', 'min' => '0'),
        )
    );
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_free_age">' . esc_html__('Gratuito até aos (anos)', 'EventsHouse') . '</label>';
    echo '<input type="number" class="short" id="eh_evt_admin_free_age" name="eh_evt_admin_free_age" min="0" max="18" step="1" value="' . esc_attr((string) ($pricing['child_free_max_age'] ?? 3)) . '" />';
    echo '<span class="description">' . esc_html__('Crianças extra com idade ≤ este valor na data do evento não pagam.', 'EventsHouse') . '</span>';
    echo '</p>';
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_paid_min">' . esc_html__('Faixa paga — idade mín. (opcional, só texto)', 'EventsHouse') . '</label>';
    echo '<input type="number" class="short" id="eh_evt_admin_paid_min" name="eh_evt_admin_paid_min" min="0" max="18" step="1" value="' . esc_attr($pricing['child_paid_age_min'] !== null ? (string) $pricing['child_paid_age_min'] : '') . '" />';
    echo '</p>';
    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_paid_max">' . esc_html__('Faixa paga — idade máx. (opcional)', 'EventsHouse') . '</label>';
    echo '<input type="number" class="short" id="eh_evt_admin_paid_max" name="eh_evt_admin_paid_max" min="0" max="18" step="1" value="' . esc_attr($pricing['child_paid_age_max'] !== null ? (string) $pricing['child_paid_age_max'] : '') . '" />';
    echo '</p>';

    echo '<p class="form-field">';
    echo '<label for="eh_evt_admin_extras_enabled">';
    echo '<input type="checkbox" id="eh_evt_admin_extras_enabled" name="eh_evt_admin_extras_enabled" value="1" ' . checked(!empty($extras['enabled']), true, false) . ' /> ';
    echo esc_html__('Permitir participantes extra', 'EventsHouse');
    echo '</label></p>';
    woocommerce_wp_text_input(
        array(
            'id'                => 'eh_evt_admin_extras_max',
            'name'              => 'eh_evt_admin_extras_max',
            'label'             => __('Máximo de participantes extra', 'EventsHouse'),
            'value'             => esc_attr((string) ($extras['max'] ?? 10)),
            'type'              => 'number',
            'description'       => __('Limite do seletor +/− no site (0–50).', 'EventsHouse'),
            'custom_attributes' => array('step' => '1', 'min' => '0', 'max' => '50'),
        )
    );

    echo '<p class="form-field"><strong>' . esc_html__('Campos do formulário de inscrição', 'EventsHouse') . '</strong></p>';
    echo '<p class="description">' . esc_html__('Campos built-in (email, telefone, consentimento) e campos personalizados com visibilidade condicional.', 'EventsHouse') . '</p>';
    echo '<div id="eh-event-fields-builder" data-config="' . esc_attr(wp_json_encode($config)) . '"></div>';
    echo '<input type="hidden" name="eh_event_config_json" id="eh_event_config_json" value="" />';
    echo '</div>';

    echo '</div>';
}
add_action('woocommerce_product_data_panels', 'EventsHouse_wc_event_product_data_panel', 10);

/**
 * Scripts admin: visibilidade do tipo event + builder.
 *
 * @param string $hook_suffix Hook.
 */
function EventsHouse_event_admin_enqueue($hook_suffix)
{
    if ($hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php') {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->post_type !== 'product') {
        return;
    }

    wp_enqueue_style(
        'eh-event-admin-config',
        EventsHouse_URI . '/assets/css/admin-event-product-config.css',
        array(),
        EventsHouse_VERSION
    );
    wp_enqueue_script(
        'eh-event-admin-config',
        EventsHouse_URI . '/assets/js/admin-event-product-config.js',
        array('jquery', 'woocommerce_admin'),
        EventsHouse_VERSION,
        true
    );
    wp_localize_script(
        'eh-event-admin-config',
        'ehEventAdminConfig',
        array(
            'builtinFields' => EventsHouse_event_builtin_fields(),
            'i18n'          => array(
                'addField'    => __('Adicionar campo', 'EventsHouse'),
                'remove'      => __('Remover', 'EventsHouse'),
                'label'       => __('Etiqueta', 'EventsHouse'),
                'type'        => __('Tipo', 'EventsHouse'),
                'required'    => __('Obrigatório', 'EventsHouse'),
                'enabled'     => __('Ativo', 'EventsHouse'),
                'options'     => __('Opções (uma por linha)', 'EventsHouse'),
                'showWhen'    => __('Mostrar quando', 'EventsHouse'),
                'fieldRef'    => __('Campo', 'EventsHouse'),
                'operator'    => __('Operador', 'EventsHouse'),
                'value'       => __('Valor', 'EventsHouse'),
                'noCondition' => __('Sempre visível', 'EventsHouse'),
                'customId'    => __('ID (slug)', 'EventsHouse'),
            ),
        )
    );

    wp_add_inline_script(
        'eh-event-admin-config',
        "jQuery(function($){
            function ehEventAdminToggle(){
                var isEvent = $('#product-type').val() === 'event';
                $('.show_if_event').each(function(){
                    $(this).toggle(isEvent);
                });
                $('li.eh_event_options, li.general_options').toggle(isEvent);
                if (isEvent) {
                    $('.options_group.pricing').addClass('show_if_event').show();
                    if (!$('li.eh_event_options').hasClass('active')) {
                        $('li.eh_event_options a').trigger('click');
                    }
                }
                var hasBase = $('#eh_evt_admin_has_base_price').is(':checked');
                $('#eh_evt_admin_base_price_fields').toggle(hasBase);
            }
            $('#eh_evt_admin_has_base_price').on('change', function(){
                $('#eh_evt_admin_base_price_fields').toggle($(this).is(':checked'));
            });
            ehEventAdminToggle();
            $('#product-type').on('change', ehEventAdminToggle);
            $(document.body).on('woocommerce-product-type-change', function(e, type){
                if (type === 'event') { ehEventAdminToggle(); }
            });
        });"
    );
}
add_action('admin_enqueue_scripts', 'EventsHouse_event_admin_enqueue', 25);

/**
 * @param int $post_id ID.
 */
function EventsHouse_wc_event_save_product_meta($post_id)
{
    if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce_meta_nonce'])), 'woocommerce_save_data')) {
        return;
    }

    $type = isset($_POST['product-type']) ? sanitize_text_field(wp_unslash($_POST['product-type'])) : '';
    if ($type !== 'event' && !EventsHouse_wc_is_event_product($post_id)) {
        return;
    }

    EventsHouse_event_save_config_from_post($post_id);

    if (isset($_POST['_regular_price'])) {
        $regular = wc_format_decimal(wp_unslash($_POST['_regular_price']));
        update_post_meta($post_id, '_regular_price', $regular);
        $sale = isset($_POST['_sale_price']) ? wc_format_decimal(wp_unslash($_POST['_sale_price'])) : '';
        if ($sale !== '' && is_numeric($sale) && (float) $sale < (float) $regular) {
            update_post_meta($post_id, '_sale_price', $sale);
            update_post_meta($post_id, '_price', $sale);
        } else {
            delete_post_meta($post_id, '_sale_price');
            update_post_meta($post_id, '_price', $regular);
        }
        $product = wc_get_product($post_id);
        if ($product) {
            $product->set_regular_price($regular);
            $product->set_sale_price($sale !== '' && is_numeric($sale) ? $sale : '');
            $product->save();
        }
    }
}
add_action('woocommerce_process_product_meta', 'EventsHouse_wc_event_save_product_meta', 20, 1);
add_action('woocommerce_process_product_meta_event', 'EventsHouse_wc_event_save_product_meta', 10, 1);
