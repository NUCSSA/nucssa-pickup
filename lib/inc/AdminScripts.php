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
    // self::loadScripts($hook);
    self::loadStyles($hook);
  }

  private static function loadScripts($hook)
  {
    if ($hook != 'toplevel_page_admin-menu-page-nucssa-pickup') {
      return;
    }

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

  private static function loadStyles($hook) {
    // NUCSSA Core Plugin Page only Styles
    if ($hook === 'toplevel_page_admin-menu-page-nucssa-pickup') {
      wp_enqueue_style(
        'nucssa_pickup_admin_plugin_page_style',
        NUCSSA_PICKUP_DIR_URL . 'public/css/admin-plugin-page.css',
        [], // deps
        false,   // version
        'all'    // media
      );
    }

    // Global Styles
    wp_enqueue_style(
      'nucssa_pickup_admin_global_style',
      NUCSSA_PICKUP_DIR_URL . 'public/css/admin-global.css',
      [], // deps
      false,   // version
      'all'    // media
    );
  }
}