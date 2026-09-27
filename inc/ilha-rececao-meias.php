<?php
/**
 * Venda de meias antiderrapantes na receção (check-in).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rececionistas, administradores e utilizadores com manage_options (admins do site).
 *
 * @param int|WP_User|null $user Utilizador ou ID; null = utilizador atual.
 * @return bool
 */
function ilha_user_can_access_rececao($user = null)
{
    if ($user === null) {
        if (!is_user_logged_in()) {
            return false;
        }

        return ilha_user_can_access_rececao(wp_get_current_user());
    }

    if (is_numeric($user)) {
        $user = get_userdata((int) $user);
    }

    if (!$user instanceof WP_User || !$user->ID) {
        return false;
    }

    if (user_can($user, 'manage_options')) {
        return true;
    }

    $roles = (array) $user->roles;

    return in_array('rececionista', $roles, true) || in_array('administrator', $roles, true);
}

/**
 * ID do produto WooCommerce — meias antiderrapantes.
 *
 * @return int
 */
function ilha_get_meias_product_id()
{
    $id = absint(get_theme_mod('woocommerce_meias_antiderrapantes_id', 392));

    return $id > 0 ? $id : 392;
}

/**
 * Métodos de pagamento aceites na receção.
 *
 * @return array<string, string> slug => rótulo
 */
function ilha_rececao_meias_payment_methods()
{
    return array(
        'mbway' => __('MB WAY', 'EventsHouse'),
    );
}

/**
 * Preço em texto simples (sem HTML nem entidades), ex.: 4,00 €
 *
 * @param float|string $amount
 * @return string
 */
function ilha_rececao_format_money($amount)
{
    if ($amount === '' || $amount === null) {
        return '';
    }

    if (function_exists('wc_price')) {
        $html = wc_price($amount);
        $text = wp_strip_all_tags($html);

        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return number_format((float) $amount, 2, ',', '.') . ' €';
}

/**
 * Total da encomenda em texto simples.
 *
 * @param WC_Order $order
 * @return string
 */
function ilha_rececao_format_order_total($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }

    $text = wp_strip_all_tags($order->get_formatted_order_total());

    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Dados do produto para o painel da receção.
 *
 * @return array|null
 */
function ilha_rececao_meias_product_payload()
{
    if (!function_exists('wc_get_product')) {
        return null;
    }

    $product_id = ilha_get_meias_product_id();
    $product    = wc_get_product($product_id);

    if (!$product || !in_array($product->get_status(), array('publish', 'private'), true)) {
        return null;
    }

    $price = $product->get_price();

    return array(
        'id'              => $product_id,
        'nome'            => $product->get_name(),
        'preco'           => (float) $price,
        'preco_formatado' => $price !== '' ? ilha_rececao_format_money($price) : '',
        'stock'           => $product->managing_stock() ? (int) $product->get_stock_quantity() : null,
        'em_stock'        => $product->is_in_stock(),
    );
}

/**
 * Gateway WooCommerce MB WAY (IfThenPay, Eupago, etc.).
 *
 * @return WC_Payment_Gateway|null
 */
function ilha_rececao_get_mbway_gateway()
{
    if (!function_exists('WC') || !WC()->payment_gateways()) {
        return null;
    }

    $gateways = WC()->payment_gateways()->payment_gateways();
    foreach ($gateways as $gateway) {
        if (!$gateway instanceof WC_Payment_Gateway) {
            continue;
        }
        $gw_id = strtolower($gateway->id);
        if (strpos($gw_id, 'mbway') !== false || strpos($gw_id, 'mb_way') !== false) {
            return $gateway;
        }
    }

    return null;
}

/**
 * Normaliza telemóvel português para MB WAY (9 dígitos, começa por 9).
 *
 * @param string $phone
 * @return string
 */
function ilha_rececao_normalize_mbway_phone($phone)
{
    $digits = preg_replace('/\D+/', '', (string) $phone);
    if (strlen($digits) === 12 && strpos($digits, '351') === 0) {
        $digits = substr($digits, 3);
    }
    if (strlen($digits) === 9 && $digits[0] === '9') {
        return $digits;
    }

    return '';
}

/**
 * Envia pedido de pagamento MB WAY ao telemóvel do cliente via gateway WooCommerce.
 *
 * @param WC_Order $order
 * @param string   $phone
 * @return array{sent:bool, message:string, phone:string}|WP_Error
 */
function ilha_rececao_trigger_mbway_payment($order, $phone)
{
    if (!$order instanceof WC_Order) {
        return new WP_Error('invalid_order', __('Encomenda inválida.', 'EventsHouse'));
    }

    $gateway = ilha_rececao_get_mbway_gateway();
    if (!$gateway) {
        return new WP_Error(
            'no_gateway',
            __('Gateway MB WAY não encontrado no WooCommerce. Verifique se o plugin de pagamentos (ex.: IfThenPay) está ativo.', 'EventsHouse')
        );
    }

    $phone = ilha_rececao_normalize_mbway_phone($phone);
    if ($phone === '') {
        return new WP_Error(
            'invalid_phone',
            __('Indique um número de telemóvel MB WAY válido (9 dígitos, ex.: 912345678).', 'EventsHouse')
        );
    }

    $order->set_billing_phone($phone);
    $order->set_payment_method($gateway->id);
    $order->set_payment_method_title($gateway->get_title());
    $order->update_meta_data('_ilha_rececao_mbway_phone', $phone);
    $order->save();

    $post_fields = array(
        'payment_method'                     => $gateway->id,
        'billing_phone'                      => $phone,
        'phone'                              => $phone,
        'mbway_phone'                        => $phone,
        'mbway_ifthen_phone'                 => $phone,
        'mbway_ifthen_for_woocommerce_phone' => $phone,
        'eupago_mbway_phone'                 => $phone,
        'eupago_mbway_telemovel'             => $phone,
    );
    $post_fields = apply_filters('ilha_rececao_mbway_post_fields', $post_fields, $order, $phone);

    foreach ($post_fields as $key => $value) {
        $_POST[$key]    = $value;
        $_REQUEST[$key] = $value;
    }

    if (!WC()->session) {
        WC()->initialize_session();
    }

    if (!method_exists($gateway, 'process_payment')) {
        return new WP_Error('no_process', __('O gateway MB WAY não suporta envio automático de cobrança.', 'EventsHouse'));
    }

    do_action('ilha_rececao_before_mbway_process_payment', $order, $phone, $gateway);

    $result = $gateway->process_payment($order->get_id());

    if (!is_array($result)) {
        return new WP_Error('payment_failed', __('Não foi possível enviar o pedido MB WAY ao cliente.', 'EventsHouse'));
    }

    if (isset($result['result']) && $result['result'] === 'success') {
        $order->add_order_note(sprintf(
            /* translators: %s: phone number */
            __('Pedido MB WAY enviado para o telemóvel %s.', 'EventsHouse'),
            $phone
        ), false, true);
        $order->save();

        return array(
            'sent'    => true,
            'message' => sprintf(
                /* translators: %s: phone number */
                __('Pedido MB WAY enviado para %s. O cliente deve confirmar no telemóvel.', 'EventsHouse'),
                $phone
            ),
            'phone'   => $phone,
        );
    }

    $message = __('Falha ao enviar pedido MB WAY.', 'EventsHouse');
    if (!empty($result['messages'])) {
        $message = wp_strip_all_tags((string) $result['messages']);
    }

    return new WP_Error('payment_failed', $message);
}

/**
 * Resolve ID/título do gateway WooCommerce para registo na encomenda.
 *
 * @param string $method mbway
 * @return array{id:string, title:string}|WP_Error
 */
function ilha_rececao_meias_resolve_payment($method)
{
    $methods = ilha_rececao_meias_payment_methods();
    if (!isset($methods[$method])) {
        return new WP_Error('invalid_payment', __('Método de pagamento inválido.', 'EventsHouse'));
    }

    $title = $methods[$method];
    $id    = $method;

    if (function_exists('WC') && WC()->payment_gateways()) {
        $gateways = WC()->payment_gateways()->payment_gateways();
        foreach ($gateways as $gateway) {
            if (!$gateway instanceof WC_Payment_Gateway) {
                continue;
            }
            $gw_id = $gateway->id;
            if ($method === 'mbway' && (stripos($gw_id, 'mbway') !== false || stripos($gw_id, 'mb_way') !== false)) {
                $id    = $gw_id;
                $title = $gateway->get_title();
                break;
            }
        }
    }

    return array(
        'id'    => $id,
        'title' => $title,
    );
}

/**
 * Cliente Ilha por ID.
 *
 * @param int $cliente_id
 * @return object|null
 */
function ilha_rececao_get_cliente($cliente_id)
{
    global $wpdb;

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ilha_clientes WHERE id = %d",
        absint($cliente_id)
    ));
}

/**
 * Dispara emails WooCommerce normais para a encomenda.
 *
 * @param WC_Order $order
 */
function ilha_rececao_send_meias_order_emails($order)
{
    if (!$order instanceof WC_Order || !function_exists('WC')) {
        return;
    }

    $mailer = WC()->mailer();
    if (!$mailer) {
        return;
    }

    $emails = $mailer->get_emails();
    $to_send = array(
        'WC_Email_New_Order',
        'WC_Email_Customer_On_Hold_Order',
        'WC_Email_Customer_Processing_Order',
    );

    foreach ($to_send as $class) {
        if (empty($emails[$class]) || !is_object($emails[$class])) {
            continue;
        }
        if (method_exists($emails[$class], 'trigger')) {
            $emails[$class]->trigger($order->get_id(), $order, true);
        }
    }
}

/**
 * Cria encomenda pendente de meias (aguarda pagamento na receção).
 *
 * @param int    $cliente_id
 * @param int    $qty
 * @param string $payment mbway
 * @param string $mbway_phone
 * @return array{order_id:int, total:string, email:string, mbway_message:string, mbway_phone:string}|WP_Error
 */
function ilha_rececao_create_meias_order_pending($cliente_id, $qty, $payment, $mbway_phone = '')
{
    if (!function_exists('wc_create_order')) {
        return new WP_Error('no_wc', __('WooCommerce não está disponível.', 'EventsHouse'));
    }

    $qty = max(0, min(20, absint($qty)));
    if ($qty < 1) {
        return new WP_Error('invalid_qty', __('Indique a quantidade de meias.', 'EventsHouse'));
    }

    $payment_info = ilha_rececao_meias_resolve_payment($payment);
    if (is_wp_error($payment_info)) {
        return $payment_info;
    }

    $product_id = ilha_get_meias_product_id();
    $product    = wc_get_product($product_id);
    if (!$product || !$product->is_purchasable()) {
        return new WP_Error('invalid_product', __('Produto de meias não encontrado ou indisponível.', 'EventsHouse'));
    }

    if ($product->managing_stock() && !$product->has_enough_stock($qty)) {
        return new WP_Error('no_stock', __('Stock insuficiente de meias antiderrapantes.', 'EventsHouse'));
    }

    $cliente = ilha_rececao_get_cliente($cliente_id);
    if (!$cliente) {
        return new WP_Error('invalid_client', __('Cliente não encontrado.', 'EventsHouse'));
    }

    if (empty($cliente->email) || !is_email($cliente->email)) {
        return new WP_Error('no_email', __('O cliente não tem email válido — não é possível enviar a confirmação de compra.', 'EventsHouse'));
    }

    if ($cliente->telefone && $mbway_phone === '') {
        $mbway_phone = $cliente->telefone;
    }

    if ($payment === 'mbway' && ilha_rececao_normalize_mbway_phone($mbway_phone) === '') {
        return new WP_Error(
            'invalid_phone',
            __('Indique um número de telemóvel MB WAY válido (9 dígitos, ex.: 912345678).', 'EventsHouse')
        );
    }

    try {
        $order = wc_create_order(array(
            'customer_id' => $cliente->user_id ? absint($cliente->user_id) : 0,
        ));
    } catch (Exception $e) {
        return new WP_Error('order_failed', $e->getMessage());
    }

    if (!$order instanceof WC_Order) {
        return new WP_Error('order_failed', __('Não foi possível criar a encomenda.', 'EventsHouse'));
    }

    $order->add_product($product, $qty);

    $nome_parts = preg_split('/\s+/u', trim((string) $cliente->nome), 2);
    $order->set_billing_email($cliente->email);
    $order->set_billing_first_name($nome_parts[0] ?? '');
    $order->set_billing_last_name($nome_parts[1] ?? '');
    if ($cliente->telefone) {
        $order->set_billing_phone($cliente->telefone);
    }

    $rececionista = wp_get_current_user();
    $order->update_meta_data('_ilha_rececao_sale', 'yes');
    $order->update_meta_data('_ilha_rececao_meias_qty', $qty);
    $order->update_meta_data('_ilha_rececao_payment', $payment);
    $order->update_meta_data('_ilha_rececao_cliente_id', absint($cliente_id));
    $order->update_meta_data('_ilha_rececao_pending_checkin', 'yes');
    if ($rececionista && $rececionista->ID) {
        $order->update_meta_data('_ilha_rececao_by_user', $rececionista->ID);
    }

    $order->calculate_totals();

    $order->add_order_note(sprintf(
        /* translators: 1: quantity, 2: payment method, 3: receptionist name */
        __('Meias antiderrapantes — encomenda criada na receção: %1$d par(es), pagamento previsto %2$s. Registado por %3$s.', 'EventsHouse'),
        $qty,
        $payment_info['title'],
        $rececionista->display_name ?: $rececionista->user_login
    ), false, true);

    $order->set_status(
        'pending',
        __('Aguarda pagamento na receção (MB WAY).', 'EventsHouse'),
        true
    );
    $order->save();

    $mbway = ilha_rececao_trigger_mbway_payment($order, $mbway_phone);
    if (is_wp_error($mbway)) {
        $order->update_status(
            'cancelled',
            sprintf(
                /* translators: %s: error message */
                __('MB WAY não enviado: %s', 'EventsHouse'),
                $mbway->get_error_message()
            ),
            true
        );

        return $mbway;
    }

    ilha_rececao_send_meias_order_emails($order);

    return array(
        'order_id'       => $order->get_id(),
        'total'          => ilha_rececao_format_order_total($order),
        'email'          => $cliente->email,
        'mbway_message'  => $mbway['message'],
        'mbway_phone'    => $mbway['phone'],
    );
}

/**
 * Confirma pagamento de encomenda de meias criada na receção.
 *
 * @param int $order_id
 * @param int $cliente_id
 * @return true|WP_Error
 */
function ilha_rececao_complete_meias_order($order_id, $cliente_id)
{
    $order_id   = absint($order_id);
    $cliente_id = absint($cliente_id);

    if (!$order_id || !$cliente_id) {
        return new WP_Error('invalid_data', __('Dados da encomenda incompletos.', 'EventsHouse'));
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('invalid_order', __('Encomenda não encontrada.', 'EventsHouse'));
    }

    if ($order->get_meta('_ilha_rececao_sale') !== 'yes') {
        return new WP_Error('invalid_order', __('Esta encomenda não foi criada na receção.', 'EventsHouse'));
    }

    if ((int) $order->get_meta('_ilha_rececao_cliente_id') !== $cliente_id) {
        return new WP_Error('invalid_order', __('A encomenda não pertence a este cliente.', 'EventsHouse'));
    }

    if ($order->has_status('completed')) {
        return true;
    }

    if ($order->has_status(array('cancelled', 'refunded', 'failed'))) {
        return new WP_Error('invalid_order', __('Esta encomenda já não pode ser confirmada.', 'EventsHouse'));
    }

    $rececionista = wp_get_current_user();

    if (!$order->get_date_paid()) {
        $order->set_date_paid(current_time('timestamp', true));
    }

    $order->update_meta_data('_ilha_rececao_pending_checkin', 'no');
    $order->add_order_note(sprintf(
        /* translators: %s: receptionist name */
        __('Pagamento confirmado na receção por %s.', 'EventsHouse'),
        $rececionista->display_name ?: $rececionista->user_login
    ), false, true);

    $order->update_status(
        'completed',
        __('Pagamento recebido na receção — encomenda concluída.', 'EventsHouse'),
        true
    );
    $order->save();

    return true;
}

/**
 * AJAX: criar encomenda pendente de meias.
 */
function ajax_ilha_rececao_meias_create()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_access_rececao()) {
        wp_send_json_error(__('Acesso negado', 'EventsHouse'));
    }

    $cliente_id = absint($_POST['cliente_id'] ?? 0);
    $qty        = max(0, min(20, absint($_POST['meias_qty'] ?? 0)));
    $payment    = sanitize_key($_POST['meias_payment'] ?? '');
    $mbway_phone = sanitize_text_field(wp_unslash($_POST['mbway_phone'] ?? ''));

    if (!$cliente_id || $qty < 1) {
        wp_send_json_error(__('Dados incompletos.', 'EventsHouse'));
    }

    $allowed = array_keys(ilha_rececao_meias_payment_methods());
    if (!in_array($payment, $allowed, true)) {
        wp_send_json_error(__('Selecione o método de pagamento (MB WAY).', 'EventsHouse'));
    }

    $result = ilha_rececao_create_meias_order_pending($cliente_id, $qty, $payment, $mbway_phone);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success($result);
}
add_action('wp_ajax_ilha_rececao_meias_create', 'ajax_ilha_rececao_meias_create');
