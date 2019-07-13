<?php
namespace nucssa_pickup\admin_dashboard\menu_page;

class AdminMenu {
  public static function init() {
    self::addMenuPage();
    self::removeWpFooter();
  }

  private static function addMenuPage(){
    // add top level menu
    add_menu_page('NUCSSA接机服务', 'NUCSSA 接机', 'manage_options', 'admin-menu-page-nucssa-pickup', function () {
      self::render();
    }, 'none');
  }

  private static function removeWpFooter()
  {
    add_filter('update_footer', '__return_empty_string', 11);
    add_filter('admin_footer_text', '__return_empty_string', 11);
  }

  private static function render() {
    /**
     * React component
     */
    echo '<div id="airport-pickup-admin-page"></div>';

    /**
     * Footer Branding
     */
    $year = date('Y');
    echo '<div class="nucssa-footer">
      <div class="brand-title">NUCSSA IT</div>
      <img class="brand-image" src="' . NUCSSA_PICKUP_DIR_URL . 'public/images/logo.png' . '" />
      <div class="copyright">© ' . $year . ' NUCSSA IT All Rights Reserved</div>
    </div>';
  }
}