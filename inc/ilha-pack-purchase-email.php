<?php
/**
 * Email ao cliente após compra de pack/passe Manhãs na Ilha (pagamento confirmado).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Opções de email — compra de pack/passe.
 *
 * @return array<string, string>
 */
function ilha_pack_purchase_email_options_map()
{
    $reserve_url = function_exists('EventsHouse_ilha_passes_page_url')
        ? EventsHouse_ilha_passes_page_url('#ilha-disponibilidade')
        : home_url('/manhas-na-ilha/#ilha-disponibilidade');

    return array(
        'eventshouse_email_ilha_pack_purchase_subject' => 'Obrigado pela sua compra — Manhãs na Ilha',
        'eventshouse_email_ilha_pack_purchase_title'   => 'Passe Manhãs na Ilha',
        'eventshouse_email_ilha_pack_purchase_body'   => "<p>Olá!</p>\n<p>Obrigado por ter adquirido um <strong>Passe das Manhãs na Ilha</strong> (%pack_label%).</p>\n<p>Estamos muito felizes por o receber na nossa comunidade!</p>\n<p>A partir de agora, pode usufruir das suas manhãs na Ilha sempre que desejar. Para isso, basta seguir estes passos:</p>\n<p><strong>1. Reserve a sua visita</strong><br>Todas as visitas requerem reserva prévia através do nosso site.<br>👉 <a href=\"%reserve_url%\">Escolha o dia pretendido e faça a sua reserva</a>.</p>\n<p><strong>2. Como funciona?</strong></p>\n<ul>\n<li>As Manhãs na Ilha decorrem das %horario%.</li>\n<li>Sempre que existir pelo menos uma reserva, o espaço estará aberto e contará com a presença da nossa Patrícia, que acompanhará e entreterá as crianças.</li>\n<li>Continua a ser obrigatória a presença de um adulto responsável durante toda a permanência.</li>\n</ul>\n<p><strong>3. No dia da visita</strong><br>À chegada, basta indicar o email utilizado na compra do passe para validarmos a sua reserva.</p>\n<p>Estamos ansiosos por recebê-lo!</p>\n<p>Até breve,<br>Equipa A Ilha 💛</p>",
    );
}

/**
 * IDs WooCommerce dos passes/packs Manhãs na Ilha.
 *
 * @return int[]
 */
function ilha_pass_product_ids()
{
    return array_values(array_filter(array_map('absint', array(
        get_theme_mod('woocommerce_weekly_pass_id'),
        get_theme_mod('woocommerce_monthly_pass_id'),
        get_theme_mod('woocommerce_pack_1_id'),
        get_theme_mod('woocommerce_pack_6_id'),
        get_theme_mod('woocommerce_pack_12_id'),
    ))));
}

/**
 * A encomenda contém pelo menos um passe/pack Manhãs na Ilha?
 *
 * @param WC_Order $order
 * @return bool
 */
function ilha_order_contains_pass_products($order)
{
    if (!$order instanceof WC_Order) {
        return false;
    }

    $pass_ids = ilha_pass_product_ids();
    if (empty($pass_ids)) {
        return false;
    }

    foreach ($order->get_items() as $item) {
        if (in_array((int) $item->get_product_id(), $pass_ids, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Rótulos legíveis dos passes na encomenda.
 *
 * @param WC_Order $order
 * @return string
 */
function ilha_order_pass_labels($order)
{
    $pass_ids = ilha_pass_product_ids();
    $map      = array(
        absint(get_theme_mod('woocommerce_weekly_pass_id'))  => __('Passe Semanal', 'EventsHouse'),
        absint(get_theme_mod('woocommerce_monthly_pass_id')) => __('Passe Mensal', 'EventsHouse'),
        absint(get_theme_mod('woocommerce_pack_1_id'))     => __('Pack 1 Manhã', 'EventsHouse'),
        absint(get_theme_mod('woocommerce_pack_6_id'))     => __('Pack 6 Manhãs', 'EventsHouse'),
        absint(get_theme_mod('woocommerce_pack_12_id'))    => __('Pack 12 Manhãs', 'EventsHouse'),
    );

    $labels = array();
    foreach ($order->get_items() as $item) {
        $pid = (int) $item->get_product_id();
        if (!in_array($pid, $pass_ids, true)) {
            continue;
        }
        $label = $map[$pid] ?? $item->get_name();
        $qty   = (int) $item->get_quantity();
        $labels[] = $qty > 1 ? $label . ' (×' . $qty . ')' : $label;
    }

    return implode(', ', array_unique($labels));
}

/**
 * Variáveis para o template de compra de pack.
 *
 * @param WC_Order $order
 * @return array<string, string>
 */
function ilha_pack_purchase_email_vars($order)
{
    $first = $order->get_billing_first_name();
    $last  = $order->get_billing_last_name();
    $name  = trim($first . ' ' . $last);
    if ($name === '') {
        $name = $first ?: __('Cliente', 'EventsHouse');
    }

    $horario = EventsHouse_email_opt('eventshouse_email_ilha_horario_padrao');
    if ($horario === '') {
        $horario = '09h00 – 13h00';
    }

    $reserve_url = function_exists('EventsHouse_ilha_passes_page_url')
        ? EventsHouse_ilha_passes_page_url('#ilha-disponibilidade')
        : home_url('/manhas-na-ilha/#ilha-disponibilidade');

    return array(
        'name'        => $name,
        'email'       => $order->get_billing_email(),
        'phone'       => $order->get_billing_phone() ?: '',
        'pack_label'  => ilha_order_pass_labels($order),
        'order_id'    => (string) $order->get_id(),
        'horario'     => $horario,
        'reserve_url' => $reserve_url,
        'site_name'   => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    );
}

/**
 * Envia email de compra de pack ao cliente.
 *
 * @param WC_Order $order
 * @return bool
 */
function ilha_send_pack_purchase_email($order)
{
    if (!$order instanceof WC_Order || !function_exists('ilha_send_reservation_email')) {
        return false;
    }

    $to = $order->get_billing_email();
    if (!is_email($to)) {
        return false;
    }

    $vars = ilha_pack_purchase_email_vars($order);

    return ilha_send_reservation_email(
        $to,
        'eventshouse_email_ilha_pack_purchase_subject',
        'eventshouse_email_ilha_pack_purchase_title',
        'eventshouse_email_ilha_pack_purchase_body',
        $vars
    );
}

/**
 * Após pagamento confirmado e passes criados — email ao cliente.
 *
 * @param int $order_id
 */
function ilha_maybe_send_pack_purchase_email($order_id)
{
    $order_id = absint($order_id);
    if (!$order_id || !function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order) {
        return;
    }

    if ($order->get_meta('_ilha_pack_purchase_email_sent') === 'yes') {
        return;
    }

    if ($order->get_meta('_ilha_passes_processed') !== 'yes') {
        return;
    }

    if (!ilha_order_contains_pass_products($order)) {
        return;
    }

    $sent = ilha_send_pack_purchase_email($order);

    $order->update_meta_data('_ilha_pack_purchase_email_sent', $sent ? 'yes' : 'no');
    if (!$sent && function_exists('EventsHouse_log_error')) {
        EventsHouse_log_error('ilha_pack_purchase', 'Falha ao enviar email de compra de pack.', array(
            'order_id'   => $order_id,
            'email'      => $order->get_billing_email(),
            'mail_error' => function_exists('EventsHouse_last_mail_error_message') ? EventsHouse_last_mail_error_message() : '',
        ), 'warning');
    }
    $order->save();
}
add_action('woocommerce_order_status_completed', 'ilha_maybe_send_pack_purchase_email', 20, 1);
