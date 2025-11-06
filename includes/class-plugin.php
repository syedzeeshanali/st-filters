<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class WC_SFT_Plugin {
    private static $instance = null;

    public static function instance() : self {
        if ( ! self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    public function init() : void {
        // Subsystems
        WC_SFT_Assets::init();
        WC_SFT_Shortcode::init();
        WC_SFT_Ajax::init();
        WC_SFT_Stock::init();

        // Elementor
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_elementor_category' ] );
        add_action( 'elementor/widgets/register',              [ $this, 'register_elementor_widget' ] );
    }

    public function register_elementor_category( $manager ) {
        $manager->add_category( 'wc-sft', [
            'title' => __( 'WC Sellable Toggle Filter', 'wc-sellable-toggle-filter' ),
            'icon'  => 'fa fa-filter',
        ], 1 );
    }

    public function register_elementor_widget( $widgets_manager ) {
        if ( ! did_action( 'elementor/loaded' ) ) return;
        require_once WC_SFT_INCLUDES_DIR . 'class-elementor-widget.php';
        $widgets_manager->register( new \WC_SFT_Elementor_Widget() );
    }
}
