# Smart Product Export

A WordPress plugin that allows you to export WooCommerce product SKUs based on various filter criteria, making inventory management and bulk operations simple and efficient.

## Features

- **Multiple Filter Options**: Export by category, tag, attribute, SKU, product ID, or all products
- **Multi-Select Support**: Select multiple categories, tags, or attributes at once (OR logic)
- **Dynamic Dropdowns**: User-friendly interface with automatically populated filter options
- **Variation Support**: Option to include product variation SKUs in exports
- **One-Click Copy**: Copy results to clipboard with a single click
- **Real-time Results**: AJAX-powered instant results without page reload
- **Comma-Separated Output**: Export format ready for spreadsheets and bulk operations
- **WooCommerce Integration**: Integrated directly into WooCommerce admin menu

## Requirements

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+

## Installation

1. Upload the plugin files to `/wp-content/plugins/smart-product-export/`
2. Activate the plugin through the WordPress admin
3. Go to **WooCommerce > Smart Product Exporter** to start exporting

## Usage

### Exporting Products

1. Navigate to **WooCommerce > Smart Product Exporter**
2. Select your filter type from the dropdown
3. Choose one or more filter values (for categories, tags, or attributes)
4. Optionally check "Include product variations"
5. Click **Export SKUs**
6. Copy the comma-separated results to your clipboard

### Filter Types

**All Products:** Export all product SKUs from your store

**By SKU:** Search for products containing specific SKU text (partial match)

**By Product ID:** Enter one or more product IDs separated by commas

**By Category:** Select one or more product categories from the dropdown

**By Tag:** Select one or more product tags from the dropdown

**By Attribute:** Select one or more attribute values (e.g., "Color: Blue", "Size: Large")

### Multi-Select Filters

For categories, tags, and attributes:
- Hold **Ctrl** (Windows/Linux) or **Cmd** (Mac) to select multiple items
- Products matching **any** selected filter will be included (OR logic)
- The dropdown shows the number of products in each category/tag

### Output Format

Results are displayed as comma-separated SKUs:
```
SKU-001, SKU-002, SKU-003, SKU-004, SKU-005
```

Use the **Copy to Clipboard** button to quickly copy results for use in spreadsheets, imports, or other systems.

## Technical Features

- **AJAX-Powered Interface**: Fast, seamless filtering without page reloads
- **Dynamic Option Loading**: Categories, tags, and attributes load on demand
- **Smart Messaging**: Clear feedback when products are found but lack SKUs
- **Security**: Nonce verification and capability checks on all AJAX requests
- **Clean Code**: Well-documented, follows WordPress coding standards

## Admin Features

- **Integrated Menu**: Appears under WooCommerce menu for easy access
- **Helpful Tooltips**: Context-sensitive descriptions for each filter type
- **Quick Guide**: Built-in reference guide on the export page
- **Product Count Display**: Shows total SKUs found in real-time
- **Responsive Design**: Works on all screen sizes and devices

## License & Author

This plugin is licensed under GPL v2 or later.

Developed by [OpenWPClub.com](https://openwpclub.com)
