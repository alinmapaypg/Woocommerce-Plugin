<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
require_once dirname(__FILE__).'/config.php';
final class AlinmaPay_Payment_Blocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'alinmapay_payment';// your payment gateway name
    //protected $name = WC_CUSTOM_PLUGIN_GATEWAY_NAME;
    public function initialize() {
        $this->settings = get_option( 'woocommerce_alinmapay_payment_settings', [] );
        $this->gateway = new AlinmaPay_Payment();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'alinmapay_payment-blocks-integration',
            plugin_dir_url(__FILE__) . 'checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'alinmapay_payment-blocks-integration');
            
        }
        return [ 'alinmapay_payment-blocks-integration' ];
    }

    public function get_payment_method_data() {
        

        return [
            'title' => $this->gateway->title,
            //'name' => $this->name,
            //'description' => $this->gateway->description,
            'icon'         =>  plugin_dir_url(__FILE__) . 'images/AlinmaPayDefault.png',
            'supports'     => $this->get_supported_features(),
            
        ];
    }
	

}
?>