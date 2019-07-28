<?php
use nucssa_pickup\Cron;

// use function nucssa_core\utils\debug\file_log;
// use nucssa_pickup\mail_service\MailService;
// add_action('plugins_loaded', function() {
// });

add_action('admin_menu', ['nucssa_pickup\admin_dashboard\menu_page\AdminMenu', 'init']);
add_action('admin_enqueue_scripts', ['nucssa_pickup\AdminScripts', 'init']);
add_action('rest_api_init', ['nucssa_pickup\RESTful', 'init']);
register_activation_hook(NUCSSA_PICKUP_PLUGIN_FILENAME, ['nucssa_pickup\Activation', 'init']);
register_deactivation_hook(NUCSSA_PICKUP_PLUGIN_FILENAME, ['nucssa_pickup\Deactivation', 'init']);
add_action('admin_notices', ['nucssa_pickup\admin_dashboard\Miscellaneous', 'verifyPermalinkSetting']);
add_filter('parse_query', ['nucssa_pickup\admin_dashboard\Miscellaneous', 'hidePickupPageFromDashboard']);
add_filter('template_include', ['nucssa_pickup\admin_dashboard\Miscellaneous', 'addPickupPageTemplate']);
add_action('phpmailer_init', ['nucssa_pickup\mail_service\MailService', 'sendGridRelay']);
add_filter('wp_mail_content_type', ['nucssa_pickup\mail_service\MailService', 'enalbeHTMLEmail']);

// Email Notifications
add_action('order_picked_up_by_driver', ['nucssa_pickup\mail_service\MailService', 'order_picked_up_by_driver'], 10, 2);
add_action('order_dropped_by_driver', ['nucssa_pickup\mail_service\MailService', 'order_dropped_by_driver'], 10, 2);
add_action('order_updated_by_owner', ['nucssa_pickup\mail_service\MailService', 'order_updated_by_owner'], 10, 2);
add_action('order_deleted_by_owner', ['nucssa_pickup\mail_service\MailService', 'order_deleted_by_owner'], 10, 2);
add_action('np_user_created', ['nucssa_pickup\mail_service\MailService', 'np_user_created']);
add_action('new_order_created', ['nucssa_pickup\mail_service\MailService', 'new_order_created'], 10, 2);
add_action('new_driver_application_submitted', ['nucssa_pickup\mail_service\MailService', 'new_driver_application_submitted']);
add_action('drivers_application_approved', ['nucssa_pickup\mail_service\MailService', 'drivers_application_approved']);
add_action('drivers_application_declined', ['nucssa_pickup\mail_service\MailService', 'drivers_application_declined']);
add_action('orders_application_approved', ['nucssa_pickup\mail_service\MailService', 'orders_application_approved']);
add_action('orders_application_declined', ['nucssa_pickup\mail_service\MailService', 'orders_application_declined']);

// Cron Events
Cron::scheduleEvents();
add_action('nucssa_pickup_send_feedback_cron', ['nucssa_pickup\mail_service\MailService', 'request_feedbacks_for_finished_orders']);
