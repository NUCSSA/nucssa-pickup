<?php
namespace nucssa_pickup\admin_dashboard\menu_page;

use nucssa_pickup\admin_dashboard\DriversListTable;
use nucssa_pickup\admin_dashboard\OrdersListTable;
use nucssa_pickup\admin_dashboard\FeedbackListTable;

class AdminPages {
  public static function init() {
    self::addMenuPage();
    self::removeWpFooter();
  }

  private static function addMenuPage(){
    // add top level menu
    add_menu_page('NUCSSA接机服务', 'NUCSSA 接机', 'manage_pickups', 'admin-menu-page-nucssa-pickup', '', 'none');
    add_submenu_page('admin-menu-page-nucssa-pickup', '接机|司机审核', '司机审核', 'manage_pickups', 'admin-menu-page-nucssa-pickup', function() {
      self::renderDriverReviewPage();
    });
    add_submenu_page('admin-menu-page-nucssa-pickup', '接机|新生订单审核', '订单审核', 'manage_pickups', 'admin-menu-page-nucssa-pickup__order-review', function() {
      self::renderOrderReviewPage();
    });
    add_submenu_page('admin-menu-page-nucssa-pickup', '接机|用户反馈', '用户反馈', 'read', 'admin-menu-page-nucssa-pickup__user-feedback', function() {
      self::renderUserFeedbackPage();
    });
  }

  private static function removeWpFooter()
  {
    add_filter('update_footer', '__return_empty_string', 11);
    add_filter('admin_footer_text', '__return_empty_string', 11);
  }

  private static function renderOrderReviewPage(){
    $ordersListTable = new OrdersListTable();
    $ordersListTable->prepare_items();
    ?>
    <div class="wrap">
      <h2>订单审核</h2>
      <?php $ordersListTable->views(); ?>
      <form method="post">
        <?php $ordersListTable->search_box('搜索乘客订单', 'order'); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php $ordersListTable->display(); ?>
      </form>
    </div>

    <?php
    echo self::footerBranding();
  }

  private static function renderDriverReviewPage() {
    /**
     * The Content Area
     * Driver List Table
     */
    $driversListTable = new DriversListTable();
    $driversListTable->prepare_items();
    ?>
    <div class="wrap">
      <h2>司机审核</h2>
      <?php $driversListTable->views(); ?>
      <form method="post">
        <?php $driversListTable->search_box('搜索司机', 'driver'); ?>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php $driversListTable->display(); ?>
      </form>
    </div>

    <?php
    echo self::footerBranding();
  }

  private static function renderUserFeedbackPage() {
    $feedbackListTable = new FeedbackListTable();
    $feedbackListTable->prepare_items();
    ?>
    <div class="wrap nucssa-pickup-feedback-page">
      <h2>用户反馈</h2>
      <?php $feedbackListTable->display(); ?>
    </div>
    <?php
    echo self::footerBranding();
  }

  private static function footerBranding() {
    $year = date('Y');
    return '<div class="nucssa-footer">
      <div class="brand-title">NUCSSA IT</div>
      <img class="brand-image" src="' . NUCSSA_PICKUP_DIR_URL . 'public/images/logo.png' . '" />
      <div class="copyright">© ' . $year . ' NUCSSA IT All Rights Reserved</div>
    </div>';
  }
}