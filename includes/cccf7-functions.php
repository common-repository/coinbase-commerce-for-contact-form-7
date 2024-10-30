<?php

/**
 * Gets API Key
 * @since 1.0
 * @version 1.0
 */
if( !function_exists( 'cccf7_get_api_key' ) ):
function cccf7_get_api_key() {

    return get_option( 'cccf7_api_key' );

}
endif;

/**
 * Gets Price by post id
 * @since 1.0
 * @version 1.0
 */
if( !function_exists( 'cccf7_get_price' ) ):
function cccf7_get_price( $post_id ) {

    return get_post_meta( $post_id, 'cccf7_price', true );

}
endif;

/**
 * Checks is Enabled
 * @since 1.0
 * @version 1.0
 */
if( !function_exists( 'cccf7_is_enabled' ) ):
function cccf7_is_enabled( $post_id ) {

    $is_enabled = get_post_meta( $post_id, 'cccf7_enable', true );

    if( $is_enabled && $is_enabled == 'enabled' ) {
        return true;
    }

    return false;

}
endif;

/**
 * Insert pending transaction
 * @since 1.0
 * @version 1.0
 */
if( !function_exists( 'cccf7_insert_pending_transaction' ) ):
function cccf7_insert_pending_transaction( $amount, $form_id, $payment_status, $checkout_id, $postarr = array() ) {

    $postarr['post_type'] = 'cccf7-payments';
    $postarr['post_status'] = 'publish';

    $post_id = wp_insert_post( $postarr );
    update_post_meta( $post_id, 'cccf7_amount', $amount );
    update_post_meta( $post_id, 'cccf7_form_id', $form_id );
    update_post_meta( $post_id, 'cccf7_payment_status', $payment_status );
    update_post_meta( $post_id, 'cccf7_checkout_id', $checkout_id );

    return $post_id;
}
endif;

/**
 * Gets Price by post id
 * @since 1.0
 * @version 1.1.0
 */
if( !function_exists( 'cccf7_get_currency_code' ) ):
function cccf7_get_currency_code( $post_id ) {

    return get_post_meta( $post_id, 'cccf7_currency_code', true );

}
endif;
