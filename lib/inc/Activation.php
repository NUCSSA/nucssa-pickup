<?php

namespace nucssa_pickup;

use function nucssa_core\utils\debug\file_log;

/**
 * Things to do on plugin activation
 */
class Activation
{
  public static function init()
  {
    self::migrate();        // Adds DB tables for pickup info persistence
    self::addPickupPage();  // Adds the frontend pickup page (nucssa.org/pickup)
  }

  /**
   * DB Migrations:
   * Adds tables for persistence of pickup service related data
   */
  private static function migrate()
  {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $create_users_table = <<<users_table
      CREATE TABLE IF NOT EXISTS pickup_service_users (
        id BIGINT NOT NULL AUTO_INCREMENT,
        wechat VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(70) NOT NULL,
        phone VARCHAR(20),
        carrier VARCHAR(50),
        passwd_hashed VARCHAR(255) NOT NULL,

        PRIMARY KEY (id),
        KEY idx_wechat (wechat),
        KEY idx_email (email)
      ) $charset_collate;
users_table;

    $create_drivers_table = <<<drivers_table
      CREATE TABLE IF NOT EXISTS pickup_service_drivers (
        id BIGINT NOT NULL AUTO_INCREMENT,
        user_id BIGINT NOT NULL,
        huskyID VARCHAR(20) NOT NULL,
        husky_card VARCHAR(255) NOT NULL,
        drivers_license VARCHAR(255) NOT NULL,
        certified BOOL,
        term VARCHAR(10) NOT NULL,

        PRIMARY KEY (id),
        FOREIGN KEY (user_id)
          REFERENCES pickup_service_users (id)
          ON DELETE CASCADE,

        UNIQUE KEY unique_user_term (user_id, term)
      ) $charset_collate;
drivers_table;

    $create_orders_table = <<<orders_table
      CREATE TABLE IF NOT EXISTS pickup_service_orders (
        id BIGINT NOT NULL AUTO_INCREMENT,
        passenger BIGINT NOT NULL,
        driver BIGINT,
        flight VARCHAR(255) NOT NULL,
        arrival_datetime DATETIME NOT NULL,
        arrival_terminal VARCHAR(5) NOT NULL,
        drop_off_address VARCHAR(255) NOT NULL,
        note VARCHAR(2000),
        term VARCHAR(10) NOT NULL,

        PRIMARY KEY (id),
        FOREIGN KEY (passenger)
          REFERENCES pickup_service_users (id)
          ON DELETE CASCADE,
        FOREIGN KEY (driver)
          REFERENCES pickup_service_users (id)
          ON DELETE SET NULL,
        KEY idx_arrival_time (arrival_datetime)
      ) $charset_collate;
orders_table;

    file_log('SQL Driver Tabe', $create_drivers_table);
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_users_table);
    dbDelta($create_drivers_table);
    dbDelta($create_orders_table);
  }

  private static function addPickupPage()
  {
    $page_slug = 'pickup';
    $page_title = '迎新生接机服务';
    $page_check = \get_page_by_path($page_slug);
    if (!isset($page_check->ID)) {
      $postID = wp_insert_post([
        'post_type' => 'page',
        'post_title' => $page_title,
        'post_status' => 'publish',
        'post_author' => 1,
        'comment_status' => 'closed',
        'post_name' => $page_slug,
      ]);
      update_post_meta($postID, '_wp_page_template', 'template-pickup-page.php');

      update_option('nucssa_pickup_service_page_id', $postID);
    }
  }
}
