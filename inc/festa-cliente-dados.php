<?php
/**
 * Dados do cliente (CC, origem, finalidade, NIF) na página «Confirmar reserva» (order-pay).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove a página legada /dados-reserva/ (formulário passou para order-pay).
 */
function EventsHouse_festa_cliente_dados_retire_legacy_page()
{
    if (get_option('eventshouse_dados_reserva_retired_v1') === '1') {
        return;
    }

    $existing = get_page_by_path('dados-reserva');
    if ($existing instanceof WP_Post) {
        wp_update_post(array(
            'ID'          => $existing->ID,
            'post_status' => 'draft',
        ));
    }

    update_option('eventshouse_dados_reserva_retired_v1', '1', true);
}
add_action('admin_init', 'EventsHouse_festa_cliente_dados_retire_legacy_page');

/**
 * Indica se a encomenda precisa do formulário de dados na confirmação (sinal).
 *
 * @param WC_Order $order
 * @return bool
 */
function EventsHouse_festa_cliente_dados_needs_form($order)
{
    if (!$order instanceof WC_Order) {
        return false;
    }
    if ((string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
        return false;
    }
    if ((string) $order->get_meta('_eventshouse_payment_stage', true) !== 'deposit') {
        return false;
    }
    return !EventsHouse_festa_cliente_dados_is_submitted($order);
}

/**
 * Indica se os dados do cliente já foram submetidos nesta encomenda.
 *
 * @param WC_Order $order
 * @return bool
 */
function EventsHouse_festa_cliente_dados_is_submitted($order)
{
    if (!$order instanceof WC_Order) {
        return false;
    }
    return (string) $order->get_meta('_eventshouse_cliente_dados_status', true) === 'submitted';
}

/**
 * URL da página Confirmar reserva (= link de pagamento WooCommerce).
 *
 * @param WC_Order $order
 * @return string
 */
function EventsHouse_festa_cliente_dados_url($order)
{
    if (!$order instanceof WC_Order) {
        return '';
    }
    return $order->get_checkout_payment_url();
}

/**
 * Bloco de texto para o email do sinal (vazio noutras fases).
 *
 * @param WC_Order $order
 * @return array{link:string,bloco:string}
 */
function EventsHouse_festa_cliente_dados_email_parts($order)
{
    $empty = array('link' => '', 'bloco' => '');
    if (!$order instanceof WC_Order) {
        return $empty;
    }

    $stage = (string) $order->get_meta('_eventshouse_payment_stage', true);
    if ($stage !== 'deposit') {
        return $empty;
    }

    $link = EventsHouse_festa_cliente_dados_url($order);
    if ($link === '') {
        return $empty;
    }

    $bloco = 'Na página Confirmar reserva poderá preencher os dados pedidos (cópia do CC, como chegou até nós, finalidade do evento e NIF na fatura).';

    return array(
        'link'  => $link,
        'bloco' => $bloco,
    );
}

/**
 * Opções «Como chegou até nós?».
 *
 * @return array<string,string>
 */
function EventsHouse_festa_cliente_dados_how_found_options()
{
    return array(
        'site'          => __('Site', 'EventsHouse'),
        'redes-sociais' => __('Redes Sociais', 'EventsHouse'),
        'motores-busca' => __('Motores de busca (google, etc)', 'EventsHouse'),
        'referencia'    => __('Referência de família ou amigos', 'EventsHouse'),
        'olx'           => __('OLX', 'EventsHouse'),
    );
}

/**
 * Opções «Finalidade do evento».
 *
 * @return array<string,string>
 */
function EventsHouse_festa_cliente_dados_purpose_options()
{
    return array(
        'aniversario-0-3'    => __('Festa de Aniversário (0 - 3 anos)', 'EventsHouse'),
        'aniversario-4-10'   => __('Festa de Aniversário (4 - 10 anos)', 'EventsHouse'),
        'baby-shower'        => __('Baby Shower / Chá do Bebé', 'EventsHouse'),
        'revelacao-genero'   => __('Revelação de Género / Chá de Revelação', 'EventsHouse'),
        'batizado'           => __('Batizado', 'EventsHouse'),
        'aniversario-adulto' => __('Festa de Aniversário Adulto', 'EventsHouse'),
    );
}

/**
 * Renderiza o bloco de dados na coluna esquerda do order-pay.
 *
 * @param WC_Order $order
 */
function EventsHouse_festa_cliente_dados_render_pay_fields($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }

    if (EventsHouse_festa_cliente_dados_is_submitted($order)
        && (string) $order->get_meta('_eventshouse_payment_stage', true) === 'deposit'
    ) {
        echo '<section class="eh-pay-dados eh-pay-dados--done">';
        echo '<h3>' . esc_html__('Dados da reserva', 'EventsHouse') . '</h3>';
        echo '<p class="eh-pay-dados-done-msg">' . esc_html__('Os seus dados já foram recebidos. Obrigado!', 'EventsHouse') . '</p>';
        echo '</section>';
        return;
    }

    if (!EventsHouse_festa_cliente_dados_needs_form($order)) {
        return;
    }

    $how_found_options     = EventsHouse_festa_cliente_dados_how_found_options();
    $event_purpose_options = EventsHouse_festa_cliente_dados_purpose_options();
    ?>
    <section class="eh-pay-dados" id="eh-pay-dados">
        <h3><?php esc_html_e('Dados da reserva', 'EventsHouse'); ?></h3>
        <p class="eh-pay-dados-intro"><?php esc_html_e('Antes de confirmar o pagamento, complete estes dados.', 'EventsHouse'); ?></p>

        <div class="eh-bform eh-pay-dados-fields">
            <div class="form-group eh-upload-group">
                <label for="eh-dados-cc"><?php esc_html_e('Cópia do CC *', 'EventsHouse'); ?></label>
                <input type="file" id="eh-dados-cc" name="cc_files[]" class="eh-file-input" required multiple accept=".jpg,.jpeg,.png,.pdf,.webp,image/*,application/pdf" data-max-files="5" data-max-size-mb="10">
                <small class="eh-field-hint"><?php esc_html_e('Carregue até 5 ficheiros suportados. Máximo de 10 MB por ficheiro.', 'EventsHouse'); ?></small>
                <div class="eh-file-list" aria-live="polite"></div>
            </div>

            <div class="form-group eh-radio-group">
                <label><?php esc_html_e('Como chegou até nós? *', 'EventsHouse'); ?></label>
                <div class="eh-radio-list">
                    <?php foreach ($how_found_options as $val => $label) : ?>
                    <label class="eh-radio-option">
                        <input type="radio" name="how_found" value="<?php echo esc_attr($val); ?>" required>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group eh-radio-group">
                <label><?php esc_html_e('Qual é a finalidade do evento? *', 'EventsHouse'); ?></label>
                <div class="eh-radio-list">
                    <?php foreach ($event_purpose_options as $val => $label) : ?>
                    <label class="eh-radio-option">
                        <input type="radio" name="event_purpose" value="<?php echo esc_attr($val); ?>" required>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                    <label class="eh-radio-option eh-radio-option--other">
                        <input type="radio" name="event_purpose" value="outra" required class="eh-other-radio">
                        <span><?php esc_html_e('Outra:', 'EventsHouse'); ?></span>
                        <input type="text" name="event_purpose_other" class="eh-other-input" placeholder="<?php esc_attr_e('Especifique…', 'EventsHouse'); ?>" disabled>
                    </label>
                </div>
            </div>

            <div class="form-group eh-radio-group eh-nif-group">
                <label><?php esc_html_e('Deseja colocar NIF na fatura? *', 'EventsHouse'); ?></label>
                <div class="eh-radio-list eh-radio-list--inline">
                    <label class="eh-radio-option">
                        <input type="radio" name="wants_nif" value="sim" required>
                        <span><?php esc_html_e('Sim', 'EventsHouse'); ?></span>
                    </label>
                    <label class="eh-radio-option">
                        <input type="radio" name="wants_nif" value="nao" required>
                        <span><?php esc_html_e('Não', 'EventsHouse'); ?></span>
                    </label>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Assets na página Confirmar reserva (order-pay).
 */
function EventsHouse_festa_cliente_dados_scripts()
{
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-pay')) {
        return;
    }

    wp_enqueue_style(
        'eventshouse-calendar',
        EventsHouse_URI . '/assets/css/calendar.css',
        array(),
        EventsHouse_VERSION
    );
    wp_enqueue_script(
        'eventshouse-festa-cliente-dados',
        EventsHouse_URI . '/assets/js/festa-cliente-dados.js',
        array(),
        EventsHouse_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'EventsHouse_festa_cliente_dados_scripts');

/**
 * Título da endpoint order-pay → «Confirmar reserva».
 *
 * @param string $title
 * @return string
 */
function EventsHouse_festa_cliente_dados_endpoint_title($title)
{
    return __('Confirmar reserva', 'EventsHouse');
}
add_filter('woocommerce_endpoint_order-pay_title', 'EventsHouse_festa_cliente_dados_endpoint_title');

/**
 * Texto do botão de pagamento na order-pay.
 *
 * @param string $text
 * @return string
 */
function EventsHouse_festa_cliente_dados_pay_button_text($text)
{
    return __('Confirmar reserva', 'EventsHouse');
}
add_filter('woocommerce_pay_order_button_text', 'EventsHouse_festa_cliente_dados_pay_button_text');

/**
 * Valida e grava os dados do cliente ao confirmar o pagamento do sinal.
 *
 * @param WC_Order $order
 */
function EventsHouse_festa_cliente_dados_process_on_pay($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if (!EventsHouse_festa_cliente_dados_needs_form($order)) {
        return;
    }

    $result = EventsHouse_festa_cliente_dados_save_from_request($order);
    if (is_wp_error($result)) {
        wc_add_notice($result->get_error_message(), 'error');
        throw new Exception($result->get_error_message());
    }
}
add_action('woocommerce_before_pay_action', 'EventsHouse_festa_cliente_dados_process_on_pay', 5);

/**
 * Lê POST/FILES, valida, grava meta e notifica a equipa.
 *
 * @param WC_Order $order
 * @return true|WP_Error
 */
function EventsHouse_festa_cliente_dados_save_from_request($order)
{
    if (!$order instanceof WC_Order) {
        return new WP_Error('bad_order', __('Encomenda inválida.', 'EventsHouse'));
    }

    $how_found           = sanitize_text_field(wp_unslash($_POST['how_found'] ?? ''));
    $event_purpose       = sanitize_text_field(wp_unslash($_POST['event_purpose'] ?? ''));
    $event_purpose_other = sanitize_text_field(wp_unslash($_POST['event_purpose_other'] ?? ''));
    $wants_nif           = sanitize_text_field(wp_unslash($_POST['wants_nif'] ?? ''));

    if ($how_found === '') {
        return new WP_Error('how_found', __('Por favor indique como chegou até nós.', 'EventsHouse'));
    }
    if ($event_purpose === '') {
        return new WP_Error('purpose', __('Por favor indique a finalidade do evento.', 'EventsHouse'));
    }
    if ($event_purpose === 'outra' && trim($event_purpose_other) === '') {
        return new WP_Error('purpose_other', __('Por favor especifique a finalidade do evento.', 'EventsHouse'));
    }
    if ($wants_nif !== 'sim' && $wants_nif !== 'nao') {
        return new WP_Error('nif', __('Por favor indique se deseja colocar NIF na fatura.', 'EventsHouse'));
    }

    $cc_upload_result = EventsHouse_handle_availability_cc_uploads();
    if (!empty($cc_upload_result['error'])) {
        $cc_error_messages = array(
            'missing'   => __('Por favor carregue a cópia do CC.', 'EventsHouse'),
            'too_many'  => __('Pode carregar no máximo 5 ficheiros na Cópia do CC.', 'EventsHouse'),
            'too_large' => __('Cada ficheiro deve ter no máximo 10 MB.', 'EventsHouse'),
            'invalid'   => __('Não foi possível processar os ficheiros da Cópia do CC. Verifique o formato (JPG, PNG, WEBP ou PDF).', 'EventsHouse'),
        );
        $error_key = $cc_upload_result['error'];
        return new WP_Error(
            'cc',
            isset($cc_error_messages[$error_key])
                ? $cc_error_messages[$error_key]
                : __('Não foi possível carregar a cópia do CC.', 'EventsHouse')
        );
    }
    $cc_files = $cc_upload_result['files'];

    $how_found_labels     = function_exists('EventsHouse_availability_how_found_labels')
        ? EventsHouse_availability_how_found_labels()
        : EventsHouse_festa_cliente_dados_how_found_options();
    $event_purpose_labels = function_exists('EventsHouse_availability_event_purpose_labels')
        ? EventsHouse_availability_event_purpose_labels()
        : EventsHouse_festa_cliente_dados_purpose_options();

    $how_found_display = isset($how_found_labels[$how_found]) ? $how_found_labels[$how_found] : $how_found;
    if ($event_purpose === 'outra') {
        $event_purpose_display = $event_purpose_other !== '' ? $event_purpose_other : 'Outra';
    } else {
        $event_purpose_display = isset($event_purpose_labels[$event_purpose])
            ? $event_purpose_labels[$event_purpose]
            : $event_purpose;
    }
    $wants_nif_display = $wants_nif === 'sim' ? 'Sim' : 'Não';
    $cc_files_display  = !empty($cc_files) ? implode(', ', array_map('wp_basename', $cc_files)) : 'Nenhum ficheiro';

    $order->update_meta_data('_eventshouse_cliente_dados_status', 'submitted');
    $order->update_meta_data('_eventshouse_cliente_dados_submitted_at', current_time('mysql'));
    $order->update_meta_data('_eventshouse_cliente_how_found', $how_found);
    $order->update_meta_data('_eventshouse_cliente_how_found_label', $how_found_display);
    $order->update_meta_data('_eventshouse_cliente_event_purpose', $event_purpose);
    $order->update_meta_data('_eventshouse_cliente_event_purpose_other', $event_purpose_other);
    $order->update_meta_data('_eventshouse_cliente_event_purpose_label', $event_purpose_display);
    $order->update_meta_data('_eventshouse_cliente_wants_nif', $wants_nif);
    $order->update_meta_data('_eventshouse_cliente_cc_filenames', $cc_files_display);
    $order->add_order_note(
        sprintf(
            /* translators: 1: how found, 2: purpose, 3: nif, 4: files */
            __('Dados da reserva recebidos — Como chegou: %1$s | Finalidade: %2$s | NIF na fatura: %3$s | CC: %4$s (em anexo no email à equipa).', 'EventsHouse'),
            $how_found_display,
            $event_purpose_display,
            $wants_nif_display,
            $cc_files_display
        ),
        false,
        true
    );
    $order->save();

    $related_id = absint($order->get_meta('_eventshouse_related_order_id', true));
    if ($related_id > 0) {
        $related = wc_get_order($related_id);
        if ($related instanceof WC_Order) {
            $related->update_meta_data('_eventshouse_cliente_dados_status', 'submitted');
            $related->update_meta_data('_eventshouse_cliente_how_found_label', $how_found_display);
            $related->update_meta_data('_eventshouse_cliente_event_purpose_label', $event_purpose_display);
            $related->update_meta_data('_eventshouse_cliente_wants_nif', $wants_nif);
            $related->update_meta_data('_eventshouse_cliente_cc_filenames', $cc_files_display);
            $related->add_order_note(
                sprintf(
                    __('Dados da reserva recebidos na encomenda de sinal #%s.', 'EventsHouse'),
                    function_exists('EventsHouse_festa_order_plain_order_number')
                        ? EventsHouse_festa_order_plain_order_number($order)
                        : (string) $order->get_order_number()
                ),
                false,
                true
            );
            $related->save();
        }
    }

    EventsHouse_festa_cliente_dados_notify_admin(
        $order,
        $how_found_display,
        $event_purpose_display,
        $wants_nif_display,
        $cc_files_display,
        $cc_files
    );

    if (!empty($cc_files)) {
        foreach ($cc_files as $cc_file) {
            @unlink($cc_file);
        }
    }

    return true;
}

/**
 * Email à equipa com os dados e anexos CC.
 *
 * @param WC_Order $order
 * @param string   $how_found_display
 * @param string   $event_purpose_display
 * @param string   $wants_nif_display
 * @param string   $cc_files_display
 * @param string[] $cc_files
 */
function EventsHouse_festa_cliente_dados_notify_admin(
    $order,
    $how_found_display,
    $event_purpose_display,
    $wants_nif_display,
    $cc_files_display,
    array $cc_files
) {
    $to = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
    if (!is_email($to)) {
        $to = EventsHouse_DEFAULT_CONTACT_EMAIL;
    }

    $customer_name  = trim($order->get_formatted_billing_full_name());
    $customer_email = (string) $order->get_billing_email();
    $order_number   = function_exists('EventsHouse_festa_order_plain_order_number')
        ? EventsHouse_festa_order_plain_order_number($order)
        : (string) $order->get_order_number();

    $subject = sprintf(
        /* translators: %s: order number */
        __('[A ilha] Dados da reserva — Encomenda #%s', 'EventsHouse'),
        $order_number
    );

    $body_inner = '<p>' . esc_html__('O cliente preencheu os dados na página Confirmar reserva (sinal).', 'EventsHouse') . '</p>'
        . '<p><strong>' . esc_html__('Encomenda:', 'EventsHouse') . '</strong> #' . esc_html($order_number) . '<br>'
        . '<strong>' . esc_html__('Cliente:', 'EventsHouse') . '</strong> ' . esc_html($customer_name !== '' ? $customer_name : '—') . '<br>'
        . '<strong>' . esc_html__('Email:', 'EventsHouse') . '</strong> ' . esc_html($customer_email) . '</p>'
        . '<p><strong>' . esc_html__('Como chegou até nós:', 'EventsHouse') . '</strong> ' . esc_html($how_found_display) . '<br>'
        . '<strong>' . esc_html__('Finalidade do evento:', 'EventsHouse') . '</strong> ' . esc_html($event_purpose_display) . '<br>'
        . '<strong>' . esc_html__('NIF na fatura:', 'EventsHouse') . '</strong> ' . esc_html($wants_nif_display) . '<br>'
        . '<strong>' . esc_html__('Cópia do CC:', 'EventsHouse') . '</strong> ' . esc_html($cc_files_display) . ' '
        . esc_html__('(em anexo)', 'EventsHouse') . '</p>';

    $body = function_exists('EventsHouse_build_email_template_html')
        ? EventsHouse_build_email_template_html(__('Dados da reserva', 'EventsHouse'), $body_inner)
        : $body_inner;

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if (is_email($customer_email)) {
        $headers[] = sprintf(
            'Reply-To: %s <%s>',
            $customer_name !== '' ? $customer_name : $customer_email,
            $customer_email
        );
    }

    wp_mail($to, $subject, $body, $headers, $cc_files);
}

/**
 * Metabox na encomenda WooCommerce com os dados recebidos.
 *
 * @param WC_Order $order
 */
function EventsHouse_festa_cliente_dados_admin_metabox($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if ((string) $order->get_meta('_eventshouse_festa_generated', true) !== 'yes') {
        return;
    }

    $status = (string) $order->get_meta('_eventshouse_cliente_dados_status', true);
    if ($status === '' && (string) $order->get_meta('_eventshouse_payment_stage', true) !== 'deposit') {
        if ((string) $order->get_meta('_eventshouse_cliente_how_found_label', true) === '') {
            return;
        }
    }

    echo '<div class="address" style="margin-top:12px;">';
    echo '<p><strong>' . esc_html__('Dados da reserva (Confirmar reserva)', 'EventsHouse') . '</strong></p>';

    if ($status === 'submitted' || (string) $order->get_meta('_eventshouse_cliente_how_found_label', true) !== '') {
        $submitted_at = (string) $order->get_meta('_eventshouse_cliente_dados_submitted_at', true);
        echo '<p>';
        echo esc_html__('Como chegou até nós:', 'EventsHouse') . ' '
            . esc_html((string) $order->get_meta('_eventshouse_cliente_how_found_label', true)) . '<br>';
        echo esc_html__('Finalidade do evento:', 'EventsHouse') . ' '
            . esc_html((string) $order->get_meta('_eventshouse_cliente_event_purpose_label', true)) . '<br>';
        $nif = (string) $order->get_meta('_eventshouse_cliente_wants_nif', true);
        echo esc_html__('NIF na fatura:', 'EventsHouse') . ' '
            . esc_html($nif === 'sim' ? __('Sim', 'EventsHouse') : ($nif === 'nao' ? __('Não', 'EventsHouse') : '—')) . '<br>';
        echo esc_html__('Cópia do CC:', 'EventsHouse') . ' '
            . esc_html((string) $order->get_meta('_eventshouse_cliente_cc_filenames', true) ?: '—');
        if ($submitted_at !== '') {
            echo '<br>' . esc_html__('Recebido em:', 'EventsHouse') . ' ' . esc_html($submitted_at);
        }
        echo '</p>';
    } else {
        $link = EventsHouse_festa_cliente_dados_url($order);
        echo '<p>' . esc_html__('Ainda não preenchido.', 'EventsHouse');
        if ($link !== '') {
            echo '<br><a href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">'
                . esc_html__('Abrir Confirmar reserva', 'EventsHouse') . '</a>';
        }
        echo '</p>';
    }
    echo '</div>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'EventsHouse_festa_cliente_dados_admin_metabox', 20);
