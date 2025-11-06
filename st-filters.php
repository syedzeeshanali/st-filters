<?php
/**
 * Plugin Name: ST Filters
 * Description: Shortcode + Elementor widget to show “sellable” products with multi-select chips (categories/tags/brands), sorting, and stock notification.
 * Version: 1.7.0
 * Author: Syed Zeeshan Ali
 * Text Domain: st-filters
 * Requires at least: 5.8
 * Tested up to: 6.6
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WC_SFT_VERSION',        '1.7.0' );
define( 'WC_SFT_FILE',           __FILE__ );
define( 'WC_SFT_DIR',            plugin_dir_path( __FILE__ ) );
define( 'WC_SFT_URL',            plugin_dir_url( __FILE__ ) );
define( 'WC_SFT_BASENAME',       plugin_basename( __FILE__ ) );
define( 'WC_SFT_TEMPLATES_DIR',  WC_SFT_DIR . 'templates/' );
define( 'WC_SFT_INCLUDES_DIR',   WC_SFT_DIR . 'includes/' );
define( 'WC_SFT_ASSETS_URL',     WC_SFT_URL . 'assets/' );

/** Basic autoload (includes/ only) */
spl_autoload_register( function( $class ) {
    if ( 0 !== strpos( $class, 'WC_SFT_' ) ) return;
    $file = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
    $path = WC_SFT_INCLUDES_DIR . $file;
    if ( file_exists( $path ) ) require_once $path;
});

require_once WC_SFT_INCLUDES_DIR . 'helpers.php';

/** Activation: DB table */
register_activation_hook( __FILE__, function() {
    WC_SFT_DB::create_table();
});

/** Bootstrap */
add_action( 'plugins_loaded', function() {
    // Optional hard dep check
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>'
                . esc_html__( 'WC Sellable Toggle Filter requires WooCommerce to be active.', 'wc-sellable-toggle-filter' )
                . '</p></div>';
        });
        return;
    }
    WC_SFT_Plugin::instance()->init();
});
