<?php
/**
 * Email Header — visual alinhado com EventsHouse (override do template WooCommerce).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 *
 * @var string $email_heading
 * @var WC_Email|null $email
 */

if (!defined('ABSPATH')) {
    exit;
}

$email       = $email ?? null;
$store_name  = $store_name ?? get_bloginfo('name', 'display');
$site_name   = wp_specialchars_decode($store_name, ENT_QUOTES);
$brand       = function_exists('EventsHouse_wc_email_brand_color') ? EventsHouse_wc_email_brand_color() : '#1f6fb2';
$logo_url    = function_exists('EventsHouse_wc_email_logo_url') ? EventsHouse_wc_email_logo_url() : '';
$heading     = !empty($email_heading) ? $email_heading : $site_name;

/**
 * @since 10.7.0
 */
$header_image_url = apply_filters('woocommerce_email_header_image_url', home_url());
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo esc_html($site_name); ?></title>
</head>
<body style="margin:0;padding:22px;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;" bgcolor="#eef3f8">
    <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #d9e2ec;border-radius:16px;overflow:hidden;box-shadow:0 10px 28px rgba(15,23,42,0.08);">
        <div style="background:linear-gradient(135deg,<?php echo esc_attr($brand); ?> 0%,#0f4c81 100%);padding:24px 26px;color:#ffffff;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">
                <tr>
                    <?php if ($logo_url) : ?>
                    <td style="vertical-align:middle;width:50%;">
                        <?php if ($header_image_url) : ?>
                        <a href="<?php echo esc_url($header_image_url); ?>" style="text-decoration:none;">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" style="max-height:58px;width:auto;display:block;border:0;" />
                        </a>
                        <?php else : ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" style="max-height:58px;width:auto;display:block;border:0;" />
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td style="text-align:<?php echo $logo_url ? 'right' : 'left'; ?>;vertical-align:middle;">
                        <div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;opacity:.88;"><?php esc_html_e('Notificação', 'EventsHouse'); ?></div>
                        <div style="font-size:22px;font-weight:700;line-height:1.2;margin-top:2px;color:#ffffff;"><?php echo esc_html($heading); ?></div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="padding:24px 26px;background:#ffffff;line-height:1.6;font-size:15px;color:#273444;">
            <div id="body_content_inner">
