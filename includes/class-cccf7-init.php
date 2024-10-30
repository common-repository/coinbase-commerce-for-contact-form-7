<?php

if( !class_exists( 'CCCF7_Init' ) ):
class CCCF7_Init {

    /**
     * Keeps Instance
     * @var $_instance
     * @version 1.0
     * @since 1.0
     */
    private static $_instance;

    /**
     * Gets Instance
     * @return Init
     * @version 1.0
     * @since 1.0
     */
    public static function get_instance() {
        if ( self::$_instance == null ) {

            self::$_instance = new self();

        }

        return self::$_instance;
    }

    /**
     * Init constructor.
     * @version 1.0
     * @since 1.0
     */
    public function __construct() {

        $this->validate();

    }

    /**
     * Validate
     * @version 1.0
     * @since 1.0
     */
    public function validate() {

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {

            $this->init();

        }

        else {

            add_action( 'admin_notices', array( $this, 'require_contact_form' ) );

        }

    }
    /**
     * Shows Notice
     * @version 1.0
     * @since 1.0
     */
    public function require_contact_form() {

        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'In order to use Coinbase Commerce for Contact Form 7, Contact form should be installed and active!', 'cccf7' ); ?></p>
        </div>
        <?php

    }

    /**
     * Finally Initialize Plugin :)
     * @version 1.0
     * @since 1.0
     */
    public function init() {

        require dirname( CCCF7_PLUGIN_FILE ) . '/includes/cccf7-functions.php';
        require dirname( CCCF7_PLUGIN_FILE ) . '/includes/class-admin-settings.php';
        require dirname( CCCF7_PLUGIN_FILE ) . '/includes/class-user.php';
        require dirname( CCCF7_PLUGIN_FILE ) . '/includes/class-webhook.php';

    }

}
endif;
