<?php

namespace nucssa_pickup\rest_endpoints\user;

use function nucssa_core\utils\debug\file_log;
use function nucssa_pickup\templates\template_utils\authenticate;

/**
 * REST GET Request
 * Get user by email, or get current logged in user if email not provided
 */
function show($email = NULL) {
  authenticate();

  global $wpdb;
  $user = $_SESSION['user'];
  // file_log($user);
  if ($email) {
    $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'");
  }

  if ($user) {
    $driver = $wpdb->get_row("SELECT * FROM pickup_service_drivers WHERE user_id = $user->id");
    ob_clean(); // clear db error ouput in buffer
    $user->isDriver = !!($driver);

    file_log('driver', $driver);
    if ($driver) {
      if ($driver->certified === NULL) {
        $user -> role = 'PENDING_DRIVER';
      } else if ($driver->certified == TRUE) {
        $user -> role = 'DRIVER';
      } else {
        $user -> role = 'FAILED_DRIVER';
      }
    } else {
      $user -> role = 'PASSENGER';
    }
    // file_log($user);
    wp_send_json_success(['user' => $user]);
  } else {
    wp_send_json_error(null, 401);
  }
}

/**
 * REST PUT Request
 */
function update($email = NULL) {
  authenticate();

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  global $wpdb;
  $user = $_SESSION['user'];
  if ($email) {
    $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'");
  }

  if ($user) {
    $wechat = sanitize_text_field($data['wechat']);
    $name = sanitize_text_field($data['name']);
    $phone = sanitize_text_field($data['phone']);
    $carrier = sanitize_text_field($data['carrier']);
    $passwd_hashed = \password_hash(sanitize_text_field($data['password']), PASSWORD_DEFAULT);
    $wpdb->update(
      'pickup_service_users',
      [
        'wechat' => $wechat,
        'name' => $name,
        'phone' => $phone,
        'carrier' => $carrier,
        'passwd_hashed' => $passwd_hashed,
      ],
      [
        'id' => $user->id
      ],
      '%s',
      '%d'
    );
    $_SESSION['user']->wechat = $wechat;
    $_SESSION['user']->name = $name;
    $_SESSION['user']->phone = $phone;
    $_SESSION['user']->carrier = $carrier;
    $_SESSION['user']->passwd_hashed = $passwd_hashed;
    wp_send_json_success();
  } else {
    wp_send_json_error(null, 401);
  }
}
