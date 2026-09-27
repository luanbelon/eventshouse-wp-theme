<?php
/**
 * Empty cart page — A Ilha (UI personalizada via hook EventsHouse_render_empty_cart).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

/*
 * @hooked EventsHouse_render_empty_cart - 5 (inc/woocommerce-cart-empty.php)
 */
do_action('woocommerce_cart_is_empty');

// Sem link «Return to shop»: o tema redireciona /shop/ e mostra grelha Manhãs / Verão / Festas.
