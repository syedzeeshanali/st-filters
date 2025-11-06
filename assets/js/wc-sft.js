jQuery(document).ready(function() {
  
  // ==============================
  // Notify Me popup handling
  // ==============================
    jQuery(document).on("click", ".wc-sft-notify-btn", function (e) {
  e.preventDefault();
  let productId = jQuery(this).data("product-id");
  let productTitle = jQuery(this).data("product-title");

  // Open Elementor popup by ID
  elementorProFrontend.modules.popup.showPopup({id: 28378}); // Replace 1234 with your popup ID

  // Optionally set hidden field values
  setTimeout(() => {
    jQuery("#form-field-sft_product_id").val(productId);
    jQuery("#form-field-sft_product_title").val(productTitle);
  }, 300);

    });
});

jQuery(function ($) {
   function loadProducts(extraData = {}) {
    var wrapper = $(".wc-sft-wrapper");
    if (!wrapper.length) return;

    var grid = wrapper.find(".wc-sft-products-grid");
    var attrs = wrapper.data("shortcode-attrs") || {};

    grid.addClass("loading").attr("aria-busy", "true");

    var data = {
      action: "wc_sft_get_products",
      _nonce: wc_sft_vars.nonce,
      shortcode_attrs: attrs,
      cat_id: wrapper.find('[data-filter="category"] a.active').data("cat") || "",
      brand_id: wrapper.find('[data-filter="brand"] a.active').data("brand") || "",
      sort: $("#wc-sft-sort").val() || "",
      price_min: parseFloat($("#wc-sft-price-min").val()) || 0,
      price_max: parseFloat($("#wc-sft-price-max").val()) || 999999,
    };

    // allow overrides
    $.extend(data, extraData);

    $.post(wc_sft_vars.ajax_url, data, function (response) {
      grid.removeClass("loading").attr("aria-busy", "false");

      if (response && response.success && response.data && response.data.html) {
        // Server should return a complete <ul class="products">...</ul>
        grid.html(response.data.html);
      } else {
        var msg =
          response && response.data && response.data.message
            ? response.data.message
            : "No products found.";
        grid.html('<div class="wc-sft-no-products">' + msg + "</div>");
      }
    }, "json").fail(function () {
      grid.removeClass("loading").attr("aria-busy", "false");
      grid.html('<div class="wc-sft-no-products">An error occurred while loading products.</div>');
    });
  }

  // initial load
  loadProducts();

  // Category / Brand click (single-select)
  $(document).on("click", ".wc-sft-filter-link", function (e) {
    e.preventDefault();
    var $t = $(this);
    $t.closest("ul").find("a").removeClass("active");
    $t.addClass("active");
    loadProducts();
    if ($(window).width() <= 768) {
      $(".wc-sft-sidebar").removeClass("open");
    }
  });

  // Sort change
  $(document).on("change", "#wc-sft-sort", function () {
    loadProducts();
  });

  // Price apply
  $(document).on("click", ".wc-sft-price-apply", function (e) {
    e.preventDefault();
    loadProducts();
    if ($(window).width() <= 768) {
      $(".wc-sft-sidebar").removeClass("open");
    }
  });

  // Toggle (mobile)
  $(document).on("click", ".wc-sft-toggle-btn", function () {
    $(".wc-sft-sidebar").toggleClass("open");
  });

});
