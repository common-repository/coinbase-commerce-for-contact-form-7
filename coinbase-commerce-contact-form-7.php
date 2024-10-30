<?php
/**
 * Plugin Name: Coinbase Commerce for Contact Form 7
 * Plugin URI: https://www.coderpress.co/
 * Author: SCI Intelligencia
 * Description: Integrate your Contact Form 7 with Coinbase Commerce.
 * Version: 1.1.2
 * Author: Syed Muhammad Usman
 * Author URI: https://www.coderpress.co/
 * License: GPL v2 or later
 * Stable tag: 1.1.2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: contact form 7, coinbase commerce, gateway, coinbase, commerce
 * @author Syed Muhammad Usman
 * @url https://www.linkedin.com/in/syed-muhammad-usman/
 */

if ( ! function_exists( 'ccfcf7_fs' ) ) {
    // Create a helper function for easy SDK access.
    function ccfcf7_fs() {
        global $ccfcf7_fs;

        if ( ! isset( $ccfcf7_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $ccfcf7_fs = fs_dynamic_init( array(
                'id'                  => '10076',
                'slug'                => 'coinbase-commerce-for-contact-form-7',
                'type'                => 'plugin',
                'public_key'          => 'pk_843030a1bc2fb6af54064541ca4e6',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $ccfcf7_fs;
    }

    // Init Freemius.
    ccfcf7_fs();
    // Signal that SDK was initiated.
    do_action( 'ccfcf7_fs_loaded' );
}

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CCCF7_PLUGIN_FILE' ) ) {
    define( 'CCCF7_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CCCF7_VERSION' ) ) {
    define( 'CCCF7_VERSION', '1.1.2' );
}

if ( ! defined( 'CCCF7_PLUGIN_URL' ) ) {
    define( 'CCCF7_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require dirname( CCCF7_PLUGIN_FILE ) . '/includes/class-cccf7-init.php';

CCCF7_Init::get_instance();
