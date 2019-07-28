<?php

namespace nucssa_pickup;

class Deactivation {
  public static function init() {
    self::removeCron();
  }

  private static function removeCron() {
    $timestamp = wp_next_scheduled('nucssa_pickup_send_feedback_cron');
    wp_unschedule_event($timestamp, 'nucssa_pickup_send_feedback_cron');
  }
}