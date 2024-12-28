<?php


class BIS_Subscriber {

  public static function bis_create_subscribers_table(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'bis_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    product_id BIGINT(20) UNSIGNED NOT NULL,
    date_subscribed DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH .'wp-admin/includes/upgrade.php';
    dbDelta( $sql );


  }


}