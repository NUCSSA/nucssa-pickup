<?php
namespace nucssa_pickup\templates\template_utils;
include_once(__DIR__.'/../inc/rest-endpoints/driver.php');
include_once(__DIR__.'/../inc/rest-endpoints/user.php');
include_once(__DIR__.'/../inc/rest-endpoints/order.php');

use function nucssa_core\utils\debug\file_log;
use function nucssa_pickup\rest_endpoints\driver\{create as create_driver, show as show_driver, update as update_driver};
use function nucssa_pickup\rest_endpoints\user\{show as show_user, update as update_user};
use function nucssa_pickup\rest_endpoints\order\{
  create as create_order, index as list_orders, delete as delete_order, update as update_order,
  list_own_orders, list_managed_orders, list_pending_orders,
  driver_pick_order, driver_drop_order
};

function is_user_logged_in(){
  return isset($_SESSION['user']);
}

function process_submission_data(){
  if (empty($_POST)) return;

  global $wpdb;
  $submission_type = $_POST['form-for'];

  if ($submission_type == 'login'){
    $email = sanitize_text_field($_POST['user']['email']);
    $password = sanitize_text_field($_POST['user']['password']);
    $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'");
    if ($user && password_verify($password, $user->passwd_hashed)) {
      $_SESSION['user'] = $user;
      $_SESSION['login-error'] = NULL;
    } else {
      $_SESSION['login-error'] = '账号密码错误, 请重试';
    }
  } else {
    // register new user
    $wechat = sanitize_text_field($_POST['user']['wechat']);
    $name = sanitize_text_field($_POST['user']['name']);
    $email = sanitize_text_field($_POST['user']['email']);
    $phone = sanitize_text_field($_POST['user']['phone']);
    $carrier = sanitize_text_field($_POST['user']['carrier']);
    $password = sanitize_text_field($_POST['user']['password']);
    $password_hashed = \password_hash($password, PASSWORD_DEFAULT);
    if ($wpdb->get_row("SELECT id FROM pickup_service_users WHERE email = '$email'")) {
      $_SESSION['register-error'] = "$email 该邮箱已被注册!";
      return;
    }
    $wpdb->insert('pickup_service_users', [
      'wechat' => $wechat,
      'name' => $name,
      'email' => $email,
      'phone' => $phone,
      'carrier' => $carrier,
      'passwd_hashed' => $password_hashed,
    ]);
    if ($wpdb->insert_id) {
      $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE id = $wpdb->insert_id");
      do_action('np_user_created', $user);
      $_SESSION['user'] = $user;
    }
  }
}

function insert_local_js() {
  global $wp;
  $pickup_data = [
    'nonce' => wp_create_nonce('pickup-nonce'),
    'pluginDirURL' => NUCSSA_PICKUP_DIR_URL,
    'userEndpoint' => home_url("$wp->request?json&endpoint=user"),
    'driverEndpoint' => home_url("$wp->request?json&endpoint=driver"),
    'orderEndpoint' => home_url("$wp->request?json&endpoint=order"),
  ];

  echo '<script>window.pickup_data = '.json_encode($pickup_data).'</script>';
}

function enableBrowserSyncOnDebugMode(){
  if (WP_DEBUG) {
    echo '<script async="" src="http://wp.localhost:3000/browser-sync/browser-sync-client.js"></script>';
  }
}

// authenticate RESTful/JSON request
function authenticate() {
  if (!isset($_SESSION['user'])) wp_send_json_error(null, 401);
}

function handle_json_request() {
  // file_log('>>>');
  // file_log($_SERVER);
  // file_log($_SESSION['user']);
  // file_log($_FILES);
  // file_log($_POST);
  // file_log($_GET);
  // file_log($_REQUEST);
  // file_log($_SERVER);
  if (isset($_REQUEST['endpoint'])) {
    switch ($_REQUEST['endpoint']) {
      case 'user':
        // file_log($_SERVER['REQUEST_METHOD']);
        // file_log($_SERVER);
        // file_log($_POST, 'post');
        // file_log($_GET, 'get');
        switch ($_SERVER['REQUEST_METHOD']){
          case 'GET':
            show_user($_REQUEST['email'] ?? NULL);
            exit;
          case 'PUT':
            update_user($_REQUEST['email'] ?? NULL);
            exit;

        }
        exit;

      case 'driver':
        switch ($_SERVER['REQUEST_METHOD']){
          case 'GET':
            show_driver();
            exit;
          case 'POST':
            create_driver();
            exit;
          case 'PUT':
            update_driver();
            exit;
        }
        exit;

      case 'order':
        switch ($_SERVER['REQUEST_METHOD']) {
          case 'GET':
            if (isset($_REQUEST['type'])) {
              switch ($_REQUEST['type']) {
                case 'pending':
                  list_pending_orders();
                  exit;
              }
            }
            if (isset($_REQUEST['user_role'])) {
              switch ($_REQUEST['user_role']) {
                case 'driver':
                  list_managed_orders();
                  exit;
                case 'passenger':
                  list_own_orders();
                  exit;
              }
            }
            get_orders();
            break;

          case 'POST':
            create_order();
            exit;

          case 'DELETE':
            delete_order();
            exit;

          case 'PUT':
            if (isset($_REQUEST['driver_action'])) {
              switch ($_REQUEST['driver_action']) {
                case 'pick':
                  driver_pick_order();
                  exit;
                case 'drop':
                  driver_drop_order();
                  exit;
              }
            } else {
              update_order();
              exit;
            }

          default:
            # code...
            break;
        }
        exit;
      default:
        # code...
        wp_send_json_error('bad request', 400);
        exit;
    }
  } else {
    wp_send_json_error('bad request', 400);
    exit;
  }
}
