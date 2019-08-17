<?php
include_once(NUCSSA_PICKUP_DIR_PATH.'/lib/templates/template-pickup-page-utils.php');
use function nucssa_pickup\templates\template_pickup_page_utils\verify_reset_link;
?>

<?php if (verify_reset_link()) { ?>
  <div class="container">
    <div class="row">
      <div class="col s12 l6 offset-l3">
        <h5 class="center-align red-text text-darken-6">Reset Password</h5>
        <?php if (!empty($_SESSION['reset-message'])) { ?>
          <?php
            [$type, $message] = $_SESSION['reset-message'];
            $color_class = $type == 'error' ? 'red lighten-3' : 'light-blue darken-3';
          ?>
          <div class="card-panel <?php echo $color_class; ?> white-text center-align"><?php echo $message; ?></div>
          <?php unset($_SESSION['reset-message']); ?>
        <?php } ?>

        <form method="post">
          <div class="row">
            <div class="input-field col s12">
              <input type="password" id="register-password" name="password1" class="validate" required />
              <label for="register-password">Password</label>
            </div>
          </div>
          <div class="row">
            <div class="input-field col s12">
              <input type="password" id="register-password2" name="password2" class="validate" required />
              <label for="register-password2">Enter Password Again</label>
            </div>
          </div>
          <div class="row center">
            <button class="btn btn-large blue waves-effect waves-light" type="submit">
              Reset
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php } else { ?>
  <div class="container">
    <div class="center-align red-text text-darken-3">
      链接已失效，请重新申请<a href="<?php echo home_url("pickup?auth=reset"); ?>">重置密码</a>。
    </div>
  </div>
<?php } ?>
