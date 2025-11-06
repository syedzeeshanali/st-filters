<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class WC_SFT_Shortcode {

    public static function init() : void {
        add_shortcode( 'sellable_toggle_filter', [ __CLASS__, 'render' ] );
        add_shortcode( 'wc_sft',                 [ __CLASS__, 'render' ] );
    }

    public static function render( $atts ) : string {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return '<div class="wc-sft-error">WooCommerce is not active.</div>';
        }

        $atts = shortcode_atts( [
            'columns'   => 3,
            'per_page'  => 9,
            'sort'      => '',
            'cat_in'    => '',
            'tag_in'    => '',
            'brand_in'  => '',
            'sellable_cat_slug' => 'bf-sale-2025',
            'price_min' => '',
            'price_max' => '',
        ], $atts, 'wc_sft' );

        // Resolve initial include scopes to IDs
        $resolved = [
            'cat_in'   => wc_sft_resolve_terms_to_ids( $atts['cat_in'], 'product_cat' ),
            'tag_in'   => wc_sft_resolve_terms_to_ids( $atts['tag_in'], 'product_tag' ),
            'brand_in' => wc_sft_resolve_terms_to_ids( $atts['brand_in'], 'product_brand' ),
        ];

        // Build counts map for categories in “sellable” set (same logic you had)
        $sellable = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => [ $atts['sellable_cat_slug'] ],
            ]],
        ]);

        $category_map = [];
        if ( $sellable->have_posts() ) {
            foreach ( $sellable->posts as $pid ) {
                $cats = wp_get_post_terms( $pid, 'product_cat' );
                if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
                    $primary = $cats[0];
                    if ( ! isset( $category_map[ $primary->term_id ] ) ) {
                        $category_map[ $primary->term_id ] = [ 'name' => $primary->name, 'count' => 0 ];
                    }
                    $category_map[ $primary->term_id ]['count']++;
                }
            }
        }
        wp_reset_postdata();

        $brand_terms = wc_sft_get_sellable_terms( 'product_brand', $atts['sellable_cat_slug'] );
        $tag_terms   = wc_sft_get_sellable_terms( 'product_tag',   $atts['sellable_cat_slug'] );

        $for_js = [
            'columns'   => (int) $atts['columns'],
            'per_page'  => (int) $atts['per_page'],
            'sort'      => sanitize_text_field( $atts['sort'] ),
            'price_min' => $atts['price_min'] !== '' ? (float) $atts['price_min'] : '',
            'price_max' => $atts['price_max'] !== '' ? (float) $atts['price_max'] : '',
            'includes'  => $resolved,
            'sellable_set' => [ 'cat_slug' => sanitize_text_field( $atts['sellable_cat_slug'] ) ],
        ];

        ob_start(); ?>
        <div class="wc-sft-wrapper" data-shortcode-attrs='<?php echo esc_attr( wp_json_encode( $for_js ) ); ?>'>
            <button class="wc-sft-toggle-btn" type="button"><?php esc_html_e('Toggle Filters','wc-sellable-toggle-filter'); ?></button>

            <aside class="wc-sft-sidebar">
                <div class="wc-sft-filter-box">
                    <h3><?php esc_html_e('Categories','wc-sellable-toggle-filter'); ?></h3>
                    <div class="wc-sft-chips" data-filter="category">
                        <button type="button" class="wc-sft-chip wc-sft-chip--all is-selected" data-term="">
                            <?php printf('%s (%d)', esc_html__('All','wc-sellable-toggle-filter'), intval($sellable->found_posts)); ?>
                        </button>
                        <?php foreach ( $category_map as $tid => $data ) : ?>
                            <button type="button" class="wc-sft-chip" data-term="<?php echo esc_attr($tid); ?>">
                                <?php echo esc_html($data['name']); ?>
                                <span class="wc-sft-chip-count"><?php echo intval($data['count']); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ( ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ) : ?>
                <div class="wc-sft-filter-box">
                    <h3><?php esc_html_e('Tags','wc-sellable-toggle-filter'); ?></h3>
                    <div class="wc-sft-chips" data-filter="tag">
                        <button type="button" class="wc-sft-chip wc-sft-chip--all is-selected" data-term=""><?php esc_html_e('All','wc-sellable-toggle-filter'); ?></button>
                        <?php foreach ( $tag_terms as $t ) : ?>
                            <button type="button" class="wc-sft-chip" data-term="<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $brand_terms ) && ! is_wp_error( $brand_terms ) ) : ?>
                <div class="wc-sft-filter-box">
                    <h3><?php esc_html_e('Brands','wc-sellable-toggle-filter'); ?></h3>
                    <div class="wc-sft-chips" data-filter="brand">
                        <button type="button" class="wc-sft-chip wc-sft-chip--all is-selected" data-term=""><?php esc_html_e('All','wc-sellable-toggle-filter'); ?></button>
                        <?php foreach ( $brand_terms as $t ) : ?>
                            <button type="button" class="wc-sft-chip" data-term="<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>

            <main class="wc-sft-main">
                <div class="wc-sft-sort-bar">
                    <label for="wc-sft-sort"><?php esc_html_e('Sort by:','wc-sellable-toggle-filter'); ?></label>
                    <select id="wc-sft-sort" class="wc-sft-sort">
                        <option value=""><?php esc_html_e('Default','wc-sellable-toggle-filter'); ?></option>
                        <option value="price_asc"><?php esc_html_e('Price: Low to High','wc-sellable-toggle-filter'); ?></option>
                        <option value="price_desc"><?php esc_html_e('Price: High to Low','wc-sellable-toggle-filter'); ?></option>
                        <option value="in_stock"><?php esc_html_e('In Stock','wc-sellable-toggle-filter'); ?></option>
                        <option value="preorder"><?php esc_html_e('Pre-Order','wc-sellable-toggle-filter'); ?></option>
                        <option value="out_of_stock"><?php esc_html_e('Out of Stock','wc-sellable-toggle-filter'); ?></option>
                    </select>
                </div>

                <ul class="wc-sft-products-grid" aria-live="polite" aria-busy="false"></ul>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
