<?php
include_once('template-pickup-page-utils.php');
include_once('template-pickup-feedback-page-utils.php');

use function nucssa_pickup\templates\template_pickup_page_utils\enableBrowserSyncOnDebugMode;
use function nucssa_pickup\templates\template_pickup_feedback_page_utils\processFeedbackSubmission;

$survey_submitted = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $survey_submitted = true;
  /**
   * Process Survey Submission
   */
  processFeedbackSubmission();
} else {
  $valid_request = true;
  if (!isset($_GET['request'])) $valid_request = false;

  if ($valid_request) {
    ['order_id' => $order_id, 'from_role' => $role] = json_decode(base64_decode(urldecode($_GET['request'])), true);
    if (is_null($order_id) || is_null($role)) {
      $valid_request = false;
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php wp_site_icon(); ?>
  <link rel="stylesheet" href="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/css/feedback-page.css'; ?>">
  <title><?php echo wp_get_document_title(); ?></title>
</head>

<body>
  <a href="<?php the_permalink(get_option('nucssa_pickup_service_page_id')); ?>">
    <h4 class="center-align red-text text-darken-4 card-panel" style="box-shadow:none;border-bottom: solid rgba(0,0,0,0.05) 1px">NUCSSA接机活动 Survey</h4>
  </a>
  <div class="container">
    <?php
    if ($survey_submitted) {
      echo '<h5 class="center-align bold" style="margin-bottom: 50px;">感谢您的反馈!</h5>';
    } else {
      if (!$valid_request) {
        echo '<div class="row">';
        echo '<h5 class="center-align bold">不乖哟~  请使用邮件里提供的survey链接</h5>';
        echo '<h5 class="center-align bold">☝️️️️☝️☝️</h5>';
        echo '</div>';
      } else {
        if ($role == 'driver')
          include_once('parts/survey-for-driver.php');
        else
          include_once('parts/survey-for-passenger.php');
      }
    }
    ?>
  </div>
  <footer class="nucssa-footer">
    <div class="brand-title">NUCSSA IT</div>
    <img src="<?php echo NUCSSA_CORE_DIR_URL . '/public/images/logo.png'; ?>" alt="brand-image" class="brand-image">
    <div class="copyright">© <?php echo date('Y'); ?> NUCSSA IT All Rights Reserved</div>
  </footer>
</body>
<script src="<?php echo NUCSSA_PICKUP_DIR_URL . 'public/js/feedback-page.js'; ?>"></script>

<?php enableBrowserSyncOnDebugMode(); ?>

</html>