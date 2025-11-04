<?php
/**
 * Plugin Name: Smart Product Export
 * Plugin URI: https://github.com/yourusername/smart-product-export
 * Description: Export WooCommerce product SKUs based on various criteria (SKU, ID, tags, attributes, categories, etc.)
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
        add_menu_page(
            __('SKU Export', 'smart-product-export'),
            __('SKU Export', 'smart-product-export'),
            'manage_woocommerce',
            'smart-product-export',
            array($this, 'render_admin_page'),
            'dashicons-download',
            56
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_smart-product-export' !== $hook) {
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
            echo '<div class="wrap"><h1>' . esc_html__('SKU Export', 'smart-product-export') . '</h1>';
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
        $filter_value = isset($_POST['filter_value']) ? sanitize_text_field($_POST['filter_value']) : '';
        $include_variations = isset($_POST['include_variations']) && $_POST['include_variations'] === 'yes';

        $skus = $this->get_filtered_skus($filter_type, $filter_value, $include_variations);

        if (empty($skus)) {
            wp_send_json_success(array(
                'skus' => '',
                'count' => 0,
                'message' => 'No products found matching the criteria.'
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
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => sanitize_title($filter_value)
                    )
                );
                break;

            case 'tag':
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_tag',
                        'field' => 'slug',
                        'terms' => sanitize_title($filter_value)
                    )
                );
                break;

            case 'attribute':
                // Format: attribute_name:value
                $attr_parts = explode(':', $filter_value);
                if (count($attr_parts) === 2) {
                    $attr_name = sanitize_title($attr_parts[0]);
                    $attr_value = sanitize_title($attr_parts[1]);
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'pa_' . $attr_name,
                            'field' => 'slug',
                            'terms' => $attr_value
                        )
                    );
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

        return $skus;
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
