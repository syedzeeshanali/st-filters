<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wc_sft_table_name() : string {
    global $wpdb;
    return "{$wpdb->prefix}sft_subscribers";
}

/** Resolve CSV of IDs/slugs/names → array of term IDs */
function wc_sft_resolve_terms_to_ids( string $csv, string $taxonomy ) : array {
    $csv = trim( $csv );
    if ( $csv === '' ) return [];
    $parts = array_filter( array_map( 'trim', preg_split( '/\s*,\s*/', $csv ) ) );
    $ids = [];
    foreach ( $parts as $p ) {
        if ( ctype_digit( $p ) ) { $ids[] = (int) $p; continue; }
        $term = get_term_by( 'slug', sanitize_title( $p ), $taxonomy );
        if ( $term && ! is_wp_error( $term ) ) { $ids[] = (int) $term->term_id; continue; }
        $term = get_term_by( 'name', $p, $taxonomy );
        if ( $term && ! is_wp_error( $term ) ) { $ids[] = (int) $term->term_id; }
    }
    return array_values( array_unique( $ids ) );
}

/** Get terms used by products in “sellable” set */
function wc_sft_get_sellable_terms( string $taxonomy, string $sellable_cat_slug = 'bf-sale-2025' ) {
    if ( ! taxonomy_exists( $taxonomy ) ) return [];
    $q = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'tax_query'      => [[
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => [ $sellable_cat_slug ],
        ]],
    ]);
    if ( empty( $q->posts ) ) return [];
    return wp_get_object_terms( $q->posts, $taxonomy );
}
