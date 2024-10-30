<?php

if( !class_exists( 'CCCF7_Admin_Settings' ) ):
    class CCCF7_Admin_Settings {

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
            $this->add_filters();

        }

        /**
         * Add Actions
         * @version 1.0
         * @since 1.0
         */
        public function add_actions() {

            add_action( 'wpcf7_after_save', array( $this, 'save_form' ) );
            add_action( 'admin_menu', array( $this, 'add_sub_menus' ) );
            add_action( 'admin_post_cccf7_save_settings', array( $this, 'save_settings' ) );
            add_action( 'init', array( $this, 'register_post' ) );
            add_action( 'manage_cccf7-payments_posts_columns', array( $this, 'manage_header_columns' ) );
            add_action( 'manage_cccf7-payments_posts_custom_column', array( $this, 'manage_body_columns' ), 10, 2 );

        }

        /**
         * Add Filters
         * @version 1.0
         * @since 1.0
         */
        public function add_filters() {

            add_filter( 'wpcf7_editor_panels', array( $this, 'add_form_tab' ) );

        }

        /**
         * Adds Tab in Form
         * @version 1.0
         * @since 1.0
         */
        public function add_form_tab( $panels ) {

            $new_page = array(
                'coinbase_commerce' => array(
                    'title'     => __( 'Coinbase Commerce', 'cccf7' ),
                    'callback'  => array( $this, 'add_form_tab_body' )
                )
            );

            $panels = array_merge( $panels, $new_page );

            return $panels;

        }

        /**
         * Add Tab's Body
         * @version 1.0
         * @since 1.0
         * @since 1.1.0 Added currency code support
         */
        public function add_form_tab_body() {

            $post_id = sanitize_text_field( $_GET['post'] );
            $enabled = cccf7_is_enabled( $post_id );
            $price = cccf7_get_price( $post_id );
            $currency_code = cccf7_get_currency_code( $post_id );
            $content = '';

            if( $enabled ) {
                $enabled = "checked";
            }
            else {
                $enabled = "";
            }
            if( $price ) {
                $price = "value='{$price}'";
            }
            else {
                $price = "";
            }
            if( $currency_code ) {
                $currency_code = "value='{$currency_code}'";
            }
            else {
                $currency_code = "";
            }

            $content = "
        <h2>Coinbase Commerce</h2>
        <table cellpadding='10'>
            <tr>
                <td>Enable/ Disable</td>
                <td><input type='checkbox' name='cccf7_enable' {$enabled} /></td>
            </tr>
            <tr>
                <td>Price</td>
                <td><input type='text' name='cccf7_price' {$price} /></td>
            </tr>
            <tr>
                <td>Currency Code</td>
                <td><input type='text' name='cccf7_currency_code' {$currency_code} /></td>
            </tr>
        </table>
        ";

            echo $content;

        }

        /**
         * Saves Form
         * @since 1.0
         * @version 1.0
         * @since 1.1.0 Added currency code support
         */
        public function save_form( $data ) {

            $post_id = (int)sanitize_text_field( $_GET['post'] );
            $cccf7_enable = isset( $_POST['cccf7_enable'] ) ? 'enabled' : 'disabled';
            $cccf7_price = isset( $_POST['cccf7_price'] ) ? sanitize_text_field( $_POST['cccf7_price'] ) : '';
            $cccf7_currency_code = isset( $_POST['cccf7_currency_code'] ) ? sanitize_text_field( $_POST['cccf7_currency_code'] ) : '';

            update_post_meta( $post_id, 'cccf7_enable', $cccf7_enable );
            update_post_meta( $post_id, 'cccf7_price', $cccf7_price );
            update_post_meta( $post_id, 'cccf7_currency_code', $cccf7_currency_code );

        }

        /**
         * Sub Menus
         * @since 1.0
         * @version 1.0
         */
        public function add_sub_menus() {

            add_submenu_page(
                'wpcf7',
                __( 'Coinbase Commerce Settings', 'cccf7' ),
                __( 'Coinbase Commerce Settings', 'cccf7' ),
                'wpcf7_edit_contact_forms',
                'cccf7-settings',
                array( $this, 'settings' ),
                3
            );

        }

        /**
         * Admin Settings
         * @since 1.0
         * @version 1.0
         */
        public function settings() {

            $api_key = cccf7_get_api_key();
            if( $api_key ) {
                $api_key = "value='{$api_key}'";
            }
            else {
                $api_key = '';
            }
            ?>
            <div class="wrap">
                <h2>Contact Form 7 - Coinbase Commerce</h2>
                <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
                    <table cellpadding="10">
                        <tr>
                            <td>API Key</td>
                            <td><input type="password" name="cccf7_api_key" <?php echo $api_key; ?> /></td>
                        </tr>
                        <tr>
                            <td>Webhook URL</td>
                            <td><?php echo site_url() . '?rest_route=/cccf7/v1/complete-payment';?></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="hidden" name="action" value="cccf7_save_settings" />
                                <input type="submit" class="button button-primary" value="Save">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php

        }

        /**
         * Saves Settings
         * @since 1.0
         * @version 1.0
         */
        public function save_settings() {

            if( isset( $_POST['action'] ) && $_POST['action'] == 'cccf7_save_settings' ) {

                $api_key = isset( $_POST['cccf7_api_key'] ) ? sanitize_text_field( $_POST['cccf7_api_key'] ) : '';
                update_option( 'cccf7_api_key', $api_key );

            }

            wp_redirect( admin_url( 'admin.php?page=cccf7-settings' ) );

        }

        /**
         * Register Post
         * @since 1.0
         * @version 1.0
         */
        public function register_post() {

            register_post_type('cccf7-payments', array(
                'labels'				=> array(
                    'name'               => __('Coinbase Commerce Payments', 'cccf7'),
                    'singular_name'      => __('Coinbase Commerce Payments', 'cccf7'),
                    'add_new'            => __('Add New', 'cccf7'),
                    'add_new_item'       => __('Add new payment', 'cccf7'),
                    'edit_item'          => __('Edit payment', 'cccf7'),
                    'new_item'           => __('New payment', 'cccf7'),
                    'view_item'          => __('View payment', 'cccf7'),
                    'search_items'       => __('Find payment', 'cccf7'),
                    'not_found'          => __('No payments found.', 'cccf7'),
                    'not_found_in_trash' => __('No payments found in Trash.', 'cccf7'),
                    'parent_item_colon'  => '',
                    'menu_name'          => __('Coinbase Commerce Payments', 'cccf7'),
                ),
                'public'				=> false,
                'show_ui'				=> true,
                'show_in_menu'			=> 'wpcf7',
                'capability_type'		=> 'page',
                //'supports'				=> array( 'custom-fields' ),
                'rewrite'				=> false,
                'query_var'				=> false,
                'delete_with_user'		=> false,
                //'register_meta_box_cb'	=> 'cf7pp_payments_add_metaboxes',
            ) );

        }

        /**
         * Modify Table Header
         * @version 1.0
         * @since 1.0
         */
        public function manage_header_columns( $post_columns ) {

            unset( $post_columns['title'] );
            unset( $post_columns['date'] );

            $post_columns['id'] = 'Transaction ID';
            $post_columns['form'] = 'Form';
            $post_columns['amount'] = 'Amount';
            $post_columns['form_id'] = 'Form ID';
            $post_columns['checkout_id'] = 'Checkout ID';
            $post_columns['payment_status'] = 'Payment Status';
            $post_columns['date'] = 'Date';

            return $post_columns;

        }

        /**
         * Modify Table Body
         * @version 1.0
         * @since 1.0
         */
        public function manage_body_columns( $column, $post_id ) {

            if( $column == 'id' ){
                echo $post_id;
            }
            if( $column == 'form' ){
                echo get_the_title( $post_id );
            }
            if( $column == 'amount' ){
                echo get_post_meta( $post_id, 'cccf7_amount', true );
            }
            if( $column == 'form_id' ){
                echo get_post_meta( $post_id, 'cccf7_form_id', true );
            }
            if( $column == 'checkout_id' ){
                echo get_post_meta( $post_id, 'cccf7_checkout_id', true );
            }
            if( $column == 'payment_status' ){
                echo get_post_meta( $post_id, 'cccf7_payment_status', true );
            }

            return $column;

        }

    }
    CCCF7_Admin_Settings::get_instance();
endif;
