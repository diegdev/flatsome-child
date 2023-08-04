<?php
$enable_email_rotation = get_field('enable_email_rotation','option');
if(!$enable_email_rotation) return;
// Reset In Rotation
function bt_reset_inrotation($email_settings){
  if($email_settings){
    foreach($email_settings as $k => $email_setting){
      $email_settings[$k]['in_rotation'] = false;
    }
  }
  return $email_settings;
}
// Item Email
function bt_item_email($email,$amount,$blance,$in_rotation = array()){
  $email_item = array(
    'email' => $email,
    'amount' => $amount,
    'blance' => $blance,
    'in_rotation' => $in_rotation,
  );
  return $email_item;
}

// get default data for payment initial

function bt_get_default_payment_data($listEmail, $email = '') {
  $default_data = [
    'user_has_email' => false,
    'email_in_rotation' => null,
    'default_payment_email' => null,
  ];
  if($email){
    $default_data['user_has_email'] = true;
    $default_data['default_payment_email'] = $email;
  }

  if(!$listEmail || count($listEmail) == 0) return $default_data;

  /**
   * return email active
   */
  foreach($listEmail as $_index => $item) {
    if($item['in_rotation'] == true) {
      $default_data['email_in_rotation'] =  $item['email'];
      if(!$email) $default_data['default_payment_email'] = $item['email'];
      return $default_data;
    }
  }

  /**
   * return first email
   */
  $default_data['default_payment_email'] = $listEmail[0]['email'];
  return $default_data;
}

function bt_get_email_payment($email_settings){
  foreach($email_settings as $k => $email_setting){
    $_email = $email_setting['email'];
    $_amount = (int)$email_setting['amount'];
    $_blance = (int)$email_setting['blance'];
    // check if blance still not over
    if($_amount >= $_blance){
      return $_email;
    }
  }
  /**
   * return first email
   */
  return $email_settings[0]['email'];
}
 
function bt_temp_update_email_settings($email_settings, $payment_email_active, $order_total, $need_update_in_rotation){ 
  foreach($email_settings as $k => $email_setting){
    $_email = $email_setting['email'];
    $_blance = (int)$email_setting['blance'];
    if($payment_email_active == $_email){
      $email_settings[$k]['blance'] = $_blance + $order_total; 
      if($need_update_in_rotation) $email_settings[$k]['in_rotation'] = 'yes';
      break;
    }
  }
  return $email_settings;
}

// udpate value setting emails
function bt_update_email_settings($email_settings, $user_payment_email, $order_total, $order){
  if(!$email_settings || count($email_settings) == 0) return;
  $need_update_in_rotation = false;
  /**
   * Update 
   * https://green-society.monday.com/boards/1418964603/views/41399451/pulses/4300946182?term=DXP
   */
  $default_payment_data = bt_get_default_payment_data($email_settings,$user_payment_email);
  $default_payment_email = $default_payment_data['default_payment_email'];
  $clone_email_settings = $email_settings;
  foreach($email_settings as $k => $email_setting){
    unset($clone_email_settings[$k]);
    $clone_email_settings[] = $email_setting;
    $_email = $email_setting['email'];
    $_amount = (int)$email_setting['amount'];
    $_blance = (int)$email_setting['blance'];
    if($default_payment_email == $_email){
      if($default_payment_data['user_has_email']){
        $payment_email_active = $_email;
      }else{
        $need_update_in_rotation = true;
        // check over blance
        if($_amount < $_blance){
          // reset blance of current email
          $email_settings[$k]['blance'] = 0;
          $email_settings[$k]['in_rotation'] = false;
          if($_amount < $_blance && !$default_payment_data['email_in_rotation']){
            // force update to the first email
            $payment_email_active = $email_settings[0]['email'];
          }else{
            // check and get the fit email payment
            $payment_email_active = bt_get_email_payment($clone_email_settings);
          }
        }else{
          $payment_email_active = $_email;
        }
      }
      // set payment email for order
      $order->__user_payment_email =  $payment_email_active;
    }
  }
  // get new email settings
  $email_settings = bt_temp_update_email_settings($email_settings, $payment_email_active, $order_total, $need_update_in_rotation);
  // run API
  if(substr($payment_email_active, 0, 2) == 'gr' || substr($payment_email_active, 0, 2) == 'gm' ){
    btCreateETransferTransaction($order);
  }

  $data_result = array(
    'payment_email_active' => $payment_email_active,
    'email_settings' => $email_settings
  );

  return $data_result;
}
// set payment email
add_action( 'woocommerce_new_order', 'bt_handle_payment_email', 20);
add_action('woocommerce_order_status_failed_to_on-hold', 'bt_handle_payment_email');
add_action('woocommerce_order_status_pending_to_on-hold', 'bt_handle_payment_email');
add_action('woocommerce_order_status_pending_to_failed', 'bt_handle_payment_email');
function bt_handle_payment_email($order_id){
  $order = new WC_Order($order_id);
  $user_id = $order->get_user_id();;
  $payment_method = $order->get_payment_method();
  if($payment_method == 'bacs'){
    // email payment current
    // $payment_email_active = get_option( 'payment_email_active' );
    // email meta order
    $payment_email = $order->get_meta('_payment_email');
    $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $gateway = isset( $available_gateways['bacs'] ) ? $available_gateways['bacs'] : false;
    $instructions = $gateway->get_option( 'instructions' );
    $order_total = (int)$order->get_total();
    // email settings data
    $email_settings = get_field('email_settings', 'option');

    if(!$payment_email):

      $user_payment_email = get_user_meta( $user_id, '_payment_email', true );
      $data_email_settings = bt_update_email_settings($email_settings, $user_payment_email, $order_total, $order);
      $payment_email = $data_email_settings['payment_email_active'];
      $new_email_settings = $data_email_settings['email_settings'];
      // add payment email to user
      update_user_meta( $user_id, '_payment_email', $payment_email );
      // add payment email to order
      $order->update_meta_data( '_payment_email', $payment_email );
      update_field( 'email_settings', $new_email_settings, 'option' );

      /**
       * Added this due to metorik not syncing up correctly with woocommerce
       * Updates the last modified time for both user and order.
       */
      wc_set_user_last_update_time( $user_id );
      $order->set_date_modified( time() );

      /*******/

      $log = new WC_Logger();
      $log->log( 'email_settings', 'order_id:'.$order_id.', order_total:'.$order_total.', user_id:'.$user_id.', payment_email:'.$payment_email.', new_email_settings:'.wc_print_r($new_email_settings,true));

      $order->save();

      do_action('woocommerce_order_after_update_email_setting', $order_id, $payment_email);
    endif;

    echo wp_kses_post( wpautop( wptexturize(str_replace('{payment_email}',$payment_email,$instructions))));
  }
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'bt_custom_checkout_field_display_admin_order_meta', 10, 1 );

function bt_custom_checkout_field_display_admin_order_meta($order){
  $payment_email = $order->get_meta('_payment_email');
  echo '<p><strong>'.__('Payment Email').':</strong> ' . $payment_email . '</p>';
}
// remove hook on thankyou page
add_action( 'init', 'bt_remove_bacs_from_thank_you_page', 100 );
function bt_remove_bacs_from_thank_you_page() {

	// Bail, if we don't have WC function
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	// Get all available gateways
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

	// Get the Bacs gateway class
	$gateway = isset( $available_gateways['bacs'] ) ? $available_gateways['bacs'] : false;

	// We won't do anything if the gateway is not available
	if ( false == $gateway ) {
		return;
	}

	// Remove the action, which places the BACS details on the thank you page
	remove_action( 'woocommerce_thankyou_bacs', array( $gateway, 'thankyou_page' ) );
}
// API create transactions
function btCreateETransferTransaction($order){
  
  // staging mode
  $endpoint = 'https://api-stg.directexpay.com/Api/DxpTransactions/ETransferTransaction';
  $SecretKey = 'j3BWHjad5JBG';
  $idAccount = 1017;
  $idMethod = 102;

  // production mode
  $endpoint = 'https://api2.directexpay.com/Api/DxpTransactions/ETransferTransaction';

  /**
   * Update 
   * https://green-society.monday.com/boards/1418964603/views/41399451/pulses/4300946182?term=DXP
   */
  if(isset($order->__user_payment_email) && $order->__user_payment_email == 'gm@dxpemail.com') {
    $SecretKey = 'h7MhP9GeaB6R';
    $idAccount = 1027;
    $idMethod = 113;
  } else {
    $SecretKey = 'GraAxBXbUdlb';
    $idAccount = 1023; 
    $idMethod = 104;
  }

  // var_dump([
  //   $order->__user_payment_email,
  //   $SecretKey, $idAccount, $idMethod
  // ]); die;
  /**
   * End
   */

  $clientClientId = $order->get_user_id();
  $level = 0;
  $numorders = wc_get_customer_order_count( $clientClientId );
  if($numorders >= 3){
    $level = 1;
  }
  $order_number = $order->get_order_number();
  $user = $order->get_user();
  $clientName = $user->first_name . ' ' . $user->last_name;
  $amount = number_format((float)$order->get_total(), 2, '.', '');
  $body_str = '"idAccount":'.$idAccount.',"idMethod":'.$idMethod.',"clientClientId":'.$clientClientId.',"clientTransactionId":"'.$order_number.'","clientName":"'.$clientName.'","clientEmail":"'.$user->user_email.'","amount":'.$amount.',"fee":0.0,"level":'.$level.',"notes":"","userip":"'.$order->get_customer_ip_address().'","currency":"'.get_woocommerce_currency().'"';
  $body_json = '{'.$body_str.'}';
  if(isset($_REQUEST['test'])){
    var_dump($body_json);//die;
  }
  $hash = hash( 'sha256', $body_json.$SecretKey );
  $body_str .= ',"hash":"'.$hash.'"';
  $body_json = '{'.$body_str.'}';
  if(isset($_REQUEST['test'])){
    //var_dump($body_json);die;
  }
  $options = [
      'body'        => $body_json,
      'headers'     => [
          'Content-Type' => 'application/json',
      ]
  ];

  $response = wp_remote_post( $endpoint, $options );
  // if(isset($_REQUEST['test_update_email_settings'])){
  //   var_dump($response);
  // }
  $log = new WC_Logger();
  if( is_wp_error( $response ) ) {
      $log->log( 'API call', 'order_id:'.$order_number.', user_id:'.$clientClientId.', data_request:'.$body_json.', result:'.wc_print_r($response->get_error_message()));
      return false;
  }else{
      $log->log( 'API call', 'order_id:'.$order_number.', user_id:'.$clientClientId.', data_request:'.$body_json.', result:'.wc_print_r($response['body'],true));
      return json_decode( $response['body'] );
  }
}
// add_action('wp_head', 'bt_test_func');
function bt_test_func(){
  if(isset($_REQUEST['test_update_email_settings'])){
    $email_settings = get_field('email_settings', 'option');
    $user_id = 109575; // not user_payment_email
    $order_id = 1752529;
    $order = new WC_Order($order_id);
    $order_total = (int)$order->get_total();
    // var_dump($email_settings);die;
    // foreach($email_settings as $k => $email_setting){   '
    //   $email_settings[$k]['in_rotation'] = false;
    // }
    // update_field( 'email_settings', $email_settings, 'option' );
    $user_id = $order->get_user_id();
    $user_payment_email = get_user_meta( $user_id, '_payment_email', true );
    $user_payment_email = 'test3@gmail.com';
    $order->__user_payment_email = 'vp5353@directexpay.com';
    // var_dump($order->__user_payment_email);die;
    $json = btCreateETransferTransaction($order);die;
    $data_email_settings = bt_update_email_settings($email_settings, $user_payment_email, $order_total, $order);

  }
}

add_action('acfe/fields/button/name=clear', 'bt_acf_button_ajax', 10, 2);
function bt_acf_button_ajax($field, $post_id){

    // retrieve field input value 'my_field'
    $acf = $_POST['acf'];
    $row = $_POST['row'];
    $data_email = $acf['field_61a44768afa1b'][$row];
    $email = $data_email['field_61a44777afa1c'];
    // $email_settings = get_field('email_settings', 'option');
    $users = get_users(array(
        'meta_key' => '_payment_email',
        'meta_value' => $email
    ));
    // Array of WP_User objects.
    foreach ( $users as $user ) {
      delete_user_meta( $user->ID, '_payment_email' );
      /**
       * Added this due to metorik not syncing up correctly with woocommerce
       * Updates the last modified time for both user and order.
       */
      wc_set_user_last_update_time( $user->ID );
      /*******/
    }
    $orders = wc_get_orders( array(
        'orderby'   => 'date',
        'order'     => 'DESC',
        'posts_per_page'   => -1,
        'meta_key' => '_payment_email',
        'meta_value' => $email
    ));
    // Array of Order objects.
    foreach ( $orders as $order ) {
      $order->delete_meta_data( '_payment_email' );
      /**
       * Added this due to metorik not syncing up correctly with woocommerce
       * Updates the last modified time for both user and order.
       */
      $order->set_date_modified( time() );
      /*******/
      $order->save();
    }
    // send json success message
    wp_send_json_success($email);

}
// reset daily

add_action( 'wp', 'bt_activation' );
add_action( 'bt_daily_event', 'bt_do_this_daily' );

function bt_activation () {
  // wp_clear_scheduled_hook('bt_daily_event');die;
  $time = strtotime( '00:00 tomorrow' );
  $local_time  = current_datetime();
  $current_time = $local_time->getTimestamp() + $local_time->getOffset();
    if ( !wp_next_scheduled( 'bt_daily_event' ) ) {
        wp_schedule_event( $time-$current_time, 'daily', 'bt_daily_event' );
    }
}
 
function bt_do_this_daily() {
  $email_settings = get_field('email_settings', 'option');
  foreach($email_settings as $k => $email_setting){
    $email_settings[$k]['blance'] = 0;
  }
  update_field( 'email_settings', $email_settings, 'option' );
  $log = new WC_Logger();
  $log->log( 'bt_daily_event', 'new_email_settings:'.wc_print_r($email_settings,true));
}


// filter
/*** Sort and Filter Users ***/
add_action('restrict_manage_users', 'bt_filter_by_email_payment');

function bt_filter_by_email_payment($which)
{
 // template for filtering
 $st = '<select name="payment_email_%s" style="float:none;margin-left:10px;">
    <option value="">%s</option>%s</select>';

 // generate options
 $options = '';
 $email_settings = get_field('email_settings', 'option');
 $top = $_GET['payment_email_top'] ? $_GET['payment_email_top'] : null;
 $bottom = $_GET['payment_email_bottom'] ? $_GET['payment_email_bottom'] : null;
 if (!empty($top) OR !empty($bottom)){
   $section = !empty($top) ? $top : $bottom;
 }
 foreach($email_settings as $k => $email_setting){
   $email = $email_setting['email'];
   $amount = (int)$email_setting['amount'];
   $blance = (int)$email_setting['blance'];
   $selected = $section == $email ? 'selected' : '';
   $options .= '<option '.$selected.' value="'.$email.'">'.$email.'</option>';
 }

 // combine template and options
 $select = sprintf( $st, $which, __( 'Select email...' ), $options );

 // output <select> and submit button
 echo $select;
 submit_button(__( 'Filter' ), null, $which, false);
}

add_filter('pre_get_users', 'bt_filter_users_by_email_payment_section');

function bt_filter_users_by_email_payment_section($query)
{
 global $pagenow;
 if (is_admin() && 'users.php' == $pagenow) {
  // figure out which button was clicked. The $which in filter_by_job_role()
  $top = $_GET['payment_email_top'] ? $_GET['payment_email_top'] : null;
  $bottom = $_GET['payment_email_bottom'] ? $_GET['payment_email_bottom'] : null;
  if (!empty($top) OR !empty($bottom))
  {
   $section = !empty($top) ? $top : $bottom;
   // change the meta query based on which option was chosen
   $meta_query = array (array (
      'key' => '_payment_email',
      'value' => $section,
      'compare' => 'LIKE'
   ));
   $query->set('meta_query', $meta_query);
  }
 }
}

/**
 * Add a custom action to order actions select box on edit order page
 * Only added for paid orders that haven't fired this action yet
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
function bt_wc_add_order_meta_box_action( $actions ) {
	$actions['wc_call_emt_api'] = __( 'Call EMT API', 'my-textdomain' );
	$actions['wc_update_dash_etran'] = __( 'Call EMT API #2', 'my-textdomain' );
	return $actions;
}
add_action( 'woocommerce_order_actions', 'bt_wc_add_order_meta_box_action' );


/**
 * Add an order note when custom action is clicked
 * Add a flag on the order to show it's been run
 *
 * @param \WC_Order $order
 */
function bt_wc_process_order_meta_box_action( $order ) {
  btCreateETransferTransaction($order);
	// add the order note
	// $message = __( 'Called EMT API.', 'bt' );
	// $order->add_order_note( $message );
}
add_action( 'woocommerce_order_action_wc_call_emt_api', 'bt_wc_process_order_meta_box_action' );
// API update transactions
function btUpadteETransferTransaction($order){
    $log = new WC_Logger();
  // staging mode
  $endpoint = 'https://api-stg.directexpay.com/Api/DxpTransactions/ETransferTransaction';
  $SecretKey = 'h7MhP9GeaB6R';
  $idAccount = 1027;
  $idMethod = 113;
  // production mode
  $endpoint = 'https://api2.directexpay.com/Api/DxpTransactions/ETransferTransaction';
  $SecretKey = 'h7MhP9GeaB6R';
  $idAccount = 1027;
  $idMethod = 113;
  $clientClientId = $order->get_user_id();;
  $level = 0;
  $numorders = wc_get_customer_order_count( $clientClientId );
//   if($numorders >= 3){
//     $level = 1;
//   }
  $order_number = $order->get_order_number();
  $user = $order->get_user();
  $clientName = $user->first_name . ' ' . $user->last_name;
  $amount = number_format((float)$order->get_total(), 2, '.', '');
  $body_str = '"idAccount":'.$idAccount.',"idMethod":'.$idMethod.',"clientClientId":'.$clientClientId.',"clientTransactionId":"'.$order_number.'","clientName":"'.$clientName.'","clientEmail":"'.$user->user_email.'","amount":'.$amount.',"fee":0.0,"level":0,"notes":"","userip":"'.$order->get_customer_ip_address().'","currency":"'.get_woocommerce_currency().'"';
  $body_json = '{'.$body_str.'}';

  $hash = hash( 'sha256', $body_json.$SecretKey );
  $body_str .= ',"hash":"'.$hash.'"';
  $body_json = '{'.$body_str.'}';
  $options = [
      'body'        => $body_json,
      'headers'     => [
          'Content-Type' => 'application/json',
      ]
  ];

  $response = wp_remote_post( $endpoint, $options );
  //$log = new WC_Logger();
  $log->log( 'API Update call', 'order_id:'.$order_number.', user_id:'.$clientClientId.', data_request:'.$body_json.', result:'.wc_print_r($response['body'],true), array( 'source' => 'amw-updatedashetran-log' ));

  return json_decode( $response['body'] );
}
function bt_wc_process_order_meta_box_update_dash_etran_action( $order ) {
  btUpadteETransferTransaction($order);
}
add_action( 'woocommerce_order_action_wc_update_dash_etran', 'bt_wc_process_order_meta_box_update_dash_etran_action' ); 
