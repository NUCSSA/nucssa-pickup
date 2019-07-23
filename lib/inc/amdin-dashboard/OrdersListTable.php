<?php
namespace nucssa_pickup\admin_dashboard;

use function nucssa_core\utils\debug\file_log;

class OrdersListTable extends WP_List_Table {
  public function __construct() {
    parent::__construct([
      'singular' => 'order',
      'plural' => 'orders',
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
      ['order_id' => 'Order Record ID'],
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
        $approval_status_clause = 'approved IS NULL';
        break;
      case 'approved':
        $approval_status_clause = 'approved = 1';
        break;
      case 'failed':
        $approval_status_clause = 'approved = 0';
        break;
      case 'all':  // all orders
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

    $total_count_query = "SELECT COUNT(*) FROM pickup_service_orders AS o
                          LEFT JOIN pickup_service_users AS u
                          ON o.passenger = u.id
                          $where_clause";
    $data_query = "SELECT o.id AS order_id, name, email, wechat, phone, carrier, huskyID, admission_notice, approved
                    FROM pickup_service_orders AS o
                    LEFT JOIN pickup_service_users AS u
                    ON o.passenger = u.id
                    $where_clause
                    LIMIT $offset, $per_page";
    $total_items = $wpdb->get_var($total_count_query);
    $total_pages = ceil($total_items / $per_page);
    $orders = $wpdb->get_results($data_query);


    $this->items = $orders;
    $this->set_pagination_args([
      'total_items' => $total_items,
      'total_pages' => $total_pages,
      'per_page' => $per_page
    ]);

    // file_log('current action', $this->current_action);
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
      'admission_notice'  => '录取通知书',
      'approved' => '审核状态',
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

  public function column_cb($item) {
    return "<input type='checkbox' name='order[]' value=$item->order_id />";
  }

  // sets permissions for ajax requests from this page
  public function ajax_user_can() {
    return current_user_can( 'manage_pickups' ) || current_user_can( 'manage_options' );
  }

  public function column_name($item) {
    $actions = [
      "<a href='".wp_nonce_url("?page={$_REQUEST['page']}&action=approve&order=$item->order_id", 'bulk-orders') . "'>通过</a>",
      "<a class='row-action decline' href='".wp_nonce_url("?page={$_REQUEST['page']}&action=decline&order=$item->order_id", 'bulk-orders')."'>拒绝</a>",
    ];
    $row_actions = $this->row_actions($actions);
    return "$item->name $row_actions";
  }
  public function column_order_id($item) {
    return $item->order_id;
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
  public function column_admission_notice($item) {
    return "<a href='$item->admission_notice' data-featherlight='image'><img class='admin-pickup-entry-image' src='$item->admission_notice' /></a>";
  }
  public function column_approved($item) {
    switch ($item->approved) {
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

    $nonce = $_GET['_wpnonce'];
    if (!wp_verify_nonce($nonce, 'bulk-orders')) {
      $this->invalid_nonce_redirect();
    } else {
      $orders = is_array($_GET['order']) ? $_GET['order'] : [$_GET['order']];
      $order_ids_str = implode(',', $orders);
      $approved = null;
      switch ($action) {
        case 'approve':
          $approved = 1;
          break;
        case 'decline':
          $approved = 0;
        default:
          break;
      }
      if (!is_null($approved)) {
        $wpdb->query(
          "UPDATE pickup_service_orders
          SET approved = $approved
          WHERE id IN ($order_ids_str)"
        );
        if ($approved === 1) {
          /**
           * @param Array $orders Order IDs
           */
          do_action('orders_application_approved', $orders);
        } else {
          /**
           * @param Array $orders Order IDs
           */
          do_action('orders_application_declined', $orders);
        }
      }
    }
  }
}
