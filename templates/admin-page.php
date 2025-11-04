<?php
/**
 * Admin Page Template
 *
 * @package Smart_Product_Export
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap spe-wrap">
    <h1><?php echo esc_html__('Smart Product Exporter', 'smart-product-export'); ?></h1>

    <div class="spe-container">
        <div class="spe-card">
            <h2><?php echo esc_html__('Filter Products', 'smart-product-export'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Select filter criteria to export product SKUs. Results will be displayed as comma-separated values.', 'smart-product-export'); ?>
            </p>

            <form id="spe-export-form" class="spe-form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="filter_type"><?php echo esc_html__('Filter Type', 'smart-product-export'); ?></label>
                            </th>
                            <td>
                                <select name="filter_type" id="filter_type" class="regular-text" aria-describedby="filter_type_desc" aria-required="false">
                                    <option value="all"><?php echo esc_html__('All Products', 'smart-product-export'); ?></option>
                                    <option value="sku"><?php echo esc_html__('By SKU (partial match)', 'smart-product-export'); ?></option>
                                    <option value="id"><?php echo esc_html__('By Product ID(s)', 'smart-product-export'); ?></option>
                                    <option value="category"><?php echo esc_html__('By Category', 'smart-product-export'); ?></option>
                                    <option value="tag"><?php echo esc_html__('By Tag', 'smart-product-export'); ?></option>
                                    <option value="attribute"><?php echo esc_html__('By Attribute', 'smart-product-export'); ?></option>
                                    <option value="stock_status"><?php echo esc_html__('By Stock Status', 'smart-product-export'); ?></option>
                                    <option value="product_type"><?php echo esc_html__('By Product Type', 'smart-product-export'); ?></option>
                                </select>
                                <p class="description" id="filter_type_desc">
                                    <?php echo esc_html__('Select how you want to filter products.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="filter_value_row">
                            <th scope="row">
                                <label for="filter_value"><?php echo esc_html__('Filter Value', 'smart-product-export'); ?></label>
                            </th>
                            <td>
                                <!-- Text input for SKU and ID -->
                                <input type="text" name="filter_value" id="filter_value" class="regular-text" placeholder="" style="display: none;" aria-describedby="filter_value_desc">

                                <!-- Multi-select for categories, tags, and attributes -->
                                <div id="filter_value_select_wrapper" style="display: none;">
                                    <select name="filter_value[]" id="filter_value_select" class="spe-multi-select" multiple size="8" aria-describedby="filter_value_desc spe_select_hint" aria-label="<?php echo esc_attr__('Select filter values (multiple selection allowed)', 'smart-product-export'); ?>">
                                        <option value="" disabled><?php echo esc_html__('Loading...', 'smart-product-export'); ?></option>
                                    </select>
                                    <p class="description spe-select-hint" id="spe_select_hint">
                                        <?php echo esc_html__('Hold Ctrl (Cmd on Mac) to select multiple items', 'smart-product-export'); ?>
                                    </p>
                                </div>

                                <p class="description" id="filter_value_desc">
                                    <?php echo esc_html__('Leave empty to get all products.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="export_format"><?php echo esc_html__('Export Format', 'smart-product-export'); ?></label>
                            </th>
                            <td>
                                <select name="export_format" id="export_format" class="regular-text" aria-describedby="export_format_desc">
                                    <option value="sku"><?php echo esc_html__('SKU only', 'smart-product-export'); ?></option>
                                    <option value="sku_title"><?php echo esc_html__('SKU + Product Title', 'smart-product-export'); ?></option>
                                    <option value="sku_price"><?php echo esc_html__('SKU + Price', 'smart-product-export'); ?></option>
                                    <option value="sku_stock"><?php echo esc_html__('SKU + Stock', 'smart-product-export'); ?></option>
                                    <option value="sku_all"><?php echo esc_html__('SKU + Title + Price + Stock', 'smart-product-export'); ?></option>
                                </select>
                                <p class="description" id="export_format_desc">
                                    <?php echo esc_html__('Choose what information to include in the export.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="delimiter"><?php echo esc_html__('Delimiter', 'smart-product-export'); ?></label>
                            </th>
                            <td>
                                <select name="delimiter" id="delimiter" class="regular-text" aria-describedby="delimiter_desc">
                                    <option value="comma"><?php echo esc_html__('Comma', 'smart-product-export'); ?></option>
                                    <option value="semicolon"><?php echo esc_html__('Semicolon', 'smart-product-export'); ?></option>
                                    <option value="tab"><?php echo esc_html__('Tab', 'smart-product-export'); ?></option>
                                    <option value="newline"><?php echo esc_html__('New Line', 'smart-product-export'); ?></option>
                                </select>
                                <p class="description" id="delimiter_desc">
                                    <?php echo esc_html__('Choose how to separate the exported values.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo esc_html__('Additional Options', 'smart-product-export'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php echo esc_html__('Additional export options', 'smart-product-export'); ?></legend>
                                    <label for="include_variations">
                                        <input type="checkbox" name="include_variations" id="include_variations" value="yes" aria-describedby="include_variations_desc">
                                        <?php echo esc_html__('Include product variations', 'smart-product-export'); ?>
                                    </label>
                                    <p class="description" id="include_variations_desc">
                                        <?php echo esc_html__('Check this to include variation SKUs for variable products.', 'smart-product-export'); ?>
                                    </p>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="spe-export-btn" aria-label="<?php echo esc_attr__('Export products based on selected filters', 'smart-product-export'); ?>">
                        <span class="dashicons dashicons-download" aria-hidden="true"></span>
                        <?php echo esc_html__('Export Products', 'smart-product-export'); ?>
                    </button>
                </p>
            </form>
        </div>

        <div class="spe-card spe-results-card" id="spe-results-card" style="display: none;" role="region" aria-labelledby="spe-results-heading" aria-live="polite">
            <div class="spe-results-header">
                <h2 id="spe-results-heading"><?php echo esc_html__('Export Results', 'smart-product-export'); ?></h2>
                <button type="button" class="button" id="spe-copy-btn" aria-label="<?php echo esc_attr__('Copy export results to clipboard', 'smart-product-export'); ?>">
                    <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                    <?php echo esc_html__('Copy to Clipboard', 'smart-product-export'); ?>
                </button>
            </div>

            <div class="spe-message" id="spe-message" role="status" aria-live="polite"></div>

            <div class="spe-results-wrapper">
                <label for="spe-results" class="screen-reader-text"><?php echo esc_html__('Export results', 'smart-product-export'); ?></label>
                <textarea id="spe-results" class="spe-results-textarea" readonly aria-label="<?php echo esc_attr__('Exported product data', 'smart-product-export'); ?>"></textarea>
            </div>

            <div class="spe-stats" role="status" aria-live="polite">
                <span class="spe-stats-label"><?php echo esc_html__('Total Products:', 'smart-product-export'); ?></span>
                <span class="spe-stats-count" id="spe-count" aria-label="<?php echo esc_attr__('Total number of exported products', 'smart-product-export'); ?>">0</span>
            </div>
        </div>

        <div class="spe-card spe-help-card">
            <h3><?php echo esc_html__('Quick Guide', 'smart-product-export'); ?></h3>
            <ul class="spe-help-list">
                <li>
                    <strong><?php echo esc_html__('All Products:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Export all product SKUs from your store.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By SKU:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Search for products with SKUs containing your search term (e.g., "SHIRT" will match "SHIRT-001", "TSHIRT-RED").', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Product ID:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Enter one or more product IDs separated by commas (e.g., "123, 456, 789").', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Category:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Select one or more categories from the dropdown. Products in ANY selected category will be exported.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Tag:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Select one or more tags from the dropdown. Products with ANY selected tag will be exported.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Attribute:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Select one or more attribute values from the dropdown (e.g., "Color: Blue", "Size: Large"). Products with ANY selected attribute will be exported.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Stock Status:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Filter products by their stock availability (In Stock, Out of Stock, or On Backorder).', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Product Type:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Filter by product type (Simple, Variable, Grouped, or External/Affiliate).', 'smart-product-export'); ?>
                </li>
            </ul>

            <h4><?php echo esc_html__('Export Options', 'smart-product-export'); ?></h4>
            <ul class="spe-help-list">
                <li>
                    <strong><?php echo esc_html__('Export Format:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Choose to export SKU only, or include additional information like product title, price, and stock levels.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('Delimiter:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Select how to separate values: comma for spreadsheets, semicolon for some European formats, tab for databases, or new line for lists.', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('Include Variations:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Check this to also export all variations of variable products with their individual SKUs.', 'smart-product-export'); ?>
                </li>
            </ul>
        </div>
    </div>
</div>
