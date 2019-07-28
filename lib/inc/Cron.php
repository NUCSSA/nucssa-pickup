<?php

namespace nucssa_pickup;

class Cron {
  public static function scheduleEvents() {
    if (!wp_next_scheduled('nucssa_pickup_send_feedback_cron')) {
      wp_schedule_event(time(), 'hourly', 'nucssa_pickup_send_feedback_cron');
    }
  }
}
