<?php
/**
 * Carrinho vazio: mensagem personalizada e grelha Manhãs / Verão / Festas.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string $slug Slug da página.
 * @return string
 */
function EventsHouse_empty_cart_page_url($slug)
{
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return get_permalink($page);
    }
    return home_url('/' . trim($slug, '/') . '/');
}

/**
 * Imagem de destaque da página ou URL de fallback.
 *
 * @param string $slug    Slug.
 * @param string $fallback URL tema.
 * @return string
 */
function EventsHouse_empty_cart_box_image($slug, $fallback = '')
{
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post && has_post_thumbnail($page)) {
        $url = get_the_post_thumbnail_url($page, 'medium_large');
        if ($url) {
            return $url;
        }
    }
    return $fallback;
}

/**
 * Caixas da grelha (Manhãs, Verão, Festas).
 *
 * @return array<int, array{title: string, url: string, image: string, excerpt: string}>
 */
function EventsHouse_get_empty_cart_boxes()
{
    $uri = defined('EventsHouse_URI') ? EventsHouse_URI : get_template_directory_uri();

    $festas_img = get_theme_mod('festas_promo_image', $uri . '/assets/images/hero-2.jpg');
    $manhas_img = get_theme_mod('manhas_promo_image', '');
    if ($manhas_img === '') {
        $manhas_img = $uri . '/assets/images/hero-1.jpg';
    }
    $verao_img = get_theme_mod('verao_ilha_hero_banner_url', $uri . '/assets/images/verao-na-ilha-banner.png');
    if (function_exists('EventsHouse_verao_ilha_default')) {
        $verao_img = get_theme_mod(
            'verao_ilha_hero_banner_url',
            EventsHouse_verao_ilha_default('verao_ilha_hero_banner_url')
        );
    }

    return array(
        array(
            'title'   => __('Manhãs na Ilha', 'EventsHouse'),
            'url'     => EventsHouse_empty_cart_page_url('manhas-na-ilha'),
            'image'   => EventsHouse_empty_cart_box_image('manhas-na-ilha', $manhas_img),
            'excerpt' => __('Passes e reservas para as manhãs na Ilha.', 'EventsHouse'),
        ),
        array(
            'title'   => __('Verão na Ilha', 'EventsHouse'),
            'url'     => EventsHouse_empty_cart_page_url('verao-na-ilha'),
            'image'   => EventsHouse_empty_cart_box_image('verao-na-ilha', $verao_img),
            'excerpt' => __('Programa de verão com atividades para crianças.', 'EventsHouse'),
        ),
        array(
            'title'   => __('Festas na Ilha', 'EventsHouse'),
            'url'     => EventsHouse_empty_cart_page_url('orcamento'),
            'image'   => EventsHouse_empty_cart_box_image('orcamento', $festas_img),
            'excerpt' => __('Pacotes e preços para festas de aniversário.', 'EventsHouse'),
        ),
    );
}

/**
 * HTML do carrinho vazio.
 */
function EventsHouse_render_empty_cart()
{
    $boxes = EventsHouse_get_empty_cart_boxes();
    ?>
    <div class="eh-cart-empty">
        <p class="eh-cart-empty__message">
            <?php esc_html_e('seu carrinho está vazio, aproveite e escolha um de nossos produtos', 'EventsHouse'); ?>
        </p>
        <div class="eh-cart-empty__grid">
            <?php foreach ($boxes as $box) : ?>
                <a class="eh-cart-empty__card" href="<?php echo esc_url($box['url']); ?>">
                    <span class="eh-cart-empty__card-media"<?php echo $box['image'] !== '' ? ' style="background-image:url(' . esc_url($box['image']) . ');"' : ''; ?>></span>
                    <span class="eh-cart-empty__card-body">
                        <span class="eh-cart-empty__card-title"><?php echo esc_html($box['title']); ?></span>
                        <?php if ($box['excerpt'] !== '') : ?>
                            <span class="eh-cart-empty__card-text"><?php echo esc_html($box['excerpt']); ?></span>
                        <?php endif; ?>
                        <span class="eh-cart-empty__card-cta"><?php esc_html_e('Explorar', 'EventsHouse'); ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Remove conteúdo padrão do WooCommerce (mensagem EN, «New in store», produtos).
 */
function EventsHouse_setup_empty_cart_hooks()
{
    if (!function_exists('is_cart') || !is_cart() || !function_exists('WC') || !WC()->cart || !WC()->cart->is_empty()) {
        return;
    }

    remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);

    if (function_exists('wc_empty_cart_products')) {
        remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_products', 20);
    }

    remove_all_actions('woocommerce_cart_is_empty');
    add_action('woocommerce_cart_is_empty', 'EventsHouse_render_empty_cart', 5);
}
add_action('wp', 'EventsHouse_setup_empty_cart_hooks', 20);

/**
 * @return string
 */
function EventsHouse_empty_cart_message_filter()
{
    return '';
}
add_filter('wc_empty_cart_message', 'EventsHouse_empty_cart_message_filter');

/**
 * Estilos só no carrinho vazio.
 */
function EventsHouse_enqueue_empty_cart_assets()
{
    if (!function_exists('is_cart') || !is_cart() || !function_exists('WC') || !WC()->cart || !WC()->cart->is_empty()) {
        return;
    }
    wp_enqueue_style(
        'eventshouse-cart-empty',
        EventsHouse_URI . '/assets/css/cart-empty.css',
        array('EventsHouse-main'),
        EventsHouse_VERSION
    );
}
add_action('wp_enqueue_scripts', 'EventsHouse_enqueue_empty_cart_assets', 35);

/**
 * Classe no body para estilos só com carrinho vazio.
 *
 * @param array $classes Classes.
 * @return array
 */
function EventsHouse_empty_cart_body_class($classes)
{
    if (function_exists('is_cart') && is_cart() && function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
        $classes[] = 'eh-cart-is-empty';
    }
    return $classes;
}
add_filter('body_class', 'EventsHouse_empty_cart_body_class');
