<?php
/**
 * Configuração modular por produto tipo «event» (schema JSON em post meta).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return string
 */
function EventsHouse_event_config_meta_key()
{
    return '_eh_event_config';
}

/**
 * Campos built-in do formulário.
 *
 * @return array<int, array<string, mixed>>
 */
function EventsHouse_event_builtin_fields()
{
    $ui = function_exists('EventsHouse_get_event_ui_strings') ? EventsHouse_get_event_ui_strings() : array();
    return array(
        array(
            'id'        => 'eh_evt_email',
            'builtin'   => true,
            'type'      => 'email',
            'label'     => $ui['label_email'] ?? __('Email', 'EventsHouse'),
            'required'  => true,
            'enabled'   => true,
            'show_when' => null,
        ),
        array(
            'id'        => 'eh_evt_phone',
            'builtin'   => true,
            'type'      => 'tel',
            'label'     => $ui['label_phone'] ?? __('Telefone', 'EventsHouse'),
            'required'  => true,
            'enabled'   => true,
            'show_when' => null,
        ),
        array(
            'id'        => 'eh_evt_photo_consent',
            'builtin'   => true,
            'type'      => 'radio',
            'label'     => $ui['label_photo_consent'] ?? __('Partilha de imagens', 'EventsHouse'),
            'hint'      => $ui['hint_photo_consent'] ?? '',
            'required'  => true,
            'enabled'   => true,
            'options'   => array(
                array('value' => 'yes', 'label' => $ui['label_yes'] ?? __('Sim', 'EventsHouse')),
                array('value' => 'no', 'label' => $ui['label_no'] ?? __('Não', 'EventsHouse')),
            ),
            'show_when' => null,
        ),
    );
}

/**
 * @return array<string, mixed>
 */
function EventsHouse_event_default_config()
{
    return array(
        'version' => 1,
        'event'   => array(
            'date'       => '',
            'day_label'  => '',
            'time_start' => '',
            'time_end'   => '',
            'location'   => '',
        ),
        'base'    => array(
            'adults'   => 1,
            'children' => 1,
        ),
        'pricing' => array(
            'has_base_price'     => true,
            'adult_extra'        => 7.0,
            'child_extra'        => 7.0,
            'child_free_max_age' => 3,
            'child_paid_age_min' => null,
            'child_paid_age_max' => null,
        ),
        'extras'  => array(
            'enabled' => true,
            'max'     => 10,
        ),
        'fields'  => EventsHouse_event_builtin_fields(),
    );
}

/**
 * @param mixed $raw Dados brutos.
 * @return array<string, mixed>
 */
function EventsHouse_event_sanitize_config($raw)
{
    $def = EventsHouse_event_default_config();
    if (!is_array($raw)) {
        return $def;
    }

    $event   = isset($raw['event']) && is_array($raw['event']) ? $raw['event'] : array();
    $base    = isset($raw['base']) && is_array($raw['base']) ? $raw['base'] : array();
    $pricing = isset($raw['pricing']) && is_array($raw['pricing']) ? $raw['pricing'] : array();
    $extras  = isset($raw['extras']) && is_array($raw['extras']) ? $raw['extras'] : array();
    $fields  = isset($raw['fields']) && is_array($raw['fields']) ? $raw['fields'] : array();

    $date = isset($event['date']) ? sanitize_text_field((string) $event['date']) : '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = '';
    }

    $time_start = isset($event['time_start']) ? sanitize_text_field((string) $event['time_start']) : '';
    $time_end   = isset($event['time_end']) ? sanitize_text_field((string) $event['time_end']) : '';
    if (!preg_match('/^\d{2}:\d{2}$/', $time_start)) {
        $time_start = '';
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $time_end)) {
        $time_end = '';
    }

    $free_age = min(18, isset($pricing['child_free_max_age']) ? absint($pricing['child_free_max_age']) : 3);

    $paid_min = isset($pricing['child_paid_age_min']) && $pricing['child_paid_age_min'] !== '' && $pricing['child_paid_age_min'] !== null
        ? absint($pricing['child_paid_age_min']) : null;
    $paid_max = isset($pricing['child_paid_age_max']) && $pricing['child_paid_age_max'] !== '' && $pricing['child_paid_age_max'] !== null
        ? absint($pricing['child_paid_age_max']) : null;

    $sanitized_fields = array();
    $seen_ids         = array();
    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }
        $id = isset($field['id']) ? sanitize_key((string) $field['id']) : '';
        if ($id === '' || isset($seen_ids[$id])) {
            continue;
        }
        $seen_ids[$id] = true;

        $type = isset($field['type']) ? sanitize_key((string) $field['type']) : 'text';
        $allowed = array('text', 'textarea', 'email', 'tel', 'date', 'select', 'radio', 'checkbox');
        if (!in_array($type, $allowed, true)) {
            $type = 'text';
        }

        $show_when = null;
        if (isset($field['show_when']) && is_array($field['show_when']) && !empty($field['show_when']['field'])) {
            $op = isset($field['show_when']['op']) ? sanitize_key((string) $field['show_when']['op']) : 'equals';
            if (!in_array($op, array('equals', 'not_equals', 'not_empty', 'empty'), true)) {
                $op = 'equals';
            }
            $show_when = array(
                'field' => sanitize_key((string) $field['show_when']['field']),
                'op'    => $op,
                'value' => isset($field['show_when']['value']) ? sanitize_text_field((string) $field['show_when']['value']) : '',
            );
        }

        $options = array();
        if (isset($field['options']) && is_array($field['options'])) {
            foreach ($field['options'] as $opt) {
                if (is_string($opt)) {
                    $label = sanitize_text_field($opt);
                    if ($label !== '') {
                        $options[] = array('value' => sanitize_title($label), 'label' => $label);
                    }
                } elseif (is_array($opt)) {
                    $val = isset($opt['value']) ? sanitize_key((string) $opt['value']) : '';
                    $lab = isset($opt['label']) ? sanitize_text_field((string) $opt['label']) : '';
                    if ($lab === '' && $val !== '') {
                        $lab = $val;
                    }
                    if ($val === '' && $lab !== '') {
                        $val = sanitize_title($lab);
                    }
                    if ($val !== '' || $lab !== '') {
                        $options[] = array('value' => $val, 'label' => $lab);
                    }
                }
            }
        }

        $sanitized_fields[] = array(
            'id'        => $id,
            'builtin'   => !empty($field['builtin']),
            'type'      => $type,
            'label'     => isset($field['label']) ? sanitize_text_field((string) $field['label']) : $id,
            'hint'      => isset($field['hint']) ? sanitize_text_field((string) $field['hint']) : '',
            'required'  => !empty($field['required']),
            'enabled'   => !isset($field['enabled']) || !empty($field['enabled']),
            'options'   => $options,
            'show_when' => $show_when,
        );
    }

    if ($sanitized_fields === array()) {
        $sanitized_fields = EventsHouse_event_builtin_fields();
    }

    return array(
        'version' => 1,
        'event'   => array(
            'date'       => $date,
            'day_label'  => isset($event['day_label']) ? sanitize_text_field((string) $event['day_label']) : '',
            'time_start' => $time_start,
            'time_end'   => $time_end,
            'location'   => isset($event['location']) ? sanitize_text_field((string) $event['location']) : '',
        ),
        'base'    => array(
            'adults'   => min(5, max(0, isset($base['adults']) ? absint($base['adults']) : 1)),
            'children' => min(5, max(0, isset($base['children']) ? absint($base['children']) : 1)),
        ),
        'pricing' => array(
            'has_base_price'     => !isset($pricing['has_base_price']) || !empty($pricing['has_base_price']),
            'adult_extra'        => max(0, (float) (isset($pricing['adult_extra']) ? $pricing['adult_extra'] : 7)),
            'child_extra'        => max(0, (float) (isset($pricing['child_extra']) ? $pricing['child_extra'] : 7)),
            'child_free_max_age' => $free_age,
            'child_paid_age_min' => $paid_min,
            'child_paid_age_max' => $paid_max,
        ),
        'extras'  => array(
            'enabled' => !isset($extras['enabled']) || !empty($extras['enabled']),
            'max'     => min(50, max(0, isset($extras['max']) ? absint($extras['max']) : 10)),
        ),
        'fields'  => $sanitized_fields,
    );
}

/**
 * @param int $product_id ID.
 * @return array<string, mixed>
 */
function EventsHouse_event_migrate_config($product_id)
{
    $config = EventsHouse_event_default_config();

    $date = get_post_meta($product_id, EventsHouse_wc_event_date_meta_key(), true);
    $time = get_post_meta($product_id, EventsHouse_wc_event_time_meta_key(), true);
    if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $config['event']['date'] = $date;
    }
    if (is_string($time) && preg_match('/^\d{2}:\d{2}$/', $time)) {
        $config['event']['time_start'] = $time;
    }

    $extra = get_post_meta($product_id, EventsHouse_wc_event_extra_price_meta_key(), true);
    $extra_f = is_numeric($extra) ? (float) $extra : 7.0;
    $config['pricing']['adult_extra'] = $extra_f;
    $config['pricing']['child_extra'] = $extra_f;

    $max = get_post_meta($product_id, EventsHouse_wc_event_max_extras_meta_key(), true);
    $config['extras']['max'] = $max === '' || $max === null ? 10 : min(50, max(0, absint($max)));

    update_post_meta($product_id, EventsHouse_event_config_meta_key(), wp_json_encode($config));

    return $config;
}

/**
 * @param WC_Product|int|null $product Produto.
 * @return array<string, mixed>
 */
function EventsHouse_event_get_config($product = null)
{
    if ($product === null) {
        global $product;
    } elseif (is_numeric($product)) {
        $product = wc_get_product((int) $product);
    }

    if (!$product instanceof WC_Product || $product->get_type() !== 'event') {
        return EventsHouse_event_default_config();
    }

    $raw = get_post_meta($product->get_id(), EventsHouse_event_config_meta_key(), true);
    if ($raw === '' || $raw === null || $raw === false) {
        return EventsHouse_event_migrate_config($product->get_id());
    }

    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return EventsHouse_event_migrate_config($product->get_id());
        }
        return EventsHouse_event_sanitize_config($decoded);
    }

    if (is_array($raw)) {
        return EventsHouse_event_sanitize_config($raw);
    }

    return EventsHouse_event_migrate_config($product->get_id());
}

/**
 * @param int                  $product_id ID.
 * @param array<string, mixed> $config     Config.
 */
function EventsHouse_event_sync_legacy_meta($product_id, array $config)
{
    $event = $config['event'] ?? array();
    update_post_meta($product_id, EventsHouse_wc_event_date_meta_key(), $event['date'] ?? '');
    update_post_meta($product_id, EventsHouse_wc_event_time_meta_key(), $event['time_start'] ?? '');

    $pricing = $config['pricing'] ?? array();
    update_post_meta($product_id, EventsHouse_wc_event_extra_price_meta_key(), (string) (float) ($pricing['child_extra'] ?? 7));

    $extras = $config['extras'] ?? array();
    update_post_meta($product_id, EventsHouse_wc_event_max_extras_meta_key(), (string) max(1, absint($extras['max'] ?? 10)));
}

/**
 * @param WC_Product|null $product Produto.
 * @return string
 */
function EventsHouse_event_get_event_date($product)
{
    $config = EventsHouse_event_get_config($product);
    $date   = $config['event']['date'] ?? '';
    return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '';
}

/**
 * @param array<string, mixed> $config Config.
 * @return string
 */
function EventsHouse_event_format_day_line(array $config)
{
    $event = $config['event'] ?? array();
    if (!empty($event['day_label'])) {
        return (string) $event['day_label'];
    }
    $date = $event['date'] ?? '';
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return '';
    }
    $ts = strtotime($date . ' 12:00:00');
    return $ts ? wp_date(get_option('date_format'), $ts) : $date;
}

/**
 * @param string $time HH:MM.
 * @return string
 */
function EventsHouse_event_format_hour_pt($time)
{
    if (!preg_match('/^(\d{2}):(\d{2})$/', $time, $m)) {
        return $time;
    }
    $h = (int) $m[1];
    $min = (int) $m[2];
    if ($min === 0) {
        return sprintf(__('%dh', 'EventsHouse'), $h);
    }
    return sprintf(__('%dh%02d', 'EventsHouse'), $h, $min);
}

/**
 * @param array<string, mixed> $config Config.
 * @return string
 */
function EventsHouse_event_format_time_range(array $config)
{
    $event = $config['event'] ?? array();
    $start = $event['time_start'] ?? '';
    $end   = $event['time_end'] ?? '';
    if ($start === '' && $end === '') {
        return '';
    }
    if ($start !== '' && $end !== '') {
        return sprintf(
            __('Das %1$s às %2$s', 'EventsHouse'),
            EventsHouse_event_format_hour_pt($start),
            EventsHouse_event_format_hour_pt($end)
        );
    }
    return $start !== '' ? EventsHouse_event_format_hour_pt($start) : EventsHouse_event_format_hour_pt($end);
}

/**
 * @param array<string, mixed> $config Config.
 * @return string
 */
function EventsHouse_event_get_location_from_config(array $config)
{
    $loc = $config['event']['location'] ?? '';
    return is_string($loc) ? trim($loc) : '';
}

/**
 * @param WC_Product|null $product Produto.
 * @return string
 */
function EventsHouse_wc_event_format_event_datetime($product)
{
    $config = EventsHouse_event_get_config($product);
    $parts  = array();
    $day    = EventsHouse_event_format_day_line($config);
    $range  = EventsHouse_event_format_time_range($config);
    if ($day !== '') {
        $parts[] = $day;
    }
    if ($range !== '') {
        $parts[] = $range;
    }
    return implode(' · ', $parts);
}

/**
 * @param array<string, mixed> $config Config.
 * @return string
 */
function EventsHouse_event_format_base_hint(array $config)
{
    $base = $config['base'] ?? array();
    $adults = isset($base['adults']) ? absint($base['adults']) : 0;
    $children = isset($base['children']) ? absint($base['children']) : 0;
    $parts = array();
    if ($adults > 0) {
        $parts[] = sprintf(_n('%d adulto incluído', '%d adultos incluídos', $adults, 'EventsHouse'), $adults);
    }
    if ($children > 0) {
        $parts[] = sprintf(_n('%d criança incluída', '%d crianças incluídas', $children, 'EventsHouse'), $children);
    }
    return $parts === array() ? '' : '(' . implode(' + ', $parts) . ')';
}

/**
 * @param string $dob        YYYY-MM-DD.
 * @param string $event_date YYYY-MM-DD.
 * @return int|null
 */
function EventsHouse_event_age_at_event($dob, $event_date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        return null;
    }
    try {
        $birth = new DateTime($dob);
        $event = new DateTime($event_date);
    } catch (Exception $e) {
        return null;
    }
    if ($birth > $event) {
        return null;
    }
    return (int) $birth->diff($event)->y;
}

/**
 * @param array{type:string,name:string,dob:string} $extra      Extra.
 * @param array<string, mixed>                     $pricing    Pricing.
 * @param string                                   $event_date Data.
 * @return float
 */
function EventsHouse_event_extra_line_fee(array $extra, array $pricing, $event_date, $product = null)
{
    if (($extra['type'] ?? '') === 'adult') {
        $raw = max(0, (float) ($pricing['adult_extra'] ?? 0));
        return $product instanceof WC_Product
            ? EventsHouse_wc_event_price_for_cart_fee($product, $raw)
            : $raw;
    }
    $dob = $extra['dob'] ?? '';
    $free_max = isset($pricing['child_free_max_age']) ? absint($pricing['child_free_max_age']) : 3;
    if ($event_date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $age = EventsHouse_event_age_at_event($dob, $event_date);
        if ($age !== null && $age <= $free_max) {
            return 0.0;
        }
    }
    $raw = max(0, (float) ($pricing['child_extra'] ?? 0));
    return $product instanceof WC_Product
        ? EventsHouse_wc_event_price_for_cart_fee($product, $raw)
        : $raw;
}

/**
 * @param array<int, array{type:string,name:string,dob:string}> $extras  Extras.
 * @param WC_Product|null                                      $product Produto.
 * @return float
 */
function EventsHouse_event_calculate_extras_total(array $extras, $product)
{
    $config = EventsHouse_event_get_config($product);
    $pricing = $config['pricing'] ?? array();
    $event_date = $config['event']['date'] ?? '';
    $total = 0.0;
    foreach ($extras as $ex) {
        if (is_array($ex)) {
            $total += EventsHouse_event_extra_line_fee($ex, $pricing, $event_date, $product);
        }
    }
    return $total;
}

/**
 * @param array<string, mixed> $config     Config.
 * @param float                $base_price Preço base.
 * @return array<int, array{icon:string,label:string,amount:string}>
 */
/**
 * O formulário de inscrição detalhado está desactivado (só quantidades na ficha).
 *
 * @return bool
 */
function EventsHouse_event_registration_form_enabled()
{
    return (bool) apply_filters('eventshouse_event_registration_form_enabled', false);
}

/**
 * @param array<string, mixed> $config Config.
 * @return bool
 */
function EventsHouse_event_has_base_price(array $config)
{
    return !empty($config['pricing']['has_base_price']);
}

/**
 * @param array<string, mixed> $config Config.
 * @param float                $base_price Preço WC.
 * @return float Preço base efectivo (0 se não configurado).
 */
function EventsHouse_event_get_display_base_price(array $config, $base_price)
{
    return EventsHouse_event_has_base_price($config) ? max(0, (float) $base_price) : 0.0;
}

/**
 * Calcula taxa de bilhetes por quantidade (modo sem formulário).
 *
 * @param array{adult_extra?:int,child_extra?:int,child_free?:int} $qty    Quantidades.
 * @param array<string, mixed>                                     $config Config.
 * @return float
 */
function EventsHouse_event_calculate_tickets_fee(array $qty, array $config, $product = null)
{
    $pricing = $config['pricing'] ?? array();
    $adult_unit = (float) ($pricing['adult_extra'] ?? 0);
    $child_unit = (float) ($pricing['child_extra'] ?? 0);
    if ($product instanceof WC_Product) {
        $adult_unit = EventsHouse_wc_event_price_for_cart_fee($product, $adult_unit);
        $child_unit = EventsHouse_wc_event_price_for_cart_fee($product, $child_unit);
    }
    $adult = max(0, absint($qty['adult_extra'] ?? 0));
    $child = max(0, absint($qty['child_extra'] ?? 0));
    return ($adult * $adult_unit) + ($child * $child_unit);
}

/**
 * @param array{adult_extra?:int,child_extra?:int,child_free?:int} $qty Quantidades.
 * @param array<string, mixed>                                     $config Config.
 * @return string
 */
function EventsHouse_event_format_tickets_display(array $qty, array $config)
{
    $ui = function_exists('EventsHouse_get_event_ui_strings') ? EventsHouse_get_event_ui_strings() : array();
    $pricing = $config['pricing'] ?? array();
    $free_max = absint($pricing['child_free_max_age'] ?? 3);
    $lines = array();
    if (!empty($qty['adult_extra'])) {
        $lines[] = sprintf($ui['cart_qty_adult'] ?? __('Adultos extra: %d', 'EventsHouse'), (int) $qty['adult_extra']);
    }
    if (!empty($qty['child_extra'])) {
        $lines[] = sprintf($ui['cart_qty_child'] ?? __('Crianças extra: %d', 'EventsHouse'), (int) $qty['child_extra']);
    }
    if (!empty($qty['child_free'])) {
        $lines[] = sprintf($ui['cart_qty_child_free'] ?? __('Até aos %1$d anos: %2$d', 'EventsHouse'), $free_max, (int) $qty['child_free']);
    }
    return implode("\n", $lines);
}

/**
 * @param array<string, mixed> $config     Config.
 * @param float                $base_price Preço base.
 * @return array<int, array{icon:string,label:string,amount:string,stepper?:string}>
 */
function EventsHouse_event_pricing_table_rows(array $config, $base_price, $product = null)
{
    $pricing = $config['pricing'] ?? array();
    $ui = function_exists('EventsHouse_get_event_ui_strings') ? EventsHouse_get_event_ui_strings() : array();
    $display_base = EventsHouse_event_get_display_base_price($config, $base_price);

    $rows = array();
    if (EventsHouse_event_has_base_price($config)) {
        $rows[] = array(
            'icon'     => 'base',
            'label'    => $ui['price_table_base'] ?? __('Inscrição base', 'EventsHouse'),
            'amount'   => wc_price($display_base),
            'stepper'  => '',
        );
    }

    $extras_on = !isset($config['extras']['enabled']) || !empty($config['extras']['enabled']);
    if ($extras_on) {
        $adult_display = (float) ($pricing['adult_extra'] ?? 0);
        $child_display = (float) ($pricing['child_extra'] ?? 0);
        if ($product instanceof WC_Product) {
            $adult_display = EventsHouse_wc_event_price_to_display($product, $adult_display);
            $child_display = EventsHouse_wc_event_price_to_display($product, $child_display);
        }

        $rows[] = array(
            'icon'    => 'adult',
            'label'   => $ui['price_table_adult_extra'] ?? __('Adulto', 'EventsHouse'),
            'amount'  => wc_price($adult_display),
            'stepper' => 'adult_extra',
        );

        $paid_min = $pricing['child_paid_age_min'] ?? null;
        $paid_max = $pricing['child_paid_age_max'] ?? null;
        $child_label = $ui['price_table_child_extra'] ?? __('Criança', 'EventsHouse');
        if ($paid_min !== null && $paid_max !== null) {
            $child_label = sprintf(
                $ui['price_table_child_range'] ?? __('Crianças dos %1$d aos %2$d anos', 'EventsHouse'),
                (int) $paid_min,
                (int) $paid_max
            );
        }
        $rows[] = array(
            'icon'    => 'child',
            'label'   => $child_label,
            'amount'  => wc_price($child_display),
            'stepper' => 'child_extra',
        );

        $free_max = isset($pricing['child_free_max_age']) ? absint($pricing['child_free_max_age']) : 3;
        $rows[] = array(
            'icon'    => 'free',
            'label'   => sprintf($ui['price_table_child_free'] ?? __('Até aos %d anos', 'EventsHouse'), $free_max),
            'amount'  => $ui['price_table_free'] ?? __('Gratuito', 'EventsHouse'),
            'stepper' => 'child_free',
        );
    }

    return $rows;
}

/**
 * @param array<string, mixed> $config Config.
 * @return array<int, array<string, mixed>>
 */
function EventsHouse_event_get_enabled_fields(array $config)
{
    $out = array();
    foreach ($config['fields'] ?? array() as $field) {
        if (is_array($field) && !empty($field['enabled'])) {
            $out[] = $field;
        }
    }
    return $out;
}

/**
 * @param array<string, mixed>  $field  Campo.
 * @param array<string, string> $values Valores.
 * @return bool
 */
function EventsHouse_event_field_is_visible(array $field, array $values)
{
    $show_when = $field['show_when'] ?? null;
    if (!is_array($show_when) || empty($show_when['field'])) {
        return true;
    }
    $val = $values[$show_when['field']] ?? '';
    $expected = $show_when['value'] ?? '';
    switch ($show_when['op'] ?? 'equals') {
        case 'not_equals':
            return (string) $val !== (string) $expected;
        case 'not_empty':
            return trim((string) $val) !== '';
        case 'empty':
            return trim((string) $val) === '';
        default:
            return (string) $val === (string) $expected;
    }
}

/**
 * Preço bruto guardado na configuração (sem conversão de IVA).
 *
 * @param WC_Product|null $product Produto.
 * @param string          $key     adult_extra|child_extra.
 * @return float
 */
function EventsHouse_wc_event_get_raw_extra_price($product, $key)
{
    $config  = EventsHouse_event_get_config($product);
    $pricing = $config['pricing'] ?? array();
    return max(0, (float) ($pricing[ $key ] ?? 0));
}

/**
 * Preço com IVA para mostrar na ficha (total estimado e tabela de valores).
 *
 * @param WC_Product|null $product Produto.
 * @param float           $price   Valor da configuração.
 * @return float
 */
function EventsHouse_wc_event_price_to_display($product, $price)
{
    $price = max(0, (float) $price);
    if (!$product instanceof WC_Product) {
        return $price;
    }
    return max(0, (float) wc_get_price_to_display($product, array('price' => $price)));
}

/**
 * Montante por unidade para taxas do carrinho (coerente com as regras de IVA do produto).
 *
 * @param WC_Product|null $product Produto.
 * @param float           $price   Valor da configuração.
 * @return float
 */
function EventsHouse_wc_event_price_for_cart_fee($product, $price)
{
    $price = max(0, (float) $price);
    if (!$product instanceof WC_Product || !wc_tax_enabled() || !$product->is_taxable()) {
        return $price;
    }
    if (wc_prices_include_tax()) {
        return max(0, (float) wc_get_price_excluding_tax($product, array('price' => $price)));
    }
    return $price;
}

/**
 * @param WC_Product|null $product Produto.
 * @return bool
 */
function EventsHouse_wc_event_fee_is_taxable($product)
{
    return $product instanceof WC_Product && wc_tax_enabled() && $product->is_taxable();
}

/**
 * @param WC_Product|null $product Produto.
 * @return float
 */
function EventsHouse_wc_event_get_adult_extra_price($product)
{
    return EventsHouse_wc_event_price_to_display($product, EventsHouse_wc_event_get_raw_extra_price($product, 'adult_extra'));
}

/**
 * @param WC_Product|null $product Produto.
 * @return float
 */
function EventsHouse_wc_event_get_child_extra_price($product)
{
    return EventsHouse_wc_event_price_to_display($product, EventsHouse_wc_event_get_raw_extra_price($product, 'child_extra'));
}

/**
 * @param WC_Product|null $product Produto.
 * @return int
 */
function EventsHouse_wc_event_get_max_extras($product)
{
    if (!$product instanceof WC_Product || $product->get_type() !== 'event') {
        return 10;
    }
    $config = EventsHouse_event_get_config($product);
    if (empty($config['extras']['enabled'])) {
        return 0;
    }
    return min(50, max(1, absint($config['extras']['max'] ?? 10)));
}

/**
 * @param WC_Product|null $product Produto.
 * @return float
 */
function EventsHouse_wc_event_get_extra_price($product)
{
    return EventsHouse_wc_event_get_child_extra_price($product);
}

/**
 * @param WC_Product|null $product Produto.
 * @return string
 */
function EventsHouse_wc_event_get_event_date($product)
{
    return EventsHouse_event_get_event_date($product);
}

/**
 * @param WC_Product|null $product Produto.
 * @return string
 */
function EventsHouse_wc_event_get_event_time($product)
{
    $config = EventsHouse_event_get_config($product);
    $t = $config['event']['time_start'] ?? '';
    return is_string($t) && preg_match('/^\d{2}:\d{2}$/', $t) ? $t : '';
}
