<?php
/**
 * WooCommerce: locale pt_PT no carrinho/checkout e ajustes de i18n.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Deteta carrinho / checkout / sub-rotas só pelo REQUEST_URI (sem get_permalink / is_cart).
 * Usar get_permalink() ou is_cart() dentro de determine_locale causa recursão
 * (traduções → determine_locale → permalink → …) e esgota a memória (503).
 */
function EventsHouse_is_woo_cart_checkout_context()
{
    static $result = null;
    if ($result !== null) {
        return $result;
    }

    $req = '';
    if (!empty($_SERVER['REQUEST_URI'])) {
        $path = wp_parse_url(wp_unslash((string) $_SERVER['REQUEST_URI']), PHP_URL_PATH);
        $req  = is_string($path) ? strtolower(trim($path, '/')) : '';
    }

    // Slugs habituais PT/EN do WooCommerce — sem queries à BD.
    $needles = array(
        'carrinho',
        'cart',
        'finalizar-compra',
        'checkout',
        'encomenda',
        'order-pay',
        'order-received',
        'minha-conta/encomendas',
        'my-account/view-order',
    );

    foreach ($needles as $needle) {
        if ($req !== '' && strpos($req, $needle) !== false) {
            $result = true;
            return true;
        }
    }

    if (!empty($_GET['order-received']) || !empty($_GET['pay_for_order'])) {
        $result = true;
        return true;
    }

    $result = false;
    return false;
}

/**
 * Força Português (Portugal) nas páginas de carrinho e checkout para carregar
 * woocommerce-pt_PT.mo (wp-content/languages/plugins/woocommerce-pt_PT.mo).
 *
 * IMPORTANTE: não chamar is_cart()/is_checkout()/get_permalink() aqui —
 * determine_locale corre muito cedo e isso gera loop + fatal de memória.
 */
function EventsHouse_woocommerce_determine_locale_pt_pt($locale)
{
    if (is_admin() && !wp_doing_ajax()) {
        return $locale;
    }

    if (!class_exists('WooCommerce')) {
        return $locale;
    }

    if (EventsHouse_is_woo_cart_checkout_context()) {
        return 'pt_PT';
    }

    return $locale;
}
add_filter('determine_locale', 'EventsHouse_woocommerce_determine_locale_pt_pt', 99);

/**
 * Texto por omissão do checkbox obrigatório (documentos de contratação) no checkout.
 *
 * @return string
 */
function EventsHouse_checkout_contract_checkbox_label_default()
{
    return __('Confirmo que vi e li os seguintes documentos de contratação:', 'EventsHouse');
}

/**
 * Texto introdutório por omissão antes da lista de documentos (Personalizar).
 *
 * @return string
 */
function EventsHouse_checkout_contract_intro_default()
{
    return __('Confirmo que vi e li os seguintes documentos de contratação:', 'EventsHouse');
}

/**
 * Monta o HTML do checkbox a partir do texto introdutório e dos 4 documentos (título + link).
 *
 * @return string HTML sanitizado ou string vazia se nenhum documento tiver título.
 */
function EventsHouse_build_contract_docs_checkbox_label_html()
{
    $intro_mod = get_theme_mod('eh_checkout_contract_intro', '');
    $intro     = ($intro_mod === '' || $intro_mod === null)
        ? EventsHouse_checkout_contract_intro_default()
        : trim((string) $intro_mod);

    $pieces  = array();
    $has_doc = false;

    for ($i = 1; $i <= 4; $i++) {
        $title = trim((string) get_theme_mod("eh_checkout_doc_{$i}_title", ''));
        $url   = trim((string) get_theme_mod("eh_checkout_doc_{$i}_url", ''));
        if ($title === '') {
            continue;
        }
        $has_doc = true;
        if ($url !== '') {
            $pieces[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($title) . '</a>';
        } else {
            $pieces[] = esc_html($title);
        }
    }

    if (!$has_doc) {
        return '';
    }

    $html = esc_html($intro) . ' ' . implode(', ', $pieces) . '.';

    return EventsHouse_sanitize_checkout_contract_checkbox_label($html);
}

/**
 * HTML só com links dos documentos (sem texto introdutório).
 *
 * @return string
 */
function EventsHouse_build_contract_docs_links_html()
{
    $pieces  = array();
    $has_doc = false;

    for ($i = 1; $i <= 4; $i++) {
        $title = trim((string) get_theme_mod("eh_checkout_doc_{$i}_title", ''));
        $url   = trim((string) get_theme_mod("eh_checkout_doc_{$i}_url", ''));
        if ($title === '') {
            continue;
        }
        $has_doc = true;
        if ($url !== '') {
            $pieces[] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($title) . '</a>';
        } else {
            $pieces[] = esc_html($title);
        }
    }

    if (!$has_doc) {
        return '';
    }

    return implode(', ', $pieces) . '.';
}

/**
 * Personalizar: texto do checkbox (permite HTML simples para links aos Termos, etc.).
 *
 * @param string $value Valor bruto.
 * @return string
 */
function EventsHouse_sanitize_checkout_contract_checkbox_label($value)
{
    $allowed = array(
        'a'      => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array(),
            'rel'    => array(),
            'class'  => array(),
        ),
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'span'   => array('class' => array()),
    );

    return wp_kses((string) $value, $allowed);
}

/**
 * Label do checkbox para o formulário clássico (HTML permitido via Personalizar).
 *
 * @return string
 */
function EventsHouse_get_checkout_contract_checkbox_label_html()
{
    $built = EventsHouse_build_contract_docs_checkbox_label_html();
    if ($built !== '') {
        return $built;
    }

    $raw = get_theme_mod('eh_checkout_contract_checkbox_label', '');
    if ($raw === '' || $raw === null) {
        $raw = EventsHouse_checkout_contract_checkbox_label_default();
    }

    return EventsHouse_sanitize_checkout_contract_checkbox_label($raw);
}

/**
 * Versão só texto (checkout em blocos / APIs).
 *
 * @return string
 */
function EventsHouse_get_checkout_contract_checkbox_label_plain()
{
    return wp_strip_all_tags(EventsHouse_get_checkout_contract_checkbox_label_html());
}

/**
 * Sanitiza o texto introdutório do checkbox (Personalizar).
 *
 * @param string $value Valor bruto.
 * @return string
 */
function EventsHouse_sanitize_checkout_contract_intro($value)
{
    return sanitize_textarea_field((string) $value);
}

/**
 * Personalizar — secção Checkout: documentos legais e mensagem do checkbox.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function EventsHouse_woocommerce_customize_register($wp_customize)
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    $wp_customize->add_section('eventshouse_woocommerce_checkout', array(
        'title'       => __('Finalizar compra — documentos legais', 'EventsHouse'),
        'description' => __('Configure o texto do checkbox obrigatório na página de checkout. Preencha o texto introdutório e até quatro documentos (título e link). Os links abrem num novo separador.', 'EventsHouse'),
        'priority'    => 165,
    ));

    $wp_customize->add_setting('eh_checkout_contract_checkbox_label', array(
        'default'              => EventsHouse_checkout_contract_checkbox_label_default(),
        'sanitize_callback'    => 'EventsHouse_sanitize_checkout_contract_checkbox_label',
        'transport'            => 'refresh',
    ));

    $wp_customize->add_setting('eh_checkout_contract_intro', array(
        'default'           => EventsHouse_checkout_contract_intro_default(),
        'sanitize_callback' => 'EventsHouse_sanitize_checkout_contract_intro',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('eh_checkout_contract_intro', array(
        'label'       => __('Texto antes dos documentos', 'EventsHouse'),
        'description' => __('Aparece antes dos links. Por omissão: confirmação de leitura dos documentos de contratação.', 'EventsHouse'),
        'section'     => 'eventshouse_woocommerce_checkout',
        'type'        => 'textarea',
    ));

    for ($i = 1; $i <= 4; $i++) {
        $wp_customize->add_setting("eh_checkout_doc_{$i}_title", array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_setting("eh_checkout_doc_{$i}_url", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ));

        $wp_customize->add_control("eh_checkout_doc_{$i}_title", array(
            /* translators: %d: document number */
            'label'   => sprintf(__('Documento %d — título', 'EventsHouse'), $i),
            'section' => 'eventshouse_woocommerce_checkout',
            'type'    => 'text',
        ));

        $wp_customize->add_control("eh_checkout_doc_{$i}_url", array(
            /* translators: %d: document number */
            'label'       => sprintf(__('Documento %d — link', 'EventsHouse'), $i),
            'description' => __('URL completo (https://…). PDF ou página.', 'EventsHouse'),
            'section'     => 'eventshouse_woocommerce_checkout',
            'type'        => 'url',
        ));
    }
}
add_action('customize_register', 'EventsHouse_woocommerce_customize_register', 20);

/**
 * Campo checkbox no checkout clássico (shortcode [woocommerce_checkout]).
 *
 * @param array $fields Campos do checkout.
 * @return array
 */
function EventsHouse_checkout_add_contract_checkbox_field($fields)
{
    if (!is_array($fields) || !isset($fields['order'])) {
        return $fields;
    }

    $fields['order']['eh_contract_docs_accept'] = array(
        'type'        => 'checkbox',
        'label'       => __('Confirmo que vi e li os documentos de contratação.', 'EventsHouse'),
        'required'    => false,
        'class'       => array('form-row-wide', 'eh-contract-checkout-field'),
        'clear'       => true,
        'priority'    => 25,
        'label_class' => array('woocommerce-form__label-for-checkbox'),
        'input_class' => array('woocommerce-form__input-checkbox'),
    );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'EventsHouse_checkout_add_contract_checkbox_field', 20);

/**
 * Checkout em blocos: força opção «telefone obrigatório» (WooCommerce → Definições).
 *
 * @param mixed $value Valor da opção.
 * @return string
 */
function EventsHouse_checkout_phone_field_required_option($value)
{
    return 'required';
}
add_filter('pre_option_woocommerce_checkout_phone_field', 'EventsHouse_checkout_phone_field_required_option');

/**
 * Campos de morada por omissão: telefone obrigatório.
 *
 * @param array $fields Campos.
 * @return array
 */
function EventsHouse_checkout_default_address_phone_required($fields)
{
    if (isset($fields['phone'])) {
        $fields['phone']['required'] = true;
    }
    return $fields;
}
add_filter('woocommerce_default_address_fields', 'EventsHouse_checkout_default_address_phone_required', 20);

/**
 * Checkout clássico: billing_phone obrigatório.
 *
 * @param array $fields Campos de facturação.
 * @return array
 */
function EventsHouse_checkout_billing_phone_required($fields)
{
    if (isset($fields['billing_phone'])) {
        $fields['billing_phone']['required'] = true;
    }
    return $fields;
}
add_filter('woocommerce_billing_fields', 'EventsHouse_checkout_billing_phone_required', 20);

/**
 * Checkout clássico (shortcode): billing_phone obrigatório.
 *
 * @param array $fields Campos do checkout.
 * @return array
 */
function EventsHouse_checkout_fields_billing_phone_required($fields)
{
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['required'] = true;
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'EventsHouse_checkout_fields_billing_phone_required', 5);

/**
 * Extrai telefone dos dados submetidos no checkout.
 *
 * @param array $data Dados do checkout.
 * @return string
 */
function EventsHouse_checkout_extract_phone_from_data(array $data)
{
    $phone = trim((string) ($data['billing_phone'] ?? ''));
    if ($phone === '' && !empty($_POST['billing_phone'])) {
        $phone = sanitize_text_field(wp_unslash($_POST['billing_phone']));
    }
    if ($phone === '' && !empty($data['phone'])) {
        $phone = sanitize_text_field((string) $data['phone']);
    }
    if ($phone === '' && function_exists('EventsHouse_wc_event_collect_cart_contact')) {
        $contact = EventsHouse_wc_event_collect_cart_contact();
        $phone   = $contact['phone'];
    }
    return $phone;
}

/**
 * Validação checkout clássico: telefone obrigatório.
 *
 * @param array    $data   Dados.
 * @param WP_Error $errors Erros.
 */
function EventsHouse_checkout_validate_phone_required($data, $errors)
{
    if (!$errors instanceof WP_Error) {
        return;
    }
    if (EventsHouse_checkout_extract_phone_from_data(is_array($data) ? $data : array()) !== '') {
        return;
    }
    $errors->add(
        'billing_phone_required',
        __('Indique o seu número de telefone para concluir a encomenda.', 'EventsHouse')
    );
}
add_action('woocommerce_after_checkout_validation', 'EventsHouse_checkout_validate_phone_required', 12, 2);

/**
 * Checkout em blocos (Store API): telefone obrigatório na morada de facturação.
 *
 * @param WC_Order        $order   Encomenda.
 * @param WP_REST_Request $request Pedido REST.
 */
function EventsHouse_checkout_store_api_validate_phone($order, $request)
{
    if (!$request instanceof WP_REST_Request) {
        return;
    }
    $billing = $request->get_param('billing_address');
    if (!is_array($billing)) {
        return;
    }
    $phone = trim((string) ($billing['phone'] ?? ''));
    if ($phone !== '') {
        return;
    }
    if (function_exists('EventsHouse_wc_event_collect_cart_contact')) {
        $contact = EventsHouse_wc_event_collect_cart_contact();
        if ($contact['phone'] !== '') {
            return;
        }
    }
    $message = __('Indique o seu número de telefone para concluir a encomenda.', 'EventsHouse');
    if (class_exists('\Automattic\WooCommerce\StoreApi\Exceptions\RouteException')) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'eventshouse_billing_phone_required',
            $message,
            400
        );
    }
}
add_action('woocommerce_store_api_checkout_update_order_from_request', 'EventsHouse_checkout_store_api_validate_phone', 5, 2);

/**
 * Validação checkout clássico.
 *
 * @param array    $data   Dados.
 * @param WP_Error $errors Erros.
 */
function EventsHouse_checkout_validate_contract_checkbox($data, $errors)
{
    if (empty($_POST['eh_contract_docs_accept'])) {
        $errors->add(
            'eh_contract_docs',
            __('Tem de confirmar que leu os documentos de contratação para finalizar a encomenda.', 'EventsHouse')
        );
    }
}
add_action('woocommerce_after_checkout_validation', 'EventsHouse_checkout_validate_contract_checkbox', 10, 2);

/**
 * Grava confirmação na encomenda (checkout clássico).
 *
 * @param WC_Order $order Objeto da encomenda.
 * @param array    $data  Dados (internos).
 */
function EventsHouse_checkout_save_contract_acceptance_on_create($order, $data)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if (!empty($_POST['eh_contract_docs_accept'])) {
        $order->update_meta_data('_eh_contract_docs_accepted', 'yes');
        $order->update_meta_data('_eh_contract_docs_accepted_at', current_time('mysql'));
    }
}
add_action('woocommerce_checkout_create_order', 'EventsHouse_checkout_save_contract_acceptance_on_create', 20, 2);

/**
 * Checkout em blocos: regista o mesmo checkbox (WooCommerce 8.3+).
 */
function EventsHouse_register_checkout_contract_block_field()
{
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }

    // No checkout em blocos, o label do checkbox é texto simples (sem HTML clicável).
    $plain = __('Confirmo que vi e li os documentos de contratação.', 'EventsHouse');

    woocommerce_register_additional_checkout_field(
        array(
            'id'            => 'eventshouse/contract-docs-accept',
            'label'         => $plain,
            'optionalLabel' => $plain,
            'type'          => 'checkbox',
            'location'      => 'contact',
            'required'      => true,
        )
    );
}
add_action('woocommerce_init', 'EventsHouse_register_checkout_contract_block_field', 20);

/**
 * Checkout: injeta lista horizontal de documentos imediatamente abaixo do checkbox.
 */
function EventsHouse_checkout_render_docs_below_checkbox_script()
{
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    $links = EventsHouse_build_contract_docs_links_html();
    if ($links === '') {
        return;
    }
    $links_html = '<div class="eh-contract-docs-inline">' . $links . '</div>';
    ?>
    <script>
    (function () {
        var html = <?php echo wp_json_encode($links_html); ?>;
        function placeDocs() {
            if (document.querySelector('.eh-contract-docs-inline')) return;
            var cands = Array.prototype.slice.call(document.querySelectorAll('label, .wc-block-components-checkbox__label, .woocommerce-input-wrapper'));
            var target = null;
            for (var i = 0; i < cands.length; i++) {
                var t = (cands[i].textContent || '').toLowerCase();
                if (t.indexOf('confirmo que vi e li os documentos') !== -1) {
                    target = cands[i];
                    break;
                }
            }
            if (!target) return;
            var row = target.closest('p, .wc-block-components-checkbox, .wc-block-components-checkout-step__content, .form-row, .wc-block-components-address-form') || target.parentElement;
            if (!row || !row.parentNode) return;
            row.insertAdjacentHTML('afterend', html);
        }
        placeDocs();
        var obs = new MutationObserver(placeDocs);
        obs.observe(document.body, { childList: true, subtree: true });
        setTimeout(function(){ obs.disconnect(); }, 12000);
    })();
    </script>
    <?php
}
add_action('wp_footer', 'EventsHouse_checkout_render_docs_below_checkbox_script', 99);

/**
 * Após encomenda via Store API: sincroniza meta se o Woo gravou o campo adicional.
 *
 * @param WC_Order $order Encomenda.
 */
function EventsHouse_store_api_sync_contract_acceptance($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_eh_contract_docs_accepted') === 'yes') {
        return;
    }

    foreach ($order->get_meta_data() as $meta) {
        $data = $meta->get_data();
        $key  = isset($data['key']) ? (string) $data['key'] : '';
        if ($key === '') {
            continue;
        }
        if (stripos($key, 'contract-docs-accept') === false && stripos($key, 'contract_docs') === false) {
            continue;
        }
        $val = $data['value'] ?? null;
        if ($val === '1' || $val === 1 || $val === true || $val === 'yes') {
            $order->update_meta_data('_eh_contract_docs_accepted', 'yes');
            if (!$order->get_meta('_eh_contract_docs_accepted_at')) {
                $order->update_meta_data('_eh_contract_docs_accepted_at', current_time('mysql'));
            }
            $order->save();
            return;
        }
    }
}
add_action('woocommerce_store_api_checkout_order_processed', 'EventsHouse_store_api_sync_contract_acceptance', 50, 1);

/**
 * Backstop: grava a partir de posted_data no checkout processado (alguns gateways).
 *
 * @param int      $order_id   ID da encomenda.
 * @param array    $posted_data Dados publicados.
 * @param WC_Order $order      Encomenda.
 */
function EventsHouse_checkout_order_processed_contract($order_id, $posted_data, $order)
{
    if (!$order instanceof WC_Order) {
        $order = wc_get_order($order_id);
    }
    if (!$order) {
        return;
    }
    if (!empty($posted_data['eh_contract_docs_accept'])) {
        $order->update_meta_data('_eh_contract_docs_accepted', 'yes');
        if (!$order->get_meta('_eh_contract_docs_accepted_at')) {
            $order->update_meta_data('_eh_contract_docs_accepted_at', current_time('mysql'));
        }
        $order->save();
    }
}
add_action('woocommerce_checkout_order_processed', 'EventsHouse_checkout_order_processed_contract', 20, 3);

/**
 * Admin: mostrar confirmação na página da encomenda.
 *
 * @param WC_Order $order Encomenda.
 */
function EventsHouse_admin_order_display_contract_acceptance($order)
{
    if (!$order instanceof WC_Order) {
        return;
    }
    if ($order->get_meta('_eh_contract_docs_accepted') !== 'yes') {
        return;
    }
    $when = $order->get_meta('_eh_contract_docs_accepted_at');
    echo '<p><strong>' . esc_html__('Documentos de contratação', 'EventsHouse') . ':</strong> ';
    echo esc_html__('Cliente confirmou no checkout', 'EventsHouse');
    if ($when) {
        echo ' <span style="color:#666;">(' . esc_html($when) . ')</span>';
    }
    echo '</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'EventsHouse_admin_order_display_contract_acceptance', 15, 1);

/**
 * Email de encomenda: linha com a confirmação.
 *
 * @param WC_Order $order         Encomenda.
 * @param bool     $sent_to_admin Se o email é para admin.
 * @param bool     $plain_text    Texto simples.
 * @param WC_Email $email         Objeto email.
 */
function EventsHouse_email_order_contract_line($order, $sent_to_admin, $plain_text, $email)
{
    if (!$order instanceof WC_Order || $order->get_meta('_eh_contract_docs_accepted') !== 'yes') {
        return;
    }
    if ($plain_text) {
        echo "\n" . esc_html__('Documentos de contratação: confirmado pelo cliente no checkout.', 'EventsHouse') . "\n";
        return;
    }
    echo '<p style="margin:12px 0;"><strong>' . esc_html__('Documentos de contratação', 'EventsHouse') . ':</strong> ';
    echo esc_html__('Confirmado no checkout', 'EventsHouse');
    echo '</p>';
}
add_action('woocommerce_email_after_order_table', 'EventsHouse_email_order_contract_line', 15, 4);

/**
 * Atualiza o contador do carrinho no cabeçalho após add-to-cart AJAX.
 *
 * @param array $fragments Fragmentos HTML.
 * @return array
 */
function EventsHouse_header_cart_count_fragment($fragments)
{
    if (!function_exists('WC') || !WC()->cart) {
        return $fragments;
    }

    $count = (int) WC()->cart->get_cart_contents_count();
    $inner = $count > 0 ? esc_html((string) $count) : '';

    $fragments['.header-cart-count'] = '<span class="header-cart-count">' . $inner . '</span>';

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'EventsHouse_header_cart_count_fragment', 10, 1);
