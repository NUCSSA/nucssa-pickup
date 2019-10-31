<?php
namespace nucssa_pickup\mail_service;

use Timber\Timber;
use function nucssa_core\utils\debug\file_log;
use stdClass;

class MailService {
  public static function sendGridRelay($mailer) {
    $mailer->isSMTP();
    $mailer->Host = SENDGRID_SMTP_HOST;
    $mailer->SMTPAuth = true;
    $mailer->Port = SENDGRID_SMTP_PORT;
    $mailer->Username = SENDGRID_SMTP_USERNAME;
    $mailer->Password = SENDGRID_API_KEY;
    $mailer->SMTPSecure = 'TLS';
    $mailer->From = 'NoReply@nucssa.org';
    $mailer->FromName = 'NUCSSA';
  }

  public static function enalbeHTMLEmail($content_type) {
    return 'text/html';
  }

  public static function np_user_created($user) {
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $user->name;

    $message = $twig->render('welcome-message.twig', $context);
    wp_mail($user->email, '欢迎来到NUCSSA接机系统', $message);
  }
  public static function new_order_created($order, $passenger) {
    $twig = self::initTwig();
    $context = self::baseContext();

    // send a notice to pickup admins
    $context['user_display_name'] = '接机管理小组成员';
    $message = $twig->render('new-order-created.twig', $context);
    wp_mail(PICKUP_ADMIN_EMAIL, '有新生订单待您审核', $message);
  }
  public static function orders_application_approved($order_ids) {
    $twig = self::initTwig();
    $context = self::baseContext();

    global $wpdb;
    $order_ids_str = implode(',', $order_ids);
    $orders = $wpdb->get_results("
      SELECT name, email, flight, arrival_datetime, arrival_terminal, companion_count,
          luggage_count, drop_off_address, urgent_contact_info, note
      FROM pickup_service_orders o
      LEFT JOIN pickup_service_users u
      ON o.passenger = u.id
      WHERE o.id IN ($order_ids_str)
    ");

    $term = 'Fall 2019';
    $drivers = $wpdb->get_results("SELECT u.* FROM pickup_service_users as u RIGHT JOIN pickup_service_drivers as d ON d.user_id = u.id WHERE d.certified = TRUE AND d.term = '$term'");
    foreach ($orders as $order) {
      // send to every valid driver
      foreach ($drivers as $driver) {
        $context['user_display_name'] = $driver->name;
        $context['order'] = $order;
        $message = $twig->render('order-application-approved-to-drivers.twig', $context);
        wp_mail($driver->email, '新订单提醒', $message);
      }

      // send to order owner as well
      $context['user_display_name'] = $order->name;
      $context['order'] = $order;
      $context['pickup_assistant_qr_code_url'] = NUCSSA_PICKUP_DIR_URL . '/public/images/pickup-assistant.png';
      $message = $twig->render('order-application-approved-to-owner.twig', $context);
      wp_mail($order->email, '订单通过审核', $message);
    }
  }
  public static function orders_application_declined($order_ids) {
    $twig = self::initTwig();
    $context = self::baseContext();

    global $wpdb;
    $order_ids_str = implode(',', $order_ids);
    $orders = $wpdb->get_results("
      SELECT name, email, flight, arrival_datetime, arrival_terminal, companion_count,
          luggage_count, drop_off_address, urgent_contact_info, note
      FROM pickup_service_orders o
      LEFT JOIN pickup_service_users u
      ON o.passenger = u.id
      WHERE o.id IN ($order_ids_str)
    ");

    foreach ($orders as $order) {
      // send notice to every order owner
      $context['user_display_name'] = $order->name;
      $context['order'] = $order;
      $message = $twig->render('order-application-declined-to-owner.twig', $context);
      wp_mail($order->email, '订单审核未通过', $message);
    }
  }
  public static function order_picked_up_by_driver($order_id, $driver) {
    global $wpdb;
    $passenger = $wpdb->get_row("SELECT passenger.name, passenger.email FROM pickup_service_users as passenger, pickup_service_orders as o WHERE o.id = $order_id AND o.passenger = passenger.id");
    $driver_vehicle_info = $wpdb->get_row("SELECT * FROM pickup_service_drivers WHERE user_id = $driver->id");
    $driver->vehicle_plate_number = $driver_vehicle_info->vehicle_plate_number;
    $driver->vehicle_make_and_model = $driver_vehicle_info->vehicle_make_and_model;
    $driver->vehicle_color = $driver_vehicle_info->vehicle_color;
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $passenger->name;

    $context['driver'] = $driver;

    $message = $twig->render('order-picked-up-by-driver.twig', $context);
    wp_mail($passenger->email, '订单更新提醒', $message);
  }
  public static function order_dropped_by_driver($order_id, $driver) {
    global $wpdb;
    $passenger = $wpdb->get_row("SELECT passenger.name, passenger.email FROM pickup_service_users as passenger, pickup_service_orders as o WHERE o.id = $order_id AND o.passenger = passenger.id");
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $passenger->name;

    $message = $twig->render('order-dropped-by-driver.twig', $context);
    wp_mail($passenger->email, '订单更新提醒', $message);
  }
  public static function order_updated_by_owner($order, $passenger) {
    global $wpdb;
    $driver = $wpdb->get_row("SELECT driver.name, driver.email FROM pickup_service_users as driver, pickup_service_orders as o WHERE o.id = {$order['id']} AND o.driver = driver.id");
    if (!$driver) return;

    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $driver->name;
    $context['passenger'] = $passenger;
    $context['order'] = $order;

    $message = $twig->render('order-updated-by-owner.twig', $context);
    wp_mail($driver->email, '订单更新提醒', $message);
  }
  public static function order_deleted_by_owner($order, $passenger) {
    global $wpdb;
    $driver = $wpdb->get_row("SELECT driver.name, driver.email FROM pickup_service_users as driver, pickup_service_orders as o WHERE o.id = $order->id AND o.driver = driver.id");
    if (!$driver) return;
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $driver->name;
    $context['passenger'] = $passenger;
    $context['order'] = $order;

    $message = $twig->render('order-deleted-by-owner.twig', $context);
    wp_mail($driver->email, '订单删除通知', $message);
  }
  public static function new_driver_application_submitted() {
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = '接机管理小组成员';
    $message = $twig->render('new-driver-application-submitted.twig', $context);
    wp_mail(PICKUP_ADMIN_EMAIL, '有新司机待您审核', $message);
  }
  public static function drivers_application_approved($driver_ids) {
    global $wpdb;
    $driver_ids_str = implode(',', $driver_ids);
    $drivers = $wpdb->get_results("SELECT * FROM pickup_service_users u JOIN pickup_service_drivers d ON u.id = d.user_id WHERE d.id IN ($driver_ids_str)");

    $twig = self::initTwig();
    $context = self::baseContext();

    foreach ($drivers as $driver) {
      $context['user_display_name'] = $driver->name;
      $context['pickup_assistant_qr_code_url'] = NUCSSA_PICKUP_DIR_URL . '/public/images/pickup-assistant.png';
      $message = $twig->render('driver-application-approved.twig', $context);
      wp_mail($driver->email, '司机申请成功', $message);
    }
  }
  public static function drivers_application_declined($driver_ids) {
    global $wpdb;
    $driver_ids_str = implode(',', $driver_ids);
    $drivers = $wpdb->get_results("SELECT * FROM pickup_service_users u JOIN pickup_service_drivers d ON u.id = d.user_id WHERE d.id IN ($driver_ids_str)");

    $twig = self::initTwig();
    $context = self::baseContext();

    foreach ($drivers as $driver) {
      $context['user_display_name'] = $driver->name;
      $message = $twig->render('driver-application-declined.twig', $context);
      wp_mail($driver->email, '司机申请失败', $message);
    }
  }
  public static function request_feedbacks_for_finished_orders() {
    global $wpdb;

    // find finished valid orders that does't have feedback notices sent yet
    // preload with passenger and driver info
    $query = "
      SELECT o.*, p.name AS passenger_name, p.email AS passenger_email,
             d.name AS driver_name, d.email AS driver_email
      FROM pickup_service_orders o
      LEFT JOIN pickup_service_users p
      ON o.passenger = p.id
      LEFT JOIN pickup_service_users d
      ON o.driver = d.id
      WHERE o.driver IS NOT NULL
      AND o.approved = TRUE
      AND o.feedback_sent = FALSE
      AND TIMESTAMPDIFF(HOUR, o.arrival_datetime, NOW()) >= 3
    ";
    $orders_for_feedbacks = $wpdb->get_results($query);
    if (!empty($orders_for_feedbacks)) {
      $twig = self::initTwig();
      $context = self::baseContext();

      foreach ($orders_for_feedbacks as $order) {
        $feedback_page_url = \get_the_permalink(get_option('nucssa_pickup_service_feedback_page_id'));
        // To Passenger
        $context['user_display_name'] = $order->passenger_name;
        $context['feedback_url'] = $feedback_page_url.'?request='.urlencode(\base64_encode(json_encode([
          'order_id' => $order->id,
          'from_role' => 'passenger'
        ])));
        $message = $twig->render('feedback-request-to-passenger.twig', $context);
        wp_mail($order->passenger_email, '为您的订单打分', $message);


        // To Driver
        $context['user_display_name'] = $order->driver_name;
        $context['feedback_url'] = $feedback_page_url.'?request='.urlencode(\base64_encode(json_encode([
          'order_id' => $order->id,
          'from_role' => 'driver'
        ])));
        $context['passenger_name'] = $order->passenger_name;
        $context['order'] = $order;
        $message = $twig->render('feedback-request-to-driver.twig', $context);
        wp_mail($order->driver_email, '请为这个订单打分', $message);
      }

      // update order.feedback_sent to TRUE in db
      $order_ids_str = implode(',', array_map(function($order){return $order->id;}, $orders_for_feedbacks));
      $wpdb->query("UPDATE pickup_service_orders SET feedback_sent = TRUE WHERE id IN ($order_ids_str)");
    }
  }

  public static function resetPassword($name, $email) {
    $twig = self::initTwig();
    $context = self::baseContext();

    global $wp;
    $transient = md5(rand());
    set_transient( "reset-by-$email", $transient, 2 * HOUR_IN_SECONDS );
    $link = home_url('pickup');
    $link = add_query_arg(
      ['auth' => 'reset', 'user' => $email, 'transient' => $transient],
      $link
    );

    $context['user_display_name'] = $name;
    $context['reset_link'] = $link;
    $message = $twig->render('reset-password.twig', $context);
    wp_mail($email, 'Reset Password', $message);
  }

  public static function testHTML() {

    $context = self::baseContext();
    $context['user_display_name'] = '纪路';
    $context['driver_display_name'] = '程然';
    $context['driver_wechat'] = 'jjpro13';
    $context['driver_phone'] = '(617) 373-2000';
    $context['driver_carrier'] = 'AT&T';
    $context['arrival_datetime'] = '';
    $driver = new stdClass();
    $driver->name = 'Name';
    $driver->wechat = 'wechat';
    $driver->phone = 'phone';
    $driver->carrier = 'carrier';
    $context['driver'] = $driver;
    $context['order'] = [
      'flight' => 'AK47',
      'arrival_datetime' => '07/26/2019 12:28 PM',
      'arrival_terminal' => 'E',
      'drop_off_address' => '666 Huntington Ave. ',
      'note' => 'Perfect😜 谢谢~',
    ];
    $context['pickup_assistant_qr_code_url'] = NUCSSA_PICKUP_DIR_URL.'/public/images/pickup-assistant.png';
    $context['passenger'] = [
      'name' => '天惟',
      'wechat' => 'abcd123',
    ];

    $twig = self::initTwig();
    echo $twig->render('driver-application-approved.twig', $context);

    $context['user_display_name'] = '接机管理小组成员';
    // echo $twig->render('new-driver-application-submitted.twig', $context);

    // $message = $twig->render('templates/welcome-message.twig', $context);
    // wp_mail('lu.ji1@me.com', 'test', $message);
    // Timber::render('./templates/welcome-message.twig', $context);
  }

  private static function baseContext() {
    $context = Timber::context();
    $context['nucssa_logo_url'] = NUCSSA_CORE_DIR_URL . '/public/images/logo.png';

    return $context;
  }

  private static function initTwig() {
    $loader = new \Twig\Loader\FilesystemLoader(dirname(__FILE__) . '/templates');
    $twig = new \Twig\Environment($loader, [
      'cache' => NUCSSA_PICKUP_DIR_PATH.'lib/inc/mail-service/templates/cache',
    ]);
    return $twig;
  }
}
