<?php
namespace nucssa_pickup\rest_endpoints\driver;
use function nucssa_core\utils\debug\file_log;
use function nucssa_pickup\templates\template_utils\authenticate;

if (!function_exists('wp_handle_upload')) {
  require_once(ABSPATH . 'wp-admin/includes/file.php');
}

// GET Request
function show() {
  authenticate();

  global $wpdb;
  $user = $_SESSION['user'];
  $driver = $wpdb->get_row("SELECT * FROM pickup_service_drivers WHERE user_id = $user->id AND term = 'Fall 2019'");
  wp_send_json_success(['driver' => $driver]);
}

// POST Request
function create() {
  authenticate();

  $user = $_SESSION['user']; // Object
  // collect infor from POST and FILES and save to local and record file path in database
  // override if exists
  $_FILES['huskycard']['name'] = $user->email.'-'. $_FILES['huskycard']['name'];
  $_FILES['license']['name'] = $user->email.'-'. $_FILES['license']['name'];
  // file_log($_FILES['huskycard']);
  $overrides = [
    'test_form' => false,
    'unique_filename_callback' => function ($dir, $name, $ext) {return $name;}, // overwrite file
  ];
  $moveHuskycard = wp_handle_upload($_FILES['huskycard'], $overrides);
  $moveLicense = wp_handle_upload($_FILES['license'], $overrides);

  if ($moveHuskycard && $moveLicense && !isset($moveHuskycard['error']) && !isset($moveLicense['error'])) {
    // file_log('file uploaded');
    // file_log($_SESSION['user']);
    // file_log($_POST);
    global $wpdb;
    $wpdb->replace('pickup_service_drivers', [
      'user_id' => $user->id,
      'huskyID' => $_POST['huskyID'],
      'husky_card' => $moveHuskycard['url'],
      'drivers_license' => $moveLicense['url'],
      'vehicle_plate_number' => $_POST['vehiclePlateNumber'],
      'vehicle_make_and_model' => $_POST['vehicleMakeAndModel'],
      'vehicle_color' => $_POST['vehicleColor'],
      'term' => $_POST['term'],
    ]);
    $wpdb->update(
      'pickup_service_users',
      [
        'phone' => $_POST['phone'],
        'carrier' => $_POST['carrier'],
      ],
      [
        'id' => $user->id
      ],
      '%s',
      '%d'
    );
    $user->phone = $_POST['phone'];
    $user->carrier = $_POST['carrier'];

    do_action('new_driver_application_submitted');
    wp_send_json_success();
  } else {
    // file_log('failed: ' . $movefile['error']);
    // TODO: delete file if file already saved. (it's not persisted in db).
    file_log('Error', $moveHuskycard['error'] ?? $moveLicense['error']);
    wp_send_json_error($moveHuskycard['error'] ?? $moveLicense['error'], 500);
  }
}

// PUT Request
function update() {
  authenticate();

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  global $wpdb;
  $user = $_SESSION['user'];
  $vehiclePlateNumber = sanitize_text_field($data['vehiclePlateNumber']);
  $vehicleMakeAndModel = sanitize_text_field($data['vehicleMakeAndModel']);
  $vehicleColor = sanitize_text_field($data['vehicleColor']);
  $wpdb->update(
    'pickup_service_drivers',
    [
      'vehicle_plate_number' => $vehiclePlateNumber,
      'vehicle_make_and_model' => $vehicleMakeAndModel,
      'vehicle_color' => $vehicleColor,
    ],
    [
      'user_id' => $user -> id,
      'term' => 'Fall 2019'
    ],
    '%s',
    ['%d', '%s']
  );
  wp_send_json_success();
}
