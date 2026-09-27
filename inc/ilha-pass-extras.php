<?php
/**
 * Extras de Manhãs na Ilha — persistência, receção e check-in.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Garante colunas de extras nas tabelas de passes e utilizações.
 */
function ilha_maybe_upgrade_extras_schema()
{
    if (get_option('ilha_extras_schema_v2') === '1') {
        return;
    }

    global $wpdb;

    $table_passes = $wpdb->prefix . 'ilha_passes';
    $cols         = $wpdb->get_col("DESC {$table_passes}", 0);
    if (!is_array($cols)) {
        return;
    }

    $add_pass = array(
        'extra_adultos_total'    => 'ALTER TABLE ' . $table_passes . ' ADD COLUMN extra_adultos_total int(11) NOT NULL DEFAULT 0',
        'extra_criancas_total'   => 'ALTER TABLE ' . $table_passes . ' ADD COLUMN extra_criancas_total int(11) NOT NULL DEFAULT 0',
        'extra_adultos_usados'   => 'ALTER TABLE ' . $table_passes . ' ADD COLUMN extra_adultos_usados int(11) NOT NULL DEFAULT 0',
        'extra_criancas_usados'  => 'ALTER TABLE ' . $table_passes . ' ADD COLUMN extra_criancas_usados int(11) NOT NULL DEFAULT 0',
    );

    foreach ($add_pass as $col => $sql) {
        if (!in_array($col, $cols, true)) {
            $wpdb->query($sql);
        }
    }

    $table_util = $wpdb->prefix . 'ilha_utilizacoes';
    $cols_util  = $wpdb->get_col("DESC {$table_util}", 0);
    if (!is_array($cols_util)) {
        return;
    }

    $add_util = array(
        'extra_adultos_usados'  => 'ALTER TABLE ' . $table_util . ' ADD COLUMN extra_adultos_usados int(11) NOT NULL DEFAULT 0',
        'extra_criancas_usados' => 'ALTER TABLE ' . $table_util . ' ADD COLUMN extra_criancas_usados int(11) NOT NULL DEFAULT 0',
    );

    foreach ($add_util as $col => $sql) {
        if (!in_array($col, $cols_util, true)) {
            $wpdb->query($sql);
        }
    }

    update_option('ilha_extras_schema_v2', '1', true);
}
add_action('after_switch_theme', 'ilha_maybe_upgrade_extras_schema');
add_action('admin_init', 'ilha_maybe_upgrade_extras_schema');

/**
 * Backfill de extras ao primeiro pedido da receção (rececionistas não acedem ao admin).
 */
function ilha_maybe_backfill_extras_on_rececao_ajax()
{
    if (!function_exists('ilha_user_can_manage_reservas') || !ilha_user_can_manage_reservas()) {
        return;
    }
    ilha_backfill_pass_extras_from_orders();
}
add_action('wp_ajax_ilha_get_active_pass', 'ilha_maybe_backfill_extras_on_rececao_ajax', 1);
add_action('wp_ajax_ilha_get_reservas', 'ilha_maybe_backfill_extras_on_rececao_ajax', 1);

/**
 * Conta extras numa encomenda WooCommerce.
 *
 * @param WC_Order|int $order
 * @return array{adultos:int, criancas:int}
 */
function ilha_count_extras_in_order($order)
{
    $order = is_numeric($order) ? wc_get_order($order) : $order;
    $out   = array('adultos' => 0, 'criancas' => 0);

    if (!$order || !function_exists('EventsHouse_ilha_extra_product_ids')) {
        return $out;
    }

    $ids = EventsHouse_ilha_extra_product_ids();

    foreach ($order->get_items() as $item) {
        $pid = (int) $item->get_product_id();
        $qty = (int) $item->get_quantity();
        if ($ids['adult'] && $pid === (int) $ids['adult']) {
            $out['adultos'] += $qty;
        }
        if ($ids['child'] && $pid === (int) $ids['child']) {
            $out['criancas'] += $qty;
        }
    }

    return $out;
}

/**
 * Dados de extras de um passe para JSON / UI.
 *
 * @param object $passe Linha de wp_ilha_passes.
 * @return array<string, mixed>
 */
function ilha_pass_extras_payload($passe)
{
    $total_a = max(0, (int) ($passe->extra_adultos_total ?? 0));
    $total_c = max(0, (int) ($passe->extra_criancas_total ?? 0));
    $usado_a = max(0, (int) ($passe->extra_adultos_usados ?? 0));
    $usado_c = max(0, (int) ($passe->extra_criancas_usados ?? 0));

    $rest_a = max(0, $total_a - $usado_a);
    $rest_c = max(0, $total_c - $usado_c);

    return array(
        'tem_extras'           => ($total_a + $total_c) > 0,
        'adultos_total'        => $total_a,
        'criancas_total'       => $total_c,
        'adultos_usados'       => $usado_a,
        'criancas_usados'      => $usado_c,
        'adultos_restantes'    => $rest_a,
        'criancas_restantes'   => $rest_c,
        'label_comprados'      => ilha_format_extras_label($total_a, $total_c, false),
        'label_restantes'      => ilha_format_extras_label($rest_a, $rest_c, false),
        'label_resumo'         => ilha_format_extras_resumo($total_a, $total_c, $rest_a, $rest_c),
    );
}

/**
 * @param int  $adultos
 * @param int  $criancas
 * @param bool $short Abrevia (ex.: 2A · 1C).
 */
function ilha_format_extras_label($adultos, $criancas, $short = true)
{
    $adultos  = max(0, (int) $adultos);
    $criancas = max(0, (int) $criancas);
    if ($adultos === 0 && $criancas === 0) {
        return '';
    }

    $parts = array();
    if ($adultos > 0) {
        if ($short) {
            $parts[] = $adultos . 'A';
        } else {
            $parts[] = sprintf(
                /* translators: %d: number of extra adults */
                _n('%d adulto extra', '%d adultos extra', $adultos, 'EventsHouse'),
                $adultos
            );
        }
    }
    if ($criancas > 0) {
        if ($short) {
            $parts[] = $criancas . 'C';
        } else {
            $parts[] = sprintf(
                /* translators: %d: number of extra children */
                _n('%d criança extra', '%d crianças extra', $criancas, 'EventsHouse'),
                $criancas
            );
        }
    }

    return implode($short ? ' · ' : ', ', $parts);
}

/**
 * Texto completo para receção: comprados + restantes.
 */
function ilha_format_extras_resumo($total_a, $total_c, $rest_a, $rest_c)
{
    $comprados = ilha_format_extras_label($total_a, $total_c, false);
    if ($comprados === '') {
        return '';
    }

    $rest_a = max(0, (int) $rest_a);
    $rest_c = max(0, (int) $rest_c);
    $restantes = ilha_format_extras_label($rest_a, $rest_c, false);

    if ($restantes === '' || ($rest_a === 0 && $rest_c === 0)) {
        return sprintf(
            /* translators: %s: purchased extras description */
            __('Extras comprados: %s (todos utilizados)', 'EventsHouse'),
            $comprados
        );
    }

    return sprintf(
        /* translators: 1: purchased extras, 2: remaining extras */
        __('Extras: %1$s · Restam: %2$s', 'EventsHouse'),
        $comprados,
        $restantes
    );
}

/**
 * Valida e aplica consumo de extras no check-in.
 *
 * @param object $passe
 * @param int    $usar_adultos
 * @param int    $usar_criancas
 * @return true|WP_Error
 */
function ilha_validate_and_apply_extras_checkin($passe, $usar_adultos, $usar_criancas)
{
    $usar_adultos  = max(0, (int) $usar_adultos);
    $usar_criancas = max(0, (int) $usar_criancas);

    if ($usar_adultos === 0 && $usar_criancas === 0) {
        return true;
    }

    $payload = ilha_pass_extras_payload($passe);
    if (!$payload['tem_extras']) {
        return new WP_Error(
            'ilha_sem_extras',
            __('Este passe não tem extras comprados.', 'EventsHouse')
        );
    }

    if ($usar_adultos > $payload['adultos_restantes']) {
        return new WP_Error(
            'ilha_extra_adulto',
            sprintf(
                /* translators: %d: max allowed */
                __('Só restam %d adulto(s) extra neste passe.', 'EventsHouse'),
                $payload['adultos_restantes']
            )
        );
    }

    if ($usar_criancas > $payload['criancas_restantes']) {
        return new WP_Error(
            'ilha_extra_crianca',
            sprintf(
                /* translators: %d: max allowed */
                __('Só restam %d criança(s) extra neste passe.', 'EventsHouse'),
                $payload['criancas_restantes']
            )
        );
    }

    return true;
}

/**
 * Preenche extras em passes antigos a partir da encomenda WC (uma vez).
 */
function ilha_backfill_pass_extras_from_orders()
{
    if (get_option('ilha_extras_backfill_done')) {
        return;
    }

    if (!function_exists('wc_get_order')) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ilha_passes';

    ilha_maybe_upgrade_extras_schema();

    $passes = $wpdb->get_results(
        "SELECT id, order_wc_id, extra_adultos_total, extra_criancas_total
        FROM {$table}
        WHERE order_wc_id IS NOT NULL AND order_wc_id > 0
        AND (extra_adultos_total = 0 AND extra_criancas_total = 0)
        LIMIT 500"
    );

    if (empty($passes)) {
        update_option('ilha_extras_backfill_done', 1, false);

        return;
    }

    foreach ($passes as $passe) {
        $order = wc_get_order((int) $passe->order_wc_id);
        if (!$order) {
            continue;
        }
        $extras = ilha_count_extras_in_order($order);
        if ($extras['adultos'] === 0 && $extras['criancas'] === 0) {
            continue;
        }

        $wpdb->update(
            $table,
            array(
                'extra_adultos_total'  => $extras['adultos'],
                'extra_criancas_total' => $extras['criancas'],
            ),
            array('id' => (int) $passe->id),
            array('%d', '%d'),
            array('%d')
        );
    }

    update_option('ilha_extras_backfill_done', 1, false);
}
add_action('admin_init', 'ilha_backfill_pass_extras_from_orders', 20);
