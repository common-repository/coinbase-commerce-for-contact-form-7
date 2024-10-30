<?php
if( !class_exists( 'CCCF7_Webhook' ) ):
class CCCF7_Webhook {

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

        add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

    }

    /**
     * Register Rest route call-back
     * @since 1.0
     * @version 1.0
     */
    public function register_rest_route()
    {
        register_rest_route( 'cccf7/v1', '/complete-payment', array(
            'methods'   => 'POST',
            'callback'  => array( $this, 'complete_payment' ),
            'permission_callback' => function () {
                return true; // security can be done in the handler
            }
        ));
    }

    /**
     * Completes payment
     * @since 1.4
     * @version 1.0
     */
    public function complete_payment()
    {
        $payload = @file_get_contents('php://input');

        $payload = json_decode( $payload );

        if( $payload->event->type != 'charge:confirmed' ) return;

        $unique_id = $payload->event->data->checkout->id;

        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT post_id from {$wpdb->postmeta} WHERE meta_value = %s",
                $unique_id
            )
        );

        $post_id = (int)$result->post_id;

        if( $post_id )
        {
            update_post_meta( $post_id, 'cccf7_payment_status', 'Completed' );

            wp_send_json_success( array(), 200 );
        }

        wp_send_json( array( 'message'	=>	'No order associated with this ID.' ), 404 );
    }

}
CCCF7_Webhook::get_instance();
endif;
