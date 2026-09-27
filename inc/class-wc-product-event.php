<?php
/**
 * Produto WooCommerce tipo «evento»: inscrição com dados no carrinho/encomenda.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Product_Event')) {

    /**
     * Produto simples virtual orientado a eventos (pagamento via WooCommerce).
     */
    class WC_Product_Event extends WC_Product_Simple
    {
        /**
         * Tipo interno WooCommerce.
         *
         * @return string
         */
        public function get_type()
        {
            return 'event';
        }

        /**
         * Sem envio físico (bilhete / inscrição).
         *
         * @param string $context edit|view.
         * @return bool
         */
        public function is_virtual($context = 'view')
        {
            return true;
        }
    }
}
