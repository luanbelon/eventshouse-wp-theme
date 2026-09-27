<?php
/**
 * Tipo de produto «event»: inscrição, preço base + extras (taxa), formulário.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verifica se o produto é do tipo evento (inscrição).
 *
 * @param WC_Product|int|null $product Produto ou ID. Usa o global $product se null.
 * @return bool
 */
function EventsHouse_wc_is_event_product($product = null)
{
    if ($product === null) {
        global $product;
    } elseif (is_numeric($product)) {
        $product = wc_get_product((int) $product);
    }
    return $product instanceof WC_Product && $product->get_type() === 'event';
}

/**
 * Meta: preço por participante extra (taxa no carrinho × quantidade).
 */
function EventsHouse_wc_event_extra_price_meta_key()
{
    return '_eh_event_extra_price';
}

/**
 * Meta: máximo de participantes extra no seletor (1–50).
 */
function EventsHouse_wc_event_max_extras_meta_key()
{
    return '_eh_event_max_extras';
}

/**
 * Meta: data do evento (YYYY-MM-DD).
 */
function EventsHouse_wc_event_date_meta_key()
{
    return '_eh_event_date';
}

/**
 * Meta: hora do evento (HH:MM).
 */
function EventsHouse_wc_event_time_meta_key()
{
    return '_eh_event_time';
}

/**
 * Regista tipo no seletor do admin.
 *
 * @param array<string, string> $types Tipos.
 * @return array<string, string>
 */
function EventsHouse_wc_event_product_type_selector($types)
{
    $types['event'] = __('Evento (inscrição)', 'EventsHouse');
    return $types;
}
add_filter('product_type_selector', 'EventsHouse_wc_event_product_type_selector');

/**
 * Classe PHP do produto.
 *
 * @param string $classname    Classe.
 * @param string $product_type Tipo.
 * @return string
 */
function EventsHouse_wc_event_product_class($classname, $product_type)
{
    if ($product_type === 'event') {
        return 'WC_Product_Event';
    }
    return $classname;
}
add_filter('woocommerce_product_class', 'EventsHouse_wc_event_product_class', 10, 2);

/**
 * Mesmo fluxo de «adicionar ao carrinho» que o simples.
 */
function EventsHouse_wc_event_register_add_to_cart_handler()
{
    add_action('woocommerce_event_add_to_cart', 'EventsHouse_wc_event_add_to_cart_template');
}
add_action('init', 'EventsHouse_wc_event_register_add_to_cart_handler', 16);

/**
 * Template do botão e formulário na ficha do evento.
 */
function EventsHouse_wc_event_add_to_cart_template()
{
    global $product;
    if (!$product instanceof WC_Product || $product->get_type() !== 'event') {
        return;
    }
    wc_get_template('single-product/add-to-cart/event.php', array('product' => $product));
}

/**
 * Eventos com preço definido (ou zero) continuam compráveis se publicados.
 *
 * @param bool       $purchasable Comprável.
 * @param WC_Product $product     Produto.
 * @return bool
 */
function EventsHouse_wc_event_is_purchasable($purchasable, $product)
{
    if (EventsHouse_wc_is_event_product($product)) {
        return $product->get_status() === 'publish';
    }
    return $purchasable;
}
add_filter('woocommerce_is_purchasable', 'EventsHouse_wc_event_is_purchasable', 10, 2);

/**
 * @param string     $text    Texto do botão.
 * @param WC_Product $product Produto.
 * @return string
 */
function EventsHouse_wc_event_add_to_cart_button_text($text, $product)
{
    if (EventsHouse_wc_is_event_product($product)) {
        return EventsHouse_get_event_ui_strings()['add_to_cart_button'];
    }
    return $text;
}
add_filter('woocommerce_product_single_add_to_cart_text', 'EventsHouse_wc_event_add_to_cart_button_text', 10, 2);

/**
 * Texto do botão no template add-to-cart/event.php apenas.
 *
 * @param string     $text    Texto.
 * @param WC_Product $product Produto.
 * @return string
 */
function EventsHouse_wc_event_add_to_cart_text_filter($text, $product)
{
    return EventsHouse_wc_is_event_product($product) ? EventsHouse_get_event_ui_strings()['add_to_cart_button'] : $text;
}
add_filter('woocommerce_event_add_to_cart_text', 'EventsHouse_wc_event_add_to_cart_text_filter', 10, 2);

/**
 * Abas de dados do produto: Geral + aba dedicada «Dados do evento».
 *
 * @param array<string, array<string, mixed>> $tabs Tabs.
 * @return array<string, array<string, mixed>>
 */
function EventsHouse_wc_event_product_data_tabs($tabs)
{
    $show_on_event = array('general', 'inventory', 'linked_product');
    foreach ($show_on_event as $id) {
        if (isset($tabs[$id]['class']) && is_array($tabs[$id]['class'])) {
            $tabs[$id]['class'][] = 'show_if_event';
        }
    }

    $tabs['eh_event'] = array(
        'label'    => __('Dados do evento', 'EventsHouse'),
        'target'   => 'eh_event_product_data',
        'class'    => array('show_if_event'),
        'priority' => 4,
    );

    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'EventsHouse_wc_event_product_data_tabs', 12);

/**
 * Uma unidade por linha (uma inscrição = uma linha).
 *
 * @param bool              $sold    Individually.
 * @param WC_Product|null $product Produto.
 * @return bool
 */
function EventsHouse_wc_event_sold_individually($sold, $product)
{
    if (EventsHouse_wc_is_event_product($product)) {
        return true;
    }
    return $sold;
}
add_filter('woocommerce_is_sold_individually', 'EventsHouse_wc_event_sold_individually', 10, 2);

/**
 * Participantes extra estruturados a partir do POST.
 *
 * @param int $max Limite.
 * @return array<int, array{type: string, name: string, dob: string}>
 */
function EventsHouse_wc_event_parse_extras_from_post($max = 50)
{
    $extras = array();
    if (!isset($_POST['eh_evt_extras']) || !is_array($_POST['eh_evt_extras'])) {
        return $extras;
    }
    foreach ($_POST['eh_evt_extras'] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $type = (isset($row['type']) && $row['type'] === 'adult') ? 'adult' : 'child';
        $name = sanitize_text_field(wp_unslash($row['name'] ?? ''));
        $dob  = sanitize_text_field(wp_unslash($row['dob'] ?? ''));
        if ($name === '') {
            continue;
        }
        if ($type === 'adult') {
            $dob = '';
        }
        $extras[] = array(
            'type' => $type,
            'name' => $name,
            'dob'  => $dob,
        );
        if (count($extras) >= $max) {
            break;
        }
    }
    return $extras;
}

/**
 * Texto multilinha dos participantes extra (carrinho / encomenda).
 *
 * @param array<int, array{type: string, name: string, dob: string}> $extras Extras.
 * @return string
 */
function EventsHouse_wc_event_format_extras_for_display(array $extras)
{
    if ($extras === array()) {
        return '';
    }
    $lines = array();
    $i     = 1;
    foreach ($extras as $ex) {
        $type_label = ($ex['type'] ?? '') === 'adult'
            ? __('Adulto', 'EventsHouse')
            : __('Criança', 'EventsHouse');
        $line = $i . '. ' . $type_label . ': ' . ($ex['name'] ?? '');
        if (($ex['type'] ?? '') !== 'adult' && !empty($ex['dob'])) {
            $line .= ' (' . $ex['dob'] . ')';
        }
        $lines[] = $line;
        ++$i;
    }
    return implode("\n", $lines);
}

/**
 * Lê campos POST de inscrição.
 *
 * @param WC_Product|null $product Para limitar extras.
 * @return array<string, mixed>|null Null se falha básica.
 */
/**
 * Quantidades de bilhetes do POST (modo rápido).
 *
 * @return array{adult_extra:int,child_extra:int,child_free:int}
 */
function EventsHouse_wc_event_parse_ticket_qty_from_post()
{
    return array(
        'adult_extra'  => isset($_POST['eh_evt_qty_adult_extra']) ? max(0, absint($_POST['eh_evt_qty_adult_extra'])) : 0,
        'child_extra'  => isset($_POST['eh_evt_qty_child_extra']) ? max(0, absint($_POST['eh_evt_qty_child_extra'])) : 0,
        'child_free'   => isset($_POST['eh_evt_qty_child_free']) ? max(0, absint($_POST['eh_evt_qty_child_free'])) : 0,
    );
}

/**
 * @param WC_Product|null $product Produto.
 * @return bool
 */
function EventsHouse_wc_event_is_quick_checkout_request($product = null)
{
    if (!empty($_POST['eh_evt_quick_checkout'])) {
        return true;
    }
    return !EventsHouse_event_registration_form_enabled();
}

function EventsHouse_wc_event_get_registration_from_post($product = null)
{
    $config = $product instanceof WC_Product ? EventsHouse_event_get_config($product) : EventsHouse_event_default_config();

    if (EventsHouse_wc_event_is_quick_checkout_request($product)) {
        $qty = EventsHouse_wc_event_parse_ticket_qty_from_post();
        $reg = array(
            'eh_evt_ticket_qty'       => $qty,
            'eh_evt_tickets_display'  => EventsHouse_event_format_tickets_display($qty, $config),
            'eh_evt_extra_count'      => '0',
            'eh_evt_extras'           => array(),
            'eh_evt_extras_display'   => '',
            'eh_evt_extras_fee'       => $product instanceof WC_Product
                ? (string) EventsHouse_event_calculate_tickets_fee($qty, $config, $product)
                : '0',
            'eh_evt_child_name'       => '',
            'eh_evt_child_dob'        => '',
            'eh_evt_adult_name'       => '',
            'eh_evt_email'            => '',
            'eh_evt_phone'            => '',
            'eh_evt_photo_consent'    => '',
            'eh_evt_base_participants' => array(),
            'eh_evt_base_display'     => '',
            'eh_evt_custom'           => array(),
            'eh_evt_custom_display'   => '',
        );
        if ($product instanceof WC_Product && $product->get_type() === 'event') {
            $reg['eh_evt_event_date']     = EventsHouse_wc_event_get_event_date($product);
            $reg['eh_evt_event_time']     = EventsHouse_wc_event_get_event_time($product);
            $reg['eh_evt_event_datetime'] = EventsHouse_wc_event_format_event_datetime($product);
            $reg['eh_evt_event_location'] = EventsHouse_event_get_location_from_config($config);
        }
        return $reg;
    }

    $custom = EventsHouse_event_parse_custom_from_post();

    $email = isset($custom['eh_evt_email']) ? sanitize_email($custom['eh_evt_email']) : '';
    $phone = isset($custom['eh_evt_phone']) ? sanitize_text_field($custom['eh_evt_phone']) : '';
    $photo = isset($custom['eh_evt_photo_consent']) ? sanitize_text_field($custom['eh_evt_photo_consent']) : '';
    $photo = ($photo === 'yes' || $photo === 'no') ? $photo : '';

    $base_adults   = EventsHouse_wc_event_parse_base_adults_from_post();
    $base_children = EventsHouse_wc_event_parse_base_children_from_post();
    $base_all      = array_merge($base_adults, $base_children);

    $max = 50;
    if ($product instanceof WC_Product && $product->get_type() === 'event') {
        $max = EventsHouse_wc_event_get_max_extras($product);
    }
    $extra_count = isset($_POST['eh_evt_extra_count']) ? min($max, max(0, absint($_POST['eh_evt_extra_count']))) : 0;
    $extras      = EventsHouse_wc_event_parse_extras_from_post($max);

    $legacy_child = $base_children[0]['name'] ?? '';
    $legacy_dob   = $base_children[0]['dob'] ?? '';
    $legacy_adult = $base_adults[0]['name'] ?? '';

    $reg = array(
        'eh_evt_child_name'         => $legacy_child,
        'eh_evt_child_dob'          => $legacy_dob,
        'eh_evt_adult_name'         => $legacy_adult,
        'eh_evt_email'              => $email,
        'eh_evt_phone'              => $phone,
        'eh_evt_photo_consent'      => $photo,
        'eh_evt_base_participants'  => $base_all,
        'eh_evt_base_display'       => EventsHouse_wc_event_format_participants_for_display($base_all),
        'eh_evt_custom'             => $custom,
        'eh_evt_custom_display'     => EventsHouse_event_format_custom_for_display($config, $custom),
        'eh_evt_extra_count'        => (string) $extra_count,
        'eh_evt_extras'             => $extras,
        'eh_evt_extras_display'     => EventsHouse_wc_event_format_extras_for_display($extras),
        'eh_evt_extras_fee'         => $product instanceof WC_Product ? (string) EventsHouse_event_calculate_extras_total($extras, $product) : '0',
    );

    if ($product instanceof WC_Product && $product->get_type() === 'event') {
        $reg['eh_evt_event_date']      = EventsHouse_wc_event_get_event_date($product);
        $reg['eh_evt_event_time']      = EventsHouse_wc_event_get_event_time($product);
        $reg['eh_evt_event_datetime']  = EventsHouse_wc_event_format_event_datetime($product);
        $reg['eh_evt_event_location']  = EventsHouse_event_get_location_from_config($config);
    }

    return $reg;
}

/**
 * @param array<string, mixed>     $config Config.
 * @param array<string, string>    $custom Valores.
 * @return string
 */
function EventsHouse_event_format_custom_for_display(array $config, array $custom)
{
    $labels = EventsHouse_event_custom_labels_map($config);
    $values = EventsHouse_event_collect_field_values_for_conditions($config);
    $lines  = array();
    foreach ($custom as $id => $val) {
        if (trim((string) $val) === '') {
            continue;
        }
        $field_def = null;
        foreach ($config['fields'] ?? array() as $f) {
            if (is_array($f) && ($f['id'] ?? '') === $id) {
                $field_def = $f;
                break;
            }
        }
        if ($field_def && !EventsHouse_event_field_is_visible($field_def, $values)) {
            continue;
        }
        $label = $labels[$id] ?? $id;
        if ($id === 'eh_evt_photo_consent') {
            $val = ($val === 'yes') ? __('Sim', 'EventsHouse') : __('Não', 'EventsHouse');
        }
        $lines[] = $label . ': ' . $val;
    }
    return implode("\n", $lines);
}

/**
 * Valida inscrição.
 *
 * @param array $reg Dados.
 * @return true|WP_Error
 */
function EventsHouse_wc_event_validate_registration(array $reg, $product = null)
{
    if ($product === null) {
        global $product;
    }
    $config = EventsHouse_event_get_config($product);

    if (EventsHouse_wc_event_is_quick_checkout_request($product)) {
        $qty = isset($reg['eh_evt_ticket_qty']) && is_array($reg['eh_evt_ticket_qty']) ? $reg['eh_evt_ticket_qty'] : array();
        $sum = absint($qty['adult_extra'] ?? 0) + absint($qty['child_extra'] ?? 0) + absint($qty['child_free'] ?? 0);
        if (!EventsHouse_event_has_base_price($config) && $sum < 1) {
            return new WP_Error('eh_evt', __('Seleccione pelo menos um bilhete.', 'EventsHouse'));
        }
        return true;
    }

    $base   = $config['base'] ?? array();
    $need_adults = min(5, max(0, absint($base['adults'] ?? 0)));
    $need_children = min(5, max(0, absint($base['children'] ?? 0)));

    $base_parts = isset($reg['eh_evt_base_participants']) && is_array($reg['eh_evt_base_participants'])
        ? $reg['eh_evt_base_participants'] : array();
    $adults = array_filter($base_parts, static function ($p) {
        return is_array($p) && ($p['type'] ?? '') === 'adult';
    });
    $children = array_filter($base_parts, static function ($p) {
        return is_array($p) && ($p['type'] ?? '') === 'child';
    });

    if (count($adults) < $need_adults) {
        return new WP_Error('eh_evt', __('Preencha o nome de todos os adultos incluídos na inscrição.', 'EventsHouse'));
    }
    if (count($children) < $need_children) {
        return new WP_Error('eh_evt', __('Preencha os dados de todas as crianças incluídas na inscrição.', 'EventsHouse'));
    }
    foreach ($children as $ch) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($ch['dob'] ?? ''))) {
            return new WP_Error('eh_evt', __('Indique uma data de nascimento válida para cada criança.', 'EventsHouse'));
        }
    }

    $custom_check = EventsHouse_event_validate_custom_fields($config, $reg);
    if (is_wp_error($custom_check)) {
        return $custom_check;
    }

    $n_extra = absint($reg['eh_evt_extra_count'] ?? 0);
    if ($n_extra > 0) {
        $extras = isset($reg['eh_evt_extras']) && is_array($reg['eh_evt_extras']) ? $reg['eh_evt_extras'] : array();
        if (count($extras) !== $n_extra) {
            return new WP_Error('eh_evt', __('Preencha os dados de todos os participantes extra.', 'EventsHouse'));
        }
        foreach ($extras as $ex) {
            if (empty($ex['name'])) {
                return new WP_Error('eh_evt', __('Indique o nome de cada participante extra.', 'EventsHouse'));
            }
            if (($ex['type'] ?? 'child') !== 'adult' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($ex['dob'] ?? ''))) {
                return new WP_Error('eh_evt', __('Indique a data de nascimento de cada criança extra.', 'EventsHouse'));
            }
        }
    }

    return true;
}

/**
 * @param bool $passed     OK.
 * @param int  $product_id ID.
 * @param int  $qty        Quantidade.
 * @return bool
 */
function EventsHouse_wc_event_add_to_cart_validation($passed, $product_id, $qty)
{
    if (!EventsHouse_wc_is_event_product($product_id)) {
        return $passed;
    }
    $product = wc_get_product($product_id);

    $reg = EventsHouse_wc_event_get_registration_from_post($product);
    if ($reg === null) {
        wc_add_notice(__('Não foi possível validar o formulário.', 'EventsHouse'), 'error');
        return false;
    }

    $check = EventsHouse_wc_event_validate_registration($reg, $product);
    if (is_wp_error($check)) {
        wc_add_notice($check->get_error_message(), 'error');
        return false;
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'EventsHouse_wc_event_add_to_cart_validation', 10, 3);

/**
 * Anexa dados ao item do carrinho.
 *
 * @param array $cart_item_data Meta.
 * @param int   $product_id     ID.
 * @param int   $variation_id   Variação.
 * @return array
 */
function EventsHouse_wc_event_add_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    if (!EventsHouse_wc_is_event_product($product_id)) {
        return $cart_item_data;
    }
    $product = wc_get_product($product_id);

    $reg = EventsHouse_wc_event_get_registration_from_post($product);
    if ($reg === null) {
        return $cart_item_data;
    }
    $check = EventsHouse_wc_event_validate_registration($reg, $product);
    if (is_wp_error($check)) {
        return $cart_item_data;
    }

    $cart_item_data['eh_event_registration'] = $reg;
    $cart_item_data['eh_evt_unique']         = function_exists('wp_unique_id')
        ? wp_unique_id('evt_')
        : ('evt_' . uniqid('', true));

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'EventsHouse_wc_event_add_cart_item_data', 10, 3);

/**
 * Restaura dados da sessão.
 *
 * @param array $cart_item Item.
 * @param array $values    Valores em sessão.
 * @return array
 */
function EventsHouse_wc_event_get_cart_item_from_session($cart_item, $values)
{
    if (isset($values['eh_event_registration'])) {
        $cart_item['eh_event_registration'] = $values['eh_event_registration'];
    }
    if (isset($values['eh_evt_unique'])) {
        $cart_item['eh_evt_unique'] = $values['eh_evt_unique'];
    }
    return $cart_item;
}
add_filter('woocommerce_get_cart_item_from_session', 'EventsHouse_wc_event_get_cart_item_from_session', 10, 2);

/**
 * Sem preço base: linha do carrinho a 0 € (taxas só nos extras).
 *
 * @param array  $cart_item Item.
 * @param string $cart_key  Chave.
 * @return array
 */
function EventsHouse_wc_event_add_cart_item_zero_base($cart_item, $cart_key)
{
    $product = isset($cart_item['data']) ? $cart_item['data'] : null;
    if (!$product instanceof WC_Product || $product->get_type() !== 'event') {
        return $cart_item;
    }
    $config = EventsHouse_event_get_config($product);
    if (!EventsHouse_event_has_base_price($config)) {
        $cart_item['data']->set_price(0);
    }
    return $cart_item;
}
add_filter('woocommerce_add_cart_item', 'EventsHouse_wc_event_add_cart_item_zero_base', 20, 2);
add_filter('woocommerce_get_cart_item_from_session', 'EventsHouse_wc_event_add_cart_item_zero_base', 20, 2);

/**
 * Etiquetas no carrinho e checkout.
 *
 * @param array $item_data Linhas.
 * @param array $cart_item Item.
 * @return array
 */
function EventsHouse_wc_event_get_item_data($item_data, $cart_item)
{
    if (empty($cart_item['eh_event_registration']) || !is_array($cart_item['eh_event_registration'])) {
        return $item_data;
    }
    $reg = $cart_item['eh_event_registration'];
    $map = EventsHouse_wc_event_registration_labels();
    foreach ($map as $key => $label) {
        if (!isset($reg[$key])) {
            continue;
        }
        if (in_array($key, array('eh_evt_extras', 'eh_evt_event_date', 'eh_evt_event_time', 'eh_evt_base_participants', 'eh_evt_custom', 'eh_evt_extras_fee', 'eh_evt_ticket_qty'), true)) {
            continue;
        }
        $val = $reg[$key];
        if (in_array($key, array('eh_evt_child_name', 'eh_evt_child_dob', 'eh_evt_adult_name', 'eh_evt_email', 'eh_evt_phone', 'eh_evt_photo_consent'), true) && trim((string) $val) === '') {
            continue;
        }
        if ($key === 'eh_evt_extra_count' && (int) $val === 0) {
            continue;
        }
        if (in_array($key, array('eh_evt_extras_display', 'eh_evt_base_display', 'eh_evt_custom_display', 'eh_evt_tickets_display'), true) && trim((string) $val) === '') {
            continue;
        }
        if ($key === 'eh_evt_extras_display') {
            $legacy = trim((string) ($reg['eh_evt_extra_names'] ?? ''));
            if (trim((string) $val) === '' && $legacy !== '') {
                $val = $legacy;
            }
        }
        if ($key === 'eh_evt_photo_consent') {
            $val = ($val === 'yes') ? __('Sim', 'EventsHouse') : __('Não', 'EventsHouse');
        }
        if ($val === '' || (is_array($val) && $val === array())) {
            continue;
        }
        $item_data[] = array(
            'name'  => $label,
            'value' => is_string($val) ? $val : (string) $val,
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'EventsHouse_wc_event_get_item_data', 10, 2);

/**
 * @return array<string, string>
 */
function EventsHouse_wc_event_registration_labels()
{
    return array(
        'eh_evt_event_datetime'  => __('Data e hora do evento', 'EventsHouse'),
        'eh_evt_event_location'  => __('Local do evento', 'EventsHouse'),
        'eh_evt_base_display'    => __('Participantes incluídos', 'EventsHouse'),
        'eh_evt_tickets_display' => __('Bilhetes seleccionados', 'EventsHouse'),
        'eh_evt_custom_display'  => __('Informação adicional', 'EventsHouse'),
        'eh_evt_extra_count'     => __('N.º de participantes extra', 'EventsHouse'),
        'eh_evt_extras_display'  => __('Participantes extra', 'EventsHouse'),
        'eh_evt_child_name'      => __('Nome da criança', 'EventsHouse'),
        'eh_evt_child_dob'       => __('Data de nascimento', 'EventsHouse'),
        'eh_evt_adult_name'      => __('Nome do adulto', 'EventsHouse'),
        'eh_evt_email'           => __('Email', 'EventsHouse'),
        'eh_evt_phone'           => __('Telefone', 'EventsHouse'),
        'eh_evt_photo_consent'   => __('Partilha de imagens (redes/newsletter)', 'EventsHouse'),
    );
}

/**
 * Taxa: preço extra × quantidade de participantes extra.
 */
function EventsHouse_wc_event_cart_calculate_fees()
{
    if (!function_exists('WC') || !WC()->cart || (is_admin() && !defined('DOING_AJAX'))) {
        return;
    }

    $fee_total   = 0.0;
    $fee_taxable = false;
    $fee_class   = '';
    foreach (WC()->cart->get_cart() as $item) {
        $product = isset($item['data']) ? $item['data'] : null;
        if (!$product instanceof WC_Product || $product->get_type() !== 'event') {
            continue;
        }
        $reg = isset($item['eh_event_registration']) ? $item['eh_event_registration'] : array();
        if (!is_array($reg)) {
            continue;
        }
        $line_fee = 0.0;
        if (!empty($reg['eh_evt_ticket_qty']) && is_array($reg['eh_evt_ticket_qty'])) {
            $config = EventsHouse_event_get_config($product);
            $line_fee = EventsHouse_event_calculate_tickets_fee($reg['eh_evt_ticket_qty'], $config, $product);
        } else {
            $extras = !empty($reg['eh_evt_extras']) && is_array($reg['eh_evt_extras']) ? $reg['eh_evt_extras'] : array();
            if ($extras !== array()) {
                $line_fee = EventsHouse_event_calculate_extras_total($extras, $product);
            }
        }
        if ($line_fee <= 0) {
            continue;
        }
        if (EventsHouse_wc_event_fee_is_taxable($product)) {
            $fee_taxable = true;
            $fee_class   = $product->get_tax_class();
        }
        $qty = isset($item['quantity']) ? (int) $item['quantity'] : 1;
        $fee_total += $line_fee * max(1, $qty);
    }

    if ($fee_total > 0) {
        WC()->cart->add_fee(__('Bilhetes / participantes extra', 'EventsHouse'), $fee_total, $fee_taxable, $fee_class);
    }
}
add_action('woocommerce_cart_calculate_fees', 'EventsHouse_wc_event_cart_calculate_fees', 20);

/**
 * Inscrição rápida: redireccionar para o carrinho.
 *
 * @param string $url URL.
 * @return string
 */
function EventsHouse_wc_event_add_to_cart_redirect($url)
{
    if (!empty($_POST['eh_evt_quick_checkout']) && function_exists('wc_get_cart_url')) {
        return wc_get_cart_url();
    }
    return $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'EventsHouse_wc_event_add_to_cart_redirect', 20);

/**
 * O carrinho contém algum produto tipo evento?
 *
 * @return bool
 */
function EventsHouse_cart_contains_event_product()
{
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }
    foreach (WC()->cart->get_cart() as $item) {
        $product = isset($item['data']) ? $item['data'] : null;
        if (EventsHouse_wc_is_event_product($product)) {
            return true;
        }
    }
    return false;
}

/**
 * Extrai email e telefone das inscrições de evento no carrinho.
 *
 * @return array{email:string,phone:string}
 */
function EventsHouse_wc_event_collect_cart_contact()
{
    $out = array('email' => '', 'phone' => '');
    if (!function_exists('WC') || !WC()->cart) {
        return $out;
    }
    foreach (WC()->cart->get_cart() as $item) {
        $reg = isset($item['eh_event_registration']) && is_array($item['eh_event_registration'])
            ? $item['eh_event_registration']
            : array();
        if ($reg === array()) {
            continue;
        }
        if ($out['phone'] === '' && !empty($reg['eh_evt_phone'])) {
            $out['phone'] = sanitize_text_field((string) $reg['eh_evt_phone']);
        }
        if ($out['email'] === '' && !empty($reg['eh_evt_email'])) {
            $out['email'] = sanitize_email((string) $reg['eh_evt_email']);
        }
        $custom = isset($reg['eh_evt_custom']) && is_array($reg['eh_evt_custom']) ? $reg['eh_evt_custom'] : array();
        if ($out['phone'] === '' && !empty($custom['eh_evt_phone'])) {
            $out['phone'] = sanitize_text_field((string) $custom['eh_evt_phone']);
        }
        if ($out['email'] === '' && !empty($custom['eh_evt_email'])) {
            $out['email'] = sanitize_email((string) $custom['eh_evt_email']);
        }
    }
    return $out;
}

/**
 * Procura telefone na encomenda (facturação ou meta das linhas).
 *
 * @param WC_Order $order Encomenda.
 * @return string
 */
function EventsHouse_wc_event_find_phone_in_order($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }
    $phone = trim((string) $order->get_billing_phone());
    if ($phone !== '') {
        return $phone;
    }

    $labels = EventsHouse_wc_event_registration_labels();
    $phone_label = $labels['eh_evt_phone'] ?? __('Telefone', 'EventsHouse');

    foreach ($order->get_items() as $item) {
        if (!$item instanceof WC_Order_Item_Product) {
            continue;
        }
        $meta_phone = trim((string) $item->get_meta($phone_label, true));
        if ($meta_phone !== '') {
            return $meta_phone;
        }
        $meta_phone = trim((string) $item->get_meta('eh_evt_phone', true));
        if ($meta_phone !== '') {
            return $meta_phone;
        }
    }

    return trim((string) $order->get_meta('_eh_evt_billing_phone', true));
}

/**
 * Garante billing_phone (e email se vazio) na encomenda.
 *
 * @param WC_Order $order Encomenda.
 * @param string   $phone Telefone.
 * @param string   $email Email (opcional).
 */
function EventsHouse_wc_event_apply_billing_contact_to_order($order, $phone, $email = '')
{
    if (!$order instanceof WC_Order) {
        return;
    }
    $phone = trim((string) $phone);
    $email = trim((string) $email);
    $changed = false;

    if ($phone !== '' && trim((string) $order->get_billing_phone()) === '') {
        $order->set_billing_phone($phone);
        $order->update_meta_data('_eh_evt_billing_phone', $phone);
        $changed = true;
    }
    if ($email !== '' && trim((string) $order->get_billing_email()) === '') {
        $order->set_billing_email($email);
        $changed = true;
    }
    if ($changed) {
        $order->save();
    }
}

/**
 * Checkout clássico: copia contacto do carrinho (inscrição evento) para a facturação.
 *
 * @param WC_Order $order Encomenda.
 * @param array    $data  Dados do checkout.
 */
function EventsHouse_wc_event_checkout_sync_billing_contact($order, $data)
{
    if (!$order instanceof WC_Order) {
        return;
    }

    $contact = EventsHouse_wc_event_collect_cart_contact();
    $phone   = trim((string) ($data['billing_phone'] ?? ''));
    if ($phone === '' && !empty($_POST['billing_phone'])) {
        $phone = sanitize_text_field(wp_unslash($_POST['billing_phone']));
    }
    if ($phone === '' && !empty($data['phone'])) {
        $phone = sanitize_text_field((string) $data['phone']);
    }
    if ($phone === '' && $contact['phone'] !== '') {
        $phone = $contact['phone'];
    }

    $email = trim((string) ($data['billing_email'] ?? ''));
    if ($email === '' && $contact['email'] !== '') {
        $email = $contact['email'];
    }

    EventsHouse_wc_event_apply_billing_contact_to_order($order, $phone, $email);
}
add_action('woocommerce_checkout_create_order', 'EventsHouse_wc_event_checkout_sync_billing_contact', 25, 2);

/**
 * Após criar a encomenda: fallback a partir das linhas (meta «Telefone»).
 *
 * @param WC_Order $order Encomenda.
 */
function EventsHouse_wc_event_checkout_order_created_sync_phone($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if (trim((string) $order->get_billing_phone()) !== '') {
        return;
    }
    $phone = EventsHouse_wc_event_find_phone_in_order($order);
    if ($phone !== '') {
        EventsHouse_wc_event_apply_billing_contact_to_order($order, $phone);
    }
}
add_action('woocommerce_checkout_order_created', 'EventsHouse_wc_event_checkout_order_created_sync_phone', 20, 1);

/**
 * Checkout em blocos (Store API).
 *
 * @param WC_Order        $order   Encomenda.
 * @param WP_REST_Request $request Pedido REST.
 */
function EventsHouse_wc_event_store_api_sync_billing_contact($order, $request)
{
    if (!$order instanceof WC_Order) {
        return;
    }

    $phone = '';
    $email = '';
    if ($request instanceof WP_REST_Request) {
        $billing = $request->get_param('billing_address');
        if (is_array($billing)) {
            $phone = sanitize_text_field((string) ($billing['phone'] ?? ''));
            $email = sanitize_email((string) ($billing['email'] ?? ''));
        }
    }

    $contact = EventsHouse_wc_event_collect_cart_contact();
    if ($phone === '' && $contact['phone'] !== '') {
        $phone = $contact['phone'];
    }
    if ($email === '' && $contact['email'] !== '') {
        $email = $contact['email'];
    }

    EventsHouse_wc_event_apply_billing_contact_to_order($order, $phone, $email);
}
add_action('woocommerce_store_api_checkout_update_order_from_request', 'EventsHouse_wc_event_store_api_sync_billing_contact', 25, 2);

/**
 * Admin: mostra telefone das linhas se a facturação estiver vazia (encomendas antigas).
 *
 * @param WC_Order $order Encomenda.
 */
function EventsHouse_wc_event_admin_show_order_phone_fallback($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if (trim((string) $order->get_billing_phone()) !== '') {
        return;
    }
    $phone = EventsHouse_wc_event_find_phone_in_order($order);
    if ($phone === '') {
        return;
    }
    echo '<p class="form-field form-field-wide eh-order-phone-fallback">';
    echo '<strong>' . esc_html__('Telefone', 'EventsHouse') . ':</strong> ';
    echo esc_html($phone);
    echo ' <span class="description">(' . esc_html__('recuperado da inscrição do evento', 'EventsHouse') . ')</span>';
    echo '</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'EventsHouse_wc_event_admin_show_order_phone_fallback', 15, 1);

/**
 * Grava meta na linha da encomenda.
 *
 * @param WC_Order_Item_Product $item     Item.
 * @param string                $cart_key Chave no carrinho.
 * @param array                 $values   Valores.
 * @param WC_Order              $order    Encomenda.
 */
function EventsHouse_wc_event_checkout_create_order_line_item($item, $cart_key, $values, $order)
{
    if (empty($values['eh_event_registration']) || !is_array($values['eh_event_registration'])) {
        return;
    }
    $labels = EventsHouse_wc_event_registration_labels();
    $reg = $values['eh_event_registration'];
    foreach ($reg as $key => $val) {
        if (in_array($key, array('eh_evt_extras', 'eh_evt_event_date', 'eh_evt_event_time', 'eh_evt_base_participants', 'eh_evt_custom', 'eh_evt_extras_fee', 'eh_evt_ticket_qty', 'eh_evt_child_name', 'eh_evt_child_dob', 'eh_evt_adult_name'), true)) {
            continue;
        }
        $label = isset($labels[$key]) ? $labels[$key] : $key;
        if ($key === 'eh_evt_extra_count' && (string) $val === '0') {
            continue;
        }
        if ($key === 'eh_evt_extras_display' && trim((string) $val) === '') {
            continue;
        }
        if ($key === 'eh_evt_base_display' && trim((string) $val) === '') {
            continue;
        }
        if ($key === 'eh_evt_custom_display' && trim((string) $val) === '') {
            continue;
        }
        if ($key === 'eh_evt_photo_consent') {
            $val = ($val === 'yes') ? __('Sim', 'EventsHouse') : __('Não', 'EventsHouse');
        }
        if ($val === '' || $val === null || (is_array($val) && $val === array())) {
            continue;
        }
        if (in_array($key, array('eh_evt_extras_display', 'eh_evt_base_display', 'eh_evt_custom_display', 'eh_evt_tickets_display'), true)) {
            $display = trim(str_replace(array("\r\n", "\r"), "\n", (string) $val));
        } else {
            $display = is_string($val) ? $val : (string) $val;
        }
        $item->add_meta_data($label, $display, true);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'EventsHouse_wc_event_checkout_create_order_line_item', 10, 4);

/**
 * Texto junto ao preço HTML na listagem / onde o Woo mostrar o preço.
 *
 * @param string     $price_html HTML.
 * @param WC_Product $product    Produto.
 * @return string
 */
function EventsHouse_wc_event_price_html_suffix($price_html, $product)
{
    if (!EventsHouse_wc_is_event_product($product)) {
        return $price_html;
    }
    $extra = EventsHouse_wc_event_get_extra_price($product);
    if ($extra <= 0) {
        return $price_html;
    }
    $suffix = '<span class="eh-event-extra-note" style="display:block;font-size:0.85em;margin-top:6px;opacity:0.9;">';
    $suffix .= esc_html(
        sprintf(
            EventsHouse_get_event_ui_strings()['catalog_extra_price_format'],
            wc_format_localized_decimal($extra)
        )
    );
    $suffix .= '</span>';
    return $price_html . $suffix;
}
add_filter('woocommerce_get_price_html', 'EventsHouse_wc_event_price_html_suffix', 12, 2);

/**
 * Script do stepper e total na ficha do produto evento.
 */
function EventsHouse_wc_event_enqueue_single_assets()
{
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }
    $product = wc_get_product($post_id);
    if (!$product || !EventsHouse_wc_is_event_product($product)) {
        return;
    }

    $config = EventsHouse_event_get_config($product);
    $u = EventsHouse_get_event_ui_strings();

    wp_enqueue_style(
        'eh-wc-event-single-page',
        EventsHouse_URI . '/assets/css/woo-event-single-page.css',
        array('EventsHouse-main'),
        EventsHouse_VERSION
    );
    wp_enqueue_style(
        'eh-wc-event-single',
        EventsHouse_URI . '/assets/css/wc-event-single.css',
        array('eh-wc-event-single-page'),
        EventsHouse_VERSION
    );
    wp_enqueue_script(
        'eh-wc-event-single',
        EventsHouse_URI . '/assets/js/wc-event-single.js',
        array(),
        EventsHouse_VERSION,
        true
    );
    wp_localize_script(
        'eh-wc-event-single',
        'ehWcEventSingle',
        array(
            'extra_child'    => $u['js_extra_child'],
            'extra_adult'    => $u['js_extra_adult'],
            'name'           => $u['js_participant_name'],
            'dob'            => $u['js_participant_dob'],
            'participant'    => $u['js_participant_heading'],
            'free_label'     => $u['js_extra_free'] ?? __('Gratuito', 'EventsHouse'),
            'err_no_selection' => $u['err_no_selection'] ?? __('Seleccione pelo menos um bilhete.', 'EventsHouse'),
            'event_date'     => EventsHouse_event_get_event_date($product),
            'adult_extra'    => EventsHouse_wc_event_get_adult_extra_price($product),
            'child_extra'    => EventsHouse_wc_event_get_child_extra_price($product),
            'free_max_age'   => absint($config['pricing']['child_free_max_age'] ?? 3),
        )
    );
}
add_action('wp_enqueue_scripts', 'EventsHouse_wc_event_enqueue_single_assets', 30);
