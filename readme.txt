=== Smart Product Export ===
Contributors: OpenWPClub
Tags: woocommerce, export, products, sku, bulk export
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Export WooCommerce product SKUs and data based on various filter criteria including categories, tags, attributes, stock status, and more.

== Description ==

Smart Product Export is a powerful and user-friendly plugin that allows you to quickly export WooCommerce product information based on flexible filtering criteria. Whether you need to export SKUs for inventory management, price lists for suppliers, or product catalogs for marketing purposes, this plugin has you covered.

= Key Features =

* **Multiple Filter Options** - Export products by:
  * All Products
  * SKU (partial match)
  * Product ID(s)
  * Categories (multiple selection)
  * Tags (multiple selection)
  * Attributes (multiple selection)
  * Stock Status (In Stock, Out of Stock, On Backorder)
  * Product Type (Simple, Variable, Grouped, External)

* **Flexible Export Formats** - Choose what to export:
  * SKU only
  * SKU + Product Title
  * SKU + Price
  * SKU + Stock Level
  * SKU + Title + Price + Stock (all information)

* **Custom Delimiters** - Separate values with:
  * Comma (for CSV/Excel)
  * Semicolon (for European formats)
  * Tab (for databases)
  * New Line (for lists)

* **Product Variations Support** - Optionally include variation SKUs for variable products

* **Performance Optimized** - Uses caching and optimized queries for fast exports even with large product catalogs

* **HPOS Compatible** - Fully compatible with WooCommerce High-Performance Order Storage

* **Accessibility Ready** - Built with WCAG 2.1 compliance in mind, includes ARIA labels and keyboard navigation support

* **Secure** - Enhanced security with proper nonce verification, input validation, and sanitization

= Use Cases =

* **Inventory Management** - Export SKUs for inventory audits and reconciliation
* **Supplier Orders** - Generate product lists for purchase orders
* **Marketing Campaigns** - Create product catalogs for email campaigns or print materials
* **Price List Updates** - Export products with prices for supplier or customer price lists
* **Stock Management** - Track products by stock status
* **Data Analysis** - Export product data for business intelligence and reporting

== Installation ==

1. Upload the `smart-product-export` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure WooCommerce is installed and activated
4. Navigate to WooCommerce > Smart Product Exporter to start using the plugin

= Minimum Requirements =

* WordPress 5.8 or greater
* WooCommerce 5.0 or greater
* PHP version 7.4 or greater

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and activated for this plugin to work.

= Can I export product variations? =

Yes! Check the "Include product variations" option to export variation SKUs along with parent products.

= What happens if a product doesn't have a SKU? =

Products without SKUs are automatically excluded from the export. You'll see a message indicating how many products were found but had no SKUs assigned.

= Can I export to a CSV file? =

The plugin exports data to a text area that you can copy to clipboard. You can then paste it into Excel, Google Sheets, or any text editor. Select "Comma" as the delimiter for best CSV compatibility.

= How do I export multiple categories at once? =

When you select "By Category" as the filter type, a multi-select dropdown appears. Hold Ctrl (Cmd on Mac) to select multiple categories. Products in ANY of the selected categories will be exported.

= Does this work with custom product attributes? =

Yes! The plugin automatically detects all your WooCommerce product attributes and displays them in the filter dropdown.

= Is the plugin compatible with HPOS (High-Performance Order Storage)? =

Yes, the plugin is fully compatible with WooCommerce's High-Performance Order Storage feature.

= How is performance on large stores? =

The plugin uses optimized database queries, caching, and efficient data processing to handle stores with thousands of products. Results are cached for 5-10 minutes to improve performance.

= Can I filter by stock status? =

Yes! You can filter products by In Stock, Out of Stock, or On Backorder status.

= What product types are supported? =

All WooCommerce product types are supported: Simple, Variable, Grouped, and External/Affiliate products.

== Screenshots ==

1. Main export interface with filter options
2. Export format and delimiter selection
3. Results display with copy to clipboard functionality
4. Quick guide and help section

== Changelog ==

= 1.0.0 =
* Initial release
* Multiple filter options (Category, Tag, Attribute, SKU, ID, Stock Status, Product Type)
* Flexible export formats (SKU only, with title, price, stock, or all)
* Custom delimiter support (comma, semicolon, tab, newline)
* Product variation support
* Performance optimization with caching
* HPOS compatibility
* Accessibility improvements (ARIA labels, keyboard navigation)
* Enhanced security (input validation, sanitization)
* Comprehensive inline help and documentation

== Upgrade Notice ==

= 1.0.0 =
Initial release of Smart Product Export.

== Support ==

For support, feature requests, or bug reports, please visit:
https://openwpclub.com/support/

== Privacy Policy ==

This plugin does not collect, store, or transmit any user data. All export operations are performed locally on your WordPress installation.

== Credits ==

Developed by OpenWPClub.com
