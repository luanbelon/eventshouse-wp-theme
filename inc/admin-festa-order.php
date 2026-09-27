<?php
/**
 * WooCommerce — Admin: Gerar encomenda festas (categoria Festas na ilha) + link Confirmar reserva.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Slug da categoria de produtos «Festas na ilha». */
const EventsHouse_FESTAS_NA_ILHA_CAT_SLUG = 'festas-na-ilha';

/** ID esperado da categoria (referência; a listagem usa o termo pelo slug). */
const EventsHouse_FESTAS_NA_ILHA_CAT_ID = 19;
const EventsHouse_FESTA_BALANCE_DUE_HOURS = 48;
const EventsHouse_FESTA_BALANCE_EMAIL_HOURS = 72;
/** Prazo máximo para pagar a 1.ª parte (reserva), em horas após a criação da encomenda. */
const EventsHouse_FESTA_DEPOSIT_DUE_HOURS = 48;

/**
 * Termo da categoria festas (slug preferido; fallback por ID).
 *
 * @return WP_Term|null
 */
function EventsHouse_festa_order_get_category_term()
{
    $term = get_term_by('slug', EventsHouse_FESTAS_NA_ILHA_CAT_SLUG, 'product_cat');
    if ($term && !is_wp_error($term)) {
        return $term;
    }
    $by_id = get_term(EventsHouse_FESTAS_NA_ILHA_CAT_ID, 'product_cat');
    if ($by_id && !is_wp_error($by_id)) {
        return $by_id;
    }
    return null;
}

/**
 * Produtos publicados nesta categoria.
 *
 * @return WC_Product[]
 */
function EventsHouse_festa_order_get_products()
{
    if (!function_exists('wc_get_products')) {
        return array();
    }

    $term = EventsHouse_festa_order_get_category_term();
    if (!$term) {
        return array();
    }

    $slug = (string) apply_filters('eventshouse_festa_order_category_slug', $term->slug);

    return wc_get_products(array(
        'limit'    => -1,
        'status'   => 'publish',
        'orderby'  => 'title',
        'order'    => 'ASC',
        'return'   => 'objects',
        'category' => array($slug),
    ));
}

/**
 * @param WC_Product $product
 * @return array<int, array{key:string, label:string, product:WC_Product}>
 */
function EventsHouse_festa_order_expand_product_rows($product)
{
    $rows = array();
    if (!$product instanceof WC_Product) {
        return $rows;
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $vid) {
            $vid = absint($vid);
            if (!$vid) {
                continue;
            }
            $v = wc_get_product($vid);
            if (!$v || !$v->is_purchasable()) {
                continue;
            }
            $attrs = wc_get_formatted_variation($v, true, false, true);
            $label = $product->get_name();
            if ($attrs !== '') {
                $label .= ' — ' . wp_strip_all_tags($attrs);
            }
            $rows[] = array(
                'key'     => 'v:' . $vid,
                'label'   => $label,
                'product' => $v,
            );
        }
        return $rows;
    }

    if ($product->is_type('simple') || $product->is_type('subscription')) {
        if (!$product->is_purchasable()) {
            return $rows;
        }
        $rows[] = array(
            'key'     => 'p:' . $product->get_id(),
            'label'   => $product->get_name(),
            'product' => $product,
        );
        return $rows;
    }

    return $rows;
}

/**
 * Definições dos extras (não são produtos WooCommerce).
 *
 * @return array<string, array{title:string, subtitle:string, description:string, footnote:string, default_price:string}>
 */
function EventsHouse_festa_order_get_extras_definitions()
{
    $extras = array(
        'hora_extra' => array(
            'title'         => __('Hora Extra', 'EventsHouse'),
            'subtitle'      => __('+ 1 hora', 'EventsHouse'),
            'description'   => __('Adicione tempo extra à sua festa.', 'EventsHouse'),
            'footnote'      => __('* mediante disponibilidade', 'EventsHouse'),
            'default_price' => '50',
        ),
        'insuflavel' => array(
            'title'         => __('Insuflável', 'EventsHouse'),
            'subtitle'      => '',
            'description'   => __('Adicione em extra de brincadeira.', 'EventsHouse'),
            'footnote'      => '',
            'default_price' => '50',
        ),
        'decoracao' => array(
            'title'         => __('Decoração', 'EventsHouse'),
            'subtitle'      => __('Balão Turquesa', 'EventsHouse'),
            'description'   => '',
            'footnote'      => '',
            'default_price' => '',
        ),
    );

    return apply_filters('eventshouse_festa_order_extras_definitions', $extras);
}

/**
 * Valor por omissão da caução (reembolsável), cobrada junto com o sinal.
 *
 * @return string
 */
function EventsHouse_festa_order_default_caucao_value()
{
    return (string) apply_filters('eventshouse_festa_order_default_caucao', '50');
}

/**
 * Texto explicativo sobre a caução (reembolsável) — usado no placeholder
 * {aviso_caucao} do email de pagamento e como nota interna da encomenda.
 *
 * @param string $amount_plain Valor formatado (ex.: "50,00 €").
 * @return string
 */
function EventsHouse_festa_order_caucao_notice_text($amount_plain)
{
    return sprintf(
        /* translators: %s: valor da caução, já formatado (ex.: "50,00 €") */
        __(
            "🔒 Caução (reembolsável): esta encomenda inclui uma caução de %s, cobrada na totalidade juntamente com o sinal (não é parcelada), para proteger o espaço e os equipamentos durante a sua festa.\nEste valor é devolvido na totalidade pela nossa equipa após o evento, desde que o espaço seja entregue em bom estado (sem danos, avarias ou necessidade de limpeza extra). Caso se verifiquem danos, o custo da reparação poderá ser descontado do valor da caução.",
            'EventsHouse'
        ),
        $amount_plain
    );
}

/**
 * Converte texto de preço (vírgula ou ponto) para float.
 *
 * @param mixed $raw
 * @return float
 */
/**
 * Assunto por omissão do email ao cliente.
 *
 * @return string
 */
function EventsHouse_festa_order_default_email_subject()
{
    return 'Reserva Espaço - Encomenda #{numero_encomenda} ({fase_pagamento})';
}

/**
 * Título do cabeçalho (notificação) por omissão.
 *
 * @return string
 */
function EventsHouse_festa_order_default_email_title()
{
    return __('Pagamento da Reserva', 'EventsHouse');
}

/**
 * Corpo por omissão do email ao cliente (texto; pode usar HTML simples).
 *
 * @return string
 */
function EventsHouse_festa_order_default_email_body()
{
    return "Olá {primeiro_nome},\n\n"
        . "A sua encomenda n.º {numero_encomenda} - {fase_pagamento} está pendente de pagamento.\n"
        . "Total: {total_encomenda}\n\n"
        . "{itens_encomenda}\n\n"
        . "{lista_extras}\n\n"
        . "Data do evento: {data_evento}\n"
        . "Prazo de pagamento: {data_limite_pagamento}\n\n"
        . "{aviso_caucao}\n\n"
        . "{bloco_dados_reserva}\n\n"
        . "Para confirmar a reserva e pagar de forma segura, use este link:\n{link_pagamento}\n\n"
        . "Com os melhores cumprimentos,\n{nome_loja}";
}

/**
 * Rótulo da fase de pagamento (1.ª / 2.ª parte).
 *
 * @param string $stage deposit|balance|…
 * @return string
 */
function EventsHouse_festa_order_phase_label($stage)
{
    if ($stage === 'deposit') {
        return __('1ª Parte', 'EventsHouse');
    }
    if ($stage === 'balance') {
        return __('2ª Parte', 'EventsHouse');
    }

    return __('Pagamento', 'EventsHouse');
}

/**
 * Número da encomenda sem sufixo de fase (usado em emails).
 *
 * @param WC_Order $order
 * @return string
 */
function EventsHouse_festa_order_plain_order_number($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }

    remove_filter('woocommerce_order_number', 'EventsHouse_festa_order_admin_number_with_stage', 20);
    $number = (string) $order->get_order_number();
    add_filter('woocommerce_order_number', 'EventsHouse_festa_order_admin_number_with_stage', 20, 2);

    return $number;
}

/**
 * A encomenda de festa tem prazo de pagamento definido e já expirou?
 *
 * @param WC_Order $order
 * @return bool
 */
function EventsHouse_festa_order_payment_deadline_passed($order)
{
    if (!$order instanceof WC_Order) {
        return false;
    }
    if ((string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
        return false;
    }
    if ($order->is_paid() || !$order->has_status(array('pending', 'failed', 'on-hold'))) {
        return false;
    }

    $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
    if ($due_ts <= 0) {
        return false;
    }

    return current_time('timestamp') > $due_ts;
}

/**
 * Atualiza modelos de email antigos guardados nas opções (uma vez).
 */
function EventsHouse_festa_order_migrate_email_template_options()
{
    if (get_option('eventshouse_festa_email_copy_v2') === '1') {
        return;
    }

    $old_subjects = array(
        '[{nome_loja}] {fase_pagamento} — encomenda #{numero_encomenda}',
        '[{nome_loja}] {fase_pagamento} - encomenda #{numero_encomenda}',
    );
    $subject = (string) get_option('eventshouse_festa_order_email_subject', '');
    if ($subject === '' || in_array($subject, $old_subjects, true)) {
        update_option('eventshouse_festa_order_email_subject', EventsHouse_festa_order_default_email_subject());
    }

    $title = (string) get_option('eventshouse_festa_order_email_title', '');
    if ($title === '' || strcasecmp($title, 'Pagamento da reserva') === 0) {
        update_option('eventshouse_festa_order_email_title', EventsHouse_festa_order_default_email_title());
    }

    $body = (string) get_option('eventshouse_festa_order_email_body', '');
    if ($body === '') {
        update_option('eventshouse_festa_order_email_body', EventsHouse_festa_order_default_email_body());
    } else {
        $body = str_replace('Prazo recomendado de pagamento', 'Prazo de pagamento', $body);
        $body = str_replace(
            'A sua encomenda n.º {numero_encomenda} ({fase_pagamento})',
            'A sua encomenda n.º {numero_encomenda} - {fase_pagamento}',
            $body
        );
        update_option('eventshouse_festa_order_email_body', $body);
    }

    update_option('eventshouse_festa_email_copy_v2', '1');
}
add_action('init', 'EventsHouse_festa_order_migrate_email_template_options', 5);

/**
 * Garante que o modelo de email (já guardado nas opções) inclui o aviso da
 * caução, mesmo que o modelo tenha sido personalizado antes desta funcionalidade existir.
 */
function EventsHouse_festa_order_migrate_add_caucao_placeholder()
{
    if (get_option('eventshouse_festa_caucao_copy_v1') === '1') {
        return;
    }

    $body = (string) get_option('eventshouse_festa_order_email_body', '');
    if ($body !== '' && strpos($body, '{aviso_caucao}') === false) {
        update_option('eventshouse_festa_order_email_body', rtrim($body) . "\n\n{aviso_caucao}");
    }

    update_option('eventshouse_festa_caucao_copy_v1', '1');
}
add_action('init', 'EventsHouse_festa_order_migrate_add_caucao_placeholder', 6);

/**
 * Garante que o modelo de email inclui o bloco do formulário de dados da reserva.
 */
function EventsHouse_festa_order_migrate_add_dados_reserva_placeholder()
{
    if (get_option('eventshouse_festa_dados_reserva_copy_v1') === '1') {
        return;
    }

    $body = (string) get_option('eventshouse_festa_order_email_body', '');
    if ($body !== '' && strpos($body, '{bloco_dados_reserva}') === false) {
        // Inserir antes do link de pagamento, se existir; senão no final.
        if (strpos($body, '{link_pagamento}') !== false) {
            $body = str_replace(
                '{link_pagamento}',
                "{bloco_dados_reserva}\n\n{link_pagamento}",
                $body
            );
        } else {
            $body = rtrim($body) . "\n\n{bloco_dados_reserva}";
        }
        update_option('eventshouse_festa_order_email_body', $body);
    }

    update_option('eventshouse_festa_dados_reserva_copy_v1', '1');
}
add_action('init', 'EventsHouse_festa_order_migrate_add_dados_reserva_placeholder', 7);

/**
 * Garante que o modelo de email inclui a lista de itens cobrados e a lista de extras.
 */
function EventsHouse_festa_order_migrate_add_items_placeholders()
{
    if (get_option('eventshouse_festa_items_list_copy_v1') === '1') {
        return;
    }

    $body = (string) get_option('eventshouse_festa_order_email_body', '');
    if ($body !== '') {
        $missing = array();
        foreach (array('{itens_encomenda}', '{lista_extras}') as $ph) {
            if (strpos($body, $ph) === false) {
                $missing[] = $ph;
            }
        }
        if (!empty($missing)) {
            $insert = implode("\n\n", $missing);
            $total_pos = strpos($body, '{total_encomenda}');
            $line_end  = $total_pos !== false ? strpos($body, "\n", $total_pos) : false;
            if ($line_end !== false) {
                $body = substr($body, 0, $line_end) . "\n\n" . $insert . substr($body, $line_end);
            } else {
                $body = rtrim($body) . "\n\n" . $insert;
            }
            update_option('eventshouse_festa_order_email_body', $body);
        }
    }

    update_option('eventshouse_festa_items_list_copy_v1', '1');
}
add_action('init', 'EventsHouse_festa_order_migrate_add_items_placeholders', 8);

/**
 * Corrige prazos da 1.ª parte criados com due = instante da criação (legado).
 */
function EventsHouse_festa_order_migrate_deposit_due_timestamps()
{
    if (get_option('eventshouse_festa_deposit_due_v2') === '1' || !function_exists('wc_get_orders')) {
        return;
    }

    $orders = wc_get_orders(
        array(
            'limit'      => 100,
            'status'     => array('pending', 'on-hold', 'failed'),
            'meta_key'   => '_eventshouse_payment_stage',
            'meta_value' => 'deposit',
            'return'     => 'objects',
        )
    );

    foreach ($orders as $order) {
        if (!$order instanceof WC_Order) {
            continue;
        }
        if ((string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
            continue;
        }

        $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
        $created = $order->get_date_created();
        if (!$created || $due_ts <= 0) {
            continue;
        }

        $created_ts = $created->getTimestamp();
        // Legado: due era gravado como current_time no momento da criação.
        if (abs($due_ts - $created_ts) < 180) {
            $order->update_meta_data(
                '_eventshouse_payment_due_ts',
                $created_ts + (EventsHouse_FESTA_DEPOSIT_DUE_HOURS * HOUR_IN_SECONDS)
            );
            $order->save();
        }
    }

    update_option('eventshouse_festa_deposit_due_v2', '1');
}
add_action('admin_init', 'EventsHouse_festa_order_migrate_deposit_due_timestamps', 6);

/**
 * Mapa de substituição para o modelo de email.
 *
 * @param WC_Order $order   Encomenda.
 * @param string   $pay_url URL «Pagar encomenda».
 * @return array<string, string>
 */
function EventsHouse_festa_order_email_placeholders_map($order, $pay_url)
{
    if (!$order instanceof WC_Order) {
        return array();
    }

    $first = trim((string) $order->get_billing_first_name());
    $last  = trim((string) $order->get_billing_last_name());
    $full  = trim($first . ' ' . $last);
    if ($full === '') {
        $full = __('Cliente', 'EventsHouse');
    }

    $cur = $order->get_currency();
    $total_plain = html_entity_decode(
        wp_strip_all_tags(wc_price($order->get_total(), array('currency' => $cur))),
        ENT_QUOTES,
        'UTF-8'
    );

    $stage = (string) $order->get_meta('_eventshouse_payment_stage', true);
    $phase_label = EventsHouse_festa_order_phase_label($stage);
    $event_ts = absint($order->get_meta('_eventshouse_event_datetime_ts', true));
    $event_end_ts = absint($order->get_meta('_eventshouse_event_end_ts', true));
    $event_start_time = $event_ts > 0 ? wp_date('H:i', $event_ts) : '';
    $event_end_time   = $event_end_ts > 0 ? wp_date('H:i', $event_end_ts) : '';
    $event_date_human = EventsHouse_festa_order_event_when_text($order);
    if ($event_date_human === '') {
        $event_date_human = __('não definida', 'EventsHouse');
    }
    $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
    $due_human = $due_ts > 0 ? wp_date('d/m/Y H:i', $due_ts) : __('não definido', 'EventsHouse');

    $caucao_amount = (float) $order->get_meta('_eventshouse_caucao_valor', true);
    $aviso_caucao = '';
    if ($caucao_amount > 0) {
        $caucao_plain = html_entity_decode(
            wp_strip_all_tags(wc_price($caucao_amount, array('currency' => $cur))),
            ENT_QUOTES,
            'UTF-8'
        );
        $aviso_caucao = EventsHouse_festa_order_caucao_notice_text($caucao_plain);
    }

    $dados_parts = function_exists('EventsHouse_festa_cliente_dados_email_parts')
        ? EventsHouse_festa_cliente_dados_email_parts($order)
        : array('link' => '', 'bloco' => '');

    $map = array(
        '{nome_cliente}'     => $full,
        '{nomedocliente}'    => $full,
        '{primeiro_nome}'    => $first !== '' ? $first : $full,
        '{apelido}'          => $last,
        '{email_cliente}'    => (string) $order->get_billing_email(),
        '{numero_encomenda}' => EventsHouse_festa_order_plain_order_number($order),
        '{link_pagamento}'   => esc_url($pay_url),
        '{total_encomenda}'  => $total_plain,
        '{nome_loja}'        => get_bloginfo('name'),
        '{site_url}'         => home_url('/'),
        '{fase_pagamento}'   => $phase_label,
        '{data_evento}'      => $event_date_human,
        '{hora_inicio}'      => $event_start_time,
        '{hora_fim}'         => $event_end_time,
        '{itens_encomenda}'  => EventsHouse_festa_order_items_list_text($order),
        '{lista_extras}'     => EventsHouse_festa_order_extras_list_text($order),
        '{data_limite_pagamento}' => $due_human,
        '{aviso_caucao}'     => $aviso_caucao,
        '{link_dados_reserva}'  => esc_url($dados_parts['link']),
        '{bloco_dados_reserva}' => $dados_parts['bloco'],
    );

    return apply_filters('eventshouse_festa_order_email_placeholders', $map, $order, $pay_url);
}

/**
 * Aplica placeholders a um texto.
 *
 * @param string $text
 * @param array  $map
 * @return string
 */
function EventsHouse_festa_order_replace_email_placeholders($text, array $map)
{
    if ($text === '' || $text === null) {
        return '';
    }
    $keys = array_map('strlen', array_keys($map));
    array_multisort($keys, SORT_DESC, $map);
    return str_replace(array_keys($map), array_values($map), $text);
}

/**
 * Envia o email ao cliente com o link de pagamento.
 *
 * @param WC_Order $order
 * @param string   $pay_url
 * @return true|WP_Error
 */
function EventsHouse_festa_order_send_payment_email($order, $pay_url)
{
    if (!$order instanceof WC_Order) {
        return new WP_Error('bad_order', __('Encomenda inválida.', 'EventsHouse'));
    }

    $to = $order->get_billing_email();
    if (!is_email($to)) {
        return new WP_Error('no_email', __('Indique um email de faturação válido para enviar o email ao cliente.', 'EventsHouse'));
    }

    $subject_tpl = get_option('eventshouse_festa_order_email_subject', EventsHouse_festa_order_default_email_subject());
    $body_tpl    = get_option('eventshouse_festa_order_email_body', EventsHouse_festa_order_default_email_body());

    $map     = EventsHouse_festa_order_email_placeholders_map($order, $pay_url);
    $subject = EventsHouse_festa_order_replace_email_placeholders((string) $subject_tpl, $map);
    $subject = sanitize_text_field($subject);
    $body    = EventsHouse_festa_order_replace_email_placeholders((string) $body_tpl, $map);

    $allowed = array(
        'p'      => array(),
        'br'     => array(),
        'strong' => array(),
        'em'     => array(),
        'a'      => array('href' => array(), 'title' => array(), 'target' => array(), 'rel' => array()),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'span'   => array('style' => array(), 'class' => array()),
    );
    $body_html = wpautop(wp_kses($body, $allowed));
    if (function_exists('EventsHouse_build_email_template_html')) {
        $email_title = get_option('eventshouse_festa_order_email_title', EventsHouse_festa_order_default_email_title());
        $body_html = EventsHouse_build_email_template_html($email_title, $body_html);
    }

    if (function_exists('wc_mail')) {
        wc_mail($to, $subject, $body_html);
    } else {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $body_html, $headers);
    }

    $order->add_order_note(
        sprintf(
            /* translators: %s: customer email */
            __('Email de pagamento enviado para %s (modelo «Gerar encomenda festas»).', 'EventsHouse'),
            $to
        ),
        false,
        true
    );

    return true;
}

/**
 * Guarda modelo de email (POST separado).
 *
 * @return array{ok:bool, message:string}|null null se não foi pedido guardar
 */
function EventsHouse_festa_order_handle_email_template_save()
{
    if (!isset($_POST['eventshouse_festa_save_email_template'])) {
        return null;
    }

    if (!current_user_can('manage_woocommerce')) {
        return array(
            'ok'      => false,
            'message' => __('Sem permissão.', 'EventsHouse'),
        );
    }

    check_admin_referer('eventshouse_festa_email_template', 'eventshouse_festa_email_template_nonce');

    $subject = isset($_POST['festa_email_subject']) ? sanitize_text_field(wp_unslash($_POST['festa_email_subject'])) : '';
    $body    = isset($_POST['festa_email_body']) ? wp_unslash($_POST['festa_email_body']) : '';
    $title   = isset($_POST['festa_email_title']) ? sanitize_text_field(wp_unslash($_POST['festa_email_title'])) : '';

    if ($subject === '') {
        $subject = EventsHouse_festa_order_default_email_subject();
    }

    update_option('eventshouse_festa_order_email_subject', $subject);
    update_option('eventshouse_festa_order_email_title', $title !== '' ? $title : EventsHouse_festa_order_default_email_title());

    $allowed = array(
        'p'      => array(),
        'br'     => array(),
        'strong' => array(),
        'em'     => array(),
        'a'      => array('href' => array(), 'title' => array(), 'target' => array(), 'rel' => array()),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'span'   => array('style' => array(), 'class' => array()),
    );
    update_option('eventshouse_festa_order_email_body', wp_kses($body, $allowed));

    return array(
        'ok'      => true,
        'message' => __('Modelo de email guardado.', 'EventsHouse'),
    );
}

function EventsHouse_festa_order_parse_price($raw)
{
    if ($raw === null || $raw === '') {
        return 0.0;
    }
    $s = is_string($raw) ? trim(wp_unslash($raw)) : (string) $raw;
    $s = str_replace(array(' ', '€'), '', $s);
    $s = str_replace(',', '.', $s);
    if ($s === '' || $s === '-') {
        return 0.0;
    }
    if (!is_numeric($s)) {
        return 0.0;
    }

    return (float) wc_format_decimal($s, wc_get_price_decimals());
}

/**
 * Data/hora local recebida do campo datetime-local.
 *
 * @param string $raw
 * @return int Unix timestamp (timezone local do site)
 */
function EventsHouse_festa_order_parse_event_datetime_to_ts($raw)
{
    $value = trim((string) $raw);
    if ($value === '') {
        return 0;
    }
    $dt = date_create_from_format('Y-m-d\TH:i', $value, wp_timezone());
    if (!$dt instanceof DateTime) {
        return 0;
    }

    return $dt->getTimestamp();
}

/**
 * Data (Y-m-d) + hora (H:i) locais para timestamp.
 *
 * @param string $date_raw
 * @param string $time_raw
 * @return int Unix timestamp (timezone local do site) ou 0 se inválido
 */
function EventsHouse_festa_order_parse_event_date_time_to_ts($date_raw, $time_raw)
{
    $date = trim((string) $date_raw);
    $time = trim((string) $time_raw);
    if ($date === '' || $time === '') {
        return 0;
    }

    return EventsHouse_festa_order_parse_event_datetime_to_ts($date . 'T' . substr($time, 0, 5));
}

/**
 * Chaves de produto válidas (p:ID / v:ID) → produto.
 *
 * @param string $key
 * @return WC_Product|null
 */
function EventsHouse_festa_order_product_from_key($key)
{
    $key = (string) $key;
    if (strpos($key, 'p:') !== 0 && strpos($key, 'v:') !== 0) {
        return null;
    }
    $id = absint(substr($key, 2));
    if (!$id) {
        return null;
    }
    $p = wc_get_product($id);
    if (!$p || !$p->is_purchasable()) {
        return null;
    }

    return $p;
}

/**
 * Copia os mesmos produtos/extras para uma encomenda.
 *
 * @param WC_Order $order
 * @param array<int, array{product:WC_Product, qty:int, discount_price?:float}> $items
 * @param array<int, array{id:string, qty:int, unit_price:float, line_total:float, title:string, subtitle:string, description:string, footnote:string}> $extra_lines
 * @return array<int, string>
 */
function EventsHouse_festa_order_populate_items($order, array $items, array $extra_lines)
{
    $extras_note_parts = array();

    foreach ($items as $row) {
        $discount_price = isset($row['discount_price']) ? (float) $row['discount_price'] : 0.0;
        if ($discount_price > 0) {
            // O preço com desconto é introduzido como o preço de venda (mesma base que os preços da loja).
            $line_total = (float) wc_get_price_excluding_tax(
                $row['product'],
                array('qty' => $row['qty'], 'price' => $discount_price)
            );
            $order->add_product($row['product'], $row['qty'], array(
                'subtotal' => $line_total,
                'total'    => $line_total,
            ));

            $cur = $order->get_currency();
            $order->add_order_note(
                sprintf(
                    /* translators: 1: product name, 2: discounted unit price, 3: regular unit price */
                    __('Preço com desconto aplicado a «%1$s»: %2$s (preço normal %3$s).', 'EventsHouse'),
                    $row['product']->get_name(),
                    html_entity_decode(wp_strip_all_tags(wc_price($discount_price, array('currency' => $cur))), ENT_QUOTES, 'UTF-8'),
                    html_entity_decode(wp_strip_all_tags(wc_price((float) $row['product']->get_price(), array('currency' => $cur))), ENT_QUOTES, 'UTF-8')
                ),
                false,
                true
            );
        } else {
            $order->add_product($row['product'], $row['qty']);
        }
    }

    foreach ($extra_lines as $ex) {
        $fee = new WC_Order_Item_Fee();
        $name_parts = array($ex['title']);
        if ($ex['subtitle'] !== '') {
            $name_parts[] = '(' . $ex['subtitle'] . ')';
        }
        if ($ex['qty'] > 1) {
            $name_parts[] = '× ' . $ex['qty'];
        }
        $fee->set_name(sprintf(
            /* translators: %s: extra name */
            __('Extra: %s', 'EventsHouse'),
            implode(' ', $name_parts)
        ));
        $fee->set_amount($ex['line_total']);
        $fee->set_total($ex['line_total']);
        $fee->set_tax_status('none');
        $order->add_item($fee);

        $detail = trim($ex['title'] . ($ex['subtitle'] !== '' ? ' — ' . $ex['subtitle'] : ''));
        if ($ex['description'] !== '') {
            $detail .= ': ' . $ex['description'];
        }
        if ($ex['footnote'] !== '') {
            $detail .= ' ' . $ex['footnote'];
        }
        $cur = $order->get_currency();
        $unit_plain = html_entity_decode(wp_strip_all_tags(wc_price($ex['unit_price'], array('currency' => $cur))), ENT_QUOTES, 'UTF-8');
        $tot_plain  = html_entity_decode(wp_strip_all_tags(wc_price($ex['line_total'], array('currency' => $cur))), ENT_QUOTES, 'UTF-8');
        $extras_note_parts[] = sprintf(
            '%s — %s',
            $detail,
            sprintf(
                /* translators: 1: quantity, 2: unit price, 3: line total */
                _x('%1$d × %2$s = %3$s', 'extra line in order note', 'EventsHouse'),
                $ex['qty'],
                $unit_plain,
                $tot_plain
            )
        );
    }

    if (!empty($extra_lines)) {
        $order->update_meta_data('_eventshouse_festa_extras', wp_json_encode($extra_lines));
    }

    return $extras_note_parts;
}

/**
 * Ajusta o total da encomenda para um valor alvo.
 * Com IVA ativo, calculate_totals() pode desviar o total após uma taxa negativa —
 * por isso forçamos o total final com set_total().
 *
 * @param WC_Order $order
 * @param float    $target_total
 * @param string   $label
 * @return void
 */
function EventsHouse_festa_order_apply_target_total($order, $target_total, $label)
{
    if (!$order instanceof WC_Order) {
        return;
    }

    $target_total = (float) wc_format_decimal($target_total, wc_get_price_decimals());
    $order->calculate_totals(true);
    $current_total = (float) $order->get_total();
    $adjustment    = (float) wc_format_decimal($target_total - $current_total, wc_get_price_decimals());

    if (abs($adjustment) >= 0.01) {
        $fee = new WC_Order_Item_Fee();
        $fee->set_name($label);
        $fee->set_amount($adjustment);
        $fee->set_total($adjustment);
        $fee->set_tax_status('none');
        $order->add_item($fee);
        // false = não recalcular impostos (evita o desvio do total com taxas negativas).
        $order->calculate_totals(false);
    }

    if (abs((float) $order->get_total() - $target_total) >= 0.01) {
        $order->set_total($target_total);
    }
}

/**
 * Substitui os itens da encomenda por taxas fixas (sem IVA), para o total
 * coincidir exatamente com o valor da parcela (ex.: 125€ + 50€ = 175€).
 *
 * @param WC_Order $order
 * @param array<int, array{name:string, amount:float}> $fee_lines
 * @param string   $products_summary Resumo dos produtos originais (nota).
 * @return float Total aplicado
 */
function EventsHouse_festa_order_set_fixed_fee_lines($order, array $fee_lines, $products_summary = '')
{
    if (!$order instanceof WC_Order) {
        return 0.0;
    }

    foreach ($order->get_items(array('line_item', 'fee', 'shipping', 'tax', 'coupon')) as $item_id => $item) {
        $order->remove_item($item_id);
    }

    $total = 0.0;
    foreach ($fee_lines as $line) {
        $amount = (float) wc_format_decimal($line['amount'], wc_get_price_decimals());
        if (abs($amount) < 0.01 && empty($line['keep_zero'])) {
            continue;
        }
        $fee = new WC_Order_Item_Fee();
        $fee->set_name((string) $line['name']);
        $fee->set_amount($amount);
        $fee->set_total($amount);
        $fee->set_tax_status('none');
        $order->add_item($fee);
        $total += $amount;
    }

    $total = (float) wc_format_decimal($total, wc_get_price_decimals());
    $order->calculate_totals(false);
    // Parcelas de sinal/valor festa são montantes fixos acordados (já com IVA no preço da festa).
    // Evita o WooCommerce a recalcular IVA e a distorcer 125+50=175 para valores como 151,93.
    $order->set_cart_tax(0);
    $order->set_shipping_tax(0);
    $order->set_discount_tax(0);
    $order->set_total($total);

    if ($products_summary !== '') {
        $order->update_meta_data('_eventshouse_festa_products_summary', $products_summary);
    }

    return $total;
}

/**
 * Resumo legível dos produtos/extras já na encomenda (antes de reconstruir as parcelas).
 *
 * @param WC_Order $order
 * @return string
 */
function EventsHouse_festa_order_items_summary($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }

    $parts = array();
    foreach ($order->get_items('line_item') as $item) {
        $parts[] = $item->get_name() . ' × ' . $item->get_quantity();
    }
    foreach ($order->get_items('fee') as $item) {
        $name = $item->get_name();
        if (strpos($name, 'Ajuste de parcela') === 0) {
            continue;
        }
        $parts[] = $name;
    }

    return implode(', ', $parts);
}

/**
 * Itens cobrados na encomenda (produtos e extras), com o valor final de cada linha (IVA incluído).
 *
 * @param WC_Order $order
 * @return array<int, array{name:string, amount:float}>
 */
function EventsHouse_festa_order_charge_lines($order)
{
    if (!$order instanceof WC_Order) {
        return array();
    }

    $lines = array();
    foreach ($order->get_items('line_item') as $item) {
        $lines[] = array(
            'name'   => $item->get_name() . ' × ' . $item->get_quantity(),
            'amount' => (float) $item->get_total() + (float) $item->get_total_tax(),
        );
    }
    foreach ($order->get_items('fee') as $item) {
        $name = $item->get_name();
        if (strpos($name, 'Ajuste de parcela') === 0) {
            continue;
        }
        $lines[] = array(
            'name'   => $name,
            'amount' => (float) $item->get_total() + (float) $item->get_total_tax(),
        );
    }

    return $lines;
}

/**
 * Divide cada item cobrado em sinal (50%) e valor festa (50%), uma linha por item.
 * Diferenças de arredondamento ficam na última linha, para as somas baterem com cada parcela.
 *
 * @param array<int, array{name:string, amount:float}> $lines
 * @param float  $deposit_party Total do sinal (sem caução).
 * @param float  $balance_party Total do valor festa.
 * @param string $currency
 * @return array{deposit: array<int, array{name:string, amount:float}>, balance: array<int, array{name:string, amount:float}>}
 */
function EventsHouse_festa_order_split_charge_lines(array $lines, $deposit_party, $balance_party, $currency)
{
    $dec = wc_get_price_decimals();

    if (empty($lines)) {
        return array(
            'deposit' => array(array('name' => __('Sinal (50% da festa)', 'EventsHouse'), 'amount' => $deposit_party)),
            'balance' => array(array('name' => __('Valor festa (50%)', 'EventsHouse'), 'amount' => $balance_party)),
        );
    }

    $deposit = array();
    $balance = array();
    $deposit_sum = 0.0;
    $balance_sum = 0.0;

    foreach ($lines as $line) {
        $amount = (float) wc_format_decimal($line['amount'], $dec);
        $half   = (float) wc_format_decimal($amount * 0.5, $dec);
        $rest   = (float) wc_format_decimal($amount - $half, $dec);
        $full_plain = html_entity_decode(wp_strip_all_tags(wc_price($amount, array('currency' => $currency))), ENT_QUOTES, 'UTF-8');

        $deposit[] = array(
            /* translators: 1: item name, 2: item full price */
            'name'   => sprintf(__('Sinal — %1$s (50%% de %2$s)', 'EventsHouse'), $line['name'], $full_plain),
            'amount' => $half,
        );
        $balance[] = array(
            /* translators: 1: item name, 2: item full price */
            'name'   => sprintf(__('Valor festa — %1$s (50%% de %2$s)', 'EventsHouse'), $line['name'], $full_plain),
            'amount' => $rest,
        );
        $deposit_sum += $half;
        $balance_sum += $rest;
    }

    $last = count($deposit) - 1;
    $deposit[$last]['amount'] = (float) wc_format_decimal($deposit[$last]['amount'] + ($deposit_party - $deposit_sum), $dec);
    $balance[$last]['amount'] = (float) wc_format_decimal($balance[$last]['amount'] + ($balance_party - $balance_sum), $dec);

    return array('deposit' => $deposit, 'balance' => $balance);
}

/**
 * Data e horário do evento em texto (ex.: «12/10/2026, das 15:00 às 19:00»).
 *
 * @param WC_Order $order
 * @return string Vazio se a encomenda não tiver data de evento.
 */
function EventsHouse_festa_order_event_when_text($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }
    $start_ts = absint($order->get_meta('_eventshouse_event_datetime_ts', true));
    if ($start_ts <= 0) {
        return '';
    }
    $end_ts = absint($order->get_meta('_eventshouse_event_end_ts', true));
    if ($end_ts <= 0) {
        return wp_date('d/m/Y H:i', $start_ts);
    }

    return sprintf(
        /* translators: 1: date, 2: start time, 3: end time */
        __('%1$s, das %2$s às %3$s', 'EventsHouse'),
        wp_date('d/m/Y', $start_ts),
        wp_date('H:i', $start_ts),
        wp_date('H:i', $end_ts)
    );
}

/**
 * Lista (texto) das linhas cobradas nesta encomenda/parcela, uma por linha.
 *
 * @param WC_Order $order
 * @return string
 */
function EventsHouse_festa_order_items_list_text($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }
    $cur  = $order->get_currency();
    $rows = array();
    foreach ($order->get_items('fee') as $item) {
        $amount = (float) $item->get_total() + (float) $item->get_total_tax();
        $rows[] = '• ' . $item->get_name() . ': ' . html_entity_decode(wp_strip_all_tags(wc_price($amount, array('currency' => $cur))), ENT_QUOTES, 'UTF-8');
    }
    if (empty($rows)) {
        return '';
    }

    return __('Detalhe do pagamento:', 'EventsHouse') . "\n" . implode("\n", $rows);
}

/**
 * Lista (texto) dos extras da festa, com quantidade e preço. Vazio se não houver extras.
 *
 * @param WC_Order $order
 * @return string
 */
function EventsHouse_festa_order_extras_list_text($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }
    $extras = json_decode((string) $order->get_meta('_eventshouse_festa_extras', true), true);
    if (!is_array($extras) || empty($extras)) {
        return '';
    }

    $cur  = $order->get_currency();
    $rows = array();
    foreach ($extras as $ex) {
        if (!is_array($ex) || empty($ex['title'])) {
            continue;
        }
        $label = (string) $ex['title'];
        if (!empty($ex['subtitle'])) {
            $label .= ' — ' . $ex['subtitle'];
        }
        $rows[] = sprintf(
            '• %1$s: %2$d × %3$s = %4$s',
            $label,
            absint($ex['qty'] ?? 1),
            html_entity_decode(wp_strip_all_tags(wc_price((float) ($ex['unit_price'] ?? 0), array('currency' => $cur))), ENT_QUOTES, 'UTF-8'),
            html_entity_decode(wp_strip_all_tags(wc_price((float) ($ex['line_total'] ?? 0), array('currency' => $cur))), ENT_QUOTES, 'UTF-8')
        );
    }
    if (empty($rows)) {
        return '';
    }

    return __('Extras incluídos na festa:', 'EventsHouse') . "\n" . implode("\n", $rows);
}

/**
 * Mostra a data/horário do evento ao cliente (página de pagamento e detalhes da encomenda) e no admin.
 *
 * @param WC_Order $order
 * @return void
 */
function EventsHouse_festa_order_render_event_box($order)
{
    if (!$order instanceof WC_Order || (string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
        return;
    }
    $when = EventsHouse_festa_order_event_when_text($order);
    if ($when === '') {
        return;
    }
    echo '<p class="eventshouse-festa-event-when"><strong>' . esc_html__('Data do evento:', 'EventsHouse') . '</strong> ' . esc_html($when) . '</p>';
}

function EventsHouse_festa_order_render_event_box_on_pay_page()
{
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-pay')) {
        return;
    }
    global $wp;
    $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;
    if ($order_id) {
        EventsHouse_festa_order_render_event_box(wc_get_order($order_id));
    }
}
add_action('before_woocommerce_pay', 'EventsHouse_festa_order_render_event_box_on_pay_page', 20);
add_action('woocommerce_order_details_before_order_table', 'EventsHouse_festa_order_render_event_box', 10, 1);
add_action('woocommerce_admin_order_data_after_billing_address', 'EventsHouse_festa_order_render_event_box', 10, 1);

/**
 * Agenda envio de email da 2.ª parcela (valor festa).
 *
 * @param WC_Order $balance_order
 * @param int      $event_ts
 * @return int timestamp agendado
 */
function EventsHouse_festa_order_schedule_balance_email($balance_order, $event_ts)
{
    $send_ts = (int) $event_ts - (EventsHouse_FESTA_BALANCE_EMAIL_HOURS * HOUR_IN_SECONDS);
    $now_ts  = current_time('timestamp');
    if ($send_ts <= $now_ts) {
        $send_ts = $now_ts + MINUTE_IN_SECONDS;
    }
    $args = array($balance_order->get_id());
    $next = wp_next_scheduled('eventshouse_festa_send_balance_email', $args);
    if ($next) {
        wp_unschedule_event($next, 'eventshouse_festa_send_balance_email', $args);
    }
    wp_schedule_single_event($send_ts, 'eventshouse_festa_send_balance_email', $args);
    $balance_order->update_meta_data('_eventshouse_balance_email_scheduled_ts', $send_ts);
    $balance_order->save();

    return $send_ts;
}

/**
 * Cron: envia email da 2.ª parcela (valor festa).
 *
 * @param int $order_id
 * @return void
 */
function EventsHouse_festa_order_send_balance_email_cron($order_id)
{
    $order_id = absint($order_id);
    if (!$order_id) {
        return;
    }
    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order) {
        return;
    }
    if ((string) $order->get_meta('_eventshouse_payment_stage', true) !== 'balance') {
        return;
    }
    if ($order->is_paid()) {
        return;
    }

    $pay_url = $order->get_checkout_payment_url();
    $sent = EventsHouse_festa_order_send_payment_email($order, $pay_url);
    if (!is_wp_error($sent)) {
        $order->update_meta_data('_eventshouse_balance_email_sent_ts', current_time('timestamp'));
        $order->save();
    }
}
add_action('eventshouse_festa_send_balance_email', 'EventsHouse_festa_order_send_balance_email_cron', 10, 1);

/**
 * No admin, adiciona sufixo ao número da encomenda para identificar fase.
 *
 * @param string        $order_number Número visível da encomenda.
 * @param WC_Order|bool $order        Objeto da encomenda.
 * @return string
 */
function EventsHouse_festa_order_admin_number_with_stage($order_number, $order)
{
    if (!is_admin() || !$order instanceof WC_Order) {
        return $order_number;
    }

    // Só no ecrã de encomendas WooCommerce (não poluir emails enviados a partir do admin).
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->id, array('shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders'), true)) {
        return $order_number;
    }

    if ((string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
        return $order_number;
    }

    $stage = (string) $order->get_meta('_eventshouse_payment_stage', true);
    if ($stage === 'deposit') {
        return $order_number . ' — ' . __('1ª Parte', 'EventsHouse');
    }
    if ($stage === 'balance') {
        return $order_number . ' — ' . __('2ª Parte', 'EventsHouse');
    }

    return $order_number;
}
add_filter('woocommerce_order_number', 'EventsHouse_festa_order_admin_number_with_stage', 20, 2);

/**
 * Remove gateways se o prazo de pagamento da festa já passou.
 *
 * @param array $gateways
 * @return array
 */
function EventsHouse_festa_order_filter_gateways_after_deadline($gateways)
{
    if (is_admin() && !wp_doing_ajax()) {
        return $gateways;
    }

    $order = null;
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay')) {
        global $wp;
        $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;
        if ($order_id) {
            $order = wc_get_order($order_id);
        }
    }

    if (!$order instanceof WC_Order && isset($_POST['woocommerce_pay'], $_POST['key'])) {
        $key = sanitize_text_field(wp_unslash($_POST['key']));
        $order_id = wc_get_order_id_by_order_key($key);
        if ($order_id) {
            $order = wc_get_order($order_id);
        }
    }

    if ($order instanceof WC_Order && EventsHouse_festa_order_payment_deadline_passed($order)) {
        return array();
    }

    return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'EventsHouse_festa_order_filter_gateways_after_deadline', 100);

/**
 * Aviso e bloqueio na página «Pagar encomenda» após o prazo.
 */
function EventsHouse_festa_order_block_pay_after_deadline()
{
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-pay')) {
        return;
    }

    global $wp;
    $order_id = isset($wp->query_vars['order-pay']) ? absint($wp->query_vars['order-pay']) : 0;
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order instanceof WC_Order || !EventsHouse_festa_order_payment_deadline_passed($order)) {
        return;
    }

    $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
    $due_human = $due_ts > 0 ? wp_date('d/m/Y H:i', $due_ts) : '';

    $message = $due_human !== ''
        ? sprintf(
            /* translators: %s: deadline datetime */
            __('O prazo de pagamento desta encomenda terminou em %s. Já não é possível pagar online. Contacte-nos se precisar de ajuda.', 'EventsHouse'),
            $due_human
        )
        : __('O prazo de pagamento desta encomenda terminou. Já não é possível pagar online. Contacte-nos se precisar de ajuda.', 'EventsHouse');

    wc_add_notice($message, 'error');
}
add_action('before_woocommerce_pay', 'EventsHouse_festa_order_block_pay_after_deadline');

/**
 * Impede submissão do pagamento após o prazo.
 *
 * @param WC_Order $order
 */
function EventsHouse_festa_order_prevent_pay_action_after_deadline($order)
{
    if (!$order instanceof WC_Order || !EventsHouse_festa_order_payment_deadline_passed($order)) {
        return;
    }

    $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
    $due_human = $due_ts > 0 ? wp_date('d/m/Y H:i', $due_ts) : '';
    $message = $due_human !== ''
        ? sprintf(
            /* translators: %s: deadline datetime */
            __('O prazo de pagamento terminou em %s. Não é possível concluir o pagamento.', 'EventsHouse'),
            $due_human
        )
        : __('O prazo de pagamento terminou. Não é possível concluir o pagamento.', 'EventsHouse');

    throw new Exception($message);
}
add_action('woocommerce_before_pay_action', 'EventsHouse_festa_order_prevent_pay_action_after_deadline', 5);

/**
 * Menu principal no admin: entre «Check-ins A Ilha» (21) e WooCommerce (~55).
 */
function EventsHouse_festa_order_admin_menu()
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    add_menu_page(
        __('Gerar encomenda festas', 'EventsHouse'),
        __('Gerar encomenda festas', 'EventsHouse'),
        'manage_woocommerce',
        'eventshouse-festa-order',
        'EventsHouse_festa_order_render_page',
        'dashicons-cart',
        22
    );
}
add_action('admin_menu', 'EventsHouse_festa_order_admin_menu', 15);

/**
 * Processa POST e devolve mensagens / dados do último pedido.
 *
 * @return array{ok:bool, message:string, order_id?:int, pay_url?:string, balance_order_id?:int, balance_pay_url?:string, email_notice?:string}
 */
function EventsHouse_festa_order_handle_post()
{
    if (!isset($_POST['eventshouse_festa_order_submit'])) {
        return array('ok' => false, 'message' => '');
    }

    if (!current_user_can('manage_woocommerce')) {
        return array('ok' => false, 'message' => __('Sem permissão.', 'EventsHouse'));
    }

    check_admin_referer('eventshouse_festa_order', 'eventshouse_festa_order_nonce');

    if (!function_exists('wc_create_order')) {
        return array('ok' => false, 'message' => __('WooCommerce não está disponível.', 'EventsHouse'));
    }

    $lines          = isset($_POST['festa_qty']) && is_array($_POST['festa_qty']) ? wp_unslash($_POST['festa_qty']) : array();
    $discount_post  = isset($_POST['festa_discount_price']) && is_array($_POST['festa_discount_price']) ? wp_unslash($_POST['festa_discount_price']) : array();
    $items = array();

    foreach ($lines as $key => $raw_qty) {
        $key = sanitize_text_field((string) $key);
        $qty = max(0, absint($raw_qty));
        if ($qty < 1 || $key === '') {
            continue;
        }
        $p = EventsHouse_festa_order_product_from_key($key);
        if (!$p) {
            continue;
        }
        $items[] = array(
            'product'        => $p,
            'qty'            => $qty,
            'discount_price' => EventsHouse_festa_order_parse_price($discount_post[$key] ?? ''),
        );
    }

    $extras_def = EventsHouse_festa_order_get_extras_definitions();
    $extra_lines = array();
    $qty_post   = isset($_POST['festa_extra_qty']) && is_array($_POST['festa_extra_qty']) ? wp_unslash($_POST['festa_extra_qty']) : array();
    $price_post = isset($_POST['festa_extra_price']) && is_array($_POST['festa_extra_price']) ? wp_unslash($_POST['festa_extra_price']) : array();

    foreach (array_keys($extras_def) as $eid) {
        $eid = sanitize_key($eid);
        $qty = isset($qty_post[$eid]) ? max(0, absint($qty_post[$eid])) : 0;
        if ($qty < 1) {
            continue;
        }
        $price = EventsHouse_festa_order_parse_price($price_post[$eid] ?? '');
        $def   = $extras_def[$eid];
        $extra_lines[] = array(
            'id'          => $eid,
            'qty'         => $qty,
            'unit_price'  => $price,
            'line_total'  => $price * $qty,
            'title'       => $def['title'],
            'subtitle'    => $def['subtitle'],
            'description' => $def['description'],
            'footnote'    => $def['footnote'],
        );
    }

    $custom_post = isset($_POST['festa_custom_extra']) && is_array($_POST['festa_custom_extra']) ? wp_unslash($_POST['festa_custom_extra']) : array();
    $custom_i = 0;
    foreach ($custom_post as $row) {
        if (!is_array($row)) {
            continue;
        }
        $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
        $qty   = isset($row['qty']) ? max(0, absint($row['qty'])) : 0;
        if ($title === '' || $qty < 1) {
            continue;
        }
        $price = EventsHouse_festa_order_parse_price($row['price'] ?? '');
        $extra_lines[] = array(
            'id'          => 'outro_' . (++$custom_i),
            'qty'         => $qty,
            'unit_price'  => $price,
            'line_total'  => $price * $qty,
            'title'       => $title,
            'subtitle'    => isset($row['desc']) ? sanitize_text_field($row['desc']) : '',
            'description' => '',
            'footnote'    => '',
        );
    }

    if (empty($items) && empty($extra_lines)) {
        return array('ok' => false, 'message' => __('Indique pelo menos um produto ou um extra (quantidade > 0).', 'EventsHouse'));
    }

    $email = isset($_POST['festa_billing_email']) ? sanitize_email(wp_unslash($_POST['festa_billing_email'])) : '';
    $first = isset($_POST['festa_billing_first_name']) ? sanitize_text_field(wp_unslash($_POST['festa_billing_first_name'])) : '';
    $last  = isset($_POST['festa_billing_last_name']) ? sanitize_text_field(wp_unslash($_POST['festa_billing_last_name'])) : '';
    $event_date_raw  = isset($_POST['festa_event_date']) ? sanitize_text_field(wp_unslash($_POST['festa_event_date'])) : '';
    $event_start_raw = isset($_POST['festa_event_start_time']) ? sanitize_text_field(wp_unslash($_POST['festa_event_start_time'])) : '';
    $event_end_raw   = isset($_POST['festa_event_end_time']) ? sanitize_text_field(wp_unslash($_POST['festa_event_end_time'])) : '';
    $event_ts        = EventsHouse_festa_order_parse_event_date_time_to_ts($event_date_raw, $event_start_raw);
    $event_end_ts    = EventsHouse_festa_order_parse_event_date_time_to_ts($event_date_raw, $event_end_raw);
    if ($event_ts <= 0 || $event_end_ts <= 0) {
        return array('ok' => false, 'message' => __('Indique a data do evento, o horário de início e o horário de fim.', 'EventsHouse'));
    }
    if ($event_end_ts <= $event_ts) {
        return array('ok' => false, 'message' => __('O horário de fim tem de ser posterior ao horário de início.', 'EventsHouse'));
    }

    $due_balance_ts = $event_ts - (EventsHouse_FESTA_BALANCE_DUE_HOURS * HOUR_IN_SECONDS);
    if ($due_balance_ts <= current_time('timestamp')) {
        return array('ok' => false, 'message' => __('A data do evento tem de permitir pagamento do valor festa até 48h antes.', 'EventsHouse'));
    }

    try {
        $deposit_order = wc_create_order();
        $balance_order = wc_create_order();
    } catch (Exception $e) {
        return array('ok' => false, 'message' => $e->getMessage());
    }

    if (!$deposit_order instanceof WC_Order || !$balance_order instanceof WC_Order) {
        return array('ok' => false, 'message' => __('Não foi possível criar as encomendas (sinal e valor festa).', 'EventsHouse'));
    }

    $orders = array($deposit_order, $balance_order);
    foreach ($orders as $order) {
        $extras_note_parts = EventsHouse_festa_order_populate_items($order, $items, $extra_lines);

        if ($email !== '') {
            $order->set_billing_email($email);
        }
        if ($first !== '') {
            $order->set_billing_first_name($first);
        }
        if ($last !== '') {
            $order->set_billing_last_name($last);
        }

        $order->update_meta_data('_eventshouse_festa_generated', 'yes');
        $order->update_meta_data('_eventshouse_event_datetime_ts', $event_ts);
        $order->update_meta_data('_eventshouse_event_datetime', wp_date('Y-m-d H:i:s', $event_ts));
        $order->update_meta_data('_eventshouse_event_end_ts', $event_end_ts);
        $order->update_meta_data('_eventshouse_event_end_datetime', wp_date('Y-m-d H:i:s', $event_end_ts));
        $order->calculate_totals();

        if (!empty($extra_lines)) {
            $note_body = implode("\n", $extras_note_parts);
            $order->add_order_note(
                __('Extras (descrição e valores):', 'EventsHouse') . "\n" . $note_body,
                false,
                true
            );
        }
    }
    $full_total = (float) wc_format_decimal($deposit_order->get_total(), wc_get_price_decimals());
    $deposit_party = (float) wc_format_decimal($full_total * 0.5, wc_get_price_decimals());
    $balance_party = (float) wc_format_decimal($full_total - $deposit_party, wc_get_price_decimals());
    $products_summary = EventsHouse_festa_order_items_summary($deposit_order);

    // Caução NÃO é parcelada: sinal = 50% da festa + caução total.
    $caucao_amount = 0.0;
    $caucao_plain  = '';
    $caucao_raw    = isset($_POST['festa_caucao_valor']) ? wp_unslash($_POST['festa_caucao_valor']) : EventsHouse_festa_order_default_caucao_value();
    $caucao_amount = EventsHouse_festa_order_parse_price($caucao_raw);
    $sinal_total   = (float) wc_format_decimal($deposit_party + $caucao_amount, wc_get_price_decimals());

    $split = EventsHouse_festa_order_split_charge_lines(
        EventsHouse_festa_order_charge_lines($deposit_order),
        $deposit_party,
        $balance_party,
        $deposit_order->get_currency()
    );

    $deposit_fees = $split['deposit'];
    if ($caucao_amount > 0) {
        $deposit_fees[] = array(
            'name'   => __('Caução (reembolsável — valor total no sinal)', 'EventsHouse'),
            'amount' => $caucao_amount,
        );
    }
    EventsHouse_festa_order_set_fixed_fee_lines($deposit_order, $deposit_fees, $products_summary);
    EventsHouse_festa_order_set_fixed_fee_lines($balance_order, $split['balance'], $products_summary);

    if ($caucao_amount > 0) {
        $deposit_order->update_meta_data('_eventshouse_caucao_valor', $caucao_amount);
        $caucao_cur   = $deposit_order->get_currency();
        $caucao_plain = html_entity_decode(wp_strip_all_tags(wc_price($caucao_amount, array('currency' => $caucao_cur))), ENT_QUOTES, 'UTF-8');
        $deposit_order->add_order_note(
            sprintf(
                /* translators: 1: 50% party amount, 2: caução amount, 3: sinal total */
                __('Sinal = 50%% da festa (%1$s) + caução total (%2$s) = %3$s. A caução não é parcelada.', 'EventsHouse'),
                html_entity_decode(wp_strip_all_tags(wc_price($deposit_party, array('currency' => $caucao_cur))), ENT_QUOTES, 'UTF-8'),
                $caucao_plain,
                html_entity_decode(wp_strip_all_tags(wc_price($sinal_total, array('currency' => $caucao_cur))), ENT_QUOTES, 'UTF-8')
            ) . "\n\n" . EventsHouse_festa_order_caucao_notice_text($caucao_plain),
            false,
            true
        );
    }

    $deposit_order->update_meta_data('_eventshouse_payment_stage', 'deposit');
    $deposit_order->update_meta_data('_eventshouse_full_total', $full_total);
    $deposit_order->update_meta_data('_eventshouse_deposit_party', $deposit_party);
    $deposit_order->update_meta_data('_eventshouse_payment_due_ts', current_time('timestamp') + (EventsHouse_FESTA_DEPOSIT_DUE_HOURS * HOUR_IN_SECONDS));
    $deposit_order->update_meta_data('_eventshouse_related_order_id', $balance_order->get_id());
    $deposit_order->update_meta_data('_eventshouse_cliente_dados_status', 'pending');
    $deposit_order->add_order_note(
        sprintf(
            /* translators: 1: full total, 2: sinal total */
            __('Encomenda de sinal criada. Total festa %1$s → sinal a pagar agora: %2$s (50%% + caução).', 'EventsHouse'),
            html_entity_decode(wp_strip_all_tags(wc_price($full_total, array('currency' => $deposit_order->get_currency()))), ENT_QUOTES, 'UTF-8'),
            html_entity_decode(wp_strip_all_tags(wc_price($sinal_total, array('currency' => $deposit_order->get_currency()))), ENT_QUOTES, 'UTF-8')
        ),
        false,
        true
    );
    $deposit_order->set_status('pending');
    $deposit_order->save();

    $balance_order->update_meta_data('_eventshouse_payment_stage', 'balance');
    $balance_order->update_meta_data('_eventshouse_full_total', $full_total);
    $balance_order->update_meta_data('_eventshouse_payment_due_ts', $due_balance_ts);
    $balance_order->update_meta_data('_eventshouse_related_order_id', $deposit_order->get_id());
    $balance_order->add_order_note(__('Encomenda de valor festa (50%) criada. Sem caução — a caução fica toda no sinal.', 'EventsHouse'), false, true);
    $balance_order->set_status('pending');
    $balance_order->save();

    $scheduled_send_ts = EventsHouse_festa_order_schedule_balance_email($balance_order, $event_ts);

    $pay_url = $deposit_order->get_checkout_payment_url();
    $balance_pay_url = $balance_order->get_checkout_payment_url();

    $email_notice = '';
    if (!empty($_POST['festa_send_client_email'])) {
        $sent = EventsHouse_festa_order_send_payment_email($deposit_order, $pay_url);
        if (is_wp_error($sent)) {
            $email_notice = $sent->get_error_message();
        } else {
            $email_notice = 'sent';
        }
    }

    $message = sprintf(
        /* translators: 1: sinal total, 2: balance total */
        __('Encomendas criadas: sinal %1$s (50%% da festa + caução) e valor festa %2$s (48h antes do evento).', 'EventsHouse'),
        html_entity_decode(wp_strip_all_tags(wc_price($sinal_total, array('currency' => $deposit_order->get_currency()))), ENT_QUOTES, 'UTF-8'),
        html_entity_decode(wp_strip_all_tags(wc_price($balance_party, array('currency' => $balance_order->get_currency()))), ENT_QUOTES, 'UTF-8')
    );
    if ($caucao_amount > 0) {
        $message .= ' ' . sprintf(
            /* translators: 1: deposit party, 2: caução, 3: sinal total */
            __('Detalhe do sinal: %1$s + caução %2$s = %3$s (caução não parcelada).', 'EventsHouse'),
            html_entity_decode(wp_strip_all_tags(wc_price($deposit_party, array('currency' => $deposit_order->get_currency()))), ENT_QUOTES, 'UTF-8'),
            $caucao_plain,
            html_entity_decode(wp_strip_all_tags(wc_price($sinal_total, array('currency' => $deposit_order->get_currency()))), ENT_QUOTES, 'UTF-8')
        );
    }

    $message .= ' ' . sprintf(
        __('Email automático do valor festa agendado para %s (72h antes).', 'EventsHouse'),
        wp_date('d/m/Y H:i', $scheduled_send_ts)
    );
    if ($email_notice === 'sent') {
        $message .= ' ' . __('Email do sinal enviado ao cliente com o link de pagamento.', 'EventsHouse');
    } elseif ($email_notice !== '') {
        $message .= ' ' . sprintf(
            __('Aviso (email): %s', 'EventsHouse'),
            $email_notice
        );
    }

    return array(
        'ok'           => true,
        'message'      => $message,
        'order_id'     => $deposit_order->get_id(),
        'pay_url'      => $pay_url,
        'balance_order_id' => $balance_order->get_id(),
        'balance_pay_url'  => $balance_pay_url,
        'caucao_amount'    => $caucao_amount,
        'caucao_plain'     => $caucao_amount > 0 ? $caucao_plain : '',
        'email_notice' => $email_notice,
    );
}

/**
 * Página de administração.
 */
function EventsHouse_festa_order_render_page()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_die(esc_html__('Sem permissão para aceder a esta página.', 'EventsHouse'));
    }

    if (!class_exists('WooCommerce')) {
        echo '<div class="wrap"><p>' . esc_html__('O WooCommerce tem de estar ativo.', 'EventsHouse') . '</p></div>';
        return;
    }

    $template_save_result = EventsHouse_festa_order_handle_email_template_save();
    $result               = EventsHouse_festa_order_handle_post();

    $email_subject_cur = get_option('eventshouse_festa_order_email_subject', '');
    if ($email_subject_cur === '' || $email_subject_cur === false) {
        $email_subject_cur = EventsHouse_festa_order_default_email_subject();
    }
    $email_title_cur = get_option('eventshouse_festa_order_email_title', EventsHouse_festa_order_default_email_title());
    $email_body_cur = get_option('eventshouse_festa_order_email_body', '');
    if ($email_body_cur === '' || $email_body_cur === false) {
        $email_body_cur = EventsHouse_festa_order_default_email_body();
    }

    $term = EventsHouse_festa_order_get_category_term();
    $products = EventsHouse_festa_order_get_products();

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Gerar encomenda festas', 'EventsHouse'); ?></h1>
        <p class="description">
            <?php esc_html_e('Monte a encomenda com produtos da categoria «Festas na ilha». A encomenda fica pendente de pagamento; envie ao cliente o link para pagar (sem editar o carrinho).', 'EventsHouse'); ?>
        </p>

        <?php if (is_array($template_save_result) && $template_save_result['message'] !== '') : ?>
            <div class="notice <?php echo !empty($template_save_result['ok']) ? 'notice-success' : 'notice-error'; ?> is-dismissible"><p><?php echo esc_html($template_save_result['message']); ?></p></div>
        <?php endif; ?>

        <h2 class="title"><?php esc_html_e('Modelo de email ao cliente', 'EventsHouse'); ?></h2>
        <p class="description"><?php esc_html_e('Texto do email enviado quando marca a opção «Enviar email ao cliente» ao criar a encomenda. Use as variáveis entre chavetas; elas são substituídas pelos dados reais.', 'EventsHouse'); ?></p>
        <p class="description" style="max-width:920px;">
            <strong><?php esc_html_e('Variáveis disponíveis:', 'EventsHouse'); ?></strong><br />
            <code>{nome_cliente}</code> <?php esc_html_e('ou', 'EventsHouse'); ?> <code>{nomedocliente}</code>
            — <?php esc_html_e('nome completo', 'EventsHouse'); ?>;
            <code>{primeiro_nome}</code>;
            <code>{apelido}</code>;
            <code>{email_cliente}</code>;
            <code>{numero_encomenda}</code>;
            <code>{link_pagamento}</code>;
            <code>{total_encomenda}</code>;
            <code>{nome_loja}</code>;
            <code>{site_url}</code>;
            <code>{fase_pagamento}</code>;
            <code>{data_evento}</code> — <?php esc_html_e('data e horário (ex.: 12/10/2026, das 15:00 às 19:00)', 'EventsHouse'); ?>;
            <code>{hora_inicio}</code>;
            <code>{hora_fim}</code>;
            <code>{itens_encomenda}</code> — <?php esc_html_e('lista dos itens cobrados nesta parcela, um por linha, com o valor', 'EventsHouse'); ?>;
            <code>{lista_extras}</code> — <?php esc_html_e('lista dos extras com quantidade e preço (vazio se não houver extras)', 'EventsHouse'); ?>;
            <code>{data_limite_pagamento}</code>;
            <code>{aviso_caucao}</code> — <?php esc_html_e('texto explicativo da caução (só aparece quando a encomenda do sinal tem caução)', 'EventsHouse'); ?>;
            <code>{bloco_dados_reserva}</code> — <?php esc_html_e('aviso (sem link) sobre preencher dados na página Confirmar reserva — só no email do sinal', 'EventsHouse'); ?>;
            <code>{link_dados_reserva}</code> — <?php esc_html_e('mesmo URL que {link_pagamento}', 'EventsHouse'); ?>
        </p>
        <form method="post" action="" style="max-width:920px;margin-bottom:28px;" class="eventshouse-festa-email-template">
            <?php wp_nonce_field('eventshouse_festa_email_template', 'eventshouse_festa_email_template_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="festa_email_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" name="festa_email_subject" id="festa_email_subject" class="large-text" value="<?php echo esc_attr($email_subject_cur); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="festa_email_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" name="festa_email_title" id="festa_email_title" class="large-text" value="<?php echo esc_attr($email_title_cur); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="festa_email_body"><?php esc_html_e('Mensagem', 'EventsHouse'); ?></label></th>
                    <td>
                        <textarea name="festa_email_body" id="festa_email_body" rows="12" class="large-text code"><?php echo esc_textarea($email_body_cur); ?></textarea>
                        <p class="description"><?php esc_html_e('Pode usar texto simples (quebras de linha) ou HTML simples (negrito, links, listas).', 'EventsHouse'); ?></p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" name="eventshouse_festa_save_email_template" class="button button-secondary" value="1">
                    <?php esc_html_e('Guardar modelo de email', 'EventsHouse'); ?>
                </button>
            </p>
        </form>

        <?php if (!$term) : ?>
            <div class="notice notice-error"><p>
                <?php
                printf(
                    /* translators: %s: category slug */
                    esc_html__('Categoria de produtos não encontrada (slug «%s» ou ID %d). Crie a categoria no WooCommerce → Produtos → Categorias.', 'EventsHouse'),
                    esc_html(EventsHouse_FESTAS_NA_ILHA_CAT_SLUG),
                    (int) EventsHouse_FESTAS_NA_ILHA_CAT_ID
                );
                ?>
            </p></div>
        <?php endif; ?>

        <?php if ($result['message'] !== '') : ?>
            <div class="notice <?php echo $result['ok'] ? 'notice-success' : 'notice-error'; ?>"><p><?php echo esc_html($result['message']); ?></p></div>
        <?php endif; ?>

        <?php if ($result['ok'] && !empty($result['pay_url'])) : ?>
            <div class="card" style="max-width:900px;padding:16px;margin:16px 0;">
                <p><strong><?php esc_html_e('Link de pagamento — Sinal (50% da festa + caução)', 'EventsHouse'); ?></strong></p>
                <?php if (!empty($result['caucao_amount'])) : ?>
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: caução amount, formatted */
                            esc_html__('O total do sinal é 50%% da festa + caução total de %s (a caução não é parcelada).', 'EventsHouse'),
                            esc_html($result['caucao_plain'])
                        );
                        ?>
                    </p>
                <?php endif; ?>
                <p style="word-break:break-all;">
                    <a href="<?php echo esc_url($result['pay_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($result['pay_url']); ?></a>
                </p>
                <p>
                    <button type="button" class="button" id="eventshouse-festa-copy-pay" data-url="<?php echo esc_attr($result['pay_url']); ?>">
                        <?php esc_html_e('Copiar link', 'EventsHouse'); ?>
                    </button>
                    <?php if (!empty($result['order_id'])) : ?>
                        <a class="button" href="<?php echo esc_url(admin_url('post.php?post=' . absint($result['order_id']) . '&action=edit')); ?>"><?php esc_html_e('Abrir encomenda do sinal', 'EventsHouse'); ?></a>
                    <?php endif; ?>
                </p>
                <?php if (!empty($result['balance_pay_url'])) : ?>
                    <hr />
                    <p><strong><?php esc_html_e('Link de pagamento — Valor festa (50%)', 'EventsHouse'); ?></strong></p>
                    <p style="word-break:break-all;">
                        <a href="<?php echo esc_url($result['balance_pay_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($result['balance_pay_url']); ?></a>
                    </p>
                    <p>
                        <?php if (!empty($result['balance_order_id'])) : ?>
                            <a class="button" href="<?php echo esc_url(admin_url('post.php?post=' . absint($result['balance_order_id']) . '&action=edit')); ?>"><?php esc_html_e('Abrir encomenda do valor festa', 'EventsHouse'); ?></a>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <script>
            (function(){
                var btn = document.getElementById('eventshouse-festa-copy-pay');
                if (!btn || !navigator.clipboard) return;
                btn.addEventListener('click', function(){
                    var u = btn.getAttribute('data-url');
                    navigator.clipboard.writeText(u).then(function(){
                        btn.textContent = '<?php echo esc_js(__('Copiado!', 'EventsHouse')); ?>';
                        setTimeout(function(){ btn.textContent = '<?php echo esc_js(__('Copiar link', 'EventsHouse')); ?>'; }, 2000);
                    });
                });
            })();
            </script>
        <?php endif; ?>

        <?php $extras_def = EventsHouse_festa_order_get_extras_definitions(); ?>
            <form method="post" action="">
                <?php wp_nonce_field('eventshouse_festa_order', 'eventshouse_festa_order_nonce'); ?>

                <h2 class="title" style="margin-top:24px;"><?php esc_html_e('Dados do cliente (opcional)', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('Preenche a fatura da encomenda; o cliente pode completar ou alterar no pagamento, conforme as definições da loja.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="festa_event_date"><?php esc_html_e('Data do evento', 'EventsHouse'); ?></label></th>
                        <td>
                            <input name="festa_event_date" id="festa_event_date" type="date" value="<?php echo isset($_POST['festa_event_date']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['festa_event_date']))) : ''; ?>" required />
                            <p class="description"><?php esc_html_e('Obrigatório. O valor festa vence 48h antes do início do evento e o email automático é enviado 72h antes.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="festa_event_start_time"><?php esc_html_e('Horário de início', 'EventsHouse'); ?></label></th>
                        <td>
                            <input name="festa_event_start_time" id="festa_event_start_time" type="time" value="<?php echo isset($_POST['festa_event_start_time']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['festa_event_start_time']))) : ''; ?>" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="festa_event_end_time"><?php esc_html_e('Horário de fim', 'EventsHouse'); ?></label></th>
                        <td>
                            <input name="festa_event_end_time" id="festa_event_end_time" type="time" value="<?php echo isset($_POST['festa_event_end_time']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['festa_event_end_time']))) : ''; ?>" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="festa_billing_email"><?php esc_html_e('Email de faturação', 'EventsHouse'); ?></label></th>
                        <td><input name="festa_billing_email" id="festa_billing_email" type="email" class="regular-text" value="<?php echo isset($_POST['festa_billing_email']) ? esc_attr(sanitize_email(wp_unslash($_POST['festa_billing_email']))) : ''; ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="festa_billing_first_name"><?php esc_html_e('Nome', 'EventsHouse'); ?></label></th>
                        <td><input name="festa_billing_first_name" id="festa_billing_first_name" type="text" class="regular-text" value="<?php echo isset($_POST['festa_billing_first_name']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['festa_billing_first_name']))) : ''; ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="festa_billing_last_name"><?php esc_html_e('Apelido', 'EventsHouse'); ?></label></th>
                        <td><input name="festa_billing_last_name" id="festa_billing_last_name" type="text" class="regular-text" value="<?php echo isset($_POST['festa_billing_last_name']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['festa_billing_last_name']))) : ''; ?>" /></td>
                    </tr>
                </table>

                <h2 class="title"><?php esc_html_e('Caução (reembolsável)', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('A caução não é parcelada: o valor total entra no sinal (1.ª parte = 50% da festa + caução). A 2.ª parte fica só com os restantes 50% da festa. Deixe em branco ou 0 para não cobrar caução nesta encomenda.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="festa_caucao_valor"><?php esc_html_e('Valor da caução (€)', 'EventsHouse'); ?></label></th>
                        <td>
                            <?php
                            $caucao_display = isset($_POST['festa_caucao_valor'])
                                ? sanitize_text_field(wp_unslash($_POST['festa_caucao_valor']))
                                : EventsHouse_festa_order_default_caucao_value();
                            ?>
                            <input type="text" name="festa_caucao_valor" id="festa_caucao_valor" inputmode="decimal" autocomplete="off" value="<?php echo esc_attr($caucao_display); ?>" style="width:6em;" placeholder="50" />
                            <p class="description"><?php esc_html_e('Exemplo: festa 400€ + caução 50€ → sinal = 200€ + 50€ = 250€; valor festa = 200€.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2 class="title"><?php esc_html_e('Produtos — Festas na ilha', 'EventsHouse'); ?></h2>
                <?php if ($term && !empty($products)) : ?>
                    <p class="description">
                        <?php
                        printf(
                            /* translators: 1: category name, 2: slug, 3: term id */
                            esc_html__('Categoria: %1$s (slug %2$s, ID %3$d).', 'EventsHouse'),
                            esc_html($term->name),
                            esc_html($term->slug),
                            (int) $term->term_id
                        );
                        ?>
                        <br /><?php esc_html_e('Preço com desconto (opcional): preço unitário que vale só para esta encomenda, em vez do preço do produto. Deixe em branco para usar o preço normal.', 'EventsHouse'); ?>
                    </p>
                    <table class="widefat striped" style="max-width:920px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Produto', 'EventsHouse'); ?></th>
                                <th style="width:110px;"><?php esc_html_e('Preço', 'EventsHouse'); ?></th>
                                <th style="width:170px;"><?php esc_html_e('Preço com desconto (€)', 'EventsHouse'); ?></th>
                                <th style="width:100px;"><?php esc_html_e('Quantidade', 'EventsHouse'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($products as $p) {
                                $rows = EventsHouse_festa_order_expand_product_rows($p);
                                foreach ($rows as $r) {
                                    $key = $r['key'];
                                    $val = isset($_POST['festa_qty'][$key]) ? absint(wp_unslash($_POST['festa_qty'][$key])) : 0;
                                    $dp  = isset($_POST['festa_discount_price'][$key]) ? sanitize_text_field(wp_unslash($_POST['festa_discount_price'][$key])) : '';
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($r['label']); ?></td>
                                        <td><?php echo wp_kses_post(wc_price((float) $r['product']->get_price())); ?></td>
                                        <td>
                                            <input type="text" name="festa_discount_price[<?php echo esc_attr($key); ?>]" inputmode="decimal" autocomplete="off" value="<?php echo esc_attr($dp); ?>" style="width:6em;" placeholder="<?php esc_attr_e('opcional', 'EventsHouse'); ?>" />
                                        </td>
                                        <td>
                                            <input type="number" name="festa_qty[<?php echo esc_attr($key); ?>]" min="0" step="1" value="<?php echo (int) $val; ?>" style="width:5em;" />
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                <?php elseif ($term && empty($products)) : ?>
                    <div class="notice notice-warning inline"><p><?php esc_html_e('Não há produtos publicados nesta categoria. Pode continuar só com extras.', 'EventsHouse'); ?></p></div>
                <?php else : ?>
                    <div class="notice notice-warning inline"><p><?php esc_html_e('Categoria de produtos indisponível — não é possível listar produtos WooCommerce.', 'EventsHouse'); ?></p></div>
                <?php endif; ?>

                <h2 class="title" style="margin-top:28px;"><?php esc_html_e('Extras', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('Extras não são produtos da loja: indique o preço unitário (€) e a quantidade. Os valores somam-se ao total da encomenda e a descrição fica registada na nota da encomenda.', 'EventsHouse'); ?></p>
                <table class="widefat striped" style="max-width:920px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Extra', 'EventsHouse'); ?></th>
                            <th style="width:120px;"><?php esc_html_e('Preço (€)', 'EventsHouse'); ?></th>
                            <th style="width:100px;"><?php esc_html_e('Quantidade', 'EventsHouse'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($extras_def as $eid => $edef) {
                            $eid = sanitize_key($eid);
                            $pq = isset($_POST['festa_extra_qty'][$eid]) ? absint(wp_unslash($_POST['festa_extra_qty'][$eid])) : 0;
                            if (isset($_POST['festa_extra_price'][$eid])) {
                                $pp = sanitize_text_field(wp_unslash($_POST['festa_extra_price'][$eid]));
                            } else {
                                $pp = $edef['default_price'] !== '' ? $edef['default_price'] : '';
                            }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($edef['title']); ?></strong>
                                    <?php if ($edef['subtitle'] !== '') : ?>
                                        <br /><span class="description"><?php echo esc_html($edef['subtitle']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($edef['description'] !== '') : ?>
                                        <br /><?php echo esc_html($edef['description']); ?>
                                    <?php endif; ?>
                                    <?php if ($edef['footnote'] !== '') : ?>
                                        <br /><em class="description"><?php echo esc_html($edef['footnote']); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" name="festa_extra_price[<?php echo esc_attr($eid); ?>]" inputmode="decimal" autocomplete="off" value="<?php echo esc_attr($pp); ?>" style="width:6em;" placeholder="<?php esc_attr_e('ex.: 50', 'EventsHouse'); ?>" />
                                </td>
                                <td>
                                    <input type="number" name="festa_extra_qty[<?php echo esc_attr($eid); ?>]" min="0" step="1" value="<?php echo (int) $pq; ?>" style="width:5em;" />
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <h3 style="margin-top:20px;"><?php esc_html_e('Outros extras', 'EventsHouse'); ?></h3>
                <p class="description"><?php esc_html_e('Adicione extras livres (ex.: título «Bolo», descrição «Maria Bonita»). Linhas sem título ou com quantidade 0 são ignoradas.', 'EventsHouse'); ?></p>
                <?php
                $custom_rows = array();
                if (isset($_POST['festa_custom_extra']) && is_array($_POST['festa_custom_extra'])) {
                    foreach (wp_unslash($_POST['festa_custom_extra']) as $crow) {
                        if (!is_array($crow)) {
                            continue;
                        }
                        $custom_rows[] = array(
                            'title' => isset($crow['title']) ? sanitize_text_field($crow['title']) : '',
                            'desc'  => isset($crow['desc']) ? sanitize_text_field($crow['desc']) : '',
                            'price' => isset($crow['price']) ? sanitize_text_field($crow['price']) : '',
                            'qty'   => isset($crow['qty']) ? absint($crow['qty']) : 1,
                        );
                    }
                }
                if (empty($custom_rows)) {
                    $custom_rows[] = array('title' => '', 'desc' => '', 'price' => '', 'qty' => 1);
                }
                ?>
                <table class="widefat striped" style="max-width:920px;" id="eventshouse-festa-custom-extras">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Título', 'EventsHouse'); ?></th>
                            <th><?php esc_html_e('Descrição', 'EventsHouse'); ?></th>
                            <th style="width:120px;"><?php esc_html_e('Preço (€)', 'EventsHouse'); ?></th>
                            <th style="width:100px;"><?php esc_html_e('Quantidade', 'EventsHouse'); ?></th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($custom_rows as $ci => $crow) : ?>
                            <tr>
                                <td><input type="text" name="festa_custom_extra[<?php echo (int) $ci; ?>][title]" value="<?php echo esc_attr($crow['title']); ?>" class="regular-text" style="width:100%;" placeholder="<?php esc_attr_e('ex.: Bolo', 'EventsHouse'); ?>" /></td>
                                <td><input type="text" name="festa_custom_extra[<?php echo (int) $ci; ?>][desc]" value="<?php echo esc_attr($crow['desc']); ?>" class="regular-text" style="width:100%;" placeholder="<?php esc_attr_e('ex.: Maria Bonita', 'EventsHouse'); ?>" /></td>
                                <td><input type="text" name="festa_custom_extra[<?php echo (int) $ci; ?>][price]" value="<?php echo esc_attr($crow['price']); ?>" inputmode="decimal" autocomplete="off" style="width:6em;" placeholder="<?php esc_attr_e('ex.: 50', 'EventsHouse'); ?>" /></td>
                                <td><input type="number" name="festa_custom_extra[<?php echo (int) $ci; ?>][qty]" value="<?php echo (int) $crow['qty']; ?>" min="0" step="1" style="width:5em;" /></td>
                                <td><button type="button" class="button-link eventshouse-festa-remove-extra" aria-label="<?php esc_attr_e('Remover', 'EventsHouse'); ?>">&times;</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>
                    <button type="button" class="button" id="eventshouse-festa-add-extra"><?php esc_html_e('+ Adicionar outro extra', 'EventsHouse'); ?></button>
                </p>
                <script>
                (function(){
                    var table = document.getElementById('eventshouse-festa-custom-extras');
                    var addBtn = document.getElementById('eventshouse-festa-add-extra');
                    if (!table || !addBtn) return;
                    var tbody = table.querySelector('tbody');
                    var nextIndex = <?php echo (int) count($custom_rows); ?>;

                    addBtn.addEventListener('click', function(){
                        var tpl = tbody.querySelector('tr');
                        var row = tpl.cloneNode(true);
                        row.querySelectorAll('input').forEach(function(input){
                            input.name = input.name.replace(/\[\d+\]/, '[' + nextIndex + ']');
                            input.value = input.type === 'number' ? '1' : '';
                        });
                        nextIndex++;
                        tbody.appendChild(row);
                    });

                    tbody.addEventListener('click', function(e){
                        var btn = e.target.closest('.eventshouse-festa-remove-extra');
                        if (!btn) return;
                        var row = btn.closest('tr');
                        if (tbody.querySelectorAll('tr').length > 1) {
                            row.remove();
                        } else {
                            row.querySelectorAll('input').forEach(function(input){
                                input.value = input.type === 'number' ? '1' : '';
                            });
                        }
                    });
                })();
                </script>

                <p class="description">
                    <label>
                        <input type="checkbox" name="festa_send_client_email" value="1" <?php checked(!empty($_POST['festa_send_client_email'])); ?> />
                        <?php esc_html_e('Enviar email do sinal (50% da festa + caução) ao cliente já ao criar a encomenda. O email do valor festa (50%) é enviado automaticamente 72h antes do evento.', 'EventsHouse'); ?>
                    </label>
                </p>

                <p class="submit">
                    <button type="submit" name="eventshouse_festa_order_submit" class="button button-primary" value="1">
                        <?php esc_html_e('Criar encomendas de sinal + valor festa e obter links', 'EventsHouse'); ?>
                    </button>
                </p>
            </form>
    </div>
    <?php
}
