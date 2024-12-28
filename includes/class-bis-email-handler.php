<?php 

class BIS_Email_Handler{
  public static function send_notifications($product_id){
  global $wpdb;

  $table_name = $wpdb->prefix . 'bis_subscribers';

  $subscribers = $wpdb->get_results($wpdb->prepare(
    "SELECT email FROM $table_name WHERE product_id = %d",
    $product_id
  ));

  if (empty($subscribers)){
    return;
  }
  $product = wc_get_product($product_id) ;
  if(!$product){
    return;
  }
  $product_name = $product->get_name();
  $product_url = get_permalink($product_id);
  $site_name = get_bloginfo('name');

  // Email template
  $subject = sprintf('%s is back in stock at %s', $product_name, $site_name);
        
  $message = sprintf(
      '<!DOCTYPE html>
      <html>
      <head>
          <meta charset="UTF-8">
          <title>Back in Stock Notification</title>
      </head>
      <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
          <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
              <h2 style="color: #444;">Good News!</h2>
              <p>The item you were interested in is back in stock:</p>
              <div style="background: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #2ecc71;">
                  <h3 style="margin: 0 0 10px 0;">%s</h3>
                  <p style="margin: 0;">Now available to purchase.</p>
              </div>
              <p><a href="%s" style="background: #2ecc71; color: white; padding: 12px 25px; text-decoration: none; border-radius: 3px; display: inline-block;">View Product</a></p>
              <p style="color: #666; font-size: 14px; margin-top: 30px;">
                  Thank you for your interest!<br>
                  %s
              </p>
          </div>
      </body>
      </html>',
      esc_html($product_name),
      esc_url($product_url),
      esc_html($site_name)
  );

  // Email headers
  $headers = array(
      'Content-Type: text/html; charset=UTF-8',
      'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
      'Reply-To: ' . get_option('admin_email')
  );

  // Send emails to each subscriber
  foreach ($subscribers as $subscriber) {
      wp_mail(
          $subscriber->email,
          $subject,
          $message,
          $headers
      );
  }

  // Delete all notifications for this product after sending
  $wpdb->delete(
      $table_name,
      ['product_id' => $product_id],
      ['%d']
  );


  }
}
new BIS_Alerts();
