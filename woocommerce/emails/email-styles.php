<?php
/**
 * Email Styles — complemento visual EventsHouse (override do template WooCommerce).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$brand = function_exists('EventsHouse_wc_email_brand_color') ? EventsHouse_wc_email_brand_color() : '#1f6fb2';

?>
#body_content_inner {
    color: #273444;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 15px;
    line-height: 1.6;
}
#body_content_inner h1,
#body_content_inner h2,
#body_content_inner h3 {
    color: #1f2937;
    font-weight: 700;
}
#body_content_inner a {
    color: <?php echo esc_attr($brand); ?>;
    text-decoration: underline;
}
#body_content_inner table {
    width: 100%;
    border-collapse: collapse;
}
#body_content_inner .td,
#body_content_inner th.td,
#body_content_inner td.td {
    padding: 10px 8px;
    vertical-align: top;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}
#body_content_inner th.td {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    text-align: left;
}
#body_content_inner .order_item img {
    max-width: 48px;
    height: auto;
    border-radius: 6px;
}
#body_content_inner address {
    font-style: normal;
    color: #475569;
    line-height: 1.5;
}
#body_content_inner .wc-item-meta {
    font-size: 13px;
    color: #64748b;
}
