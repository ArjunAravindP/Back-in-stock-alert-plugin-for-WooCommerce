<?php
// AJAX Handler for Back-in-Stock Notifications
add_action('wp_ajax_bis_subscribe', 'bis_subscribe');
add_action('wp_ajax_nopriv_bis_subscribe', 'bis_subscribe');

function bis_subscribe() {
    if (!isset($_POST['email']) || !isset($_POST['product_id'])) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }

    // Sanitize inputs
    $email = sanitize_email($_POST['email']);
    $product_id = absint($_POST['product_id']);

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }

    // Validate product
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Invalid product.']);
    }

    // Check if product is actually out of stock
    if ($product->is_in_stock()) {
        wp_send_json_error(['message' => 'This product is already in stock.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'bis_subscribers';

    // Check if already subscribed
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE email = %s AND product_id = %d",
        $email,
        $product_id
    ));

    if ($existing) {
        wp_send_json_error(['message' => 'You are already subscribed to notifications for this product.']);
    }

    // Add the subscription
    $result = $wpdb->insert(
        $table_name,
        [
            'email' => $email,
            'product_id' => $product_id,
            'date_subscribed' => current_time('mysql')
        ],
        ['%s', '%d', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Unable to save subscription. Please try again.']);
    }

    wp_send_json_success(['message' => 'You will be notified when this product is back in stock.']);
}