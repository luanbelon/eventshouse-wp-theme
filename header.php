<?php
/**
 * Header Template
 *
 * @package EventsHouse
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="EventsHouse - Espaço de Eventos Infantis em Lisboa. Festas de aniversário, eventos e muita diversão para crianças!">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5C3T48ZP"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <header id="site-header" class="site-header">
        <div class="header-container">
            <!-- Logo -->
            <div class="header-logo">
                <?php $logo_width = get_theme_mod('logo_max_width', 200); ?>
                <style>
                    .header-logo img.custom-logo { max-width: <?php echo esc_attr($logo_width); ?>px; height: auto; display: block; }
                </style>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link" aria-label="EventsHouse Home">
                    <?php if (has_custom_logo()): ?>
                        <?php 
                            $custom_logo_id = get_theme_mod('custom_logo');
                            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
                            echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '" class="custom-logo">';
                        ?>
                    <?php else: ?>
                        <div class="logo-text">
                            <span class="logo-fradi">Events</span><span class="logo-festas">House</span>
                        </div>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Navigation -->
            <nav id="main-navigation" class="main-navigation" aria-label="Menu Principal">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'nav-menu',
                    'container' => false,
                    'fallback_cb' => false,
                ));
                ?>
            </nav>

            <div class="header-end">
                <?php if (class_exists('WooCommerce')) : ?>
                <div class="header-woo-icons">
                    <?php
                    $eh_account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '';
                    $eh_cart_url    = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';
                    if ($eh_account_url) :
                        ?>
                    <a href="<?php echo esc_url($eh_account_url); ?>" class="header-woo-icon header-woo-account" aria-label="<?php esc_attr_e('Minha conta', 'EventsHouse'); ?>" title="<?php esc_attr_e('Minha conta', 'EventsHouse'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>
                        <?php
                    endif;
                    if ($eh_cart_url) :
                        $eh_cart_count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
                        ?>
                    <a href="<?php echo esc_url($eh_cart_url); ?>" class="header-woo-icon header-woo-cart" aria-label="<?php esc_attr_e('Carrinho', 'EventsHouse'); ?>" title="<?php esc_attr_e('Carrinho', 'EventsHouse'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="header-cart-count"><?php echo $eh_cart_count ? esc_html((string) $eh_cart_count) : ''; ?></span>
                    </a>
                        <?php
                    endif;
                    ?>
                </div>
                <?php endif; ?>

                <!-- CTA Button -->
                <div class="header-cta">
                    <a href="<?php echo esc_url(home_url('/disponibilidade')); ?>" class="btn btn-cta">
                        <span>Reservar Festa</span>
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-label="Abrir Menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>

        <!-- Mobile Navigation Overlay -->
        <div id="mobile-nav-overlay" class="mobile-nav-overlay">
            <nav class="mobile-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'mobile-nav-menu',
                    'container' => false,
                    'fallback_cb' => false,
                ));
                ?>
                <a href="<?php echo esc_url(home_url('/disponibilidade')); ?>" class="btn btn-cta mobile-cta">Pedir
                    Orçamento</a>
            </nav>
        </div>
    </header>
