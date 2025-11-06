<?php
/**
 * Custom product template for Sellable Toggle Filter (AJAX).
 * Outputs a single <li> per product. Must NOT output <ul> or other wrappers.
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

// Stock status badge
$stock_status = $product->get_stock_status(); // instock, outofstock, onbackorder
$badge_label  = '';
switch ( $stock_status ) {
    case 'instock':
        $badge_label = __( 'In Stock', 'wc-sellable-toggle-filter' );
        break;
    case 'onbackorder':
        $badge_label = __( 'Pre-Order', 'wc-sellable-toggle-filter' );
        break;
    case 'outofstock':
        $badge_label = __( 'Out of Stock', 'wc-sellable-toggle-filter' );
        break;
}
?>

<li <?php wc_product_class( 'wc-sft-product', $product ); ?>>
    <div class="wc-sft-product-inner">

        <?php if ( $badge_label ) : ?>
            <span class="wc-sft-stock-badge wc-sft-<?php echo esc_attr( $stock_status ); ?>">
                <?php echo esc_html( $badge_label ); ?>
            </span>
        <?php endif; ?>

        <a href="<?php the_permalink(); ?>" class="wc-sft-thumb-link" aria-hidden="true">
            <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
        </a>

        <h4 class="wc-sft-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h4>

        <div class="wc-sft-price">
            <?php echo $product->get_price_html(); ?>
        </div>

<?php if ( $stock_status === 'outofstock' ) : ?>
    <button type="button" 
    class="wc-sft-notify-btn" 
    data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" 
    data-product-title="<?php echo esc_attr( $product->get_name() ); ?>">
    <?php esc_html_e( 'Notify Me', 'wc-sellable-toggle-filter' ); ?>
</button>
<?php else : ?>
    <div class="wc-sft-add-to-cart">
        <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
<?php endif; ?>



        <?php 
        $productPrice = (float) $product->get_price();
        if ( $productPrice > 30 ) : ?>
            <div class="wc-sft-paypal" aria-hidden="true">
                <a href="https://www.paypal.com/credit-presentment/lander/modal?payer_id=YHXF4GEEBVRBC&amp;offer=PAY_LATER_SHORT_TERM"
                   target="_blank"
                   rel="noopener" class="wc-sft-paypal-link">
                    <img loading="lazy"
                         class="wc-sft-paypal-logo"
                         src="https://vanrooy.com.au/wp-content/uploads/2024/06/paypal-logo.svg"
                         alt="PayPal"
                         width="80" height="25">
                </a>
                <p class="wc-sft-paypal-text">
                    <?php esc_html_e( 'Pay in 4 interest-free payments on purchases of $30–$2,000', 'wc-sellable-toggle-filter' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</li>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log("I'm loaded");
    $('.wc-sft-notify-btn').on('click', function(e) {
        e.preventDefault();

        // Collect product info
        var productId = $(this).data('product-id');
        var productTitle = $(this).data('product-title');

        // Store globally
        window.WC_SFT_Product = { id: productId, title: productTitle };

        // Open Elementor popup
        if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules.popup) {
            elementorProFrontend.modules.popup.showPopup({ id: 28378 });
        } else {
            console.warn('Elementor Pro frontend not loaded or popup module missing.');
        }
    });

    // When popup opens, fill the form field
    $(document).on('elementor/popup/show', function(event, id, instance) {
        if (id === 28378 && window.WC_SFT_Product) {
            var productName = window.WC_SFT_Product.title;

            // Find the product name field in your Elementor form
            var $field = $('#form-field-product_name');

            if ($field.length) {
                $field.val(productName).prop('readonly', true);
            }
        }
    });
});
</script>