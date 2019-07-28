<?php

namespace nucssa_pickup\templates\template_pickup_feedback_page_utils;

// POST
function processFeedbackSubmission() {
  global $wpdb;
  ['role' => $role, 'order_id' => $order_id] = $_POST;

  $opponent_role = ($role == 'driver') ? 'passenger' : 'driver';

  $table = 'pickup_service_feedback';
  $data = [
    "{$role}_feedback" => wp_json_encode([
      "{$opponent_role}_rating" => $_POST["{$opponent_role}-rating"],
      'activity_rating' => $_POST['activity-rating'],
      'comment' => sanitize_textarea_field($_POST['comment']),
    ]),
    'order_id' => $order_id,
  ];
  // check existence of record
  if ($wpdb->get_row("SELECT * FROM pickup_service_feedback WHERE order_id = $order_id")) {
    $wpdb->update(
      $table,
      $data,
      ['order_id' => $order_id],
    );
  } else {
    $wpdb->insert(
      $table,
      $data,
    );
  }
}