<?php

if( !class_exists( 'CCCF7_User' ) ):
class CCCF7_User {

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

        $this->add_actions();

    }

    /**
     * Add Actions
     * @version 1.0
     * @since 1.0
     */
    public function add_actions() {

        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        add_action( 'wp_ajax_cccf7-form-submit', array( $this, 'form_submit' ) );
        add_action( 'wp_ajax_nopriv_cccf7-form-submit', array( $this, 'form_submit' ) );

    }

    /**
     * Enqueue Scripts
     * @version 1.0
     * @since 1.0
     */
    public function wp_enqueue_scripts() {

        wp_enqueue_script( 'cccf7-front-end', CCCF7_PLUGIN_URL . '/assets/js/front-end.min.js', array( 'jquery' ), CCCF7_VERSION, true );
        wp_localize_script(
            'cccf7-front-end',
            'cccf7',
            array(
                'ajaxurl'   =>  admin_url( 'admin-ajax.php' )
            )
        );
    }

    /**
     * Catch submitted form :D
     * @version 1.0
     * @since 1.0
     */
    public function form_submit() {

        if( isset( $_POST['action'] ) && $_POST['action'] == 'cccf7-form-submit' ) {

            $form_id = (int)sanitize_text_field( $_POST['form_id'] );
            $amount = (int)cccf7_get_price( $form_id );
            $currency = cccf7_get_currency_code( $form_id );
            $site_url = site_url();


            if( !cccf7_is_enabled( $form_id ) ) {
                wp_send_json_success(
                    array(
                    'message'   =>  'Do nothing.'
                ),
                    200
                );
            }

            if( cccf7_is_enabled( $form_id ) ) {

                $postarr['post_title'] = get_the_title( $form_id );
                $transaction_id = cccf7_insert_pending_transaction( $amount, $form_id, 'Pending', 'Not Checkout yet', $postarr );

                $headers = array(
                    'Content-Type'  =>  'application/json',
                    'X-Cc-Api-Key'  =>  cccf7_get_api_key(),
                    'X-Cc-Version'  =>  '2018-03-22'
                );

                $body = array (
                    'name' => "Transaction ID: {$transaction_id}",
                    'description' => 'Contact Form ID: ' . $form_id,
                    'local_price' =>
                        array (
                            'amount'    => $amount,
                            'currency'  => $currency,
                        ),
                    'pricing_type' => 'fixed_price',
                    'requested_info' =>
                        array (
                            0 => 'email',
                        ),
                    "redirect_url"  =>  $site_url
                );

                $body = json_encode( $body );

                $endpoint = 'https://api.commerce.coinbase.com/checkouts';

                $options = array(
                    'body'          =>  $body,
	                'headers'       =>  $headers,
	                'method'        =>  'POST',
	                'timeout'       =>  45,
	                'redirection'   =>  5,
	                'httpversion'   =>  '1.0',
	                'sslverify'     =>  false,
	                'data_format'   => 'body'
                );

                $response = wp_remote_post(
                    $endpoint,
                    $options
                );
                $response_code = wp_remote_retrieve_response_code( $response );
                $response_msg = wp_remote_retrieve_response_message( $response );

                if( $response_code == 201 ) {

                    $response = json_decode( $response['body'] );

                    update_post_meta( $transaction_id, 'cccf7_checkout_id', $response->data->id );

                    wp_send_json_success(
                        array( 'redirect'   =>  "https://commerce.coinbase.com/checkout/{$response->data->id}" ),
                        $response_code
                    );
                }
                else {
                    wp_send_json_error(
                        array( 'message'    =>  $response_msg ),
                        $response_code
                    );
                }

                wp_send_json_error( 'Something went wrong.', 300 );
            }
        }

    }
}
CCCF7_User::get_instance();
endif;
