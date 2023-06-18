<?php

namespace nucssa_pickup\AdminDashboard;

class FeedbackListTable extends WP_List_Table
{
  public function __construct()
  {
    parent::__construct([
      'singular' => 'feedback',
      'plural' => 'feedbacks',
      'ajax' => false,
    ]);

    /**
     * REQUIRED. Define column headers.
     * This property requires a 4-value array :
     *  - The first value is an array containing column slugs and titles (see the get_columns() method).
     *  - The second value is an array containing the values of fields to be hidden.
     *  - The third value is an array of columns that should allow sorting (see the get_sortable_columns() method).
     *  - The fourth value is a string defining which column is deemed to be the primary one, displaying the row's actions (edit, view, etc).
     *    The value should match that of one of your column slugs in the first value.
     */
    $this->_column_headers = [
      $this->get_columns(),
      [],
      [],
      'arrival_time_and_address'
    ];
  }

  public function prepare_items()
  {
    global $wpdb;

    $current_page = $this->get_pagenum();
    $per_page = 10;
    $offset = ($current_page - 1) * $per_page;

    $total_count_query = "SELECT COUNT(*) FROM pickup_service_feedback";
    $data_query = "SELECT o.drop_off_address AS address, o.arrival_datetime AS arrival,
                      f.passenger_feedback, f.driver_feedback, f.created_at AS feedback_created_at,
                      passenger.name AS passenger_name, passenger.wechat AS passenger_wechat,
                      driver.name AS driver_name, driver.wechat AS driver_wechat
                    FROM pickup_service_orders o
                    RIGHT JOIN pickup_service_feedback f
                    ON f.order_id = o.id
                    LEFT JOIN pickup_service_users passenger
                    ON o.passenger = passenger.id
                    LEFT JOIN pickup_service_users driver
                    ON o.driver = driver.id
                    ORDER BY feedback_created_at
                    LIMIT $offset, $per_page";
    $total_items = $wpdb->get_var($total_count_query);
    $total_pages = ceil($total_items / $per_page);
    $feedbacks = $wpdb->get_results($data_query);


    $this->items = $feedbacks;
    $this->set_pagination_args([
      'total_items' => $total_items,
      'total_pages' => $total_pages,
      'per_page' => $per_page
    ]);

  }

  /********** SET UP TABLE LAYOUT **********/
  /**
   * Sets column mapping relationship from slug to Title
   */
  public function get_columns()
  {
    return [
      'order_info'     => '订单信息',
      'passenger_feedback'    => '乘客反馈',
      'driver_feedback'  => '司机反馈',
      'feedback_created_at' => '反馈时间'
    ];
  }

  public function column_order_info($item)
  {
    ?>
    <span class="label">地址</span><br />
    <span class="content"><?php echo $item->address ?></span>
    <br />
    <span class="label">到达</span><br />
    <span class="content"><?php echo $item->arrival ?></span>
  <?php
  }
  public function column_passenger_feedback($item)
  {
    $passenger_feedback = json_decode($item->passenger_feedback);
    ?>
    <?php if ($passenger_feedback) { ?>
      <div class="feedback">
        <span class="label">对司机评价</span><span class="content"><?php echo $passenger_feedback->driver_rating ?></span>
        <br />
        <span class="label">活动打分</span><span class="content"><?php echo $passenger_feedback->activity_rating ?></span>
        <br />
        <span class="label">留言</span>
        <p>
          <?php echo esc_textarea($passenger_feedback->comment); ?>
        </p>
      </div>
    <?php } ?>
  <?php
  }
  public function column_driver_feedback($item)
  {
    $driver_feedback = json_decode($item->driver_feedback);
    ?>
    <div class="contact">
      <?php echo "$item->driver_name | $item->driver_wechat" ?>
    </div>
    <?php if ($driver_feedback) { ?>
      <div class="feedback">
        <span class="label">对乘客评价</span><span class="content"><?php echo $driver_feedback->passenger_rating ?></span>
        <br />
        <span class="label">活动打分</span><span class="content"><?php echo $driver_feedback->activity_rating ?></span>
        <br />
        <span class="label">留言</span>
        <p>
          <?php echo esc_textarea($driver_feedback->comment); ?>
        </p>
      </div>
    <?php } ?>
  <?php
  }
  public function column_feedback_created_at($item)
  {
    return $item->feedback_created_at;
  }
}
