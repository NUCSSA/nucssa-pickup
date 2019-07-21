<div class="row">
  <div class="col s12">
    <ul class="tabs">
      <li class="tab col s2"><a href="#1">登录</a></li>
      <li class="tab col s2"><a href="#2" <?php if (!empty($_SESSION['register-error'])) echo 'class="active"'; ?> >注册</a></li>
    </ul>
  </div>
  <div id="1" class="col s12">
    <div id="message">
      <?php if (!empty($_SESSION['login-error'])) { ?>
        <div class="card-panel red lighten-3 white-text center-align"><?php echo $_SESSION['login-error']; ?></div>
        <?php unset($_SESSION['login-error']); ?>
      <?php } ?>
    </div>
    <form class="login col s12" method="post">
      <input type="hidden" name="form-for" value="login">
      <div class="row">
        <div class="input-field col s12">
          <i class="material-icons prefix">email</i>
          <input type="email" id="login-email" name="user[email]" class="validate" required />
          <label for="login-email">Email</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <i class="material-icons prefix">lock</i>
          <input type="password" id="login-password" name="user[password]" class="validate" required />
          <label for="login-password">Password</label>
        </div>
      </div>
      <div class="row center">
        <button class="btn btn-large waves-effect waves-light" type="submit">
          Log in <i class="material-icons right">send</i>
        </button>
      </div>
    </form>
  </div>
  <div id="2" class="col s12">
    <form class="register col s12" method="post">
      <div id="message">
        <?php if (!empty($_SESSION['register-error'])) { ?>
          <div class="card-panel red lighten-3 white-text center-align"><?php echo $_SESSION['register-error']; ?></div>
          <?php unset($_SESSION['register-error']); ?>
        <?php } ?>
      </div>
      <input type="hidden" name="form-for" value="register">
      <div class="row">
        <div class="input-field col s12">
          <i class="icon icon-wechat material-icons prefix"></i>
          <input type="text" id="register-wechat" name="user[wechat]" class="validate" required />
          <label for="register-wechat">WeChat</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <i class="material-icons prefix">account_circle</i>
          <input type="text" id="register-name" name="user[name]" class="validate" required>
          <label for="register-name">姓名</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <i class="material-icons prefix">email</i>
          <input type="email" id="register-email" name="user[email]" class="validate" required />
          <label for="register-email">Email</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s6">
          <i class="material-icons prefix">phone</i>
          <input type="number" id="register-phone" name="user[phone]" />
          <label for="register-phone">手机号(Optional)</label>
        </div>
        <div class="input-field col s6">
          <input type="text" id="register-carrier" name="user[carrier]" />
          <label for="register-carrier">手机运营商(Optional)</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s6">
          <i class="material-icons prefix">lock</i>
          <input type="password" id="register-password" name="user[password]" class="validate" required />
          <label for="register-password">Password</label>
        </div>
        <div class="input-field col s6">
          <input type="password" id="register-password2" name="user[password2]" class="validate" required />
          <label for="register-password2">Enter Password Again</label>
        </div>
      </div>
      <div class="row center">
        <button class="btn btn-large blue waves-effect waves-light" type="submit">
          Submit <i class="material-icons right">send</i>
        </button>
      </div>
    </form>
  </div>
</div>