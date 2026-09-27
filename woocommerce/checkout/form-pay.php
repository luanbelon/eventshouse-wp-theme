<?php
/**
 * Pay for order form — «Confirmar reserva» (EventsHouse).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.9.0
 */

defined('ABSPATH') || exit;

$is_festa_order = ($order instanceof WC_Order) && ((string) $order->get_meta('_eventshouse_festa_generated', true) === 'yes');
$product_rows   = array();
$extra_rows     = array();
$other_fees     = array();

if ($order instanceof WC_Order) {
    foreach ($order->get_items(array('line_item', 'fee')) as $item) {
        if ($item instanceof WC_Order_Item_Product) {
            $product_rows[] = $item;
            continue;
        }

        if (!$item instanceof WC_Order_Item_Fee) {
            continue;
        }

        $fee_name = trim((string) $item->get_name());
        if ($is_festa_order && stripos($fee_name, 'Ajuste de parcela:') === 0) {
            continue;
        }
        if ($is_festa_order && stripos($fee_name, 'Extra:') === 0) {
            $extra_rows[] = $item;
            continue;
        }
        $other_fees[] = $item;
    }
}

$needs_dados = $order instanceof WC_Order
    && function_exists('EventsHouse_festa_cliente_dados_needs_form')
    && EventsHouse_festa_cliente_dados_needs_form($order);

do_action('woocommerce_before_pay_form', $order);
?>

<form id="order_review" method="post"<?php echo $needs_dados ? ' enctype="multipart/form-data"' : ''; ?>>
    <div class="eh-pay-grid<?php echo $is_festa_order ? ' eh-pay-grid--festa' : ''; ?>">
        <div class="eh-pay-main">
            <section class="eh-pay-summary">
                <h3><?php esc_html_e('Resumo da cobrança', 'EventsHouse'); ?></h3>
                <table class="shop_table">
                    <thead>
                        <tr>
                            <th class="product-name"><?php esc_html_e('Produto', 'woocommerce'); ?></th>
                            <th class="product-quantity"><?php esc_html_e('Qtd', 'woocommerce'); ?></th>
                            <th class="product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_rows as $item_id => $item) : ?>
                            <?php
                            if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
                                continue;
                            }
                            $product = $item->get_product();
                            $qty     = $item->get_quantity();
                            ?>
                            <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
                                <td class="product-name">
                                    <?php
                                    echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));
                                    do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
                                    wc_display_item_meta($item);
                                    do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
                                    ?>
                                </td>
                                <td class="product-quantity"><?php echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', esc_html($qty)) . '</strong>', $item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                <td class="product-subtotal"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!empty($extra_rows)) : ?>
                            <tr class="eh-extra-group">
                                <td class="product-name">
                                    <strong><?php esc_html_e('Extras', 'EventsHouse'); ?></strong>
                                    <ul class="eh-extra-sublist">
                                        <?php foreach ($extra_rows as $extra_item) : ?>
                                            <li><?php echo esc_html($extra_item->get_name()); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td class="product-quantity">-</td>
                                <td class="product-total">
                                    <?php
                                    $extras_total = 0.0;
                                    foreach ($extra_rows as $extra_item) {
                                        $extras_total += (float) $extra_item->get_total();
                                    }
                                    echo wp_kses_post(wc_price($extras_total, array('currency' => $order->get_currency())));
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($other_fees as $fee_item) : ?>
                            <tr>
                                <td class="product-name"><?php echo esc_html($fee_item->get_name()); ?></td>
                                <td class="product-quantity">-</td>
                                <td class="product-total"><?php echo wp_kses_post(wc_price((float) $fee_item->get_total(), array('currency' => $order->get_currency()))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php
                        $full_total = (float) $order->get_meta('_eventshouse_full_total', true);
                        $stage      = (string) $order->get_meta('_eventshouse_payment_stage', true);
                        if ($is_festa_order && $full_total > 0) :
                        ?>
                            <tr class="eh-full-total-row">
                                <th scope="row"><?php esc_html_e('Total da festa (100%)', 'EventsHouse'); ?></th>
                                <td colspan="2"><?php echo wp_kses_post(wc_price($full_total, array('currency' => $order->get_currency()))); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="order-total">
                            <th scope="row">
                                <?php
                                if ($is_festa_order && $stage === 'deposit') {
                                    esc_html_e('Total a pagar agora (1.ª parcela 50%)', 'EventsHouse');
                                } elseif ($is_festa_order && $stage === 'balance') {
                                    esc_html_e('Total a pagar agora (valor festa — 50%)', 'EventsHouse');
                                } else {
                                    esc_html_e('Total a pagar', 'EventsHouse');
                                }
                                ?>
                            </th>
                            <td colspan="2"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <?php
            if ($order instanceof WC_Order && function_exists('EventsHouse_festa_cliente_dados_render_pay_fields')) {
                EventsHouse_festa_cliente_dados_render_pay_fields($order);
            }
            ?>
        </div>

        <section class="eh-pay-methods">
            <h3><?php esc_html_e('Métodos de pagamento', 'EventsHouse'); ?></h3>

            <?php
            /**
             * @since 8.2.0
             */
            do_action('woocommerce_pay_order_before_payment');
            ?>

            <div id="payment">
                <?php
                $payment_expired = $is_festa_order && function_exists('EventsHouse_festa_order_payment_deadline_passed')
                    && EventsHouse_festa_order_payment_deadline_passed($order);
                ?>
                <?php if ($payment_expired) : ?>
                    <p class="woocommerce-info eh-pay-deadline-expired">
                        <?php
                        $due_ts = absint($order->get_meta('_eventshouse_payment_due_ts', true));
                        $due_human = $due_ts > 0 ? wp_date('d/m/Y H:i', $due_ts) : '';
                        if ($due_human !== '') {
                            printf(
                                /* translators: %s: deadline datetime */
                                esc_html__('O prazo de pagamento terminou em %s. Já não é possível pagar esta encomenda online.', 'EventsHouse'),
                                esc_html($due_human)
                            );
                        } else {
                            esc_html_e('O prazo de pagamento terminou. Já não é possível pagar esta encomenda online.', 'EventsHouse');
                        }
                        ?>
                    </p>
                <?php elseif ($order->needs_payment()) : ?>
                    <ul class="wc_payment_methods payment_methods methods" aria-label="<?php esc_attr_e('Payment methods', 'woocommerce'); ?>">
                        <?php
                        if (!empty($available_gateways)) {
                            foreach ($available_gateways as $gateway) {
                                wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
                            }
                        } else {
                            echo '<li>';
                            wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__('Desculpe, parece que não existem métodos de pagamento disponíveis para a sua região. Entre em contacto connosco se precisar de ajuda ou quiser negociar outra forma de pagamento.', 'woocommerce') : esc_html__('Por favor, preencha os seus dados para ver os métodos de pagamento disponíveis.', 'woocommerce')), 'notice');
                            echo '</li>';
                        }
                        ?>
                    </ul>
                <?php endif; ?>

                <div class="form-row">
                    <input type="hidden" name="woocommerce_pay" value="1" />

                    <?php wc_get_template('checkout/terms.php'); ?>

                    <?php do_action('woocommerce_pay_order_before_submit'); ?>

                    <?php
                    if (!$payment_expired) {
                        $button_class = 'button alt';
                        if (function_exists('wc_wp_theme_get_element_class_name')) {
                            $theme_button = wc_wp_theme_get_element_class_name('button');
                            if ($theme_button) {
                                $button_class .= ' ' . $theme_button;
                            }
                        }
                        echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="' . esc_attr($button_class) . '" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    ?>

                    <?php do_action('woocommerce_pay_order_after_submit'); ?>

                    <?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
                </div>
            </div>
        </section>
    </div>
</form>

<?php do_action('woocommerce_after_pay_form', $order); ?>
