<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class WC_SFT_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'st_filter'; }
    public function get_title() { return __( 'ST Filter', 'wc-sellable-toggle-filter' ); }
    public function get_icon() { return 'eicon-filter'; }
    public function get_categories() { return [ 'wc-sft' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'section_query', [ 'label' => __( 'Query', 'wc-sellable-toggle-filter' ), 'tab' => Controls_Manager::TAB_CONTENT ] );

        $this->add_control( 'cat_in', [
            'label' => __( 'Categories Include', 'wc-sellable-toggle-filter' ),
            'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true,
            'options' => $this->terms_options( 'product_cat' ),
        ] );
        $this->add_control( 'tag_in', [
            'label' => __( 'Tags Include', 'wc-sellable-toggle-filter' ),
            'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true,
            'options' => $this->terms_options( 'product_tag' ),
        ] );
        $this->add_control( 'brand_in', [
            'label' => __( 'Brands Include', 'wc-sellable-toggle-filter' ),
            'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true,
            'options' => taxonomy_exists('product_brand') ? $this->terms_options('product_brand') : [],
        ] );

        $this->add_control( 'columns',  [ 'label'=>__('Columns','wc-sellable-toggle-filter'),'type'=>Controls_Manager::NUMBER,'min'=>1,'max'=>6,'step'=>1,'default'=>3 ] );
        $this->add_control( 'per_page', [ 'label'=>__('Per Page','wc-sellable-toggle-filter'),'type'=>Controls_Manager::NUMBER,'min'=>1,'max'=>60,'step'=>1,'default'=>9 ] );
        $this->add_control( 'sort', [
            'label'=>__('Default Sort','wc-sellable-toggle-filter'),'type'=>Controls_Manager::SELECT,'default'=>'',
            'options'=>[
                ''=>'Default','price_asc'=>__('Price: Low to High','wc-sellable-toggle-filter'),
                'price_desc'=>__('Price: High to Low','wc-sellable-toggle-filter'),
                'in_stock'=>__('In Stock','wc-sellable-toggle-filter'),
                'preorder'=>__('Pre-Order','wc-sellable-toggle-filter'),
                'out_of_stock'=>__('Out of Stock','wc-sellable-toggle-filter'),
            ],
        ] );
        $this->add_control( 'sellable_cat_slug', [
            'label'=>__('Sellable Base Category Slug','wc-sellable-toggle-filter'),
            'type'=>Controls_Manager::TEXT, 'default'=>'bf-sale-2025',
        ] );
        $this->end_controls_section();

        // Style controls (Card/Title/Price) – same as before
        $this->start_controls_section('section_style_card',[ 'label'=>__('Card','wc-sellable-toggle-filter'),'tab'=>Controls_Manager::TAB_STYLE ]);
        $this->add_group_control( Group_Control_Background::get_type(), [ 'name'=>'card_bg','selector'=>'{{WRAPPER}} .wc-sft-product-inner' ] );
        $this->add_group_control( Group_Control_Border::get_type(),     [ 'name'=>'card_border','selector'=>'{{WRAPPER}} .wc-sft-product-inner' ] );
        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name'=>'card_shadow','selector'=>'{{WRAPPER}} .wc-sft-product-inner' ] );
        $this->add_responsive_control('card_padding',[ 'label'=>__('Padding','wc-sellable-toggle-filter'),'type'=>Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em'],'selectors'=>['{{WRAPPER}} .wc-sft-product-inner'=>'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'] ]);
        $this->end_controls_section();

        $this->start_controls_section('section_style_title',[ 'label'=>__('Product Title','wc-sellable-toggle-filter'),'tab'=>Controls_Manager::TAB_STYLE ]);
        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name'=>'title_typo','selector'=>'{{WRAPPER}} .wc-sft-title, {{WRAPPER}} .wc-sft-title a' ] );
        $this->add_control('title_color',[ 'label'=>__('Color','wc-sellable-toggle-filter'),'type'=>Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .wc-sft-title, {{WRAPPER}} .wc-sft-title a'=>'color: {{VALUE}};'] ]);
        $this->end_controls_section();

        $this->start_controls_section('section_style_price',[ 'label'=>__('Price','wc-sellable-toggle-filter'),'tab'=>Controls_Manager::TAB_STYLE ]);
        $this->add_group_control( Group_Control_Typography::get_type(), [ 'name'=>'price_typo','selector'=>'{{WRAPPER}} .wc-sft-price' ] );
        $this->add_control('price_color',[ 'label'=>__('Color','wc-sellable-toggle-filter'),'type'=>Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .wc-sft-price'=>'color: {{VALUE }};'] ]);
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $cat   = !empty($s['cat_in'])   ? implode(',', array_map('intval', (array) $s['cat_in']))   : '';
        $tag   = !empty($s['tag_in'])   ? implode(',', array_map('intval', (array) $s['tag_in']))   : '';
        $brand = !empty($s['brand_in']) ? implode(',', array_map('intval', (array) $s['brand_in'])) : '';

        echo do_shortcode( sprintf(
            '[wc_sft columns="%d" per_page="%d" sort="%s" cat_in="%s" tag_in="%s" brand_in="%s" sellable_cat_slug="%s"]',
            (int)($s['columns'] ?? 3),
            (int)($s['per_page'] ?? 9),
            esc_attr($s['sort'] ?? ''),
            esc_attr($cat), esc_attr($tag), esc_attr($brand),
            esc_attr($s['sellable_cat_slug'] ?? 'bf-sale-2025')
        ) );
    }

    private function terms_options( $taxonomy ) : array {
        $out=[]; if ( ! taxonomy_exists($taxonomy) ) return $out;
        $terms = get_terms([ 'taxonomy'=>$taxonomy, 'hide_empty'=>false ]);
        if ( is_wp_error($terms) || empty($terms) ) return $out;
        foreach( $terms as $t ) $out[(string)$t->term_id] = $t->name.' (#'.$t->term_id.')';
        return $out;
    }
}
