<?php
// Add this at the beginning of your plugin file or in the admin class
add_action('admin_post_bis_delete_subscriber', 'bis_delete_subscriber');

function bis_render_admin_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'bis_subscribers';
    
    // Add success message display
    if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Subscriber deleted successfully.</p></div>';
    }
    
    $subscribers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_subscribed DESC");

    $table_headers = [
        'ID' => 'id',
        'Email' => 'email', 
        'Product ID' => 'product_id',
        'Date Subscribed' => 'date_subscribed'
    ];

    ?>
    <div class="wrap">
        <h1>Back in Stock Subscribers</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php foreach($table_headers as $label => $field): ?>
                        <th><?php echo esc_html($label); ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($subscribers): ?>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <?php foreach($table_headers as $field): ?>
                                <td><?php echo esc_html($subscriber->$field); ?></td>
                            <?php endforeach; ?>
                            <td>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="bis_delete_subscriber">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($subscriber->id); ?>">
                                    <?php wp_nonce_field('bis_delete_subscriber_nonce'); ?>
                                    <button type="submit" class="button button-secondary" 
                                            onclick="return confirm('Are you sure you want to delete this subscriber?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No subscribers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function bis_delete_subscriber() {
    // Verify that user has proper security level
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'bis_delete_subscriber_nonce')) {
        wp_die('Security check failed');
    }

    // Get and sanitize the ID
    $subscriber_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$subscriber_id) {
        wp_die('Invalid subscriber ID');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'bis_subscribers';

    // Delete the subscriber
    $result = $wpdb->delete(
        $table_name,
        ['id' => $subscriber_id],
        ['%d']
    );

    // Redirect back to the admin page with a status message
    $redirect_url = add_query_arg(
        ['page' => 'bis-subscribers', 'deleted' => $result ? '1' : '0'],
        admin_url('admin.php')
    );

    wp_redirect($redirect_url);
    exit;
}