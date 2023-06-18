<?php

// echo \nucssa_pickup\MailService\MailService::testHTML();
// exit;

include_once('template-pickup-page-utils.php');

use function nucssa_pickup\templates\template_pickup_page_utils\is_user_logged_in;
use function nucssa_pickup\templates\template_pickup_page_utils\process_submission_data;
use function nucssa_pickup\templates\template_pickup_page_utils\insert_local_js;
use function nucssa_pickup\templates\template_pickup_page_utils\enableBrowserSyncOnDebugMode;
use function nucssa_pickup\templates\template_pickup_page_utils\handle_json_request;

session_start();

// Process all sorts of submission data:
// Login/Registration, JSON Request, Password Reset, Logout
process_submission_data();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php wp_site_icon(); ?>
  <link rel="stylesheet" href="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/css/pickup-page.css'; ?>">
  <title><?php echo wp_get_document_title(); ?></title>
</head>

<body>
  <a href="<?php the_permalink(); ?>">
    <h4 class="center-align red-text text-darken-4 card-panel" style="box-shadow:none;border-bottom: solid rgba(0,0,0,0.05) 1px">NUCSSA迎新生接机系统</h4>
  </a>
  <?php
  if (!is_user_logged_in()) {
    if (isset($_GET['auth']) && $_GET['auth'] == 'reset') {
      if (isset($_GET['user'], $_GET['transient'])) {
        // Render Reset Password Resetting Form
        include('parts/reset-password-resetting-form.php');
      } else {
        // Render Reset Password Request Form
        include('parts/reset-password-request-form.php');
      }
    } else {
      include('parts/login-registration-form.php');
    }
  } else {
    insert_local_js();
    echo '<div id="app"></div>';
  }
  ?>
  <footer class="nucssa-footer">
    <div class="brand-title">NUCSSA IT</div>
    <img src="<?php echo NUCSSA_CORE_DIR_URL . '/public/images/logo.png'; ?>" alt="brand-image" class="brand-image">
    <div class="copyright">© <?php echo date('Y'); ?> NUCSSA IT All Rights Reserved</div>
  </footer>
</body>
<script src="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/js/all.js'; ?>"></script>
<?php if (is_user_logged_in()) { ?>
  <script src="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/js/app.js?v1.0.8'; ?>"></script>
<?php } ?>
<?php enableBrowserSyncOnDebugMode(); ?>

</html>