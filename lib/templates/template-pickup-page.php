<?php

// echo \nucssa_pickup\mail_service\MailService::testHTML();
// exit;

include_once('template-utils.php');

use function nucssa_pickup\templates\template_utils\is_user_logged_in;
use function nucssa_core\utils\debug\file_log;
use function nucssa_pickup\templates\template_utils\process_submission_data;
use function nucssa_pickup\templates\template_utils\insert_local_js;
use function nucssa_pickup\templates\template_utils\enableBrowserSyncOnDebugMode;
use function nucssa_pickup\templates\template_utils\handle_json_request;

session_start();

if (isset($_GET['json'])) {
  handle_json_request();
  exit;
}

process_submission_data(); // login and registration form post

// file_log('>>>');
// file_log($_SERVER);
// file_log($_SESSION['user']);
// file_log($_FILES);
// file_log($_POST);
// file_log($_GET);

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
    <h4 class="center-align red-text text-darken-4 card-panel">NUCSSA迎新生接机系统</h4>
  </a>
  <?php
  if (!is_user_logged_in()) {
    include('parts/login-registration-form.php');
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
  <script src="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/js/app.v5.js'; ?>"></script>
<?php } ?>
<?php enableBrowserSyncOnDebugMode(); ?>

</html>