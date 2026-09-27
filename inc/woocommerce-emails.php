<?php
/**
 * WooCommerce: emails de encomenda com o mesmo visual dos emails do tema.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cor de marca dos emails (opção do tema).
 */
function EventsHouse_wc_email_brand_color()
{
    $brand = trim(EventsHouse_email_opt('eventshouse_email_brand_color'));
    if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $brand)) {
        $brand = '#1f6fb2';
    }
    return $brand;
}

/**
 * URL do logótipo nos emails.
 */
function EventsHouse_wc_email_logo_url()
{
    $custom_logo_id = absint(get_theme_mod('custom_logo'));
    if ($custom_logo_id) {
        $url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($url) {
            return $url;
        }
    }
    return EventsHouse_URI . '/assets/images/logo.png';
}

/**
 * Texto do rodapé dos emails WooCommerce.
 */
function EventsHouse_wc_email_footer_text()
{
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $footer_tpl = EventsHouse_email_opt('eventshouse_email_footer_note');
    return EventsHouse_email_replace_vars($footer_tpl, array('site_name' => $site_name));
}

/**
 * Sincroniza a cor base do WooCommerce com a opção do tema.
 *
 * @param string $color Cor actual.
 * @return string
 */
function EventsHouse_wc_email_sync_base_color($color)
{
    return EventsHouse_wc_email_brand_color();
}
add_filter('woocommerce_email_base_color', 'EventsHouse_wc_email_sync_base_color');

add_filter('woocommerce_email_background_color', function () {
    return '#eef3f8';
});
add_filter('woocommerce_email_body_background_color', function () {
    return '#ffffff';
});
add_filter('woocommerce_email_text_color', function () {
    return '#273444';
});
add_filter('woocommerce_email_footer_text_color', function () {
    return '#64748b';
});

/**
 * Rodapé dos emails WooCommerce (fallback se o template usar o filtro core).
 *
 * @param string        $text  Texto por omissão.
 * @param WC_Email|null $email Instância do email.
 * @return string
 */
function EventsHouse_wc_email_footer_text_filter($text, $email)
{
    $custom = EventsHouse_wc_email_footer_text();
    return $custom !== '' ? $custom : $text;
}
add_filter('woocommerce_email_footer_text', 'EventsHouse_wc_email_footer_text_filter', 10, 2);

/**
 * Emails para a loja (ex.: nova encomenda → geral@ailha.pt) em HTML, igual aos do cliente.
 *
 * @param WC_Email $email Instância do email.
 */
function EventsHouse_wc_email_force_html_before_send($email)
{
    if (!$email instanceof WC_Email) {
        return;
    }
    if ($email->is_customer_email()) {
        return;
    }
    $email->email_type = 'html';
}
add_action('woocommerce_email_before_send', 'EventsHouse_wc_email_force_html_before_send', 1, 1);

/**
 * Garante tipo HTML nas definições guardadas dos emails de administração.
 *
 * @param mixed $value Valor da opção.
 * @return mixed
 */
function EventsHouse_wc_email_force_html_option($value)
{
    if (!is_array($value)) {
        return $value;
    }
    $value['email_type'] = 'html';
    return $value;
}

$eventshouse_wc_admin_email_options = array(
    'woocommerce_new_order_settings',
    'woocommerce_cancelled_order_settings',
    'woocommerce_failed_order_settings',
);
foreach ($eventshouse_wc_admin_email_options as $eventshouse_wc_opt) {
    add_filter('pre_option_' . $eventshouse_wc_opt, 'EventsHouse_wc_email_force_html_option');
}
