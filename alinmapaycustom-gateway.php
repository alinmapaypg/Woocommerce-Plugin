<?php
/*
Plugin Name: AlinmaPay Payment
Description: A AlinmaPay payment gateway for WooCommerce.
Version: 3.0.3
Author: 
Author URI: 
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: custom-gateway
Domain Path: /languages
*/

// Your plugin code goes here

//$config =  plugin_dir_path( __FILE__ ) . 'config.php' ;

add_action('plugins_loaded', 'alinmapay_customplugin', 0);
function alinmapay_customplugin(){
    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class 

    include(plugin_dir_path(__FILE__) . 'alinmapay-gateway.php');
}


add_filter('alinmapay_payment_gateways', 'add_alinmapay_payment');

function add_alinmapay_payment($gateways) {
  $gateways[] = 'AlinmaPay_Payment';
  return $gateways;
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature 
*/
function declare_cart_checkout_blocks_compatibility() {
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action( 'woocommerce_blocks_loaded', 'oawoo_register_order_approval_payment_method_type' );

/**
 * Custom function to register a payment method type

 */
function oawoo_register_order_approval_payment_method_type() {
    // Check if the required class exists
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    // Include the custom Blocks Checkout class
    require_once plugin_dir_path(__FILE__) . 'alinmapay-block.php';

    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            // Register an instance of My_Custom_Gateway_Blocks
            $payment_method_registry->register( new AlinmaPay_Payment_Blocks() );
        }
    );
}


// Register activation hook
register_activation_hook(__FILE__, 'log_plugin_installation_and_dependencies');

function log_plugin_installation_and_dependencies() {
	
	global $wp_version;
	// Log WooCommerce version
    if (defined('WC_VERSION')) {
      $log_message = "Found WooCommerce version: " . WC_VERSION;
    } else {
       $log_message = "WooCommerce is not installed or activated.";
    }
    $config = include( plugin_dir_path( __FILE__ ) . 'config.php' );

	//error_log( 'Config data: ' . print_r( $config, true ) );
    $plugin_version = $config['plugin_version'];
    $plugin_name = $config['plugin_name'];
    $woocommerce_version = $config['woocommerce_version'];
    $wordpress_version = $config['wordpress_version'];
	
    // Log the details into the error log
    error_log("Expected Woocommerce Version: " .$config['woocommerce_version'] . ' | Found Version: '. WC_VERSION);
	error_log("Expected Wordpress Version :" .$config['wordpress_version']. ' | Found  Version:' . $wp_version);

   
    // Initialize log details
    $log_message = "==== Plugin Installation Log ====\n";
    error_log("Plugin Installation Started At: " . date('Y-m-d H:i:s'));

    // Log PHP version
    error_log("PHP Version Detected: " . PHP_VERSION);

    // Log server software information (optional)
    error_log("Server software: " . $_SERVER['SERVER_SOFTWARE']);

    // Check for a required library (e.g., a specific PHP extension or plugin)
    $dependency = 'curl'; // Example dependency
    if (extension_loaded($dependency)) {
        $log_message .= "Dependency '$dependency' is installed and available.\n";
    } else {
        $log_message .= "Dependency '$dependency' is NOT installed. Please install '$dependency' for the plugin to work properly.\n";
    }

    // Log plugin activation success
    error_log("Plugin Activation Completed Successfully.\n");
  
    // Write to a custom log file in the plugin directory
    //$log_file_path = plugin_dir_path(__FILE__) . 'installation_log.txt';
    //file_put_contents($log_file_path, $log_message, FILE_APPEND);

   
}


?>