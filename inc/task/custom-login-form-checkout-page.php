<?php 
/**
 * Custom login form checkout page 
 * https://green-society.monday.com/boards/1418964603/views/41399451/pulses/3275366906
 */

/**
 * Style inline
 */
add_action('wp_head', function() {
  ?>
  <style>
    /** Hidden toggle login form default */
    .woocommerce-form-login-toggle { display: none; }
    .form-row.form-row-wide.create-account.woocommerce-validated { display: none; }

    .green-user-login-form-container,
    .green-user-login-form-container * { font-family: 'Arial'; }
    .green-form-title {
      font-size: 1.5em;
      font-weight: bold;
    }
    .green-user-login-form-container a {
      text-decoration: underline;
    }
    .green-form {
      margin-bottom: 2em;
      padding: 1.5em;
      border: solid 1px #eee;
      background: #fafafa;
      border-radius: 2px;
    }
    .green-form input[type=text],
    .green-form input[type=email],
    .green-form input[type=password] {
      padding: 10px !important;
      border-color: #e0e0e0 !important;
      height: auto !important;
      min-height: auto !important;
      margin-bottom: .5em !important;
      border-radius: 2px !important;
    }
    .green-form .btn-submit {
      background-color: black !important;
      font-weight: bold !important;
      font-size: 13px !important;
      text-transform: initial;
      letter-spacing: 0;
      padding: 13px 20px !important;
      height: auto;
      min-height: auto;
      border-radius: 2px !important;
      /* margin-top: 1em; */
      margin: 0!important;
      background-size: 20px !important;
      background-position: 5px center !important;
      transition: .3s ease;
      -webkit-transition: .3s ease;
    }
    .green-form .btn-submit:hover {
      opacity: .7;
    }
    .green-form .btn-submit.__loading {
      padding-left: 30px !important;
      background: url(<?php echo get_stylesheet_directory_uri() . '/images/loading-animate.svg' ?>) no-repeat 5px center, black;
      opacity: .5;
      pointer-events: none;
    }
    #green-register-form {
      display: flex;
      flex-wrap: wrap;
    }
    #green-register-form input[name=fname],
    #green-register-form input[name=lname] {
      width: calc(50% - 0.25em);
    }
    #green-register-form input[name=fname] {
      margin-right: 0.5em;
    }
    .error-log-wrap {
      border: solid 1px #ffd5d5;
      padding: 1em;
      margin-bottom: 1em;
      background: #fff8f8;
      border-radius: 2px;
      color: red;
    }
    .success-log-wrap {
      border: solid 1px #046738;
      padding: 1em;
      margin-bottom: 1em;
      background: #f7fffb;
      border-radius: 2px;
      color: #046738;
    }

    #green-user-register { display: none; }
    body.woocommerce-checkout .select2.select2-container .select2-selection {
      height: auto;
    }
    body.woocommerce-checkout .wfacp_main_form .select2-container .select2-selection--single .select2-selection__rendered {
      border-width: 0 !important;
      border-color: transparent !important;
      background: none !important;
    }
    body.woocommerce-checkout label.wfacp-form-control-label {
      /* bottom: 10px !important; */
    }

    /**
     * Customer CSS
     */
    .green-form {
      border-radius: 4px;
      box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
    }

    #green-login-form button.btn-submit {
      background-color: #046a39!important;
      border-radius:4px!important;
      box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08);
    }

    .__user-toggle-form {
      color: #046a39 !important;
      text-decoration: none !important;
    }

    .__user-toggle-form:hover {
      opacity: 0.5;
    }

    #green-login-form .green-login-form-button-container{
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    #green-login-form .green-login-form-button-container .btn-submit,
    #green-login-form .green-login-form-button-container .__checkout-google-login{
      width: 48%;
    }

    @media all and (max-width: 520px){
      #green-login-form .green-login-form-button-container{
        flex-direction: column;
      }

      #green-login-form .green-login-form-button-container .btn-submit,
      #green-login-form .green-login-form-button-container .__checkout-google-login{
        width: 100%!important;
        margin-top: 1rem!important;
      }
    }

    /**
     * END Customer CSS
     */

    #green-register-form button.btn-submit {
      background-color: #046a39!important;
      border-radius: 4px!important;
      box-shadow: 0 4px 6px rgb(50 50 93 / 11%), 0 1px 3px rgb(0 0 0 / 8%);
    }

    .green-register-form-button-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      width: 100%;
    }

    .green-register-form-button-container > * {
      width: 48%;
    }

    @media(max-width: 520px) {
      #green-register-form button.btn-submit,
      #green-register-form .__checkout-google-login {
        width: 100% !important;
        margin-top: 1rem !important;
      }
    }
  </style>
  <?php
});

function green_user_login_form() {
  ob_start();
  ?>
  <div class="green-user-login-form-container">
    <?php do_action('green/before_login_form'); ?>
    <div id="green-user-login">
      <h4 class="green-form-title"><?php _e('Login', 'gree') ?></h4>
      <p><?php echo sprintf(
        '%s <a href="javascript:" class="__user-toggle-form" data-form-open="register-form">%s</a>', 
        __('Don\'t have an account?', 'green'),  
        __('Register in less than 1 minute.', 'green')) ?></p>
      <div class="__message-log"></div>
      <form id="green-login-form" class="green-form" method="POST">
        <input type="text" name="username" placeholder="<?php _e('E-mail Address', 'green') ?>" required>
        <input type="password" name="password" placeholder="<?php _e('Password', 'green') ?>" required>
        <div class="green-login-form-button-container">
          <button type="submit" class="btn-submit"><?php _e('Log in', 'green') ?></button>
          <?php do_action('green/jy_after_login_form'); ?>
        </div>
      </form>
    </div> <!-- #green-user-login -->

    <div id="green-user-register">
      <h4 class="green-form-title"><?php _e('Register', 'green') ?></h4>
      <p><?php echo sprintf(
        '%s <a href="javascript:" class="__user-toggle-form" data-form-open="login-form">%s</a>', 
        __('Already have an account with us?', 'green'),
        __('Log in for a quicker checkout experience.', 'green')) ?></p>
      <div class="__message-log"></div>
      <form id="green-register-form" class="green-form" method="POST">
        <input type="text" name="username" placeholder="<?php _e('Username', 'green') ?>" required>
        <input type="text" name="fname" placeholder="<?php _e('First Name', 'green') ?>" required>
        <input type="text" name="lname" placeholder="<?php _e('Last Name', 'green') ?>" required>
        <input type="email" name="email" placeholder="<?php _e('E-mail', 'green') ?>" required>
        <input type="password" name="password" placeholder="<?php _e('Password', 'green') ?>" required>
        <div class="green-register-form-button-container">
          <button type="submit" class="btn-submit"><?php _e('Register', 'green') ?></button>
          <?php do_action('green/jy_after_register_form'); ?>
        </div>
      </form>
    </div> <!-- #green-user-register -->
    <?php do_action('green/after_login_form'); ?>
  </div> <!-- .green-user-login-form-container -->
  <?php
  return ob_get_clean();
}

add_action('wp_footer', function() {
  if(is_checkout() && !is_user_logged_in()) {
    ?>
    <script src="/wp-content/plugins/um-social-login/assets/js/um-social-connect.min.js"></script>
    <?php
  }
  ?>
  
  <script>
  ((w, $) => {
    'use strict';
    const loginForm_Html = `<?php echo green_user_login_form(); ?>`;
    const ajaxUrl = `<?php echo admin_url('admin-ajax.php'); ?>`;

    const __request = async (data) => {
      return await $.ajax({
        type: 'POST',
        url: ajaxUrl,
        data,
        error: e => console.log(e)
      });
    }

    const loginHandle = () => {
      const loginForm = $('form#green-login-form');
      const btnSubmit = loginForm.find('button[type=submit]');
      const messageLog = $('#green-user-login .__message-log');

      const login = async (data) => {
        return await __request({
          ...data,
          action: 'greenUserLoginHandle'
        });
      }

      loginForm.on('submit', async function(e) {
        e.preventDefault();
        let dataForm = $(this).serializeArray().reduce((previousValue, currentValue) => {
          previousValue[currentValue.name] = currentValue.value;
          return previousValue;
        }, {});

        btnSubmit.addClass('__loading');

        const { success, message } = await login(dataForm);

        btnSubmit.removeClass('__loading');
        
        if(success == true) {
          messageLog.html(`<div class="success-log-wrap">${ message }</div>`);
          setTimeout(() => {
            w.location.reload();
          }, 1500);
        } else {
          messageLog.html(`<div class="error-log-wrap">${ message }</div>`);
        }
      })
    }

    const userRegisterHandle = () => {
      const userRegisterForm = $('form#green-register-form');
      const btnSubmit = userRegisterForm.find('button[type=submit]');
      const messageLog = $('#green-user-register .__message-log');

      const userRegister = async (data) => {
        return await __request({
          ...data,
          action: 'greenUserRegisterHandle'
        });
      }

      userRegisterForm.on('submit', async function(e) {
        e.preventDefault();
        let dataForm = $(this).serializeArray().reduce((previousValue, currentValue) => {
          previousValue[currentValue.name] = currentValue.value;
          return previousValue;
        }, {});

        btnSubmit.addClass('__loading');

        const { success, message } = await userRegister(dataForm);
        // console.log(success, message);

        btnSubmit.removeClass('__loading');

        if(success == true) {
          messageLog.html(`<div class="success-log-wrap">${ message }</div>`);
          setTimeout(() => {
            w.location.reload();
          }, 1500);
        } else {
          messageLog.html(`<div class="error-log-wrap">${ message }</div>`);
        }
      })
    }

    const switchForm = () => {
      const loginForm = $('#green-user-login');
      const registerForm = $('#green-user-register');

      $('a.__user-toggle-form').on('click', function(e) {
        e.preventDefault();
        const formName = $(this).data('form-open');

        if(formName == 'login-form') {
          loginForm.slideDown();
          registerForm.slideUp();
        } else {
          loginForm.slideUp();
          registerForm.slideDown();
        }
      })
    }

    const pushUserLoginFormToDOM = () => {
      $('.wfacp-login-wrapper').after(loginForm_Html);
    }

    $(() => {
      pushUserLoginFormToDOM();
      loginHandle();
      userRegisterHandle();
      switchForm();
    })
  })(window, jQuery)
  </script>
  <?php
});

add_action('wp_ajax_nopriv_greenUserLoginHandle', function() {
  extract($_POST);

  $creds = array(
		'user_login'    => $username,
		'user_password' => $password,
		'remember'      => true
	);

	$user = wp_signon($creds, false);

	if (is_wp_error($user)) {
		// echo $user->get_error_message();
    wp_send_json([
      'success' => false,
      'message' => $user->get_error_message(),
    ]);
	}

  wp_send_json([
    'success' => true,
    'message' => __('Login success', 'green'),
  ]);
}, 20);

add_action('wp_ajax_nopriv_greenUserRegisterHandle', function() {
  extract($_POST);

  $user_id = username_exists($username);

  if (!$user_id && false == email_exists($email)) {
    $user_id = wp_create_user($username, $password, $email);

    /**
     * Update first name & last name
     */
    wp_update_user([
      'ID' => $user_id,
      'first_name' => $fname,
      'last_name' => $lname,
      'display_name' => $fname . ' ' . $lname,
    ]);

    $creds = array(
      'user_login'    => $username,
      'user_password' => $password,
      'remember'      => true
    );
  
    $user = wp_signon($creds, false);

    wp_send_json([
      'success' => true,
      'message' => __('Register account <b>success</b>! Thank you.', 'green'),
      'user_id' => $user_id,
    ]);
  } else {
    wp_send_json([
      'success' => false,
      'message' => __('<b>Fail</b>::User already exists. Please try again!', 'green'),
    ]);
  }
}, 20);

add_action('wp_head', function() {
  ?>
  <style>
    .tabbed-content li.tab a h1 {
      font-size: 16px;
      margin: 0;
      padding: 0;
    }

    .user-form-button-wrap {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      margin-top: 10px;
    }

    .login-register-tabs .user-form-button-wrap > * {
      width: 48% !important;
      margin: 0 0 15px 0 !important;
      max-width: none !important;
      min-width: unset !important;
    }

    @media(max-width: 820px) {

      .login-register-tabs .user-form-button-wrap > * {
        width: 100% !important;
      }
    }

    /**
      Jeff Yun Styles
    */
    .page-id-1536857 .page-wrapper,
    .page-id-1536857 .entry-content {
      padding-top: 0 !important;
    }

    .page-id-1536857 .um-field {
      padding: 7px 0 0 0 !important;
    }

    .page-id-1536857 .g-recaptcha {
      margin-bottom: 0px !important;
    }

  </style>
  <?php
});

add_action('wp_footer', function() {
  ?>
  <script>
    ((w, $) => {
      'use strict';
      
      const moveButtonLoginRegisterSocial = () => {
        const isLoginPage = () => {
          return $('.um-page-login.um-page-loggedout').length;
        }

        if(isLoginPage() == 0) return;

        $('.um-button-social.um-button-google').each(function() {
          const $button = $(this);
          const $form = $button.parents('form');

          $form.find('input[type=submit]').parent().addClass('user-form-button-wrap');
          $form.find('input[type=submit]').after($button);
        })
      }

      $(moveButtonLoginRegisterSocial);
    })(window, jQuery)
  </script>
  <?php
});

add_action('green/jy_after_login_form', function() {
  if(! class_exists('UM')) return;
  if(UM()->options()->get('login_show_social') != true) return;
  ?>
  <a href="?oauthWindow=true&amp;provider=google" title="Sign in with Google" data-redirect-url="?oauthWindow=true&amp;provider=google" class="um-button um-alt um-button-social um-button-google __checkout-google-login" onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
    <i class="um-sso-icon-google"></i>
    <span><?php _e('Sign in with Google', 'green') ?></span>
  </a>
  <?php
});

add_action('green/jy_after_register_form', function() {
  if(! class_exists('UM')) return;
  if(UM()->options()->get('register_show_social') != true) return;
  ?>
  <a href="?oauthWindow=true&amp;provider=google" title="Sign in with Google" data-redirect-url="?oauthWindow=true&amp;provider=google" class="um-button um-alt um-button-social um-button-google __checkout-google-login" onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
    <i class="um-sso-icon-google"></i>
    <span><?php _e('Register with Google', 'green') ?></span>
  </a>
  <?php
});