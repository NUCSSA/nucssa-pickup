<?php

namespace nucssa_pickup\admin_dashboard;

use function nucssa_core\utils\debug\file_log;

class Miscellaneous
{
  public static function hidePickupPageFromDashboard($query)
  {
    global $pagenow, $post_type;
    if (is_admin() && $pagenow == 'edit.php' && $post_type == 'page') {
      $query->query_vars['post__not_in'] = [get_option('nucssa_pick_service_page_id')];
    }
  }

  public static function verifyPermalinkSetting()
  {
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) {
    ?>
      <div class="notice notice-error is-dismissible">
        <p>
          Plugin <strong><?php echo NUCSSA_PICKUP_PLUGIN_NAME ?></strong> doesn't work with Default Permalink Settings.
          <br>
          Please update <strong><a href="<?php echo admin_url('options-permalink.php'); ?>">Permalink Settings</a></strong> to something other than <strong>Plain</strong>.
        </p>
      </div>
    <?php
    }
  }

  public static function addPickupPageTemplate($template) {
    if (is_page_template('template-pickup-page.php')){
      $template = NUCSSA_PICKUP_DIR_PATH . 'lib/templates/template-pickup-page.php';
    } else if (is_page_template('template-pickup-feedback-page.php')) {
      $template = NUCSSA_PICKUP_DIR_PATH . 'lib/templates/template-pickup-feedback-page.php';
    }
    return $template;
  }
}
