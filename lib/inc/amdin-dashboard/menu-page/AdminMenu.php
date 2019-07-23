<?php
namespace nucssa_pickup\admin_dashboard\menu_page;

use nucssa_pickup\admin_dashboard\DriversListTable;
use nucssa_pickup\admin_dashboard\OrdersListTable;

class AdminMenu {
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
  }

  private static function removeWpFooter()
  {
    add_filter('update_footer', '__return_empty_string', 11);
    add_filter('admin_footer_text', '__return_empty_string', 11);
  }

  private static function renderOrderReviewPage(){
    $ordersListTable = new OrdersListTable();
    $ordersListTable->prepare_items();
    $plugin_page_slug = $_GET['page'];
    $approval_status = $_GET['approval_status'] ?? '';
    ?>
    <div class="wrap">
      <h2>订单审核</h2>
      <ul class="subsubsub">
        <li class="pending"><a class="<?php echo empty($approval_status) ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug" ?>">待审核</a></li>
        <li class="approved"><a class="<?php echo $approval_status == 'approved' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=approved" ?>">已通过</a></li>
        <li class="failed"><a class="<?php echo $approval_status == 'failed' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=failed" ?>">被拒的</a></li>
        <li class="all"><a class="<?php echo $approval_status == 'all' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=all" ?>">所有订单</a></li>
      </ul>
      <form method="get">
        <p class="search-box">
          <input type="search" name="s" value="<?php echo $_REQUEST['s'] ?? '' ?>" />
          <input type="submit" class="button" value="搜索乘客订单" />
        </p>
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
    $plugin_page_slug = $_GET['page'];
    $approval_status = $_GET['approval_status'] ?? '';
    ?>
    <div class="wrap">
      <h2>司机审核</h2>
      <ul class="subsubsub">
        <li class="pending"><a class="<?php echo empty($approval_status) ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug" ?>">待审核</a></li>
        <li class="approved"><a class="<?php echo $approval_status == 'approved' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=approved" ?>">已通过</a></li>
        <li class="failed"><a class="<?php echo $approval_status == 'failed' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=failed" ?>">被拒的</a></li>
        <li class="all"><a class="<?php echo $approval_status == 'all' ? 'current' : ''; ?>" href="<?php echo "admin.php?page=$plugin_page_slug&approval_status=all" ?>">所有司机</a></li>
      </ul>
      <form method="get">
        <p class="search-box">
          <input type="search" name="s" value="<?php echo $_REQUEST['s'] ?? '' ?>" />
          <input type="submit" class="button" value="Search Drivers" />
        </p>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php $driversListTable->display(); ?>
      </form>
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