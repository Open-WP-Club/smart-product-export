<?php

/**
 * Plugin Name: Smart Product Export
 * Description: Export WooCommerce product SKUs based on various filter criteria including categories, tags, attributes, and more
 * Version: 1.0.0
 * Author: OpenWPClub.com
 * Author URI: https://openwpclub.com
 * License: GPL v2 or later
 * Text Domain: smart-product-export
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SPE_VERSION', '1.0.0');
define('SPE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SPE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Smart_Product_Export {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_spe_export_skus', array($this, 'ajax_export_skus'));
        add_action('wp_ajax_spe_get_categories', array($this, 'ajax_get_categories'));
        add_action('wp_ajax_spe_get_tags', array($this, 'ajax_get_tags'));
        add_action('wp_ajax_spe_get_attributes', array($this, 'ajax_get_attributes'));
    }

    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Smart Product Exporter', 'smart-product-export'),
            __('Smart Product Exporter', 'smart-product-export'),
            'manage_woocommerce',
            'smart-product-export',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('woocommerce_page_smart-product-export' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'spe-admin-style',
            SPE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SPE_VERSION
        );

        wp_enqueue_script(
            'spe-admin-script',
            SPE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SPE_VERSION,
            true
        );

        wp_localize_script('spe-admin-script', 'speAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spe_export_nonce')
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!$this->is_woocommerce_active()) {
            echo '<div class="wrap"><h1>' . esc_html__('Smart Product Exporter', 'smart-product-export') . '</h1>';
            echo '<div class="notice notice-error"><p>' . esc_html__('WooCommerce must be installed and activated to use this plugin.', 'smart-product-export') . '</p></div></div>';
            return;
        }

        include SPE_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * AJAX handler for exporting SKUs
     */
    public function ajax_export_skus() {
        // Enhanced security checks
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Unauthorized access.', 'smart-product-export')));
            return;
        }

        // Validate and sanitize filter type
        $allowed_filter_types = array('all', 'sku', 'id', 'category', 'tag', 'attribute', 'stock_status', 'product_type');
        $filter_type = isset($_POST['filter_type']) ? sanitize_text_field(wp_unslash($_POST['filter_type'])) : 'all';

        if (!in_array($filter_type, $allowed_filter_types, true)) {
            wp_send_json_error(array('message' => __('Invalid filter type.', 'smart-product-export')));
            return;
        }

        // Sanitize filter value
        $filter_value = isset($_POST['filter_value']) ? $_POST['filter_value'] : '';
        if (is_array($filter_value)) {
            $filter_value = array_map('sanitize_text_field', array_map('wp_unslash', $filter_value));
        } else {
            $filter_value = sanitize_text_field(wp_unslash($filter_value));
        }

        // Validate and sanitize additional options
        $include_variations = isset($_POST['include_variations']) && $_POST['include_variations'] === 'yes';

        // New export options
        $export_format = isset($_POST['export_format']) ? sanitize_text_field(wp_unslash($_POST['export_format'])) : 'sku';
        $allowed_formats = array('sku', 'sku_title', 'sku_price', 'sku_stock', 'sku_all');
        if (!in_array($export_format, $allowed_formats, true)) {
            $export_format = 'sku';
        }

        $delimiter = isset($_POST['delimiter']) ? sanitize_text_field(wp_unslash($_POST['delimiter'])) : 'comma';
        $allowed_delimiters = array('comma', 'semicolon', 'tab', 'newline');
        if (!in_array($delimiter, $allowed_delimiters, true)) {
            $delimiter = 'comma';
        }

        $result = $this->get_filtered_skus($filter_type, $filter_value, $include_variations);
        $products = $result['products'];
        $products_found = $result['products_found'];

        if (empty($products)) {
            $message = __('No products found matching the criteria.', 'smart-product-export');
            if ($products_found > 0) {
                $message = sprintf(
                    __('%d product(s) found, but none have SKUs assigned. Please add SKUs to your products in WooCommerce.', 'smart-product-export'),
                    $products_found
                );
            }
            wp_send_json_success(array(
                'skus' => '',
                'count' => 0,
                'message' => $message
            ));
        } else {
            $formatted_output = $this->format_export_data($products, $export_format, $delimiter);
            wp_send_json_success(array(
                'skus' => $formatted_output,
                'count' => count($products),
                'message' => sprintf(
                    __('%d product(s) exported.', 'smart-product-export'),
                    count($products)
                )
            ));
        }
    }

    /**
     * Get filtered products based on criteria with enhanced performance
     */
    private function get_filtered_skus($filter_type, $filter_value, $include_variations = false) {
        // Check cache first for taxonomy queries
        $cache_key = 'spe_products_' . md5(serialize(array($filter_type, $filter_value, $include_variations)));
        $cached_result = get_transient($cache_key);

        if (false !== $cached_result && is_array($cached_result)) {
            return $cached_result;
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        );

        // Apply filters based on type
        switch ($filter_type) {
            case 'sku':
                if (!empty($filter_value)) {
                    $args['meta_query'] = array(
                        array(
                            'key' => '_sku',
                            'value' => sanitize_text_field($filter_value),
                            'compare' => 'LIKE'
                        )
                    );
                }
                break;

            case 'id':
                $ids = array_map('intval', array_filter(array_map('trim', explode(',', $filter_value))));
                if (!empty($ids)) {
                    $args['post__in'] = $ids;
                }
                break;

            case 'category':
                $categories = is_array($filter_value) ? $filter_value : array($filter_value);
                $categories = array_filter(array_map('intval', $categories));
                if (!empty($categories)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'term_id',
                            'terms' => $categories,
                            'operator' => 'IN'
                        )
                    );
                }
                break;

            case 'tag':
                $tags = is_array($filter_value) ? $filter_value : array($filter_value);
                $tags = array_filter(array_map('intval', $tags));
                if (!empty($tags)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_tag',
                            'field' => 'term_id',
                            'terms' => $tags,
                            'operator' => 'IN'
                        )
                    );
                }
                break;

            case 'attribute':
                $attributes = is_array($filter_value) ? $filter_value : array($filter_value);
                $attributes = array_filter($attributes);

                if (!empty($attributes)) {
                    $grouped_attrs = array();
                    foreach ($attributes as $attr) {
                        $parts = explode('|', $attr);
                        if (count($parts) === 2) {
                            $taxonomy = sanitize_text_field($parts[0]);
                            $term_slug = sanitize_text_field($parts[1]);
                            if (!isset($grouped_attrs[$taxonomy])) {
                                $grouped_attrs[$taxonomy] = array();
                            }
                            $grouped_attrs[$taxonomy][] = $term_slug;
                        }
                    }

                    if (!empty($grouped_attrs)) {
                        $tax_query = array('relation' => 'OR');
                        foreach ($grouped_attrs as $taxonomy => $terms) {
                            $tax_query[] = array(
                                'taxonomy' => $taxonomy,
                                'field' => 'slug',
                                'terms' => $terms,
                                'operator' => 'IN'
                            );
                        }
                        $args['tax_query'] = $tax_query;
                    }
                }
                break;

            case 'stock_status':
                $allowed_statuses = array('instock', 'outofstock', 'onbackorder');
                $statuses = is_array($filter_value) ? $filter_value : array($filter_value);
                $statuses = array_filter($statuses);
                if (!empty($statuses)) {
                    $valid_statuses = array_intersect($statuses, $allowed_statuses);
                    if (!empty($valid_statuses)) {
                        $args['meta_query'] = array(
                            array(
                                'key' => '_stock_status',
                                'value' => $valid_statuses,
                                'compare' => 'IN'
                            )
                        );
                    }
                }
                break;

            case 'product_type':
                $allowed_types = array('simple', 'variable', 'grouped', 'external');
                $types = is_array($filter_value) ? $filter_value : array($filter_value);
                $types = array_filter($types);
                if (!empty($types)) {
                    $valid_types = array_intersect($types, $allowed_types);
                    if (!empty($valid_types)) {
                        $args['tax_query'] = array(
                            array(
                                'taxonomy' => 'product_type',
                                'field' => 'slug',
                                'terms' => $valid_types,
                                'operator' => 'IN'
                            )
                        );
                    }
                }
                break;

            case 'all':
                // No additional filters
                break;
        }

        $query = new WP_Query($args);
        $product_ids = $query->posts;

        $products = array();

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);

            if (!$product || !$product->get_sku()) {
                continue;
            }

            $products[] = array(
                'id' => $product_id,
                'sku' => $product->get_sku(),
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'stock' => $product->get_stock_quantity(),
                'stock_status' => $product->get_stock_status(),
                'type' => $product->get_type()
            );

            // Include variations if requested
            if ($include_variations && $product->is_type('variable')) {
                $variation_ids = $product->get_children();
                foreach ($variation_ids as $variation_id) {
                    $variation_obj = wc_get_product($variation_id);
                    if ($variation_obj && $variation_obj->get_sku()) {
                        $products[] = array(
                            'id' => $variation_id,
                            'sku' => $variation_obj->get_sku(),
                            'name' => $variation_obj->get_name(),
                            'price' => $variation_obj->get_price(),
                            'stock' => $variation_obj->get_stock_quantity(),
                            'stock_status' => $variation_obj->get_stock_status(),
                            'type' => 'variation'
                        );
                    }
                }
            }
        }

        $result = array(
            'products' => $products,
            'products_found' => count($product_ids)
        );

        // Cache result for 5 minutes
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);

        return $result;
    }

    /**
     * Format export data based on format and delimiter
     */
    private function format_export_data($products, $format, $delimiter) {
        $delimiter_map = array(
            'comma' => ', ',
            'semicolon' => '; ',
            'tab' => "\t",
            'newline' => "\n"
        );

        $sep = isset($delimiter_map[$delimiter]) ? $delimiter_map[$delimiter] : ', ';
        $output = array();

        foreach ($products as $product) {
            switch ($format) {
                case 'sku_title':
                    $output[] = $product['sku'] . ' - ' . $product['name'];
                    break;

                case 'sku_price':
                    $price = !empty($product['price']) ? wc_price($product['price']) : __('N/A', 'smart-product-export');
                    $output[] = $product['sku'] . ' - ' . wp_strip_all_tags($price);
                    break;

                case 'sku_stock':
                    $stock = $product['stock'] !== null ? $product['stock'] : $product['stock_status'];
                    $output[] = $product['sku'] . ' - ' . $stock;
                    break;

                case 'sku_all':
                    $price = !empty($product['price']) ? wc_price($product['price']) : __('N/A', 'smart-product-export');
                    $stock = $product['stock'] !== null ? $product['stock'] : $product['stock_status'];
                    $output[] = sprintf(
                        '%s | %s | %s | %s',
                        $product['sku'],
                        $product['name'],
                        wp_strip_all_tags($price),
                        $stock
                    );
                    break;

                case 'sku':
                default:
                    $output[] = $product['sku'];
                    break;
            }
        }

        return implode($sep, $output);
    }

    /**
     * AJAX handler for getting categories with caching
     */
    public function ajax_get_categories() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Unauthorized access.', 'smart-product-export')));
            return;
        }

        // Check cache first
        $cache_key = 'spe_categories';
        $cached_options = get_transient($cache_key);

        if (false !== $cached_options) {
            wp_send_json_success(array('options' => $cached_options));
            return;
        }

        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (is_wp_error($categories)) {
            wp_send_json_error(array('message' => __('Failed to load categories.', 'smart-product-export')));
            return;
        }

        $options = array();
        foreach ($categories as $category) {
            $options[] = array(
                'value' => $category->term_id,
                'label' => $category->name,
                'count' => $category->count
            );
        }

        // Cache for 10 minutes
        set_transient($cache_key, $options, 10 * MINUTE_IN_SECONDS);

        wp_send_json_success(array('options' => $options));
    }

    /**
     * AJAX handler for getting tags with caching
     */
    public function ajax_get_tags() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Unauthorized access.', 'smart-product-export')));
            return;
        }

        // Check cache first
        $cache_key = 'spe_tags';
        $cached_options = get_transient($cache_key);

        if (false !== $cached_options) {
            wp_send_json_success(array('options' => $cached_options));
            return;
        }

        $tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (is_wp_error($tags)) {
            wp_send_json_error(array('message' => __('Failed to load tags.', 'smart-product-export')));
            return;
        }

        $options = array();
        foreach ($tags as $tag) {
            $options[] = array(
                'value' => $tag->term_id,
                'label' => $tag->name,
                'count' => $tag->count
            );
        }

        // Cache for 10 minutes
        set_transient($cache_key, $options, 10 * MINUTE_IN_SECONDS);

        wp_send_json_success(array('options' => $options));
    }

    /**
     * AJAX handler for getting product attributes with caching
     */
    public function ajax_get_attributes() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Unauthorized access.', 'smart-product-export')));
            return;
        }

        // Check cache first
        $cache_key = 'spe_attributes';
        $cached_options = get_transient($cache_key);

        if (false !== $cached_options) {
            wp_send_json_success(array('options' => $cached_options));
            return;
        }

        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $options = array();

        if (!empty($attribute_taxonomies)) {
            foreach ($attribute_taxonomies as $attribute) {
                $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ));

                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        $options[] = array(
                            'value' => $taxonomy . '|' . $term->slug,
                            'label' => $attribute->attribute_label . ': ' . $term->name,
                            'count' => $term->count
                        );
                    }
                }
            }
        }

        // Cache for 10 minutes
        set_transient($cache_key, $options, 10 * MINUTE_IN_SECONDS);

        wp_send_json_success(array('options' => $options));
    }
}

/**
 * Declare HPOS compatibility
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Initialize the plugin
 */
function spe_init() {
    return Smart_Product_Export::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'spe_init');
