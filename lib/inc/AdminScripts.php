<?php
namespace nucssa_pickup;

/**
 * Manage JS and CSS scripts used in Admin Dashboard
 */
class AdminScripts
{
  /**
   * @param $hook param provided by admin_enqueue_scripts action
   */
  public static function init($hook)
  {
    self::loadPickupPageScripts($hook);
    self::loadPickupPageStyles($hook);
    self::loadAdminGlobalStyles();

    // load browserSync script for development
    self::enableBrowserSyncOnDebugMode();
  }

  private static function loadPickupPageScripts($hook)
  {
    if (!self::isPickupMenuPages($hook)) return;

    $handle = 'nucssa_pickup_amdin_script';
    // load core script
    wp_enqueue_script(
      $handle,
      NUCSSA_PICKUP_DIR_URL . 'public/js/admin.js',
      [], // deps
      false, // version
      true // in_footer?
    );

  }

  private static function loadPickupPageStyles($hook) {
    if (!self::isPickupMenuPages($hook)) return;

    wp_enqueue_style(
      'nucssa_pickup_admin_page_style',
      NUCSSA_PICKUP_DIR_URL . 'public/css/admin-pickup-page.css',
      [], // deps
      false,   // version
      'all'    // media
    );
  }

  private static function loadAdminGlobalStyles() {
    // Global Styles
    wp_enqueue_style(
      'nucssa_pickup_admin_global_style',
      NUCSSA_PICKUP_DIR_URL . 'public/css/admin-global.css',
      [], // deps
      false,   // version
      'all'    // media
    );
  }

  private static function enableBrowserSyncOnDebugMode() {
    if (WP_DEBUG) {
      add_action('admin_print_scripts', function(){
        echo '<script async="" src="http://wp.localhost:3000/browser-sync/browser-sync-client.js"></script>';
      });
    }
  }

  private static function isPickupMenuPages($hook) {
    return $hook === 'toplevel_page_admin-menu-page-nucssa-pickup' || strpos($hook, 'pickup__order-review') !== false;
  }
}