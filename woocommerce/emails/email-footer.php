<?php
/**
 * Email Footer — visual alinhado com EventsHouse (override do template WooCommerce).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$email = $email ?? null;

$footer_text = function_exists('EventsHouse_wc_email_footer_text') ? EventsHouse_wc_email_footer_text() : '';
if ($footer_text === '' && function_exists('get_option')) {
    $footer_text = (string) get_option('woocommerce_email_footer_text', '');
    $footer_text = (string) apply_filters('woocommerce_email_footer_text', $footer_text, $email);
}
?>
            </div>
        </div>
        <?php if ($footer_text !== '') : ?>
        <div style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e5e7eb;color:#64748b;font-size:12px;line-height:1.5;">
            <?php echo wp_kses_post(wpautop(wptexturize($footer_text))); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
