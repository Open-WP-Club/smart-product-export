# Smart Product Export for WooCommerce

A powerful WordPress plugin that enables flexible export of WooCommerce product data with advanced filtering options.

## Features

### Filter Options
- **All Products** - Export entire product catalog
- **By SKU** - Search with partial SKU matching
- **By Product ID(s)** - Export specific products by ID
- **By Category** - Multiple category selection with OR logic
- **By Tag** - Multiple tag selection with OR logic
- **By Attribute** - Filter by any product attribute
- **By Stock Status** - Filter by In Stock, Out of Stock, or On Backorder
- **By Product Type** - Filter by Simple, Variable, Grouped, or External products

### Export Formats
- SKU only
- SKU + Product Title
- SKU + Price
- SKU + Stock Level
- SKU + Title + Price + Stock (comprehensive export)

### Delimiter Options
- Comma (CSV-friendly)
- Semicolon (European CSV format)
- Tab (database-friendly)
- New Line (list format)

### Advanced Features
- ✅ Product variation support
- ✅ Performance optimized with caching (5-10 minute cache lifetime)
- ✅ HPOS (High-Performance Order Storage) compatible
- ✅ WCAG 2.1 accessibility compliant
- ✅ Enhanced security (nonce verification, input validation, sanitization)
- ✅ Responsive design
- ✅ Real-time AJAX processing
- ✅ Copy to clipboard functionality

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/smart-product-export/`
2. Activate the plugin through the WordPress admin
3. Go to **WooCommerce > Smart Product Exporter** to start exporting

## Usage

### Basic Export
1. Navigate to **WooCommerce > Smart Product Exporter**
2. Select a filter type from the dropdown
3. Enter or select your filter criteria
4. Choose export format and delimiter
5. Click "Export Products"
6. Copy the results to clipboard

### Advanced Filtering

#### Multiple Categories
```
Filter Type: By Category
Select: Electronics, Clothing, Books
Result: Products in ANY of these categories
```

#### Partial SKU Match
```
Filter Type: By SKU
Value: SHIRT
Matches: SHIRT-001, TSHIRT-RED, SHIRT-BLUE-L, etc.
```

#### Multiple Product IDs
```
Filter Type: By Product ID(s)
Value: 123, 456, 789
Result: Only products with these specific IDs
```

#### Stock Status Filtering
```
Filter Type: By Stock Status
Select: In Stock, On Backorder
Result: Products that are either in stock or on backorder
```

### Export Format Examples

**SKU Only:**
```
SHIRT-001, SHIRT-002, PANTS-001
```

**SKU + Title:**
```
SHIRT-001 - Blue Cotton Shirt, SHIRT-002 - Red Polo Shirt
```

**SKU + All (using newline delimiter):**
```
SHIRT-001 | Blue Cotton Shirt | $29.99 | 45
SHIRT-002 | Red Polo Shirt | $34.99 | 23
```

## Performance Optimization

### Caching Strategy
- Taxonomy terms (categories, tags, attributes): 10-minute cache
- Product query results: 5-minute cache
- Cache keys: Automatically generated based on filter criteria

## Support

For support, bug reports, or feature requests:
- Website: https://openwpclub.com/support/

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html