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
    <h1><?php echo esc_html__('Smart Product Export - SKU Export Tool', 'smart-product-export'); ?></h1>

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
                                <select name="filter_type" id="filter_type" class="regular-text">
                                    <option value="all"><?php echo esc_html__('All Products', 'smart-product-export'); ?></option>
                                    <option value="sku"><?php echo esc_html__('By SKU (partial match)', 'smart-product-export'); ?></option>
                                    <option value="id"><?php echo esc_html__('By Product ID(s)', 'smart-product-export'); ?></option>
                                    <option value="category"><?php echo esc_html__('By Category Slug', 'smart-product-export'); ?></option>
                                    <option value="tag"><?php echo esc_html__('By Tag Slug', 'smart-product-export'); ?></option>
                                    <option value="attribute"><?php echo esc_html__('By Attribute', 'smart-product-export'); ?></option>
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
                                <input type="text" name="filter_value" id="filter_value" class="regular-text" placeholder="">
                                <p class="description" id="filter_value_desc">
                                    <?php echo esc_html__('Leave empty to get all products.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo esc_html__('Options', 'smart-product-export'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="include_variations" id="include_variations" value="yes">
                                    <?php echo esc_html__('Include product variations', 'smart-product-export'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Check this to include variation SKUs for variable products.', 'smart-product-export'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="spe-export-btn">
                        <span class="dashicons dashicons-download"></span>
                        <?php echo esc_html__('Export SKUs', 'smart-product-export'); ?>
                    </button>
                </p>
            </form>
        </div>

        <div class="spe-card spe-results-card" id="spe-results-card" style="display: none;">
            <div class="spe-results-header">
                <h2><?php echo esc_html__('Export Results', 'smart-product-export'); ?></h2>
                <button type="button" class="button" id="spe-copy-btn">
                    <span class="dashicons dashicons-clipboard"></span>
                    <?php echo esc_html__('Copy to Clipboard', 'smart-product-export'); ?>
                </button>
            </div>

            <div class="spe-message" id="spe-message"></div>

            <div class="spe-results-wrapper">
                <textarea id="spe-results" class="spe-results-textarea" readonly></textarea>
            </div>

            <div class="spe-stats">
                <span class="spe-stats-label"><?php echo esc_html__('Total SKUs:', 'smart-product-export'); ?></span>
                <span class="spe-stats-count" id="spe-count">0</span>
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
                    <?php echo esc_html__('Enter the category slug (e.g., "clothing", "electronics").', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Tag:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Enter the product tag slug (e.g., "sale", "featured").', 'smart-product-export'); ?>
                </li>
                <li>
                    <strong><?php echo esc_html__('By Attribute:', 'smart-product-export'); ?></strong>
                    <?php echo esc_html__('Enter in format "attribute_name:value" (e.g., "color:blue", "size:large").', 'smart-product-export'); ?>
                </li>
            </ul>
        </div>
    </div>
</div>
