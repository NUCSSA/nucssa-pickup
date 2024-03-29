<?php

namespace nucssa_pickup\AdminDashboard;

class DriversListTable extends WP_List_Table {
  public function __construct() {
    parent::__construct([
      'singular' => 'driver',
      'plural' => 'drivers',
      'ajax' => true,
    ]);

    /**
     * REQUIRED. Define column headers.
     * This property requires a 4-value array :
     *  - The first value is an array containing column slugs and titles (see the get_columns() method).
     *  - The second value is an array containing the values of fields to be hidden.
     *  - The third value is an array of columns that should allow sorting (see the get_sortable_columns() method).
     *  - The fourth value is a string defining which column is deemed to be the primary one, displaying the row's actions (edit, view, etc).
     *    The value should match that of one of your column slugs in the first value.
     */
    $this->_column_headers = [
      $this->get_columns(),
      ['driver_id' => 'Driver Record ID'],
      [],
      'name'
    ];
  }

  public function prepare_items() {
    global $wpdb;

    $this->processAction();

    $search_keyword = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
    $search_clause = empty($search_keyword) ? '' : "name LIKE '%$search_keyword%' OR email LIKE '%$search_keyword%' OR wechat LIKE '%$search_keyword%'";
    // pending, approved, failed, all
    $approval_status = $_REQUEST['approval_status'] ?? '';

    switch ($approval_status) {
      case '': // pending
        $approval_status_clause = 'certified IS NULL';
        break;
      case 'approved':
        $approval_status_clause = 'certified = 1';
        break;
      case 'failed':
        $approval_status_clause = 'certified = 0';
        break;
      case 'all':  // all drivers
        $approval_status_clause = '';
        break;
    }

    if ($approval_status_clause && $search_clause) {
      $where_clause = "WHERE $approval_status_clause AND $search_clause";
    } elseif ($approval_status_clause || $search_clause) {
      $where_clause = "WHERE $approval_status_clause $search_clause";
    } else {
      $where_clause = '';
    }

    $current_page = $this->get_pagenum();
    $per_page = 10;
    $offset = ($current_page - 1) * $per_page;

    $total_count_query = "SELECT COUNT(*) FROM pickup_service_drivers AS d
                          LEFT JOIN pickup_service_users AS u
                          ON d.user_id = u.id
                          $where_clause";
    $data_query = "SELECT d.id AS driver_id, name, email, wechat, phone, carrier, huskyID, husky_card, drivers_license, certified
                    FROM pickup_service_drivers AS d
                    LEFT JOIN pickup_service_users AS u
                    ON d.user_id = u.id
                    $where_clause
                    LIMIT $offset, $per_page";
    $total_items = $wpdb->get_var($total_count_query);
    $total_pages = ceil($total_items / $per_page);
    $drivers = $wpdb->get_results($data_query);


    $this->items = $drivers;
    $this->set_pagination_args([
      'total_items' => $total_items,
      'total_pages' => $total_pages,
      'per_page' => $per_page
    ]);
  }

  /********** SET UP TABLE LAYOUT **********/
  /**
   * Sets column mapping relationship from slug to Title
   */
  public function get_columns(){
    return [
      'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
      'name'     => '姓名',
      'wechat'    => '微信',
      'email'  => 'Email',
      'phone'  => '电话',
      'huskyID'  => 'Husky ID',
      'husky_card'  => 'Husky Card',
      'drivers_license'  => "Driver's License",
      'certified' => '审核状态',
    ];
  }

  /**
   * Specifies bulk actions
   */
  public function get_bulk_actions() {

    return [
      'approve' => '通过审核',
      'decline' => '拒绝',
    ];
  }

  protected function get_views() {
    $views_map = [
      '' => '待审核',
      'approved' => '已通过',
      'failed' => '杯具了',
      'all' => '所有司机',
    ];
    $query_arg_name = 'approval_status';
    $current_view = $_REQUEST[$query_arg_name] ?? '';
    $url_base = admin_url('admin.php?page=' . $_REQUEST['page']);

    foreach ($views_map as $query_arg_val => $display_name) {
      $url = $query_arg_val == '' ? $url_base : add_query_arg($query_arg_name, $query_arg_val, $url_base);
      $class = $current_view == $query_arg_val ? 'class="current"' : '';
      $views[$query_arg_val] = "<a href='$url' $class>$display_name</a>";
    }
    return $views;
  }

  protected function addtional_query_args_for_pagination_link() {
    $addtional_args = [];
    if (isset($_REQUEST['s'])) {
      $addtional_args['s'] = wp_unslash(trim($_REQUEST['s']));
    }
    return $addtional_args;
  }

  public function column_cb($item) {
    return "<input type='checkbox' name='driver[]' value=$item->driver_id />";
  }

  // sets permissions for ajax requests from this page
  public function ajax_user_can() {
    return current_user_can( 'manage_pickups' ) || current_user_can( 'manage_options' );
  }

  public function column_name($item) {
    $actions = [
      "<a href='".wp_nonce_url("?page={$_REQUEST['page']}&action=approve&driver=$item->driver_id&noheader=true", 'bulk-drivers') . "'>通过</a>",
      "<a class='row-action decline' href='".wp_nonce_url("?page={$_REQUEST['page']}&action=decline&driver=$item->driver_id&noheader=true", 'bulk-drivers')."'>拒绝</a>",
    ];
    $row_actions = $this->row_actions($actions);
    return "<strong>$item->name</strong> $row_actions";
  }
  public function column_driver_id($item) {
    return $item->driver_id;
  }
  public function column_wechat($item) {
    return $item->wechat;
  }
  public function column_email($item) {
    return $item->email;
  }
  public function column_huskyID($item) {
    return $item->huskyID;
  }
  public function column_phone($item) {
    return "$item->phone<br/>$item->carrier";
  }
  public function column_husky_card($item) {
    return "<a href='$item->husky_card' data-featherlight='image'><img class='admin-pickup-entry-image' src='$item->husky_card' /></a>";
  }
  public function column_drivers_license($item) {
    return "<a href='$item->drivers_license' data-featherlight='image'><img class='admin-pickup-entry-image' src='$item->drivers_license' /></a>";
  }
  public function column_certified($item) {
    switch ($item->certified) {
      case null:
        return '等待审核';
      case 1:
        return '已通过';
      case 0:
        return '杯具了';
    }
  }

  /****** HELPER METHODS *****/
  private function processAction() {
    global $wpdb;
    $action = $this->current_action();
    if (!$action) return;

    $nonce = $_REQUEST['_wpnonce'];
    if (!wp_verify_nonce($nonce, 'bulk-drivers')) {
      $this->invalid_nonce_redirect();
    } else {
      $drivers = is_array($_REQUEST['driver']) ? $_REQUEST['driver'] : [$_REQUEST['driver']];
      $driver_ids_str = implode(',', $drivers);
      $certified = null;
      switch ($action) {
        case 'approve':
          $certified = 1;
          break;
        case 'decline':
          $certified = 0;
        default:
          break;
      }
      if (!is_null($certified)) {
        $wpdb->query(
          "UPDATE pickup_service_drivers
          SET certified = $certified
          WHERE id IN ($driver_ids_str)"
        );
        if ($certified === 1) {
          /**
           * @param Array $drivers Driver IDs
           */
          do_action('drivers_application_approved', $drivers);
        } else {
          /**
           * @param Array $drivers Driver IDs
           */
          do_action('drivers_application_declined', $drivers);
        }
      }
    }

    // Redirect back to original url if is row action
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $referer = wp_get_referer();
      wp_redirect($referer);
      exit();
    }
  }
}
