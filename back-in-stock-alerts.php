<?php
/**
 * Plugin Name: Back-in-Stock Alerts
 * Description: Notify customers when out-of-stock products are back in stock.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-bis-email-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bis-subscriber.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-bis-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';

class BIS_Alerts {
    public function __construct() {
        register_activation_hook(__FILE__, ['BIS_Subscriber', 'bis_create_subscribers_table']);
        add_action('woocommerce_single_product_summary', [$this, 'display_notify_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('woocommerce_product_set_stock_status', [$this, 'notify_subscribers'], 10, 3);
        add_action('admin_menu', [$this, 'bis_add_admin_page']);
    }

    public function bis_add_admin_page() {
        add_menu_page(
            'Back in Stock Subscribers',
            'BIS Subscribers',
            'manage_options',
            'bis-subscribers',
            'bis_render_admin_page',
            'dashicons-welcome-widgets-menus',
            20
        );
    }

    public function display_notify_button() {
        global $product;

        // Check if the button has already been displayed
        if (isset($GLOBALS['bis_notify_button_displayed']) && $GLOBALS['bis_notify_button_displayed']) {
            return; // Exit if already displayed
        }

        if (!$product->is_in_stock()) {
            $product_id = $product->get_id();
            echo '<div id="bis-notify-container" data-product-id="' . esc_attr($product_id) . '">
            <p>The product is out of stock.</p>
                <button id="bis-notify-btn">Notify me</button>
                <div id="bis-subscribe-form" style="display: none;">
                    <p>Be the first person to know when the product is back in store</p>
                    <input type="email" id="bis-email" placeholder="Enter your email id">
                    <button id="bis-submit">Subscribe</button>
                </div>
            </div>';
            
            // Mark the button as displayed
            $GLOBALS['bis_notify_button_displayed'] = true;
        }
    }

    public function enqueue_scripts() {
        // Only enqueue on single product pages
        if (!is_product()) {
            return;
        }

        global $product;
        
        wp_enqueue_script('bis-scripts', plugin_dir_url(__FILE__) . 'assets/js/bis-scripts.js', ['jquery'], '1.0', true);
        wp_enqueue_style('bis-styles', plugin_dir_url(__FILE__) . 'assets/css/bis-styles.css');

        // Only localize script if we have a valid product
        if ($product) {
            wp_localize_script('bis-scripts', 'bis_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                // 'product_id' => $product->get_id(),
            ]);
        }
    }

    public function notify_subscribers($product_id, $stock_status, $product) {
        if ($stock_status === 'instock') {
            BIS_Email_Handler::send_notifications($product_id);
        }else{
          return;
        }
    }
}

new BIS_Alerts();