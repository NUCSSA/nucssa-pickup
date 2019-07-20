<?php
// use function nucssa_core\utils\debug\file_log;
// use nucssa_pickup\mail_service\MailService;
// add_action('plugins_loaded', function() {
// });

add_action('admin_menu', ['nucssa_pickup\admin_dashboard\menu_page\AdminMenu', 'init']);
add_action('admin_enqueue_scripts', ['nucssa_pickup\AdminScripts', 'init']);
add_action('rest_api_init', ['nucssa_pickup\RESTful', 'init']);
register_activation_hook(NUCSSA_PICKUP_PLUGIN_FNAME, ['nucssa_pickup\Activation', 'init']);
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
add_action('pickup_user_created', ['nucssa_pickup\mail_service\MailService', 'pickup_user_created'], 10, 2);
add_action('pickup_order_created', ['nucssa_pickup\mail_service\MailService', 'pickup_order_created'], 10, 2);
add_action('new_driver_application_submitted', ['nucssa_pickup\mail_service\MailService', 'new_driver_application_submitted']);
