<?php
/**
 * Otimizações de performance (LCP, fontes, JS).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Preconnect aos domínios das Google Fonts (reduz latência do primeiro byte).
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Tipo.
 * @return array
 */
function EventsHouse_resource_hints_fonts($urls, $relation_type)
{
    if ('preconnect' !== $relation_type) {
        return $urls;
    }

    $urls[] = 'https://fonts.googleapis.com';
    $urls[] = array(
        'href'        => 'https://fonts.gstatic.com',
        'crossorigin' => 'anonymous',
    );

    return $urls;
}
add_filter('wp_resource_hints', 'EventsHouse_resource_hints_fonts', 10, 2);

/**
 * CSS das Google Fonts sem bloquear a renderização (media print → all após load).
 *
 * @param string $tag    Tag HTML.
 * @param string $handle Handle do estilo.
 * @return string
 */
function EventsHouse_async_google_fonts_css($tag, $handle)
{
    if ('EventsHouse-google-fonts' !== $handle) {
        return $tag;
    }

    if (strpos($tag, "media='all'") !== false) {
        return str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
    }
    if (strpos($tag, 'media="all"') !== false) {
        return str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $tag);
    }

    return $tag;
}
add_filter('style_loader_tag', 'EventsHouse_async_google_fonts_css', 10, 2);

/**
 * defer no script principal do tema (não bloqueia parsing).
 *
 * @param string $tag    Tag HTML.
 * @param string $handle Handle.
 * @return string
 */
function EventsHouse_defer_main_script($tag, $handle)
{
    if ('EventsHouse-main' !== $handle || false !== strpos($tag, ' defer')) {
        return $tag;
    }

    return str_replace(' src', ' defer src', $tag);
}
add_filter('script_loader_tag', 'EventsHouse_defer_main_script', 10, 2);

/**
 * URL da primeira imagem do hero (igual à lógica em front-page.php).
 *
 * @return string
 */
function EventsHouse_get_first_hero_slide_image_url()
{
    $defaults = array(
        1 => EventsHouse_URI . '/assets/images/hero-1.jpg',
    );
    $url = get_theme_mod('hero_slide_1_image', '');
    if ($url === '' || $url === null) {
        $url = $defaults[1];
    }

    return $url;
}

/**
 * Preload da imagem LCP provável (primeiro slide do hero na página inicial).
 */
function EventsHouse_preload_lcp_hero_image()
{
    if (!is_front_page()) {
        return;
    }

    $url = EventsHouse_get_first_hero_slide_image_url();
    if ($url === '') {
        return;
    }

    printf(
        '<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
        esc_url($url)
    );
}
add_action('wp_head', 'EventsHouse_preload_lcp_hero_image', 1);
