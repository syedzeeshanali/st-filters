<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class WC_SFT_Ajax {

    public static function init() : void {
        add_action( 'wp_ajax_wc_sft_get_products',        [ __CLASS__, 'get_products' ] );
        add_action( 'wp_ajax_nopriv_wc_sft_get_products', [ __CLASS__, 'get_products' ] );
    }

    public static function get_products() : void {
        check_ajax_referer( 'wc_sft_nonce', '_nonce' );

        $cat_ids   = isset($_POST['cat_ids'])   ? array_map('intval', (array) $_POST['cat_ids'])   : [];
        $tag_ids   = isset($_POST['tag_ids'])   ? array_map('intval', (array) $_POST['tag_ids'])   : [];
        $brand_ids = isset($_POST['brand_ids']) ? array_map('intval', (array) $_POST['brand_ids']) : [];

        $sort      = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : '';
        $price_min = (isset($_POST['price_min']) && $_POST['price_min'] !== '') ? (float) $_POST['price_min'] : null;
        $price_max = (isset($_POST['price_max']) && $_POST['price_max'] !== '') ? (float) $_POST['price_max'] : null;

        $attrs     = isset($_POST['shortcode_attrs']) ? (array) $_POST['shortcode_attrs'] : [];
        $per_page  = isset($attrs['per_page']) ? max(1, (int) $attrs['per_page']) : 9;

        $includes = [
            'cat_in'   => isset($attrs['includes']['cat_in'])   ? array_map('intval', (array) $attrs['includes']['cat_in'])   : [],
            'tag_in'   => isset($attrs['includes']['tag_in'])   ? array_map('intval', (array) $attrs['includes']['tag_in'])   : [],
            'brand_in' => isset($attrs['includes']['brand_in']) ? array_map('intval', (array) $attrs['includes']['brand_in']) : [],
        ];
        $sellable_cat_slug = isset($attrs['sellable_set']['cat_slug']) ? sanitize_text_field($attrs['sellable_set']['cat_slug']) : 'bf-sale-2025';

        // Build tax_query
        $tax_query = [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => [ $sellable_cat_slug ],
                ],
            ],
        ];

        if ( !empty($includes['cat_in']) ) {
            $tax_query[] = [ 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $includes['cat_in'], 'operator' => 'IN' ];
        }
        if ( !empty($includes['tag_in']) ) {
            $tax_query[] = [ 'taxonomy' => 'product_tag', 'field' => 'term_id', 'terms' => $includes['tag_in'], 'operator' => 'IN' ];
        }
        if ( !empty($includes['brand_in']) ) {
            $tax_query[] = [ 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => $includes['brand_in'], 'operator' => 'IN' ];
        }

        // Narrow by chip selections
        if ( !empty($cat_ids) )   $tax_query[] = [ 'taxonomy' => 'product_cat',   'field' => 'term_id', 'terms' => $cat_ids,   'operator' => 'IN' ];
        if ( !empty($tag_ids) )   $tax_query[] = [ 'taxonomy' => 'product_tag',   'field' => 'term_id', 'terms' => $tag_ids,   'operator' => 'IN' ];
        if ( !empty($brand_ids) ) $tax_query[] = [ 'taxonomy' => 'product_brand', 'field' => 'term_id', 'terms' => $brand_ids, 'operator' => 'IN' ];

        // Meta query
        $meta_query = WC()->query->get_meta_query();
        if ( $price_min !== null || $price_max !== null ) {
            $min = $price_min !== null ? $price_min : 0;
            $max = $price_max !== null ? $price_max : 999999999;
            $meta_query[] = [ 'key' => '_price', 'value' => [ $min, $max ], 'compare' => 'BETWEEN', 'type' => 'NUMERIC' ];
        }

        $orderby  = 'date'; $order = 'DESC'; $meta_key = '';
        switch ( $sort ) {
            case 'price_asc':  $orderby='meta_value_num'; $meta_key='_price'; $order='ASC'; break;
            case 'price_desc': $orderby='meta_value_num'; $meta_key='_price'; $order='DESC'; break;
            case 'in_stock':     $meta_query[] = [ 'key' => '_stock_status', 'value' => 'instock' ]; break;
            case 'preorder':     $meta_query[] = [ 'key' => '_stock_status', 'value' => 'onbackorder' ]; break;
            case 'out_of_stock': $meta_query[] = [ 'key' => '_stock_status', 'value' => 'outofstock' ]; break;
        }

        $args = [
            'post_type'        => 'product',
            'posts_per_page'   => $per_page > 0 ? $per_page : -1,
            'post_status'      => 'publish',
            'tax_query'        => $tax_query,
            'meta_query'       => $meta_query,
            'orderby'          => $orderby,
            'order'            => $order,
            'suppress_filters' => false,
        ];
        if ( $meta_key ) $args['meta_key'] = $meta_key;

        // Force numeric order on price
        $posts_orderby_callback = null;
        if ( in_array( $sort, [ 'price_asc', 'price_desc' ], true ) ) {
            $args['sft_force_price_order'] = true;
            $posts_orderby_callback = function( $orderby_sql, $query ) {
                if ( ! $query->get( 'sft_force_price_order' ) ) return $orderby_sql;
                global $wpdb;
                $dir = ( isset( $_POST['sort'] ) && $_POST['sort'] === 'price_desc' ) ? 'DESC' : 'ASC';
                return "{$wpdb->postmeta}.meta_value+0 {$dir}";
            };
            add_filter( 'posts_orderby', $posts_orderby_callback, 20, 2 );
        }

        $loop = new WP_Query( $args );
        if ( $posts_orderby_callback ) {
            remove_filter( 'posts_orderby', $posts_orderby_callback, 20, 2 );
        }

        if ( ! $loop->have_posts() ) {
            wp_send_json_error( [ 'message' => __( 'No products found', 'wc-sellable-toggle-filter' ) ] );
        }

        ob_start();
        while ( $loop->have_posts() ) {
            $loop->the_post();
            wc_get_template( 'content-product-sft.php', [], '', WC_SFT_TEMPLATES_DIR );
        }
        wp_reset_postdata();

        wp_send_json_success( [ 'html' => ob_get_clean() ] );
    }
}
