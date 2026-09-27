<?php
/**
 * The Template for displaying all single products
 *
 * Override to provide a clean and custom design for passes and packs.
 * Produto tipo «event»: mini-hero + 2 colunas (sobre | preço/data) + secção formulário.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();
    global $product;

    if (!$product instanceof WC_Product) {
        continue;
    }

    $is_event = ($product->get_type() === 'event');

    if ($is_event) {
        $evt_ui       = EventsHouse_get_event_ui_strings();
        $evt_config   = function_exists('EventsHouse_event_get_config') ? EventsHouse_event_get_config($product) : array();
        $max_extras   = function_exists('EventsHouse_wc_event_get_max_extras') ? EventsHouse_wc_event_get_max_extras($product) : 10;
        $adult_extra  = function_exists('EventsHouse_wc_event_get_adult_extra_price') ? EventsHouse_wc_event_get_adult_extra_price($product) : 0;
        $child_extra  = function_exists('EventsHouse_wc_event_get_child_extra_price') ? EventsHouse_wc_event_get_child_extra_price($product) : 0;
        $free_max_age = absint($evt_config['pricing']['child_free_max_age'] ?? 3);
        $evt_day      = function_exists('EventsHouse_event_format_day_line') ? EventsHouse_event_format_day_line($evt_config) : '';
        $evt_time     = function_exists('EventsHouse_event_format_time_range') ? EventsHouse_event_format_time_range($evt_config) : '';
        $evt_location = function_exists('EventsHouse_event_get_location_from_config') ? EventsHouse_event_get_location_from_config($evt_config) : '';
        $has_base     = function_exists('EventsHouse_event_has_base_price') ? EventsHouse_event_has_base_price($evt_config) : true;
        $pricing_rows = function_exists('EventsHouse_event_pricing_table_rows') ? EventsHouse_event_pricing_table_rows($evt_config, (float) wc_get_price_to_display($product), $product) : array();
        $event_date   = function_exists('EventsHouse_event_get_event_date') ? EventsHouse_event_get_event_date($product) : '';
        $base_num     = function_exists('EventsHouse_event_get_display_base_price')
            ? EventsHouse_event_get_display_base_price($evt_config, (float) wc_get_price_to_display($product))
            : (float) wc_get_price_to_display($product);
        $cart_url     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';
        $sym          = html_entity_decode(wp_strip_all_tags(get_woocommerce_currency_symbol()), ENT_QUOTES, 'UTF-8');
        $banner_style = '';
        $image_id     = $product->get_image_id();
        if ($image_id) {
            $banner_url = wp_get_attachment_image_url($image_id, 'large');
            if ($banner_url) {
                $banner_style = '--woo-event-banner-image: url(' . esc_url($banner_url) . ');';
            }
        }
        ?>
<main id="product-<?php the_ID(); ?>" <?php wc_product_class('site-main custom-woo-single woo-single--event', $product); ?>>
    <?php do_action('woocommerce_before_single_product'); ?>
    <?php woocommerce_output_all_notices(); ?>

    <div class="woo-event-hero-mini"<?php echo $banner_style !== '' ? ' style="' . esc_attr($banner_style) . '"' : ''; ?> role="img" aria-label="<?php echo esc_attr(get_the_title()); ?>"></div>

    <div class="container woo-event-page">
        <div class="woo-event-split">
            <article class="woo-event-about">
                <div class="woo-event-about__card">
                    <h1 class="woo-event-about__product-title"><?php the_title(); ?></h1>
                    <h2 class="woo-event-about__heading"><?php echo esc_html($evt_ui['about_heading']); ?></h2>
                    <div class="woo-single-description">
                        <?php
                        if ($product->get_short_description()) {
                            echo apply_filters('woocommerce_short_description', $product->get_short_description()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                            the_content();
                        }
                        ?>
                    </div>
                </div>
            </article>

            <aside class="woo-event-info">
                <div class="woo-event-info__card">
                    <div class="woo-event-info__fixed">
                        <?php if ($evt_day !== '') : ?>
                            <p class="woo-event-info__line woo-event-info__day">
                                <span class="woo-event-info__icon" aria-hidden="true">🗓️</span>
                                <?php echo esc_html($evt_day); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($evt_time !== '') : ?>
                            <p class="woo-event-info__line woo-event-info__time">
                                <span class="woo-event-info__icon" aria-hidden="true">⏰</span>
                                <?php echo esc_html($evt_time); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($evt_location !== '') : ?>
                            <p class="woo-event-info__line woo-event-info__location">
                                <span class="woo-event-info__icon" aria-hidden="true">📍</span>
                                <?php echo esc_html($evt_location); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <form
                        class="eh-event-quick-cart-form"
                        method="post"
                        action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                    >
                        <?php if ($pricing_rows !== array()) : ?>
                            <div class="eh-event-pricing-table">
                                <p class="eh-event-pricing-table__title"><?php echo esc_html($evt_ui['price_table_title'] ?? __('Valores', 'EventsHouse')); ?></p>
                                <ul class="eh-event-pricing-table__list">
                                    <?php foreach ($pricing_rows as $row) : ?>
                                        <li class="eh-event-pricing-table__row eh-event-pricing-table__row--<?php echo esc_attr($row['icon']); ?>">
                                            <span class="eh-event-pricing-table__label"><?php echo esc_html($row['label']); ?></span>
                                            <span class="eh-event-pricing-table__amount"><?php echo wp_kses_post($row['amount']); ?></span>
                                            <?php if (!empty($row['stepper'])) : ?>
                                                <div class="eh-event-qty-stepper" data-qty-key="<?php echo esc_attr($row['stepper']); ?>">
                                                    <button type="button" class="button eh-event-qty-stepper__btn eh-event-qty-stepper__btn--minus" data-qty-key="<?php echo esc_attr($row['stepper']); ?>" aria-label="-">−</button>
                                                    <span class="eh-event-qty-value" data-qty-key="<?php echo esc_attr($row['stepper']); ?>">0</span>
                                                    <button type="button" class="button eh-event-qty-stepper__btn eh-event-qty-stepper__btn--plus" data-qty-key="<?php echo esc_attr($row['stepper']); ?>" aria-label="+">+</button>
                                                    <input type="hidden" class="eh-event-qty-input" name="eh_evt_qty_<?php echo esc_attr($row['stepper']); ?>" data-qty-key="<?php echo esc_attr($row['stepper']); ?>" value="0" />
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div
                            class="eh-event-pricing"
                            data-base="<?php echo esc_attr((string) $base_num); ?>"
                            data-has-base-price="<?php echo $has_base ? '1' : '0'; ?>"
                            data-adult-extra="<?php echo esc_attr((string) $adult_extra); ?>"
                            data-child-extra="<?php echo esc_attr((string) $child_extra); ?>"
                            data-free-max-age="<?php echo esc_attr((string) $free_max_age); ?>"
                            data-event-date="<?php echo esc_attr($event_date); ?>"
                            data-max="<?php echo esc_attr((string) $max_extras); ?>"
                            data-decimals="<?php echo esc_attr((string) wc_get_price_decimals()); ?>"
                            data-decimal-sep="<?php echo esc_attr(wc_get_price_decimal_separator()); ?>"
                            data-thousand-sep="<?php echo esc_attr(wc_get_price_thousand_separator()); ?>"
                            data-currency-symbol="<?php echo esc_attr($sym); ?>"
                        >
                            <p class="eh-event-pricing__total">
                                <strong><?php echo esc_html($evt_ui['total_estimated_label']); ?>:</strong>
                                <span class="eh-event-live-total"><?php echo wp_kses_post(wc_price($base_num)); ?></span>
                            </p>
                        </div>

                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr((string) $product->get_id()); ?>" />
                        <input type="hidden" name="eh_evt_quick_checkout" value="1" />
                        <input type="hidden" name="quantity" value="1" />

                        <div class="woo-event-enroll-wrap">
                            <button type="button" class="button woo-event-enroll-btn" id="woo-event-enroll-submit">
                                <?php echo esc_html($evt_ui['enroll_button']); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </aside>
        </div>
    </div>

    <?php if (function_exists('EventsHouse_event_registration_form_enabled') && EventsHouse_event_registration_form_enabled()) : ?>
    <section id="woo-event-registration" class="woo-event-form-section woo-event-form-section--hidden" aria-hidden="true">
        <div class="container woo-event-form-section__inner">
            <h2 class="woo-event-form-section__title"><?php echo esc_html($evt_ui['form_section_title']); ?></h2>
            <div class="woo-single-action woo-event-booking__form">
                <?php
                if (function_exists('EventsHouse_wc_event_add_to_cart_template')) {
                    EventsHouse_wc_event_add_to_cart_template();
                } else {
                    do_action('woocommerce_event_add_to_cart');
                }
                ?>
            </div>
            <p class="woo-single-footer">
                <a href="javascript:history.back()" class="btn-back">← <?php echo esc_html($evt_ui['back_link']); ?></a>
            </p>
        </div>
    </section>
    <?php endif; ?>

    <?php do_action('woocommerce_after_single_product'); ?>
</main>
        <?php
    } else {
        ?>
<main class="site-main custom-woo-single">
    <div class="woo-single-banner">
        <div class="woo-banner-content">
            <h1><?php the_title(); ?></h1>
            <p class="woo-single-price"><?php echo wp_kses_post($product->get_price_html()); ?></p>
        </div>
    </div>

    <div class="woo-single-container">
        <div class="woo-single-card">

            <div class="woo-single-description">
                <?php
                if ($product->get_short_description()) {
                    echo apply_filters('woocommerce_short_description', $product->get_short_description()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    the_content();
                }
                ?>
            </div>

            <div class="woo-single-action">
                <?php do_action('woocommerce_' . $product->get_type() . '_add_to_cart'); ?>
            </div>

            <?php do_action('woocommerce_before_single_product'); ?>
        </div>

        <div class="woo-single-footer">
            <a href="javascript:history.back()" class="btn-back">⬅ Voltar</a>
        </div>
    </div>
</main>

<style>
.custom-woo-single {
    background-color: var(--EventsHouse-light-gray, #f9f9fa);
    min-height: 80vh;
    padding-bottom: 80px;
}
.woo-single-banner {
    background: linear-gradient(135deg, var(--EventsHouse-primary-color, #75c9b7) 0%, #5eb0a0 100%);
    padding: 80px 20px 100px;
    text-align: center;
    color: #fff;
}
.woo-banner-content h1 {
    font-size: 42px;
    font-weight: 800;
    margin: 0 0 15px 0;
    color: #fff;
}
.woo-single-price {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    opacity: 0.9;
}
.woo-single-price ins, .woo-single-price .amount {
    text-decoration: none;
    background: transparent;
    color: #fff;
}
.woo-single-price del {
    opacity: 0.6;
    font-size: 20px;
}
.woo-single-container {
    max-width: 700px;
    margin: -60px auto 0;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}
.woo-single-card {
    background: #fff;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}
.woo-single-description {
    font-size: 16px;
    line-height: 1.8;
    color: var(--EventsHouse-text-color, #4a4a4a);
    margin-bottom: 30px;
}
.woo-single-description ul {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}
.woo-single-description ul li {
    position: relative;
    padding-left: 30px;
    margin-bottom: 15px;
}
.woo-single-description ul li:before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 0;
    color: var(--EventsHouse-secondary-color, #f0a2b0);
    font-weight: bold;
    font-size: 18px;
}
.woo-single-action {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}
.woo-single-action form.cart {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    justify-content: center;
}
.woo-single-action form.cart .quantity { display: flex; }
.woo-single-action form.cart .quantity input.qty {
    width: 70px;
    height: 50px;
    border: 2px solid #eaeded;
    border-radius: 12px;
    text-align: center;
    font-size: 18px;
    font-family: inherit;
    font-weight: 600;
    color: var(--EventsHouse-text-dark, #2c3e50);
}
.woo-single-action form.cart button.single_add_to_cart_button {
    background: var(--EventsHouse-secondary-color, #f0a2b0);
    color: white;
    font-size: 18px;
    font-weight: 700;
    padding: 0 30px;
    height: 50px;
    border-radius: 25px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(240, 162, 176, 0.4);
}
.woo-single-action form.cart button.single_add_to_cart_button:hover {
    background: #d98a97;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(240, 162, 176, 0.5);
}
.woo-single-footer { text-align: center; margin-top: 30px; }
.btn-back {
    color: var(--EventsHouse-text-color, #4a4a4a);
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: color 0.2s;
}
.btn-back:hover { color: var(--EventsHouse-primary-color, #75c9b7); }
.woocommerce-notices-wrapper { margin-bottom: 20px; }
.woocommerce-notices-wrapper .woocommerce-message {
    background: #f0fdf4;
    color: #166534;
    padding: 15px 20px;
    border-radius: 10px;
    border: 1px solid #bbf7d0;
    margin-bottom: 20px;
    font-weight: 500;
}
.woocommerce-error {
    background: #fef2f2;
    color: #991b1b;
    padding: 15px 20px;
    list-style: none;
    border-radius: 10px;
    border: 1px solid #fecaca;
    margin-bottom: 20px;
}
@media (max-width: 768px) {
    .woo-banner-content h1 { font-size: 32px; }
    .woo-single-card { padding: 30px 20px; }
    .woo-single-action form.cart { flex-direction: column; }
    .woo-single-action form.cart button.single_add_to_cart_button { width: 100%; }
}
</style>
        <?php
    }
endwhile;

get_footer();
