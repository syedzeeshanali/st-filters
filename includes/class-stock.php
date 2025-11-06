<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class WC_SFT_Stock {

    public static function init() : void {
        add_action( 'wp_ajax_wc_sft_subscribe_stock',        [ __CLASS__, 'subscribe' ] );
        add_action( 'wp_ajax_nopriv_wc_sft_subscribe_stock', [ __CLASS__, 'subscribe' ] );
        add_action( 'transition_post_status', [ __CLASS__, 'notify_on_restock' ], 10, 3 );
    }

    public static function subscribe() : void {
        check_ajax_referer( 'wc_sft_nonce', '_nonce' );
        global $wpdb;

        $product_id = (int) ($_POST['product_id'] ?? 0);
        $email      = sanitize_email( $_POST['email'] ?? '' );
        if ( ! $product_id || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid request.', 'wc-sellable-toggle-filter' ) ] );
        }

        $table = wc_sft_table_name();
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table WHERE product_id = %d AND email = %s", $product_id, $email
        ) );

        if ( $exists ) {
            wp_send_json_success( [ 'message' => __( 'You are already subscribed.', 'wc-sellable-toggle-filter' ) ] );
        }

        $wpdb->insert( $table, [ 'product_id' => $product_id, 'email' => $email ], [ '%d','%s' ] );
        wp_send_json_success( [ 'message' => __( 'You will be notified when this product is back in stock.', 'wc-sellable-toggle-filter' ) ] );
    }

    public static function notify_on_restock( $new_status, $old_status, $post ) : void {
        if ( 'product' !== $post->post_type ) return;
        $product = wc_get_product( $post->ID );
        if ( ! $product ) return;
        if ( $product->get_stock_status() !== 'instock' ) return;

        global $wpdb;
        $table = wc_sft_table_name();
        $subs = $wpdb->get_results( $wpdb->prepare( "SELECT id, email FROM $table WHERE product_id = %d", $post->ID ) );
        if ( empty( $subs ) ) return;

        $subject = sprintf( __( 'Product Back in Stock: %s', 'wc-sellable-toggle-filter' ), $product->get_name() );
        $message = sprintf(
            __( "Good news!\n\nThe product \"%s\" is now back in stock.\n\nView it here: %s", 'wc-sellable-toggle-filter' ),
            $product->get_name(),
            get_permalink( $post->ID )
        );

        foreach ( $subs as $row ) wp_mail( $row->email, $subject, $message );
        $wpdb->delete( $table, [ 'product_id' => $post->ID ], [ '%d' ] );
    }
}
