<?php

namespace nucssa_pickup\rest_endpoints\order;

use function nucssa_core\utils\debug\file_log;
use function nucssa_pickup\templates\template_utils\authenticate;

// GET
function show() {
  authenticate();
}

// GET
function index() {
  authenticate();
}

// GET
function list_own_orders() {
  authenticate();

  global $wpdb;
  $passenger = $_SESSION['user'];
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE passenger = $passenger->id ORDER BY arrival_datetime DESC");
  // file_log('orders', $orders);
  // preload driver and passenger information
  foreach ($orders as $order) {
    // file_log('driver_id ', $order->driver);

    $driver = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE id = $order->driver");

    $order->driver = $driver;
    $order->passenger = $passenger;
    // file_log('order', $order);
  }

  // clean up buffer and send response
  ob_clean();
  wp_send_json_success($orders);
}

// GET
function list_managed_orders() {
  authenticate();

  global $wpdb;
  $driver = $_SESSION['user'];
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE driver = $driver->id ORDER BY arrival_datetime DESC");
  // preload driver and passenger information
  foreach ($orders as $order) {
    // file_log('passenger_id ', $order->passenger);
    $passenger = $wpdb->get_row("SELECT * FROM pickup_service_users WHERE id = $order->passenger");

    $order->driver = $driver;
    $order->passenger = $passenger;
  }

  // clean up buffer and send response
  ob_clean();
  wp_send_json_success($orders);
}

// GET
// Return all unpicked orders
function list_pending_orders() {
  authenticate();

  global $wpdb;
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE driver is NULL ORDER BY arrival_datetime DESC");
  wp_send_json_success($orders);
}

// PUT
// current user is driver, get driver info from SESSION
function driver_pick_order() {
  authenticate();

  global $wpdb;
  $driver = $_SESSION['user'];
  $data = json_decode(file_get_contents('php://input'), true);
  $order_id = $data['order_id'];

  // verify if it is already taken
  if ($wpdb->get_row("SELECT * FROM pickup_service_orders WHERE id = $order_id AND driver IS NOT NULL")) {
    wp_send_json_error('order taken', 410);
  }

  $count = $wpdb->update(
    'pickup_service_orders',
    [
      'driver' => $driver->id,
    ],
    [
      'id' => $order_id,
    ],
    '%d', '%d'
  );

  if ($count !== 1) {
    wp_send_json_error('order taken', 410);
  } else {
    /**
     * @param int $order_id
     * @param Object $driver Object containing driver user's contact information
     */
    do_action('order_picked_up_by_driver', $order_id, $driver);
    wp_send_json_success();
  }
}

// PUT
// current user is driver, get driver info from SESSION
function driver_drop_order() {
  authenticate();

  global $wpdb;
  $driver = $_SESSION['user'];
  $data = json_decode(file_get_contents('php://input'), true);
  $order_id = $data['order_id'];

  // file_log('order_id', $order_id);
  $wpdb->update(
    'pickup_service_orders',
    [
      'driver' => null,
    ],
    [
      'id' => $order_id,
    ],
    null,
    '%d'
  );
  /**
   * @param int $order_id
   * @param Object $driver Object containing driver user's contact information
   */
  do_action('order_dropped_by_driver', $order_id, $driver);
  wp_send_json_success();
}

// PUT
function update() {
  authenticate();

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  // file_log('>>>', $data);
  $passenger = $_SESSION['user'];
  if ($data['passenger'] != $passenger->id) wp_send_json_error(null, 401);

  global $wpdb;
  $address = sanitize_text_field($data['address']);
  $flight = sanitize_text_field($data['flight']);
  $arrivalDatetime = sanitize_text_field($data['arrivalDatetime']);
  $terminal = sanitize_text_field($data['terminal']);
  $companionCount = sanitize_text_field($data['companionCount']);
  $urgentContactInfo = sanitize_text_field($data['urgentContactInfo']);
  $note = sanitize_text_field($data['note']);
  $term = sanitize_text_field($data['term']);

  $resp = $wpdb->update(
    'pickup_service_orders',
    [
      'drop_off_address' => $address,
      'flight' => $flight,
      'arrival_datetime' => $arrivalDatetime,
      'arrival_terminal' => $terminal,
      'companion_count' => $companionCount,
      'urgent_contact_info' => $urgentContactInfo,
      'note' => $note,
      'term' => $term,
    ],
    [
      'id' => $data['id']
    ],
    '%s',
    '%d'
  );
  if ($resp === false){
    wp_send_json_error(null, 500);
  } else {
    $order = [
      'id' => $data['id'],
      'drop_off_address' => $address,
      'flight' => $flight,
      'arrival_datetime' => $arrivalDatetime,
      'arrival_terminal' => $terminal,
      'companion_count' => $companionCount,
      'urgent_contact_info' => $urgentContactInfo,
      'note' => $note,
    ];
    /**
     * @param order
     * @param passenger
     * @param driver
     */
    do_action('order_updated_by_owner', $order, $passenger);
    wp_send_json_success();
  }
}

// POST
function create() {
  authenticate();

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  global $wpdb;
  $user = $_SESSION['user'];

  $address = sanitize_text_field($data['address']);
  $flight = sanitize_text_field($data['flight']);
  $arrivalDatetime = sanitize_text_field($data['arrivalDatetime']);
  $terminal = sanitize_text_field($data['terminal']);
  $companionCount = sanitize_text_field($data['companionCount']);
  $urgentContactInfo = sanitize_text_field($data['urgentContactInfo']);
  $note = sanitize_text_field($data['note']);
  $term = sanitize_text_field($data['term']);

  $order_data = [
    'passenger' => $user->id,
    'drop_off_address' => $address,
    'flight' => $flight,
    'arrival_datetime' => $arrivalDatetime,
    'arrival_terminal' => $terminal,
    'companion_count' => $companionCount,
    'urgent_contact_info' => $urgentContactInfo,
    'note' => $note,
    'term' => $term,
  ];

  $wpdb->insert(
    'pickup_service_orders',
    $order_data
  );

  /**
   * @param $oder
   * @param passenger
   */
  do_action('pickup_order_created', $order_data, $user);
  wp_send_json_success();
}

// DELETE, user deletes her own order
function delete() {
  authenticate();

  global $wpdb;
  $order_id = $_REQUEST['order_id'];
  $order = $wpdb->get_row("SELECT * FROM pickup_service_orders WHERE id = $order_id");
  $passenger = $_SESSION['user'];
  $res = $wpdb->delete(
    'pickup_service_orders',
    [
      'id' => $order_id,
      'passenger' => $passenger->id,
    ],
    '%d'
  );

  if ($res) {
    do_action('order_deleted_by_owner', $order, $passenger);
    wp_send_json_success();
  } else {
    wp_send_json_error(null, 500);
  }
}
