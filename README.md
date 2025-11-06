=== Sellable Filters ===
Contributors: syedzeeshan
Tags: woocommerce, filters, product-filter, ajax, elementor, chips, facets
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sellable Filters adds modern multi-select chip filters (categories, tags, brands), AJAX product grid, and an Elementor widget (“ST Filter”) for WooCommerce.

== Description ==
**Sellable Filters** is a lightweight, repo-friendly product filtering plugin for WooCommerce. It ships with:
- **Multi-select chips** for Categories, Tags, and Brands (optional).
- **AJAX product grid** with sorting and price range support.
- **Elementor widget: ST Filter** with Query and Style tabs (background, typography, colors, spacing).
- **Stock notification** (“Notify me”) hook compatible with Elementor Popup.
- Clean architecture: slim bootstrap, classes in `/includes`, templates in `/templates`.

**Why this plugin?**
Most filters feel heavy or dated. Sellable Filters focuses on the modern UX your shoppers expect: chip selectors, instant updates, and a clean codebase you can extend.

== Features ==
* Multi-select chips for Categories, Tags, Brands (if taxonomy exists)
* AJAX loading (no page reloads)
* Sort: Price (asc/desc), In Stock, Pre-Order, Out of Stock
* Elementor widget (**ST Filter**) that renders the same shortcode
* Shortcode `[wc_sft]` with include scopes: `cat_in`, `tag_in`, `brand_in`
* Optional price_min / price_max
* Custom product loop template overrideable in theme via `woocommerce/content-product-sft.php`
* Stock notify endpoint for back-in-stock popups

== Shortcode ==
Basic:
`[wc_sft]`

With scope and options:
`[wc_sft cat_in="helmets,45" tag_in="summer,clearance" brand_in="arai,7" columns="3" per_page="12" sort="price_asc"]`

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate **Sellable Filters** through the Plugins screen.
3. Add the shortcode `[wc_sft]` to a page OR drag the **ST Filter** widget in Elementor.
4. (Optional) Create an Elementor popup and map the hidden fields for the “Notify me” button.

== Frequently Asked Questions ==
= Does it work with my theme? =
It uses WooCommerce templates and standard hooks, so it should work with most themes. The grid uses simple Flexbox CSS.

= Can I style the product cards? =
Yes. Use the Elementor Style tab or add CSS to your theme. The card markup lives in `templates/content-product-sft.php` and can be overridden in your theme.

= How do I change the base “sellable” set? =
By default, products in the `bf-sale-2025` category are considered sellable. Change the slug via shortcode (`sellable_cat_slug`) or in a future settings screen.

== Screenshots ==
1. Multi-select chip filters (Categories, Tags, Brands)
2. AJAX product grid with stock badges
3. Elementor “ST Filter” widget (Query & Style tabs)
4. “Notify me” popup sample

== Changelog ==
= 1.7.0 =
* Initial public release: clean bootstrap, includes/, Elementor widget, chip filters, AJAX.

== Upgrade Notice ==
= 1.7.0 =
This is the first repository release.
