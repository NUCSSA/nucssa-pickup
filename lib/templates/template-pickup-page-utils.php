<?php
namespace nucssa_pickup\templates\template_pickup_page_utils;

use nucssa_pickup\mail_service\MailService;

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

function logout() {
  session_unset();
  session_destroy();
  $redirect = remove_query_arg('auth');
  wp_redirect($redirect);
}

function process_submission_data(){

  // Process JSON REST Request
  if (isset($_REQUEST['json'])) {
    handle_json_request();
    exit;
  }
  elseif (isset($_REQUEST['auth'])){
    switch ($_REQUEST['auth']) {
  // Process Logout Request
      case 'logout':
        logout();
        break;

      case 'reset':
        if (isset($_GET['user'], $_GET['transient'], $_POST['password1'], $_POST['password2']) ){
          // Process Password Resetting Form Submission
          process_reset_resetting_form_submission();
        } elseif (!empty($_POST)){
          // Process Password Reset Request
          process_reset_request();
        }

        break;

  // Process Login | Registrtion Data
      case 'login-reg':
        if (!empty($_POST)) {
          process_login_or_registration();
        }
        break;
    }
  }
  return;
}

function process_reset_request() {
  global $wpdb;
  $email = $_POST['user']['email'];

  // check existence of account
  if ($user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'")) {
    // create transient link and send to user
    MailService::resetPassword($user->name, $email);

    // show success message
    $_SESSION['reset-message'] = ['success', 'Reset email is sent, please check your email.'];
  } else {
    // show error message
    $_SESSION['reset-message'] = ['error', 'The email does not exist.'];
  }
}

function process_reset_resetting_form_submission() {
  ['password1' => $pass1, 'password2' => $pass2] = $_POST;
  if ($pass1 !== $pass2) {
    $_SESSION['reset-message'] = [
      'error',
      '两次输入不一致'
    ];
  } else {
    $password_hashed = \password_hash(sanitize_text_field($pass1), PASSWORD_DEFAULT);
    $email = $_GET['user'];

    global $wpdb;
    $wpdb->update(
      'pickup_service_users',
      ['passwd_hashed' => $password_hashed],
      ['email' => $email]
    );

    $_SESSION['login-message'] = [
      'success',
      '重置成功'
    ];

    // Finally Clear Transient
    delete_transient("reset-by-$email");

    // Redirect to Login Screen
    wp_redirect(home_url('pickup'));
  }
}

/**
 * @return {bool} returns true if the reset link is valid
 */
function verify_reset_link() {
  ['user' => $email, 'transient' => $transient] = $_GET;
  $saved_transient = get_transient( "reset-by-$email" );
  return $saved_transient === $transient;
}

function process_login_or_registration() {
  global $wpdb;
  $submission_type = $_POST['form-for'];

  if ($submission_type == 'login'){
    $email = sanitize_text_field($_POST['user']['email']);
    $password = sanitize_text_field($_POST['user']['password']);
    $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'");
    if ($user && password_verify($password, $user->passwd_hashed)) {
      $_SESSION['user'] = $user;
      $_SESSION['login-message'] = NULL;

      $redirect = remove_query_arg('auth');
      wp_redirect($redirect);
    } else {
      $_SESSION['login-message'] = ['error', '账号密码错误, 请重试'];
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
    $redirect = remove_query_arg('auth');
    wp_redirect($redirect);
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
    'pickupAdminEmail' => PICKUP_ADMIN_EMAIL,
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
  if (isset($_REQUEST['endpoint'])) {
    switch ($_REQUEST['endpoint']) {
      case 'user':
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
