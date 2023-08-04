<?php
add_action( 'wp_enqueue_scripts', 'bt_ultimate_member_enqueue_styles' );
function bt_ultimate_member_enqueue_styles() {
  $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
  $theme = wp_get_theme();

  wp_enqueue_style( 'child-ultimate-member', get_stylesheet_directory_uri().'/dist/ultimate-member.css',
      array( $parenthandle ),
      $theme->get('Version') // this only works if you have Version in the style header
  );

  wp_enqueue_script( 'child-ultimate-member', get_stylesheet_directory_uri().'/dist/ultimate-member.min.js',
      array( 'jquery' ),
      $theme->get('Version') // this only works if you have Version in the style header
  );
} 

add_filter( 'um_account_page_default_tabs_hook', 'bt_account_page_default_tabs', 999, 1 );
function bt_account_page_default_tabs( $tabs ) {
  $tabs = array();
  $tabs[100]['general'] = array(
    'icon'          => 'um-faicon-user',
    'title'         => __( 'Account Details', 'ultimate-member' ),
    'submit_title'  => __( 'Update Account', 'ultimate-member' ),
  );

  /*$tabs[200]['password'] = array(
    'icon'          => 'um-faicon-asterisk',
    'title'         => __( 'Change Password', 'ultimate-member' ),
    'submit_title'  => __( 'Update Password', 'ultimate-member' ),
  );*/

  $tabs[300]['privacy'] = array(
    'icon'          => 'um-faicon-lock',
    'title'         => __( 'Privacy', 'ultimate-member' ),
    'submit_title'  => __( 'Update Privacy', 'ultimate-member' ),
  );

  $tabs[400]['notifications'] = array(
    'icon'          => 'um-faicon-envelope',
    'title'         => __( 'Notifications', 'ultimate-member' ),
    'submit_title'  => __( 'Update Notifications', 'ultimate-member' ),
  );

  //if user cannot delete profile hide delete tab
  if ( um_user( 'can_delete_profile' ) || um_user( 'can_delete_everyone' ) ) {

    $tabs[99999]['delete'] = array(
      'icon'          => 'um-faicon-trash-o',
      'title'         => __( 'Delete Account', 'ultimate-member' ),
      'submit_title'  => __( 'Delete Account', 'ultimate-member' ),
    );

  }

  if ( um_user( 'woo_account_billing' ) && ! UM()->options()->get( 'woo_hide_billing_tab_from_account' ) ) {
    $tabs[210]['billing'] = array(
      'icon'          => 'um-faicon-credit-card',
      'title'         => __( 'Billing Address', 'um-woocommerce' ),
      'submit_title'  => __( 'Save Address', 'um-woocommerce' ),
      'custom'        => true,
    );
  }

  if ( um_user( 'woo_account_shipping' ) && ! UM()->options()->get('woo_hide_shipping_tab_from_account') ) {
    $tabs[230]['shipping'] = array(
      'icon'          => 'um-faicon-truck',
      'title'         => __( 'Address', 'um-woocommerce' ),
      'submit_title'  => __( 'Save Address', 'um-woocommerce' ),
      'custom'        => true,
    );
  }

  if ( um_user( 'woo_account_orders' ) ) {
    $tabs[220]['orders'] = array(
      'icon'          => 'um-faicon-shopping-cart',
      'title'         => __( 'Orders', 'um-woocommerce' ),
      'custom'        => true,
      'show_button'   => false,
    );
  }

  if ( um_user( 'woo_account_subscription' ) && class_exists( 'WC_Subscriptions' ) ) {
    $tabs[240]['subscription'] = array(
      'icon'          => 'um-faicon-book',
      'title'         => __( 'Subscriptions', 'um-woocommerce' ),
      'custom'        => true,
      'show_button'   => false,
    );
  }

  /*if ( um_user( 'woo_account_downloads' ) ) {
    $tabs[250]['downloads'] = array(
      'icon'          => 'um-faicon-download',
      'title'         => __( 'Downloads', 'um-woocommerce' ),
      'custom'        => true,
      'show_button'   => false,
    );
  }*/

  if ( um_user( 'woo_account_payment_methods' ) ) {
    $tabs[260]['payment-methods'] = array(
      'icon'          => 'um-faicon-credit-card',
      'title'         => __( 'Payment methods', 'um-woocommerce' ),
      'custom'        => true,
      'show_button'   => false,
    );
  }

  /**
   * Integration for plugin WooCommerce Memberships
   * @link https://docs.woocommerce.com/document/woocommerce-memberships/ WooCommerce Memberships
   * @since 2019-05-02
   */
  if ( class_exists( 'WC_Memberships' ) ) {
    $tabs[ 235 ][ 'memberships' ] = array(
        'icon'          => 'um-faicon-users',
        'title'         => __( 'Memberships', 'um-woocommerce' ),
        'custom'        => true,
        'show_button'   => false,
    );
  }

  /**
   * Integration for plugin "WCFM - WooCommerce Multivendor Marketplace"
   * @link https://wclovers.com/blog/woocommerce-multivendor-marketplace-wcfm-marketplace/
   * @since 2012-01-14
   */
  if( class_exists('WCFMmp_Frontend') && function_exists('wcfm_is_vendor') && wcfm_is_vendor() ) {
    $dashboard_page_title = __( 'Store Manager', 'wc-multivendor-marketplace' );
    $pages = get_option("wcfm_page_options");
    if( !empty($pages['wc_frontend_manager_page_id']) ) {
      $dashboard_page_title = get_the_title( $pages['wc_frontend_manager_page_id'] );
    }

    $tabs[ 10 ][ 'store-manager' ] = array(
        'icon'          => 'um-faicon-star-o',
        'title'         => __( $dashboard_page_title, 'wc-multivendor-marketplace' ),
        'custom'        => true,
        'show_button'   => false,
    );
  }
  if( class_exists('WCFMu_Vendor_Followers') ) {

    $tabs[ 280 ][ 'followings' ] = array(
        'icon'          => 'um-faicon-star-o',
        'title'         => __( 'Followings', 'wc-frontend-manager-ultimate' ),
        'custom'        => true,
        'show_button'   => false,
    );
  }
  if( class_exists('WCFM_Enquiry') ) {

    $tabs[ 280 ][ 'inquiry' ] = array(
        'icon'          => 'um-faicon-star-o',
        'title'         => __( 'Inquiries', 'wc-frontend-manager' ),
        'custom'        => true,
        'show_button'   => false,
    );
  }
  if( class_exists('WCFMu_Support') ) {

    $tabs[ 280 ][ 'support-tickets' ] = array(
        'icon'          => 'um-faicon-star-o',
        'title'         => __( 'Support Tickets', 'wc-frontend-manager-ultimate' ),
        'custom'        => true,
        'show_button'   => false,
    );
  }

  return $tabs;
}

add_filter( 'um_account_tab_general_fields', 'bt_account_tab_general_fields', 10, 2 );
function bt_account_tab_general_fields( $args, $shortcode_args ) {
  $args .= ',user_password';

  return $args;
}

/* add new tab called "Welcome" */
add_filter('um_account_page_default_tabs_hook', 'welcome_tab_in_um', 9999 );
function welcome_tab_in_um( $tabs ) {
	$tabs[90]['welcome']['icon'] = 'um-faicon-home';
	$tabs[90]['welcome']['title'] = __( 'Welcome', 'woocommerce-points-and-rewards' );
	$tabs[90]['welcome']['custom'] = true;
  $tabs[90]['welcome']['show_button'] = false;

	$tabs[120]['society_rewards']['icon'] = 'um-faicon-search';
	$tabs[120]['society_rewards']['title'] = __( 'Society Rewards', 'woocommerce-points-and-rewards' );
	$tabs[120]['society_rewards']['custom'] = true;
  $tabs[120]['society_rewards']['show_button'] = false;
	return $tabs;
}

/* make our new tab hookable */

add_action('um_account_tab__welcome', 'um_account_tab__welcome');
function um_account_tab__welcome( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('welcome');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_welcome', 'um_account_content_hook_welcome');
function um_account_content_hook_welcome( $output ){

  global $wc_points_rewards;

  $points_balance = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );
  $points_label   = $wc_points_rewards->get_points_label( $points_balance );

	ob_start();
	?>

	<div class="um-welcome-content">

    <?php echo do_shortcode('[block id="welcome"]'); ?>

	</div>

	<?php

	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}

//[woo_price_rewards]
function woo_price_rewards_func($atts){
  global $wc_points_rewards;

  $points_balance = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );
  $points_label   = $wc_points_rewards->get_points_label( $points_balance );

	return '<div class="woo-price-rewards">' . wc_price(WC_Points_Rewards_Manager::calculate_points_value($points_balance)) . '</div>';
}
add_shortcode( 'woo_price_rewards', 'woo_price_rewards_func' );

/* add new tab called "Cashback Points" */
add_filter('um_account_page_default_tabs_hook', 'cashback_points_tab_in_um', 9999 );
function cashback_points_tab_in_um( $tabs ) {
	$tabs[800]['cashback_points']['icon'] = 'um-faicon-dollar';
	$tabs[800]['cashback_points']['title'] = __( 'Cashback Rewards', 'woocommerce-points-and-rewards' );
	$tabs[800]['cashback_points']['custom'] = true;
  $tabs[800]['cashback_points']['show_button'] = false;
	return $tabs;
}

/* make our new tab hookable */

add_action('um_account_tab__cashback_points', 'um_account_tab__cashback_points');
function um_account_tab__cashback_points( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('cashback_points');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_cashback_points', 'um_account_content_hook_cashback_points');
function um_account_content_hook_cashback_points( $output ){
  global $wc_points_rewards;

  $points_balance = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );
  $points_label   = __('Cashback', 'woocommerce-points-and-rewards');//$wc_points_rewards->get_points_label( $points_balance );

  $count        = 1000;//apply_filters( 'wc_points_rewards_my_account_points_events', 5, get_current_user_id() );
  $current_page = empty( $current_page ) ? 1 : absint( $current_page );

  // get a set of points events, ordered newest to oldest
  $args = array(
    'calc_found_rows' => true,
    'orderby' => array(
      'field' => 'date',
      'order' => 'DESC',
    ),
    'per_page' => $count,
    'paged'    => $current_page,
    'user'     => get_current_user_id(),
  );

  $events = WC_Points_Rewards_Points_Log::get_points_log_entries( $args );
  $total_rows = WC_Points_Rewards_Points_Log::$found_rows;

	ob_start();
	?>

	<div class="um-cashback_points-content">

    <div class="um-cashback-message"><?php printf( __( "You have earned %s cashback rewards", 'woocommerce-points-and-rewards' ), wc_price(WC_Points_Rewards_Manager::calculate_points_value($points_balance)) ); ?></div>

    <?php if ( $events ) : ?>
    	<table class="shop_table my_account_points_rewards my_account_orders">
    		<thead>
    			<tr>
    				<th class="points-rewards-event-description"><span class="nobr"><?php _e( 'Event', 'woocommerce-points-and-rewards' ); ?></span></th>
    				<th class="points-rewards-event-date"><span class="nobr"><?php _e( 'Date', 'woocommerce-points-and-rewards' ); ?></span></th>
    				<th class="points-rewards-event-points"><span class="nobr"><?php echo esc_html( $points_label ); ?></span></th>
    			</tr>
    		</thead>
    		<tbody>
    		<?php foreach ( $events as $event ) : ?>
      		<?php if ( $event->points > 0 ) : ?>
      			<tr class="points-event">
      				<td class="points-rewards-event-description">
      					<?php echo str_replace('Points', 'Cashback', $event->description); ?>
      				</td>
      				<td class="points-rewards-event-date">
      					<?php echo '<abbr title="' . esc_attr( $event->date_display ) . '">' . esc_html( $event->date_display_human ) . '</abbr>'; ?>
      				</td>
      				<td class="points-rewards-event-points" width="1%">
      					<?php
                  //echo ( $event->points > 0 ? '+' : '' ) . $event->points;
                  echo ( $event->points > 0 ? '+' : '' ) . wc_price(WC_Points_Rewards_Manager::calculate_points_value($event->points))
                ?>
      				</td>
      			</tr>
      		<?php endif; ?>
    		<?php endforeach; ?>
    		</tbody>
    	</table>

    	<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
    	<?php if ( $current_page != 1 ) : ?>
    		<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'points-and-rewards', $current_page - 1 ) ); ?>"><?php _e( 'Previous', 'woocommerce-points-and-rewards' ); ?></a>
    	<?php endif; ?>

    	<?php if ( $current_page * $count < $total_rows ) : ?>
    		<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'points-and-rewards', $current_page + 1 ) ); ?>"><?php _e( 'Next', 'woocommerce-points-and-rewards' ); ?></a>
    	<?php endif; ?>
    	</div>

    <?php endif; ?>

	</div>

	<?php

	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}

/* add new tab called "Refer A Friend" */
add_filter('um_account_page_default_tabs_hook', 'refer_a_friend_tab_in_um', 9999 );
function refer_a_friend_tab_in_um( $tabs ) {
	$tabs[810]['refer_a_friend']['icon'] = 'um-faicon-refresh';
	$tabs[810]['refer_a_friend']['title'] = __( 'Refer A Friend', 'woocommerce-points-and-rewards' );
	$tabs[810]['refer_a_friend']['custom'] = true;
  $tabs[810]['refer_a_friend']['show_button'] = false;
	return $tabs;
}

/* make our new tab hookable */

add_action('um_account_tab__refer_a_friend', 'um_account_tab__refer_a_friend');
function um_account_tab__refer_a_friend( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('refer_a_friend');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_refer_a_friend', 'um_account_content_hook_refer_a_friend');
function um_account_content_hook_refer_a_friend( $output ){
  $WPGens_RAF_MyAccount = new WPGens_RAF_MyAccount();
  $share_text     = __(get_option( 'gens_raf_myaccount_text' ),'gens-raf');
  $title          = __(get_option( 'gens_raf_twitter_title' ),'gens-raf');
  $twitter_via    = __(get_option( 'gens_raf_twitter_via' ),'gens-raf');
  $email_hide     = get_option( 'gens_raf_email_hide' );

  $referral_code  = get_option( 'gens_raf_referral_codes' );
  $template_path  = WPGens_RAF::get_template_path('myaccount-tab.php');
  $rafLink        = $WPGens_RAF_MyAccount->get_referral_link();
  $raf_id         = $WPGens_RAF_MyAccount->get_referral_id();
  $coupons        = $WPGens_RAF_MyAccount->prepare_coupons();
  $referrer_data  = $WPGens_RAF_MyAccount->prepare_friends();

  if (!is_readable($template_path)) {
      return sprintf('<!-- Could not read "%s" file -->', $template_path);
  }

	ob_start();
	?>

	<div class="um-raf-content">

		<?php include $template_path; ?>

	</div>

	<?php

	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}

/* add new tab called "Review" */
add_filter('um_account_page_default_tabs_hook', 'review_tab_in_um', 9999 );
function review_tab_in_um( $tabs ) {
	$tabs[820]['review']['icon'] = 'um-faicon-star-o';
	$tabs[820]['review']['title'] = __( 'Review', 'woocommerce-points-and-rewards' );
	$tabs[820]['review']['custom'] = true;
  $tabs[820]['review']['show_button'] = false;
	return $tabs;
}

/* make our new tab hookable */

add_action('um_account_tab__review', 'um_account_tab__review');
function um_account_tab__review( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('review');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_review', 'um_account_content_hook_review');
function um_account_content_hook_review( $output ){

	ob_start();
	?>

	<div class="um-review-content">

		<?php echo do_shortcode('[block id="review"]'); ?>

	</div>

	<?php

	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}
/* make our society_rewards tab hookable */

add_action('um_account_tab__society_rewards', 'um_account_tab__society_rewards');
function um_account_tab__society_rewards( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('society_rewards');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_society_rewards', 'um_account_content_hook_society_rewards');
function um_account_content_hook_society_rewards( $output ){
    global $wc_points_rewards;

    $points_balance = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );
    $points_label   = $wc_points_rewards->get_points_label( $points_balance );

  	ob_start();
  	?>

  	<div class="um-welcome-content">

      <?php echo do_shortcode('[block id="society-rewards"]'); ?>
      <?php echo do_shortcode('[um_account_rewards]') ?>
  	</div>

  	<?php

  	$output .= ob_get_contents();
  	ob_end_clean();
  	return $output;
}
// rewards shortcode
add_shortcode('um_account_rewards', 'um_account_content_hook_rewards');
function um_account_content_hook_rewards( $atts ){
  $enable_reward = get_field('enable_reward','option');
  if(!$enable_reward) return;
  $atts = shortcode_atts( array(
      'title' => 'Rewards'
  ), $atts, 'um_account_rewards' );

  $output = ''; 
  
	ob_start();
  $user_id = get_current_user_id();
  $_user_rewards = (int)get_user_meta( $user_id, '_user_rewards', true );
  get_template_part( 'templates/products-rewards', null, array('_user_rewards' => $_user_rewards) );
	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}
// get number of rewards able
function bt_get_remain_rewards($_user_rewards){
  $count = 0;
  for($i = 1; $i <= $_user_rewards; $i++){
    $reward = get_field('reward_'.$i,'option');
    if($reward['rw_product']){
      $product = wc_get_product($reward['rw_product']);
      $product_id = $product->get_parent_id() ? $product->get_parent_id(): $product->get_id();
      if(!bt_matched_cart_items($product_id)){
        $count++;
      }
    }
  }
  return $count;
}
// get reward of user
function bt_get_user_rewards($user_id = false){
  if(!$user_id) $user_id = get_current_user_id();
  $user_rewards = (int)get_user_meta( $user_id, '_user_rewards', true );
  return $user_rewards;
}
// set reward of user
function bt_set_user_rewards($user_id = false, $user_rewards){
  if(!$user_id) $user_id = get_current_user_id();
  update_user_meta( $user_id, '_user_rewards', $user_rewards );
}
