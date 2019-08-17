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
      <form method="post" class="col s12">
        <div class="row">
          <div class="input-field col s12">
            <i class="material-icons prefix">email</i>
            <input type="email" id="login-email" name="user[email]" class="validate" required />
            <label for="login-email">Enter the email you used for login</label>
          </div>
        </div>
        <div class="row center">
          <button class="btn btn-large waves-effect waves-light" type="submit">
            Send Reset Email
          </button>
        </div>
        <div class="row center">
          <a class="btn btn-large yellow darken-3 waves-effect waves-light" href="<?php echo home_url($wp->request); ?>">
            Get Back
          </a>
        </div>
      </form>
    </div>
  </div>
</div>