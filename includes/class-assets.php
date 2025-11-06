<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class WC_SFT_Assets {
    public static function init() : void {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }
    public static function enqueue() : void {
        wp_enqueue_style( 'wc-sft-style', WC_SFT_ASSETS_URL . 'css/wc-sft.css', [], WC_SFT_VERSION );
        wp_enqueue_script( 'wc-sft-js',   WC_SFT_ASSETS_URL . 'js/wc-sft.js', [ 'jquery','jquery-ui-slider' ], WC_SFT_VERSION, true );
        wp_localize_script( 'wc-sft-js', 'wc_sft_vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wc_sft_nonce' ),
        ] );
    }
}
