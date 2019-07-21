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
    $mailer->FromName = 'NUCSSA接机';
  }

  public static function enalbeHTMLEmail($content_type) {
    return 'text/html';
  }

  public static function pickup_user_created($user) {
    $twig = self::initTwig();
    $context = self::baseContext();
    $context['user_display_name'] = $user->name;

    $message = $twig->render('welcome-message.twig', $context);
    wp_mail($user->email, '欢迎来到NUCSSA接机系统', $message);
  }
  public static function pickup_order_created($order, $passenger) {
    $twig = self::initTwig();
    $context = self::baseContext();

    // send to every valid driver
    global $wpdb;
    $term = 'Fall 2019';
    $drivers = $wpdb->get_results("SELECT u.* FROM pickup_service_users as u, pickup_service_drivers as d WHERE d.certified = TRUE AND d.term = $term");
    foreach ($drivers as $driver) {
      $context['user_display_name'] = $driver->name;
      $context['order'] = $order;
      $message = $twig->render('new-order-created.twig', $context);
      wp_mail($driver->email, '欢迎来到NUCSSA接机系统', $message);
    }
  }
  public static function order_picked_up_by_driver($order_id, $driver) {
    global $wpdb;
    $passenger = $wpdb->get_row("SELECT passenger.name, passenger.email FROM pickup_service_users as passenger, pickup_service_orders as o WHERE o.id = $order_id AND o.passenger = passenger.id");
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
    wp_mail('pickup@nucssa.org', '新司机待您审核', $message);
  }
  public static function drivers_application_approved($driver_ids) {
    global $wpdb;
    $driver_ids_str = implode(',', $driver_ids);
    $drivers = $wpdb->get_results("SELECT * FROM pickup_service_users u JOIN pickup_service_drivers d ON u.id = d.user_id WHERE d.id IN ($driver_ids_str)");

    $twig = self::initTwig();
    $context = self::baseContext();

    foreach ($drivers as $driver) {
      $context['user_display_name'] = $driver->name;
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
    $context['passenger'] = [
      'name' => '天惟',
      'wechat' => 'abcd123',
    ];

    $twig = self::initTwig();
    // echo $twig->render('order-updated-by-owner.twig', $context);

    $context['user_display_name'] = '接机管理小组成员';
    echo $twig->render('new-driver-application-submitted.twig', $context);

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
    $twig = new \Twig\Environment($loader);
    return $twig;
  }
}
