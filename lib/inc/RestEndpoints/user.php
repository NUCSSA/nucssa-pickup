<?php

namespace nucssa_pickup\RestEndpoints\user;

use function nucssa_pickup\templates\template_pickup_page_utils\authenticate;

/**
 * REST GET Request
 * Get user by email, or get current logged in user if email not provided
 */
function show($email = NULL) {
  authenticate();

  global $wpdb;
  $user = $_SESSION['user'];
  $term = 'Fall 2019';
  if ($email) {
    $user = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE email = '$email'");
  }

  if ($user) {
    $driver = $wpdb->get_row("SELECT * FROM pickup_service_drivers WHERE user_id = $user->id");
    ob_clean(); // clear db error ouput in buffer
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
    $email = sanitize_text_field($data['email']);
    $phone = sanitize_text_field($data['phone']);
    $carrier = sanitize_text_field($data['carrier']);
    $passwd_hashed = \password_hash(sanitize_text_field($data['password']), PASSWORD_DEFAULT);
    $wpdb->update(
      'pickup_service_users',
      [
        'wechat' => $wechat,
        'name' => $name,
        'email' => $email,
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
    $_SESSION['user']->email = $email;
    $_SESSION['user']->phone = $phone;
    $_SESSION['user']->carrier = $carrier;
    $_SESSION['user']->passwd_hashed = $passwd_hashed;
    wp_send_json_success();
  } else {
    wp_send_json_error(null, 401);
  }
}
