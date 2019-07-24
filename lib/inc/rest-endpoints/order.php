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
// User as a passenger, getting the orders that she created
function list_own_orders() {
  authenticate();

  global $wpdb;
  $passenger = $_SESSION['user'];
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE passenger = $passenger->id ORDER BY arrival_datetime ASC");
  // file_log('orders', $orders);
  // preload driver and passenger information
  foreach ($orders as $order) {
    // file_log('driver_id ', $order->driver);

    $driver = $wpdb->get_row("SELECT * FROM pickup_service_drivers d, pickup_service_users u WHERE u.id = d.user_id AND u.id = $order->driver");
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
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE driver = $driver->id AND approved = 1 ORDER BY arrival_datetime ASC");
  // preload driver and passenger information
  $driver_vehicle_info = $wpdb->get_row("SELECT * FROM pickup_service_drivers WHERE user_id = $driver->id");
  $driver->vehicle_plate_number = $driver_vehicle_info->vehicle_plate_number;
  $driver->vehicle_make_and_model = $driver_vehicle_info->vehicle_make_and_model;
  $driver->vehicle_color = $driver_vehicle_info->vehicle_color;
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
  $orders = $wpdb->get_results("SELECT * FROM pickup_service_orders WHERE driver is NULL AND approved = 1 ORDER BY arrival_datetime ASC");
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
  $wpdb->query("UPDATE pickup_service_drivers SET drop_count = drop_count + 1 WHERE user_id = $driver->id AND term = 'Fall 2019'");
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

  $passenger = $_SESSION['user'];

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if ($data['passenger'] != $passenger->id) wp_send_json_error(null, 401);

  global $wpdb;
  $order = [
    'drop_off_address' => sanitize_text_field($data['address']),
    'flight' => sanitize_text_field($data['flight']),
    'arrival_datetime' => sanitize_text_field($data['arrivalDatetime']),
    'arrival_terminal' => sanitize_text_field($data['terminal']),
    'companion_count' => sanitize_text_field($data['companionCount']),
    'luggage_count' => sanitize_text_field($data['luggageCount']),
    'urgent_contact_info' => sanitize_text_field($data['urgentContactInfo']),
    'note' => sanitize_text_field($data['note']),
    'term' => sanitize_text_field($data['term']),
    'huskyID' => $data['huskyID'],
  ];
  $resp = $wpdb->update(
    'pickup_service_orders',
    $order,
    [
      'id' => $data['id']
    ],
    '%s',
    '%d'
  );

  if ($resp === false) wp_send_json_error(null, 500);

  $order['id'] = $data['id'];
  /**
   * @param order
   * @param passenger
   * @param driver
   */
  do_action('order_updated_by_owner', $order, $passenger);
  wp_send_json_success();
}

// POST
function create() {
  authenticate();

  $user = $_SESSION['user'];

  // collect info from POST and FILES, save to disk, record URL in DB
  // override if exists
  $_FILES['admissionNotice']['name'] = $user->email.'-'.$_FILES['admissionNotice']['name'];
  $overrides = [
    'test_form' => false,
    // overwrite file
    'unique_filename_callback' => function ($dir, $name, $ext) {
      return $name;
    },
  ];
  $moveAdmissionNotice = wp_handle_upload($_FILES['admissionNotice'], $overrides);

  if ($moveAdmissionNotice && !isset($moveAdmissionNotice['error'])) {
    global $wpdb;
    $order_data = [
      'passenger' => $user->id,
      'drop_off_address' => sanitize_text_field($_POST['address']),
      'flight' => sanitize_text_field($_POST['flight']),
      'arrival_datetime' => sanitize_text_field($_POST['arrivalDatetime']),
      'arrival_terminal' => sanitize_text_field($_POST['terminal']),
      'companion_count' => sanitize_text_field($_POST['companionCount']),
      'luggage_count' => sanitize_text_field($_POST['luggageCount']),
      'urgent_contact_info' => sanitize_text_field($_POST['urgentContactInfo']),
      'note' => sanitize_text_field($_POST['note']),
      'term' => sanitize_text_field($_POST['term']),
      'huskyID' => $_POST['huskyID'],
      'admission_notice' => $moveAdmissionNotice['url'],
    ];
    $wpdb->replace(
      'pickup_service_orders',
      $order_data
    );
    /**
     * @param $oder
     * @param passenger
     */
    do_action('new_order_created', $order_data, $user);
    wp_send_json_success();
  }
  wp_send_json_error(null, 500);
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
