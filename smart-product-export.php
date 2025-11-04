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
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $filter_type = isset($_POST['filter_type']) ? sanitize_text_field($_POST['filter_type']) : '';
        $filter_value = isset($_POST['filter_value']) ? $_POST['filter_value'] : '';
        $include_variations = isset($_POST['include_variations']) && $_POST['include_variations'] === 'yes';

        // Handle array values for multi-select
        if (is_array($filter_value)) {
            $filter_value = array_map('sanitize_text_field', $filter_value);
        } else {
            $filter_value = sanitize_text_field($filter_value);
        }

        $result = $this->get_filtered_skus($filter_type, $filter_value, $include_variations);
        $skus = $result['skus'];
        $products_found = $result['products_found'];

        if (empty($skus)) {
            $message = 'No products found matching the criteria.';
            if ($products_found > 0) {
                $message = sprintf('%d product(s) found, but none have SKUs assigned. Please add SKUs to your products in WooCommerce.', $products_found);
            }
            wp_send_json_success(array(
                'skus' => '',
                'count' => 0,
                'message' => $message
            ));
        } else {
            wp_send_json_success(array(
                'skus' => implode(', ', $skus),
                'count' => count($skus),
                'message' => sprintf('%d SKU(s) found.', count($skus))
            ));
        }
    }

    /**
     * Get filtered SKUs based on criteria
     */
    private function get_filtered_skus($filter_type, $filter_value, $include_variations = false) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        );

        // Apply filters based on type
        switch ($filter_type) {
            case 'sku':
                $args['meta_query'] = array(
                    array(
                        'key' => '_sku',
                        'value' => $filter_value,
                        'compare' => 'LIKE'
                    )
                );
                break;

            case 'id':
                $ids = array_map('intval', array_filter(array_map('trim', explode(',', $filter_value))));
                if (!empty($ids)) {
                    $args['post__in'] = $ids;
                }
                break;

            case 'category':
                // Handle multiple categories (OR logic)
                $categories = is_array($filter_value) ? $filter_value : array($filter_value);
                $categories = array_filter($categories);
                if (!empty($categories)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'term_id',
                            'terms' => array_map('intval', $categories),
                            'operator' => 'IN'
                        )
                    );
                }
                break;

            case 'tag':
                // Handle multiple tags (OR logic)
                $tags = is_array($filter_value) ? $filter_value : array($filter_value);
                $tags = array_filter($tags);
                if (!empty($tags)) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_tag',
                            'field' => 'term_id',
                            'terms' => array_map('intval', $tags),
                            'operator' => 'IN'
                        )
                    );
                }
                break;

            case 'attribute':
                // Handle multiple attributes (OR logic)
                // Format: taxonomy_slug|term_slug
                $attributes = is_array($filter_value) ? $filter_value : array($filter_value);
                $attributes = array_filter($attributes);

                if (!empty($attributes)) {
                    // Group by taxonomy
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

                    // Build tax query with OR relation between different taxonomies
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

            case 'all':
                // No additional filters - get all products
                break;
        }

        $query = new WP_Query($args);
        $product_ids = $query->posts;

        $skus = array();

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);

            if (!$product) {
                continue;
            }

            // Get parent product SKU
            if ($product->get_sku()) {
                $skus[] = $product->get_sku();
            }

            // Include variations if requested
            if ($include_variations && $product->is_type('variable')) {
                $variations = $product->get_available_variations();
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    if ($variation_obj && $variation_obj->get_sku()) {
                        $skus[] = $variation_obj->get_sku();
                    }
                }
            }
        }

        // Remove duplicates and empty values
        $skus = array_unique(array_filter($skus));

        return array(
            'skus' => $skus,
            'products_found' => count($product_ids)
        );
    }

    /**
     * AJAX handler for getting categories
     */
    public function ajax_get_categories() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (is_wp_error($categories)) {
            wp_send_json_error(array('message' => 'Failed to load categories'));
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

        wp_send_json_success(array('options' => $options));
    }

    /**
     * AJAX handler for getting tags
     */
    public function ajax_get_tags() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (is_wp_error($tags)) {
            wp_send_json_error(array('message' => 'Failed to load tags'));
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

        wp_send_json_success(array('options' => $options));
    }

    /**
     * AJAX handler for getting product attributes
     */
    public function ajax_get_attributes() {
        check_ajax_referer('spe_export_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
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

        wp_send_json_success(array('options' => $options));
    }
}

/**
 * Initialize the plugin
 */
function spe_init() {
    return Smart_Product_Export::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'spe_init');
