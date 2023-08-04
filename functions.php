<?php
// required
require( get_stylesheet_directory().'/inc/theme-scripts.php' );
require( get_stylesheet_directory().'/inc/common.php' );
require( get_stylesheet_directory().'/inc/rotation-email-payment.php' );
require( get_stylesheet_directory().'/inc/optimize-site.php' );
require( get_stylesheet_directory().'/rest.php' );

{
  /**
   * Task include
   */
  require(get_stylesheet_directory() . '/inc/task/override-template-hooks.php');
  // require(get_stylesheet_directory() . '/inc/task/dynamic-pricing.php');
  require(get_stylesheet_directory() . '/inc/task/custom-login-form-checkout-page.php');
  require(get_stylesheet_directory() . '/inc/task/product-backend-bulk-update.php');
  require(get_stylesheet_directory() . '/inc/task/like-product.php');
  require(get_stylesheet_directory() . '/inc/task/fix-mix-and-match-product.php');
  require(get_stylesheet_directory() . '/inc/task/rotating-daily-deal/index.php');
  require(get_stylesheet_directory() . '/inc/task/optimize/index.php');
  require(get_stylesheet_directory() . '/inc/task/search-suggestion.php');
  require(get_stylesheet_directory() . '/inc/task/free-gifts-feedback.php');
  require(get_stylesheet_directory() . '/inc/task/xero-task.php');
  require(get_stylesheet_directory() . '/inc/task/account-fields.php');
  require(get_stylesheet_directory() . '/inc/task/tips-checkout.php');
  require(get_stylesheet_directory() . '/inc/plugins/plugins-override.php');
  require(get_stylesheet_directory() . '/inc/plugins/mini-cart-qty.php');
  require(get_stylesheet_directory() . '/inc/plugins/free-shipping-label.php');
  // require(get_stylesheet_directory() . '/inc/task/variation-add-to-cart.php');
}

add_filter( 'wp_is_large_user_count', '__return_false' );

/*
 * Ultimate member
 */
if ( class_exists( 'UM_Functions' ) ) {

  require( get_stylesheet_directory().'/ultimate-member/helper.php' );

}
/*
 * WooCommerce
 */
if ( class_exists( 'WooCommerce' ) ) {

  require( get_stylesheet_directory().'/woocommerce/helper.php' );

}


/**
 * Auto applies GS20 coupon code to first orders
 */
require(get_stylesheet_directory() . '/inc/auto-apply-coupon.php');

 /**
  * Suppresses warning messages about deprecated or improper usage of functions
  * TODO: possibly remove
  */
add_filter('doing_it_wrong_trigger_error', '__return_false', 10, 0);

/**
 * Fix display warning
 * 
 * TODO: possibly remove
 */
add_filter('wfacp_remove_theme_js_css_files', 'bt_wfacp_remove_theme_js_css_files', 10, 0);
function bt_wfacp_remove_theme_js_css_files(){
  if ( !is_user_logged_in() ) return false;
  return true;
}


/**
 * Login shortcode
 * 
 * TODO: possibly remove
 */
add_shortcode( 'wc_login_form', 'bt_wc_login_form' );
function bt_wc_login_form($atts) {
   if ( is_admin() ) return;
   if ( is_user_logged_in() ) return;
   $atts = extract(shortcode_atts(array(
        'redirect' => '',
    ), $atts, 'wc_login_form' ));
    ob_start();
    woocommerce_login_form( array( 'redirect' => $redirect ) );
    return ob_get_clean();
}

/**
 * Custom themes options page
 * 
 */
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Theme Custom Option',
		'menu_title'	=> 'Theme Custom Setting',
		'menu_slug' 	=> 'theme-notice-page-checkout-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

}
// hook to init
function bt_init_hooks(){
    // remove cross sell
    remove_action( 'woocommerce_after_cart_table', 'woocommerce_cross_sell_display' );
    // search results
    set_theme_mod('search_result', 0);
    // // add new order bump positions
    new WFOB_Add_New_Position( [
  		'position_id'   => 'wfob_before_mini_cart_order_total',
  		'hook'          => 'wfacp_mini_cart_before_order_total',
  		'position_name' => 'Above Mini Cart Order Total',
  		'hook_priority' => 21
  	] );

  	new WFOB_Add_New_Position( [
  		'position_id'   => 'wfob_after_mini_cart_order_total',
  		'hook'          => 'wfacp_mini_cart_after_order_total',
  		'position_name' => 'Below Mini Cart Order Total',
  		'hook_priority' => 21
  	] );
}
add_action( 'init', 'bt_init_hooks', 20);
// fix name attribule
function bt_fix_attribute_name($name){
    if(!is_admin()){
      $name = str_replace(' Grams','G',$name);
      $name = str_replace(' Gram', 'G', $name);
    }
    return $name;
}
add_filter( 'woocommerce_variation_option_name', 'bt_fix_attribute_name' );
add_filter( 'wvs_variable_item', 'bt_fix_attribute_name' );
// Apply Discount text
function bt_wc_points_rewards_redeem_points_message($mesages){
    $mesages = str_replace('Apply Discount', strip_tags($mesages), $mesages);
    $mesages = str_replace('woocommerce-info','woocommerce--info',$mesages);
    return $mesages;
}
add_filter( 'wc_points_rewards_redeem_points_message', 'bt_wc_points_rewards_redeem_points_message' );
// Point on cart page
function bt_wc_points_rewards_earn_points_message($mesages,$points_earned){
    $mesages = str_replace('{points_earn_value}',wc_price(WC_Points_Rewards_Manager::calculate_points_value($points_earned)),$mesages);
    $mesages = str_replace('woocommerce-info','woocommerce--info',$mesages);
    return $mesages;
}
add_filter( 'wc_points_rewards_earn_points_message', 'bt_wc_points_rewards_earn_points_message', 10, 2 );
// Point on single product
function bt_wc_points_rewards_single_product_message($mesages,$object){
  global $product;
  $points_earned = $object::get_points_earned_for_product_purchase( $product );
  $points_earned = WC_Points_Rewards_Manager::round_the_points( $points_earned );
  $mesages = str_replace('{points_earn_value}',wc_price(WC_Points_Rewards_Manager::calculate_points_value($points_earned)),$mesages);
  return $mesages;
}
add_filter( 'wc_points_rewards_single_product_message', 'bt_wc_points_rewards_single_product_message', 10, 2 );
// remove popup points
function bt_woocommerce_queued_js($js){
    $js = str_replace('var points = prompt','var points = max_points;// var points = prompt',$js);
    return $js;
}
add_filter( 'woocommerce_queued_js', 'bt_woocommerce_queued_js' );
//
function bt_add_price_per_unit($data, $contents, $type, $args, $saved_attribute){
  $handle = $args['attribute'];
  return $data;
}
// add_filter('wvs_variable_items_wrapper', 'bt_add_price_per_unit', 10, 5);
// change position free shipping bar
function bt_modify_actions(){
    global $wp_filter;
    // cart page
    if(isset($wp_filter['woocommerce_proceed_to_checkout'])):
        $woocommerce_proceed_to_checkout = $wp_filter['woocommerce_proceed_to_checkout'];
        if(isset($woocommerce_proceed_to_checkout->callbacks[10]) && $woocommerce_proceed_to_checkout->callbacks[10]){
          foreach($woocommerce_proceed_to_checkout->callbacks[10] as $k=>$action){
            $pos = strpos($k, 'free_shipping_bar');
            if ($pos !== false) {
                unset($wp_filter['woocommerce_proceed_to_checkout']->callbacks[10][$k]);
                if(isset($wp_filter['woocommerce_before_cart_totals'])){
                  $wp_filter['woocommerce_before_cart_totals']->add_filter( 'woocommerce_before_cart_totals', $action['function'], 10, $action['accepted_args'] );
                }else{
                  $hook = new WP_Hook();
                  $hook->add_filter( 'woocommerce_before_cart_totals', $action['function'], 10, $action['accepted_args'] );
                  $wp_filter['woocommerce_before_cart_totals'] = $hook;
                }
            }
          }
        }
    endif;
    // cart mini
    if(isset($wp_filter['woocommerce_widget_shopping_cart_before_buttons'])):
        $woocommerce_widget_shopping_cart_before_buttons = $wp_filter['woocommerce_widget_shopping_cart_before_buttons'];
        if(isset($woocommerce_widget_shopping_cart_before_buttons->callbacks[10]) && $woocommerce_widget_shopping_cart_before_buttons->callbacks[10]){
          foreach($woocommerce_widget_shopping_cart_before_buttons->callbacks[10] as $k=>$action){
              $pos = strpos($k, 'free_shipping_bar');
              if ($pos !== false) {
                  unset($wp_filter['woocommerce_widget_shopping_cart_before_buttons']->callbacks[10][$k]);
                  if(isset($wp_filter['woocommerce_before_mini_cart_contents'])){
                    $wp_filter['woocommerce_before_mini_cart_contents']->add_filter( 'woocommerce_before_mini_cart_contents', $action['function'], 10, $action['accepted_args'] );
                  }else{
                    $hook = new WP_Hook();
                    $hook->add_filter( 'woocommerce_before_mini_cart_contents', $action['function'], 10, $action['accepted_args'] );
                    $wp_filter['woocommerce_before_mini_cart_contents'] = $hook;
                  }
              }
          }
        }
    endif;
    // SHIPPING BAR ON CHECKOUT PAGE
    if(isset($wp_filter['woocommerce_review_order_before_submit'])):
        $woocommerce_review_order_before_submit = $wp_filter['woocommerce_review_order_before_submit'];
        foreach($woocommerce_review_order_before_submit->callbacks[10] as $k=>$action){
            $pos = strpos($k, 'free_shipping_bar');
            if ($pos !== false) {
                unset($wp_filter['woocommerce_review_order_before_submit']->callbacks[10][$k]);
                if(isset($wp_filter['wfacp_before_form'])){
                  $wp_filter['wfacp_before_form']->add_filter( 'wfacp_before_form', $action['function'], 5, $action['accepted_args'] );
                }else{
                  $hook = new WP_Hook();
                  $hook->add_filter( 'wfacp_before_form', $action['function'], 5, $action['accepted_args'] );
                  $wp_filter['wfacp_before_form'] = $hook;
                }
            }
        }
    endif;
    // point
    if(isset($wp_filter['woocommerce_before_checkout_form'])):
        $woocommerce_before_checkout_form = $wp_filter['woocommerce_before_checkout_form'];
        foreach($woocommerce_before_checkout_form->callbacks[6] as $k=>$action){
            $pos = strpos($k, 'render_redeem_points_message');
            if ($pos !== false) {
              unset($wp_filter['woocommerce_before_checkout_form']->callbacks[6][$k]);
              if (!(defined('DOING_AJAX') && DOING_AJAX)) {
                if(isset($wp_filter['wfacp_mini_cart_before_order_total'])){
                  $wp_filter['wfacp_mini_cart_before_order_total']->add_filter( 'wfacp_mini_cart_before_order_total', $action['function'], 11, $action['accepted_args'] );
                }else{
                  $hook = new WP_Hook();
                  $hook->add_filter( 'wfacp_mini_cart_before_order_total', $action['function'], 11, $action['accepted_args'] );
                  $wp_filter['wfacp_mini_cart_before_order_total'] = $hook;
                }
              }
            }
        }
        // foreach($woocommerce_before_checkout_form->callbacks[5] as $k=>$action){
        //     $pos = strpos($k, 'render_earn_points_message');
        //     if ($pos !== false) {
        //         unset($wp_filter['woocommerce_before_checkout_form']->callbacks[5][$k]);
        //     }
        // }
    endif;
    if(isset($wp_filter['woocommerce_before_cart'])):
        $woocommerce_before_cart = $wp_filter['woocommerce_before_cart'];
        foreach($woocommerce_before_cart->callbacks[15] as $k=>$action){
            $pos = strpos($k, 'render_earn_points_message');
            if ($pos !== false) {
                unset($wp_filter['woocommerce_before_cart']->callbacks[15][$k]);
                $wp_filter['woocommerce_proceed_to_checkout']->add_filter( 'woocommerce_proceed_to_checkout', $action['function'], 5, $action['accepted_args'] );
            }
        }
        foreach($woocommerce_before_cart->callbacks[16] as $k=>$action){
            $pos = strpos($k, 'render_redeem_points_message');
            if ($pos !== false) {
                unset($wp_filter['woocommerce_before_cart']->callbacks[16][$k]);
                $wp_filter['woocommerce_cart_actions']->add_filter( 'woocommerce_proceed_to_checkout', $action['function'], 20, $action['accepted_args'] );
            }
        }
    endif;
    if(isset($wp_filter['woocommerce_before_add_to_cart_button'])):
        $woocommerce_before_add_to_cart_button = $wp_filter['woocommerce_before_add_to_cart_button'];
        foreach($woocommerce_before_add_to_cart_button->callbacks[25] as $k=>$action){
            $pos = strpos($k, 'add_variation_message_to_product_summary');
            if ($pos !== false) {
                unset($wp_filter['woocommerce_before_add_to_cart_button']->callbacks[25][$k]);
                if(isset($wp_filter['woocommerce_before_single_variation'])){
                  $wp_filter['woocommerce_before_single_variation']->add_filter( 'woocommerce_before_single_variation', $action['function'], 10, $action['accepted_args'] );
                }else{
                  $hook = new WP_Hook();
                  $hook->add_filter( 'woocommerce_before_single_variation', $action['function'], 10, $action['accepted_args'] );
                  $wp_filter['woocommerce_before_single_variation'] = $hook;
                }
            }
        }
    endif;
}
add_action( 'init', 'bt_modify_actions' );
// display THC in product meta
add_action( 'flatsome_woocommerce_shop_loop_images', 'bt_add_meta_thc_percentages' );
add_action( 'woocommerce_single_product_summary', 'bt_add_meta_thc_percentages',6 );
function bt_add_meta_thc_percentages(){
  global $product;
  $thc = get_field('thc_percentages_product',$product->get_id());
  $cbd = get_field('cbd_level',$product->get_id());
  $psilocybin_Levels = get_field('Psilocybin_Levels',$product->get_id());
  if(!empty($thc) || !empty($cbd) || !empty($psilocybin_Levels)):
    get_template_part( 'templates/extra-meta-product', null, array('thc' => $thc, 'cbd' => $cbd, 'psilocybin_Levels' => $psilocybin_Levels) );
  endif;
}
//this hook will create a new filter on the admin area for the specified post type
add_action( 'restrict_manage_posts', 'tb_restrict_manage_posts');
function tb_restrict_manage_posts(){
  if($_REQUEST['post_type'] == 'shop_order'):
    echo do_shortcode('[scan_barcode]');
  endif;
}
// disable sercutity check
add_filter('woocommerce_order_barcodes_do_nonce_check', '__return_false');
/* custom view order when scan barcode */
function bears_woocommerce_after_account_orders($order_id){
	if(isset($_POST['scan_action'])):
	  $action = esc_attr( $_POST['scan_action'] );
		$order = new WC_Order( $order_id );
		$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );
		$tracking_number = '';
		if($tracking_items){
			$tracking_number = $tracking_items[0]['tracking_number'];
		}
		$status = $order->get_status();
    $args = array(
      'action' => $action,
      'order' => $order,
      'status' => $status,
      'order_id' => $order_id,
      'tracking_number' => $tracking_number
    );
		get_template_part( 'templates/scan-barcode', null, $args );
	endif;
}

/* custom view order when scan barcode */
add_action( 'woocommerce_view_order', 'bears_woocommerce_after_account_orders', 10);


/* scan barcode view complete order */
add_action( 'wp_ajax_completed_order', 'bears_completed_order' );
add_action( 'wp_ajax_nopriv_completed_order', 'bears_completed_order' );
function bears_completed_order(){
	$order_id = $_POST['order_id'];
	$order = new WC_Order( $order_id );
	if ( 'completed' === $order->get_status() ) {
		$response      = __( 'Order already completed', 'woocommerce-order-barcodes' );
		$response_type = 'notice';
	} else {
		$order->update_status( 'completed' );
		$response      = __( 'Completed', 'woocommerce-order-barcodes' );
		$response_type = 'notice';
		$order = new WC_Order( $order_id );

    /**
     * Added this due to metorik not syncing up correctly with woocommerce
     * Updates the last modified time for both user and order.
     */
    $order->set_date_modified( time() );
    $order->save();
    /*******/
	}
	die($response);
}
/* scan barcode view save tracking */
add_action( 'wp_ajax_save_tracking_order', 'bears_save_tracking_order' );
add_action( 'wp_ajax_nopriv_save_tracking_order', 'bears_save_tracking_order' );
function bears_save_tracking_order(){
	$order_id = $_POST['order_id'];
	$tracking_number = $_POST['tracking_number'];
	$order = new WC_Order( $order_id );
	$tracking_items = array();
	$tracking_item = array();
	$tracking_item['custom_tracking_provider'] = 'Canada Post';
	$tracking_item['tracking_id'] = md5( "{$tracking_number}" . microtime() );
	$tracking_item['tracking_number'] = wc_clean( $tracking_number );
	$s = $tracking_item['tracking_number'];
	$tracking_item['custom_tracking_link'] = "http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=$s";
	$tracking_item['date_shipped'] = time();
	$tracking_items[] = $tracking_item;
	$order->update_meta_data( '_wc_shipment_tracking_items', $tracking_items );
	$order->save_meta_data();

  /**
   * Added this due to metorik not syncing up correctly with woocommerce
   * Updates the last modified time for both user and order.
   */
  $order->set_date_modified( time() );
  $order->save();
  /*******/

	die($tracking_number);
}
//
add_filter('wvs_variable_items_wrapper', 'bt_wvs_default_variable_item', 20, 5);
function bt_wvs_default_variable_item($data, $contents, $type, $args, $saved_attribute){
  if(is_product()){
    return $data;
  }
  $term_id = array(31,9859,13080);
  $product = $args['product'];
  $id = $product->get_id();
  if(!has_term( $term_id, 'product_cat', $id )){
    return "";
  }
  return $data;
}
// change position select options
add_filter( 'woocommerce_post_class', 'bt_wvs_pro_wc_product_loop_post_class', 30, 2 );
function bt_wvs_pro_wc_product_loop_post_class( $classes, $product ) {
  $term_id = array(31,9859,13080);
  $id = $product->get_id();
  if( !has_term( $term_id, 'product_cat', $id )){
    $classes[] = 'bt_force_show_select';
  }
  return array_unique( $classes );
}
// custom product loop
add_action('woocommerce_after_shop_loop_item_title', 'bt_custom_product_loop');
function bt_custom_product_loop(){
  global $product;
  $term_id = array(31,9859,13080);
  $id = $product->get_id();
  $show_template_variation_new = get_field('variation_template_new_custom','option');

  if(has_term( $term_id, 'product_cat', $id ) && 'variable' == $product->get_type()):
      $handle = new WC_Product_Variable($id);
      $variations = $handle->get_children();
      $price_per_units = array();
      $prices = array();
      foreach ($variations as $key => $value) {
        $single_variation = new WC_Product_Variation($value);
        $attributes = $single_variation->get_data()['attributes'];
        $first_key = array_key_first($attributes);
        $value = isset($attributes[$first_key]) ? $attributes[$first_key] : '';
        $weight = $single_variation->get_weight();
        if(!$weight) continue;
        $price = $single_variation->get_price();
        $price_per_unit_value = $weight !== 0 ? $price/$weight : 0;
        $price_per_unit = strip_tags(wc_price($price_per_unit_value, array('decimals' => 1)));
        $price_per_units[$value] = $price_per_unit;
        $prices[] = $price_per_unit_value;
      }
      $min_price = $prices ? min($prices) : 0;
      echo "<div data-price_per_unit='".wc_esc_json( wp_json_encode( $price_per_units ) )."' class='bt_from_price'>From <span class='bt_from_price_amount'>". strip_tags(wc_price($min_price, array('decimals' => 1))) ."</span><span class='icon-g'> per gram<span></div>";
  endif;
}
// Override the woocommerce default filter for getting max price for filter widget.
add_filter( 'woocommerce_price_filter_widget_max_amount', 'theme_woocommerce_price_filter_widget_max_amount', 10, 2 );

/**
 * Fix max_price issue in price filter widget.
 *
 * @param int $max_price The price filter form max_price.
 * @return int Max price for the filter.
 */
function theme_woocommerce_price_filter_widget_max_amount( $max_price ) {
	$prices = theme_woocommerce_get_filtered_price();
	$max_price = $prices->max_price;
	return $max_price;
}

/**
 * Gets and returns the min and max prices from database. WooCommerce filter function.
 *
 * @return object Min and Max prices from database.
 */
function theme_woocommerce_get_filtered_price() {
	global $wpdb, $wp_the_query;
	$args       = $wp_the_query->query_vars;
	$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
	$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
	if ( ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
		$tax_query[] = array(
			'taxonomy' => $args['taxonomy'],
			'terms'    => array( $args['term'] ),
			'field'    => 'slug',
		);
	}
	foreach ( $meta_query as $key => $query ) {
		if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
			unset( $meta_query[ $key ] );
		}
	}
	$meta_query = new WP_Meta_Query( $meta_query );
	$tax_query  = new WP_Tax_Query( $tax_query );
	$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
	$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
	$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
	$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
	$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
				AND price_meta.meta_value > '' ";
	$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];
	return $wpdb->get_row( $sql );
}
// add meta order
add_action('woocommerce_new_order', 'bt_enroll_order_meta', 10, 2);
function bt_enroll_order_meta( $order_id, $order ) {
    if ( ! $order_id )
      return;
    $user_id = $order->get_user_id();
    $_user_order_count = get_user_meta( $user_id, '_user_order_count', true );
    $current_count = 0;
    if(!$_user_order_count){
      $_user_order_count = array();
    }else{
      $_user_order_count = (array)json_decode($_user_order_count, true);
      $current_count = (int)end($_user_order_count)['order_count'];
    }
    $_user_order_count[$order_id] = [ 'order_id' => $order_id, 'order_count' => $current_count + 1 ];
    $_user_order_count = json_encode($_user_order_count);
    update_user_meta( $user_id, '_user_order_count', $_user_order_count );

    $int = wc_get_customer_order_count( $user_id );
    $order->update_meta_data( '_customer_order_count', $int );
    $shipping_option = $order->get_items( 'shipping_option' );

    if($shipping_option):
      foreach( $order->get_items( 'shipping_option' ) as $item_id => $shipping_item_obj ){
        $name_shipping = $shipping_item_obj->get_name();

        if($name_shipping == 'Shipping Insurance (Optional): add' || $name_shipping=='Shipping Insurance: add'){
          $cost_shipping_option = $shipping_item_obj->get_total();
          $order->update_meta_data( '_insurance', 'Shipping Insurance Added' );
          $order->update_meta_data( 'checked_add_shipping_option', $cost_shipping_option );
          break;
        }
      }
    endif;

      /**
       * Added this due to metorik not syncing up correctly with woocommerce
       * Updates the last modified time for both user and order.
       */
      wc_set_user_last_update_time( $user_id );
      $order->set_date_modified( time() );

      /*******/
    $order->save();
}



/**Remove additional information tab on single product page */
function remove_additional_info_tab( $tabs ) {
  unset( $tabs['additional_information'] );
  return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'remove_additional_info_tab', 9999 );


/**Remove single product meta */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/**
* Show Categories Again @ Single Product Page - WooCommerce
*/
function show_cats_again_single_product() {

  global $product;

  ?> <div class="product_meta">
    <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>
  </div>   <?php
  }
add_action( 'woocommerce_single_product_summary', 'show_cats_again_single_product', 40 );


/**
 * Add custom breadcrumbs on cart page
 */
function theme_cart_breadcrumbs(){
  get_template_part( 'templates/breadcrumbs-cart' );
}
add_action('woocommerce_before_cart', 'theme_cart_breadcrumbs');
// add breadcrumb
add_action('wfacp_before_content', 'bt_woocommerce_before_checkout_form');
function bt_woocommerce_before_checkout_form(){
  get_template_part( 'templates/breadcrumbs-checkout' );
}

// auto select option
// add_filter('woocommerce_dropdown_variation_attribute_options_args','bt_fun_select_default_option',10,1);
function bt_fun_select_default_option( $args){
    if(is_product()):
      $variations = $args['product']->get_available_variations();
      $attribute = sanitize_title($args['attribute']);
      $visible_option = array();
      if($variations){
        foreach ($variations as $key => $value) {
          $visible_option[] = isset($value['attributes']['attribute_'.$attribute]) ? $value['attributes']['attribute_'.$attribute] : '';
        }
      }
      if(count($args['options']) > 0){
        foreach ($args['options'] as $key => $value) {
          if(in_array($value,$visible_option)){
            $args['selected'] = $value; break;
          }
        }
      }
      remove_filter('woocommerce_dropdown_variation_attribute_options_args','bt_fun_select_default_option',10,1);
    endif;
    return $args;
}
// show email on orders
add_action( 'manage_shop_order_posts_custom_column', 'bt_shop_order_column_barcode', 20, 2 );
function bt_shop_order_column_barcode( $column, $postid ) {
  if ( $column == 'order_number' ) {
    $order = wc_get_order( $postid );
    // Get the Customer billing email
    $billing_email  = $order->get_billing_email();
    echo "<div>".$billing_email."</div>";
  }
}
//
add_filter( 'views_edit-shop_order', 'wpo_wcpdf_add_without_packing_slips_view' );
function wpo_wcpdf_add_without_packing_slips_view( $views ) {
  $views['without_packing_slip'] = '<a href=' . add_query_arg('without_packing_slip','1') . '>Unprinted Packing Slips</a>';;
  return $views;
}

add_filter( 'request', 'wpo_wcpdf_filter_orders_without_packing_slips_request' );
function wpo_wcpdf_filter_orders_without_packing_slips_request( $vars ) {
  global $typenow;
  if ( 'shop_order' === $typenow && ! empty( $_GET['without_packing_slip'] ) ) {
  $vars['meta_query'][] = array(
  'key' => '_wcpdf_packing_slip_date',
  'compare' => 'NOT EXISTS',
  );
  }
  return $vars;
}
/**
 * Set a minimum order amount for checkout
 */
function bt_matched_cart_items( $search_products, $reward = false ) {
   $count = 0;
   if ( ! WC()->cart->is_empty() ) {
       // Loop though cart items
       foreach(WC()->cart->get_cart() as $cart_item ) {
           // Handling also variable products and their products variations
           $cart_item_ids = array($cart_item['product_id'], $cart_item['variation_id']);
           $is_pass = !$reward ? !$reward : ( $reward == true && isset( $cart_item['custom_price']) ? $reward : false );
           // Handle a simple product Id (int or string) or an array of product Ids
           if( (( is_array($search_products) && array_intersect($search_products, $cart_item_ids) )
           || ( !is_array($search_products) && in_array($search_products, $cart_item_ids))) && $is_pass ){
             $count++;
           }
       }
   }
   return $count; // returning matched items count
}
// set minimum cart

add_action('woocommerce_checkout_process', 'bt_wc_minimum_order_amount');
add_action('woocommerce_before_cart', 'bt_wc_minimum_order_amount');
function bt_wc_minimum_order_amount(){
  $minimum = 50;
  $discount_cart = WC()->cart->discount_cart;
  if ((WC()->cart->subtotal - $discount_cart) < $minimum){
    if (is_cart()){
      wc_print_notice(
        sprintf('You must have an order with a minimum of %s to place your order, your current order total is %s.',
        wc_price($minimum) ,
        wc_price(WC()->cart->subtotal - $discount_cart)) , 'error'
      );
    }else{
      wc_add_notice(
          sprintf('You must have an order with a minimum of %s to place your order, your current order total is %s.',
          wc_price($minimum) ,
          wc_price(WC()
            ->cart->subtotal - $discount_cart)
          ) , 'error'
      );
    }
  }
}


// change text related products
add_filter('woocommerce_product_related_products_heading', 'bt_woocommerce_product_related_products_heading');
function bt_woocommerce_product_related_products_heading($heading){
  $heading = __('Customers also viewed','flatsome');
  return $heading;
}
// change text related products
add_filter('woocommerce_cart_totals_coupon_label', 'bt_woocommerce_cart_totals_coupon_label', 5);
function bt_woocommerce_cart_totals_coupon_label($label){
  if ( strstr( strtoupper( $label ), 'WC_POINTS_REDEMPTION' ) ) {
		$label = esc_html( __( 'Cashback redemption', 'woocommerce-points-and-rewards' ) );
	}
  return $label;
}
// show notice CASHback on cart page
//add_action('woocommerce_before_cart', 'bt_display_notice_cashback');
add_action('woocommerce_widget_shopping_cart_before_buttons', 'bt_display_notice_cashback', 5);
function bt_display_notice_cashback(){
  $existing_discount = WC_Points_Rewards_Discount::get_discount_code();
  /*
   * Don't display a points message to the user if:
   * The cart total is fully discounted OR
   * Coupons are disabled OR
   * Points have already been applied for a discount.
   */

  if ( ! wc_coupons_enabled() || ( ! empty( $existing_discount ) && WC()->cart->has_discount( $existing_discount ) ) ) {
    return;
  }

  // get the total discount available for redeeming points
  $discount_available = get_discount_for_redeeming_points( false, null, true );

  $message = generate_redeem_points_message();

  if ( null === $message ) {
    return;
  }

  // add 'Apply Discount' button
  $message .= '<form class="wc_points_rewards_apply_discount" action="' . esc_url( wc_get_cart_url() ) . '" method="post" style="display:inline">';
  $message .= '<input type="hidden" name="wc_points_rewards_apply_discount_amount" class="wc_points_rewards_apply_discount_amount" />';
  $message .= '<input type="submit" class="button wc_points_rewards_apply_discount" name="wc_points_rewards_apply_discount" value="' . __( 'Apply Discount', 'woocommerce-points-and-rewards' ) . '" /></form>';

  // wrap with info div
  $message = '<div class="woocommerce-info wc_points_redeem_earn_points">' . $message . '</div>';

  echo apply_filters( 'wc_points_rewards_redeem_points_message_on_cart', $message, $discount_available );
}

// required logged in before checkout
add_action('init','bt_redirect_to_gs_login_page');
function bt_redirect_to_gs_login_page(){
    if(!is_user_logged_in() && $GLOBALS['pagenow'] === 'wp-login.php')
    {
        $url = add_query_arg(
            'redirect_to',
            get_permalink($pageid),
            function_exists("um_get_core_page") ? um_get_core_page( "login" ) : null // your my acount url
        );
        wp_redirect($url);
        exit;
    }
}
// Account login style
function bt_flatsome_account_login_($content){
  // Show Login Lightbox if selected
    return "<h3 class='bt_checkout_mesage'>".$content."</h3>".do_shortcode('[block id="login-greensociety"]');
}
add_filter('woocommerce_checkout_must_be_logged_in_message', 'bt_flatsome_account_login_', 10);
/* calculate stock on hold orders */
//if ( is_admin() && isset($_REQUEST['test']) ) {
//if ( is_admin() && isset($_POST['recalculate-stock-onhold']) ) {
	//add_action( 'admin_init', 'recalculate_stock_onhold', 5);
//}

// add_action('admin_footer', 'bt_recalculate_stock_onhold_submenu_page_callback');
function bt_recalculate_stock_onhold_submenu_page_callback() {
    ob_start(); ?>
    <form style="margin-left: 200px" method="POST" action="">
      <?php submit_button('Recalculate On Hold', 'primary', 'recalculate-stock-onhold'); ?>
    </form>
    <?php
    echo ob_get_clean();
}

/*
function recalculate_stock_onhold(){
  $customer_orders = wc_get_orders( array(
      'limit'    => 100,
      'status'   => array( 'wc-on-hold' )
  ) );
  $old_onhold_products = get_option('__onhold_products');
  //print_r($old_onhold_products);
  // Iterating through each Order with pending status
  $products = array();
  $onhold_products = array();
  
  /*
  
  foreach ( $customer_orders as $order ) {
    // Going through each current customer order items
    foreach ( $order->get_items( array( 'line_item' ) ) as $item_id => $item ) {
      $product = $item->get_product();
      if ( $product->get_manage_stock() ) {
        $product = $item->get_product();
        $product_id = $product->get_id();
        $qty = $item->get_quantity();
        $onhold_products[$product_id] = $product_id;
        if(isset($products[$product_id])){
          $products[$product_id] += $qty;
        }else{
          $products[$product_id] = $qty;
        }
      }
  	}
  }
  
 */
  /*
  foreach ($products as $key => $value) {
    update_post_meta( $key, '__stock_onhold', $value );
  }
  $onhold_diff = array_diff($old_onhold_products, $onhold_products);
  foreach ($onhold_diff as $key => $value) {
    delete_post_meta( $value, '__stock_onhold' );
  }
  update_option('__onhold_products', $onhold_products);
}

*/

// stock on hold
add_filter( 'manage_edit-product_columns', 'show_product_order', 999999);
add_filter( 'manage_edit-product_variation_columns', 'show_product_order', 999999);
function show_product_order($columns){
  $columns['onholdstock'] = __( 'On Hold');
  return $columns;
}

add_action( 'manage_product_posts_custom_column', 'bears_product_column_onhold_stock', 10, 2 );
add_action( 'manage_product_variation_posts_custom_column', 'bears_product_column_onhold_stock', 10, 2 );
function bears_product_column_onhold_stock( $column, $postid ) {
  $onhold_products = get_option('__onhold_products');
  if ( $column == 'onholdstock' ) {
    $product = wc_get_product( $postid );
    if((method_exists($product,'get_manage_stock'))){
      if ( ! $product->get_manage_stock() || !in_array( $postid, $onhold_products ) ) {
        echo "-";
        delete_post_meta( $postid, '__stock_onhold' );
      }else{
        $onhold = get_post_meta($postid, '__stock_onhold', true);
        echo $onhold ? $onhold : '-';
      }
    }
  }
}

/* Lets Shop Managers edit users with these user roles */
function allow_shop_manager_role_edit_capabilities( $roles ) {
  $roles = array('um_verified', 'um_bulk', 'um_member', 'customer', 'subscriber', 'flagged');
  return $roles;
}
add_filter( 'woocommerce_shop_manager_editable_roles', 'allow_shop_manager_role_edit_capabilities' );

//** *Enable upload for webp image files.*/
function webp_upload_mimes($existing_mimes) {
  $existing_mimes['webp'] = 'image/webp';
  return $existing_mimes;
}
add_filter('mime_types', 'webp_upload_mimes');


/**
 * Star Rewards
 */
add_action( 'woocommerce_order_status_completed', 'bt_add_user_meta_rewards', 10, 2 );
function bt_add_user_meta_rewards( $order_id, $order) {
  $enable_reward = get_field('enable_reward','option');
  if(!$enable_reward) return;
  
  $allow_for_double_stars = get_field('allow_for_double_stars','option');
  $schedule_double_stars = get_field('schedule_double_stars','option');

  $order_created_date = $order->get_date_created();

  $start_date = new DateTime($schedule_double_stars['start_date']);
  $end_date = new DateTime($schedule_double_stars['end_date']);

  $reward_point = 1;
  $is_double_star = false;

  /**
   * Checking if double stars is enabled, then checking
   * if today is between the start and end dates or if it's Monday
   */
  if ( $allow_for_double_stars && (($order_created_date >= $start_date && $order_created_date <= $end_date) || $order_created_date->format( 'l' ) === 'Monday') ) {
    $reward_point = 2; // Double the reward points
    $is_double_star = true;
  }

  // Update user rewards
  $user_id = $order->get_user_id();
  $_user_rewards = get_user_meta( $user_id, '_user_rewards', true );
  $_new_user_rewards = $_user_rewards ? (int)$_user_rewards + $reward_point : $reward_point;
  update_user_meta( $user_id, '_user_rewards', $_new_user_rewards );

  // Add a order note for stars rewarded
  $reward_point_display = $reward_point * 10;
  $total_rewards_display = $_new_user_rewards * 10;
  $is_double_star = $is_double_star ? "YES" : "NO";
  $completed_order_note = sprintf("Added %d stars for order completed.\nOrder on Double Stars: %s\nTotal Stars: %d",$reward_point_display, $is_double_star, $total_rewards_display);
  $order->add_order_note($completed_order_note, false);

  // Society Rewards Log - hooked: order_completed_society_rewards_log_func
  // Logs the reward into database
  do_action("order_completed_society_rewards_log", $order );
}

/**
 * Star Rewards - handling cancelled orders
 * Reimburses the customers stars if their order is cancelled
 */
add_action( 'woocommerce_order_status_cancelled', 'bt_back_rewards_points_wc_order_status_cancelled' );
function bt_back_rewards_points_wc_order_status_cancelled( $order_id) {
  $enable_reward = get_field('enable_reward','option');

  if(!$enable_reward) return;

  $redeem_point = 0;

  $order = wc_get_order( $order_id );
  $user_id = $order->get_user_id();
  $items = $order->get_items();

  foreach ($items as $cart_item_key => $cart_item) {
    if( isset($cart_item['custom_price']) && isset($cart_item['redeem_point']) ){
      $redeem_point += $cart_item['redeem_point'];
    }
  }
  
  if($redeem_point){
    $_user_rewards = bt_get_user_rewards($user_id);
    $_new_user_rewards = max( 0, ($_user_rewards + $redeem_point));
    bt_set_user_rewards($user_id, $_new_user_rewards);
  }
}

/**
 * Star Rewards - handle ajax claim reward for templates/products-rewards.php POST
 */
add_action( 'wp_ajax_add_to_cart_reward', 'bt_add_to_cart_reward' );
add_action( 'wp_ajax_nopriv_add_to_cart_reward', 'bt_add_to_cart_reward' );
function bt_add_to_cart_reward() {
  if(isset($_POST['product_id'])){
    $product_id = $_POST['product_id'];
    $variation_id = $_POST['variation_id'];
    $custom_price = $_POST['custom_price'];
    $redeem_point = (int)$_POST['redeem_point'];
    $product = wc_get_product($product_id);

    if(!bt_matched_cart_items($product->get_id())){
      WC()->cart->add_to_cart( $product_id, 1, $variation_id, array(), array('custom_price' => $custom_price, 'redeem_point' => $redeem_point) );
    }

    $user_id = get_current_user_id();
    $_user_rewards = bt_get_user_rewards($user_id);
    $_new_user_rewards = max( 0, ($_user_rewards - $redeem_point));

    bt_set_user_rewards($user_id, $_new_user_rewards);
  }
  die();
}

/**
 * Star Rewards - handle giving back star rewards for removing star reward from cart
 */
add_action( 'woocommerce_remove_cart_item', 'bt_back_rewards_points_woocommerce_cart_item_removed', 10, 2 );
function bt_back_rewards_points_woocommerce_cart_item_removed( $cart_item_key, $cart ) {
    $cart_item = $cart->get_cart_item( $cart_item_key );
    $user_id = get_current_user_id();
    if( isset($cart_item['custom_price']) && isset($cart_item['redeem_point']) ){
        $redeem_point = $cart_item['redeem_point'];
        $_user_rewards = bt_get_user_rewards($user_id);
        $_new_user_rewards = max( 0, ($_user_rewards + $redeem_point));
        bt_set_user_rewards($user_id, $_new_user_rewards);
    }
}

/**
 * Star Rewards - handle adding redeem point meta to order item
 */
add_action('woocommerce_new_order_item','bt_add_redeem_point_to_order_item_meta', 1, 3);
function bt_add_redeem_point_to_order_item_meta($item_id, $cart_item, $order){
  // $user_custom_values = $values['wdm_user_custom_data_value'];
  if( isset($cart_item['custom_price']) && isset($cart_item['redeem_point']) ){
    wc_add_order_item_meta($item_id,'custom_price',$cart_item['custom_price']);
    wc_add_order_item_meta($item_id,'redeem_point',$cart_item['redeem_point']);
  }
}


function bt_force_cart_calculation() {
	if ( is_cart() || is_checkout() ) {
		return;
	}

	if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
		define( 'WOOCOMMERCE_CART', true );
	}

	WC()->cart->calculate_totals();
}
add_action( 'woocommerce_before_mini_cart', 'bt_force_cart_calculation' );
// dont allow change qty
// add_filter( 'woocommerce_quantity_input_args', 'bt_hide_cart_quantity_input_field', 20, 2 );
function bt_hide_cart_quantity_input_field( $args, $product ) {
  // Only on cart page for a specific product category
  if( (is_cart() || is_checkout()) && isset($product['custom_price']) ){
      $input_value = $args['input_value'];
      $args['min_value'] = $args['max_value'] = $input_value;
  }
  return $args;
}
add_filter( 'wfacp_cart_item_min_max_quantity', 'bt_cart_item_min_max_quantity_on_checkout', 20, 4 );
function bt_cart_item_min_max_quantity_on_checkout( $minmax, $cart_item, $aero_item_key, $cart_item_key ) {
  // Only on cart page for a specific product category
  if( isset($cart_item['custom_price']) ){
      $minmax = [ 'min' => 1, 'max' => 1, 'step' => 1 ];
  }
  return $minmax;
}
// Updating cart item price
add_action( 'woocommerce_before_calculate_totals', 'change_cart_item_price', 30, 1 );
function change_cart_item_price( $cart ) {
    if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item ) {
        // Set the new price
        if( isset($cart_item['custom_price']) ){
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}

// Hooks near the bottom of profile page (if current user)
add_action('show_user_profile', 'bt_custom_user_profile_rewards_fields');

// Hooks near the bottom of the profile page (if not current user)
add_action('edit_user_profile', 'bt_custom_user_profile_rewards_fields');

// @param WP_User $user
function bt_custom_user_profile_rewards_fields( $user ) {
  get_template_part( 'templates/user-profile-rewards', null, array('user' => $user) );
}


// Hook is used to save custom fields that have been added to the WordPress profile page (if current user)
add_action( 'personal_options_update', 'bt_update_reward_profile_fields' );

// Hook is used to save custom fields that have been added to the WordPress profile page (if not current user)
add_action( 'edit_user_profile_update', 'bt_update_reward_profile_fields' );

function bt_update_reward_profile_fields( $user_id ) {
    if ( current_user_can( 'edit_user', $user_id ) )
        update_user_meta( $user_id, '_user_rewards', $_POST['_user_rewards'] );
}
// fix redirect payment bitcoin
add_filter( 'woocommerce_get_return_url', 'bt_utm_nooverride', 99, 2 );
function bt_utm_nooverride($return_url, $order){
  $payment_method = $order->get_payment_method();
  if($payment_method == 'gourlpayments'){
    $return_url = remove_query_arg( 'utm_nooverride', $return_url );
    $return_url = add_query_arg( 'prvw', '1', $return_url );
  }
  return $return_url;
}

function show_rewards_products_shortcode() {

  $template = get_template_part( 'templates/products-rewards-grid', null, array() );

  return $template;
}
add_shortcode('rewards_products_grid', 'show_rewards_products_shortcode');

/**
 * Custom Swatches
 * 
 * Adds custom attributes to handle price switching
 */
add_filter('__swatches_atts_data_custom_hook', function($atts, $term, $type_tmp, $product, $swatch) {
  
  // if(isset($_GET['__dev'])) {
  //   var_dump($swatch);
  // }
  // return $atts;

  $single_variation = new WC_Product_Variation($swatch['variation_id']);
  $price = $single_variation->get_price();
  $sale_price = $single_variation->get_sale_price() ? $single_variation->get_sale_price() : 0;

  $attributes = $single_variation->get_data()['attributes'];
  $first_key = array_key_first($attributes);
  $value = isset($attributes[$first_key]) ? $attributes[$first_key] : '';
  $weight = $single_variation->get_weight();
  $price_per_unit_value = 0;
  if($weight) {
    $price = $single_variation->get_price();
    $price_per_unit_value = $weight !== 0 ? $price/$weight : 0;
  }

  $sale_price = $single_variation->get_sale_price();
  $regular_price = $single_variation->get_regular_price();

  if($sale_price) {
    $price = number_format((float) $sale_price, 2, '.', '');
    $sale_price = number_format((float) $regular_price, 2, '.', '');
  } else {
    $price = number_format((float) $price, 2, '.', '');
    $sale_price = number_format((float) 0, 2, '.', ''); 
  }

  $atts['onclick'] = sprintf(
    'pg_show_price(%s, %s, %s, %s, %s, this);', 
    $product->get_id(), 
    $swatch['variation_id'], 
    $price, 
    $price_per_unit_value, // '4.83', 
    $sale_price);

  return $atts;
}, 20, 5);

/**
 * Custom Swatches
 */
add_filter( 'flatsome_box_swatch_item', 'bt_swatch_html', 10, 5 );
function bt_swatch_html($html_item, $term, $type_tmp, $product, $swatch){
  // echo '___dev';
  // if(isset($_GET['__dev'])) {
  //   var_dump($html_item);
  // }

  $swatch_classes    = array( 'ux-swatch' );
  $color_classes     = array( 'ux-swatch__color' );
  $name              = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );
  $img_size          = apply_filters( 'flatsome_swatch_image_size', 'woocommerce_gallery_thumbnail', $term );
  $data              = array();
  $swatch_inner_html = '';

  // if ( isset( $swatch['image_src'] ) ) {
  //   $thumb_id = get_post_thumbnail_id( $swatch['variation_id'] );
  //
  //   if ( $thumb_id ) {
  //     $type_tmp          = 'variation-image';
  //     $swatch_classes[]  = 'ux-swatch--image';
  //     $swatch_inner_html = wp_get_attachment_image( $thumb_id, $img_size, false, array(
  //       'class' => "ux-swatch__img attachment-$img_size size-$img_size",
  //       'alt'   => $name,
  //     ) );
  //   }
  // }

  switch ( $type_tmp ) {
    case 'ux_color':
      $color = flatsome_swatches()->parse_ux_color_term_meta( isset( $swatch['ux_color'] ) ? $swatch['ux_color'] : '' );

      if ( $color['class'] ) $color_classes[] = $color['class'];

      $swatch_classes[]  = 'ux-swatch--color';
      $swatch_inner_html = '<span class="' . implode( ' ', $color_classes ) . '" style="' . $color['style'] . '"></span>';
      break;
    case 'ux_image':
      $swatch_classes[]  = 'ux-swatch--image';
      $swatch_inner_html = isset( $swatch['ux_image'] ) ? wp_get_attachment_image( $swatch['ux_image'], $img_size, false, array(
        'class' => "ux-swatch__img attachment-$img_size size-$img_size",
        'alt'   => $name,
      ) ) : wc_placeholder_img( $img_size );
      break;
    case 'ux_label':
      $swatch_classes[] = 'ux-swatch--label-';
      break;
  }

  // if ( isset( $swatch['image_src'] ) ) {
  //   $data['data-image-src']    = $swatch['image_src'];
  //   $data['data-image-srcset'] = $swatch['image_srcset'];
  //   $data['data-image-sizes']  = $swatch['image_sizes'];
  //
  //   if ( ! $swatch['is_in_stock'] ) {
  //     $swatch_classes[] = 'out-of-stock';
  //   }
  // }

  $data['data-attribute_name'] = 'attribute_attribute_pa_weight';
  $data['data-value']          = $term->slug;
  $single_variation = new WC_Product_Variation($swatch);
  $price = $single_variation->get_price();
  $sale_price = $single_variation->get_sale_price() ? $single_variation->get_sale_price() : 0;

  /**
   * Fix sale price
   */
  $sale_price = $single_variation->get_sale_price();
  $regular_price = $single_variation->get_regular_price();

  if($sale_price) {
    $price = number_format((float) $sale_price, 2, '.', '');
    $sale_price = number_format((float) $regular_price, 2, '.', '');
  } else {
    $price = number_format((float) $price, 2, '.', '');
    $sale_price = number_format((float) 0, 2, '.', '');
  }
  /**
   * End fix sale price
   */

  $html_item = '<div onclick="pg_show_price('.$product->get_id().', '.$swatch.', '.$price.', 4.83, '.$sale_price.', this);" class="' . esc_attr( implode( ' ', $swatch_classes ) ) . '" ' . flatsome_html_atts( $data ) . '>' . $swatch_inner_html . '<span class="ux-swatch__text">' . $name . '</span></div>';
  return $html_item;
}
// add_filter('woocommerce_dropdown_variation_attribute_options_html', '__return_empty_string', 200);

// add radio template for substitutions on checkout page
function gs_oos_checkout_buttons( ){
  $template = get_template_part( 'templates/out-of-stock-radio-buttons');

  echo $template;
}
add_action('wfacp_template_before_2_single_step_section_form_print', 'gs_oos_checkout_buttons');


// hide place order button on checkout if user is not logged in
function hide_place_order_logged_out($html){
  if(is_user_logged_in()){
    return $html;
  } else {
    echo "<div class='wfacp-order-place-btn-wrap'><button  style='font-size:1rem;white-space:nowrap;cursor:not-allowed;background-color:#ff6b6b;' disabled type='submit' class='button name='woocommerce_checkout_place_order' id='place_order' value='LOG IN TO PLACE YOUR ORDER' data-value='LOG IN TO PLACE YOUR ORDER'>LOG IN TO PLACE YOUR ORDER</button></div>";
  }
}
add_filter('woocommerce_order_button_html', 'hide_place_order_logged_out', 10, 1);

// hide quantity button if product is society reward
function hide_qty_cart_page_society_reward($product_quantity, $cart_item_key){
  $cart_item = WC()->cart->cart_contents[ $cart_item_key];
  if(isset($cart_item['custom_price'])) return '';
  return $product_quantity;

  // $cart_item_id = $cart_item['product_id'];

  // $rewards_ids = [];
  // $counter = 1;
  // while( !is_null( get_field('reward_'.$counter, 'option') ) ){
  //   $reward = get_field('reward_'.$counter, 'option');
  //   $product = wc_get_product($reward['rw_product']);
  //   $product_id = $product->get_parent_id() ? $product->get_parent_id(): $product->get_id();
  //   array_push($rewards_ids, $product_id);
  //   $counter++;
  // }

  // if( in_array($cart_item_id, $rewards_ids)){
  //   return '';
  // }

  // return $product_quantity;
}
add_action("woocommerce_cart_item_quantity", 'hide_qty_cart_page_society_reward', 10, 2);

function require_out_of_stock_note_selection($data, $errors){
  $order_notes_value = $data['order_notes_out_of_stock'];

	$default_option = "Please Select"; // the default value of the select

	if($order_notes_value == $default_option){

		$key = "gs-substitution-option";	// data-id for label

		$field_label = "Picking a substitution option";

		$errors->add( $key . '_required', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is required.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label, $key ), array( 'id' => $key ) );
	}


}
add_action('woocommerce_after_checkout_validation', 'require_out_of_stock_note_selection', 10, 2);


function add_featured_category_labels_shop_loop($category_name, $product){

  $featured_label_text = get_field('best_seller', $product->get_id());

  // if getting label is false or is empty return normal html
  if(!$featured_label_text || empty($featured_label_text)) {
    echo $category_name;
  } else {
    $html = "<p class='best-seller-loop best-seller-wrapper is-smaller'><span class='best-seller-product-loop'>{$featured_label_text}</span> IN {$category_name}</p>";
    echo $html;
  }

}
add_filter('flatsome_woocommerce_shop_loop_category', 'add_featured_category_labels_shop_loop', 10, 2);

function add_best_seller_label_single_product(){
  global $product;
  $id = $product->get_id();

  $featured_label_text = get_field('best_seller', $id);

  // if getting label is false or is empty do nothing
  if(!$featured_label_text || empty($featured_label_text)) return;

  $category_list = wc_get_product_category_list($id);

  $split_categories = explode(", ", $category_list);

  $deepest_category = $split_categories[0];

  // $html = "<p class='best-seller-wrapper'><span class='best-seller-product-loop'><span style='color:#fff;'>&starf;</span> {$featured_label_text} in {$deepest_category} <span style='color:#fff;'>&starf;</span></span></p>";

  $html = "<p class='best-seller-wrapper'><span class='best-seller-product-loop'>{$featured_label_text} in {$deepest_category}</span></p>";
    echo $html;
}
add_action( 'woocommerce_single_product_summary', 'add_best_seller_label_single_product', 5);

function jy_custom_woocommerce_sale_flash_product_loop(){
  global $product;
  $id = $product->get_id();
  $oz_field = get_field('ounce_sale_badge', $id);
  $is_daily_deal = green_is_product_daily_deal($id);

  // if the product is not a daily deal and does not have a badge, do nothing.
  if(($oz_field == "none" || is_null($oz_field)) && ($is_daily_deal == false && empty($is_daily_deal)) ) return;
  // if(($oz_field == "none" || is_null($oz_field))) return;

  if($is_daily_deal !== false && !empty($is_daily_deal)){
    echo "<div class='oz-sale-flash daily-deal-badge'><img src='https://greensociety.cc/wp-content/uploads/2022/12/daily-deal-sale-badge.png'></div>";
  } else {
      $oz_amount = explode('_', $oz_field)[0];
      echo "<div class='product-loop-badge oz-sale-flash ounce-badge-".$oz_amount."'><img src='https://greensociety.cc/wp-content/uploads/2022/11/ounce-".$oz_amount."-sale-badge.png'></div>";
  }

}
add_action('woocommerce_before_shop_loop_item', 'jy_custom_woocommerce_sale_flash_product_loop', 10);
add_action('woocommerce_before_single_product_summary', 'jy_custom_woocommerce_sale_flash_product_loop', 11);

function jy_remove_sale_badge_if_oz($html, $post, $product){
  $id = $product->get_id();
  $oz_field = get_field('ounce_sale_badge', $id);
  $is_daily_deal = green_is_product_daily_deal($id);

  if(($oz_field == "none" || is_null($oz_field)) && ($is_daily_deal == false && empty($is_daily_deal))) {
  // if(($oz_field == "none" || is_null($oz_field))) {
    // if we are in here, we know that the product does not have an ounce badge AND is not a daily deal, return the regular sale html
    return "<div class='oz-sale-flash custom-sale-badge'><img loading='lazy' src='https://greensociety.cc/wp-content/uploads/2022/12/generic-sale-badge.png'></div>";
  } else {
    return;
  };

}
add_filter( 'woocommerce_sale_flash', 'jy_remove_sale_badge_if_oz', 10, 3);

// component for showing daily deal
function display_daily_deal_product() {
  $enable = get_field('rdd_enable', 'option');
  if($enable != true) return;

  // if enabled, get the daily deal
  $product_deal_by_day = get_field('rdd_' . strtolower(current_time('l')), 'option');

  // exit if there are no daily deals
  if(!$product_deal_by_day['products']) return;

  // if there is a daily deal, get the first
  // we'll start with displaying only 1, we can enhance this to be a mini slider
  $first_daily_deal_product = $product_deal_by_day['products'][0];
  $first_daily_deal_discount = $product_deal_by_day['deal'];

  // create array of data to pass into template
  $args = array(
    'id' => $first_daily_deal_product->ID,
    'discount_amount' => $first_daily_deal_discount,
    'title' => $first_daily_deal_product->post_title,
    'link' => get_post_permalink($first_daily_deal_product->ID),
    'image_src' => wp_get_attachment_image_src( get_post_thumbnail_id($first_daily_deal_product->ID), 'medium' )[0]
  );

  ob_start();
  get_template_part('templates/daily-deal-product', null, $args);
  return ob_get_clean();
}
// add_shortcode( 'daily_deal_product', 'display_daily_deal_product' );


// component for showing daily deal
function display_gs_featured_product($atts, $content, $tag) {

  // ['titleName'] => "Hello"        ->         ['titlename' => "Hello"]
  $atts = array_change_key_case( (array) $atts, CASE_LOWER );

  // override default attributes with user attributes
	$featured_product_atts = shortcode_atts(
		array(
			'title' => "Featured Product",
      'product_id' => 1274028,
      'daily_deal' => 0
		), $atts, $tag
	);

  $args = array();
  
  $product = wc_get_product($featured_product_atts['product_id']);
  $product_categories = wc_get_product_category_list($product->get_id());
  $product_category = explode(', ', $product_categories)[0]; // get the most nested category

  $is_variable = false;

  
  // if daily deal is true, skip everything and auto populate
  if($featured_product_atts['daily_deal'] != 0){
 
    $enable = get_field('rdd_enable', 'option');
    if($enable != true) return;
    
    // if enabled, get the daily deal
    $product_deal_by_day = get_field('rdd_' . strtolower(current_time('l')), 'option');
    
    // exit if there are no daily deals
    if(!$product_deal_by_day['products']) return;
  

    // if there is a daily deal, get the first
    // we'll start with displaying only 1, we can enhance this to be a mini slider
    $first_daily_deal_product = $product_deal_by_day['products'][0];
    $first_daily_deal_discount = $product_deal_by_day['deal'];
    
    // reassign product category
    $product_categories = wc_get_product_category_list($first_daily_deal_product->ID);
    $product_category = explode(', ', $product_categories)[0]; // get the most nested category
   
    $wc_prod = wc_get_product($first_daily_deal_product->ID);
    $price = $wc_prod->get_price_html();

    if($wc_prod->get_type() === "variable"){
      $children = $wc_prod->get_children();
      
      $lowest_product = wc_get_product($children[0]);
      $highest_product = wc_get_product(end($children));
      
      $lowest_price = wc_get_price_to_display($lowest_product);
      $highest_price = wc_get_price_to_display($highest_product);
      
      $lowest_price_display = wc_price($lowest_price);
      $highest_price_display = wc_price($highest_price);

      $price = $lowest_price_display . " - " . $highest_price_display;
      
      $is_variable = true;
    }
  
    $args = array(
      'product_id' => $first_daily_deal_product->ID,
      'title' => "Today's Daily Deal",
      'price' => $price,
      'product_name' => $wc_prod->get_name(),
      'permalink' => $wc_prod->get_permalink(),
      'category' => $product_category,
      'image' => wp_get_attachment_image( get_post_thumbnail_id($first_daily_deal_product->ID), 'thumbnail', false, array("class" => "daily-deal-image")),
      'discount_amount' => $first_daily_deal_discount,
      'variable' => $is_variable,
    );

  } else {

    if($product->get_type() === "variable"){
      // $first_child = $product->get_children()[0];
      // $product = wc_get_product($first_child);
      $is_variable = true;
    }

    $args = array(
      'product_id' => $product->get_id(),
      'title' => $featured_product_atts['title'],
      'price' => $product->get_price_html(),
      'product_name' => $product->get_name(),
      'permalink' => $product->get_permalink(),
      'category' => $product_category,
      'image' => $product->get_image('woocommerce_thumbnail', array("class" => "daily-deal-image")),
      'discount_amount' => "",
      'variable' => $is_variable,
    );

  }

  ob_start();
  get_template_part('templates/gs-featured-product', null, $args);
  return ob_get_clean();


}
add_shortcode( 'gs_featured_product', 'display_gs_featured_product' );

add_filter( 'woocommerce_update_order_review_fragments', 'bt_custom_checkout_fragments', 990, 1 );
function bt_custom_checkout_fragments($fragments){
  ob_start();
  include get_stylesheet_directory() . '/funnel-builder-pro/views/template-parts/order-review.php';
  $fragments['.wfacp_template_9_cart_item_details'] = ob_get_clean();
  return $fragments;
}
// add_action('wp_head', 'bt_update_menu_order_products');
// function bt_update_menu_order_products(){
//   if(isset($_REQUEST['test'])){
//     $args = array(
//         'limit' => -1,
//         'type' => 'mix-and-match',
//     );
//     $products = wc_get_products( $args );
//     foreach ($products as $key => $value) {
//       $value->set_menu_order(-1);$value->save();
//     }
//   }
// }

 
function add_custom_user_fields($user) {
    if(!empty($_GET['user_data'])){
        $user_id = $user->ID;
      var_dump(get_user_meta( $user_id ));
     }
}
add_action('edit_user_profile', 'add_custom_user_fields');
function display_extra_notice_before_submit(){
  echo "<p style='font-size:0.75rem;text-align:left;'><small>*Once you have placed your order, you are automatically agreeing to our <a href='https://greensociety.cc/privacy-policy/'>privacy policy</a> and are giving up your right to make a request for any refunds after placing your order. You also agree that all orders from Green Society are final and non-refundable; in the case of an error or product issue, you will be reimbursed in points. Any questions of concerns please view our <a href='https://greensociety.cc/faq/'>FAQ</a> or contact <a href='mailto:support@greensociety.cc'>support@greensociety.cc.</a></small></p>";
  echo "<p style='font-size:0.75rem;text-align:left;'><small>*Due to hot weather conditions, high temperatures may cause concentrates and edibles to melt during transit. If you receive any items that defective due to heat, please get in touch with support.</small></p>";
}
add_action('woocommerce_review_order_before_submit', 'display_extra_notice_before_submit');

// klaviyo update 
 
function update_user_klavio_blance_stars($klavio_id,$array_data){
      $curl = curl_init();
$api_key = 'pk_f7ce2a88dc591c159611a4eb51ce3bc243';
$array_url = http_build_query($array_data);
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://a.klaviyo.com/api/v1/person/'.$klavio_id.'?'.$array_url.'&api_key='.$api_key,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;

  }
  
function get_user_klavio($user_email){
   //   $user_email = $user->user_email;
$curl = curl_init();
$api_key = 'pk_f7ce2a88dc591c159611a4eb51ce3bc243';
$date = date('Y-m-d');
$email_encode = urlencode($user_email);
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://a.klaviyo.com/api/profiles/?additional-fields%5Bprofile%5D=predictive_analytics&fields%5Bprofile%5D=first_name,email&filter=equals(email,%22'.$email_encode.'%22)&page%5Bsize%5D=20',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'revision: '.$date,
    'Authorization: Klaviyo-API-Key '.$api_key
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$resp_array = json_decode($response,1);
// echo $response;
$user_klavio_id = $resp_array['data'][0]['id'];
return $user_klavio_id;
  }


function klavio_update($order){
	   
		 $billing_email  = $order->get_billing_email();
		    $kavio_id = get_user_klavio($billing_email);
		    if(!empty($kavio_id)){
		        $arrayData=[];
		       $user_id = $order->get_user_id();
		       $stars = get_user_meta($user_id,'_user_rewards',1);
		     	$balance = WC_Points_Rewards_Manager::get_users_points( $user_id);
		       if(!empty($stars)){
		           $stars = $stars*10;
		           $arrayData['star rewards']=$stars;
		       }
		       if(!empty($balance)){
		          $arrayData['points balance']= round($balance/40, 2);
		       }
		       //var_dump($stars);
		       if(!empty($arrayData)){
		       update_user_klavio_blance_stars($kavio_id,$arrayData);
		           
		       }
		    }
}

add_action( 'woocommerce_order_status_completed', 'klavio_wc_after_order_complete'  );

function klavio_update_by_user($user){
	$user_file = get_template_directory().'/user_update_klavio.txt';
	
 
			 $user_email  = $user->user_email;
		    $kavio_id = get_user_klavio($user_email);
	var_dump($kavio_id);
	file_put_contents($user_file,$klavio_id.' | ',FILE_APPEND);
		    if(!empty($kavio_id)){
		        $arrayData=[];
		       $user_id = $user->ID;
		       $stars = get_user_meta($user_id,'_user_rewards',1);
		      // $balance = get_user_meta($user_id,'wc_points_balance',1);
				$balance = WC_Points_Rewards_Manager::get_users_points( $user_id);
		       if(!empty($stars)){
		           $stars = $stars*10;
		           $arrayData['star rewards']=$stars;
		       }
		       if(!empty($balance)){
		          $arrayData['points balance']= round($balance/40, 2);
		       }
		       //var_dump($stars);
		       if(!empty($arrayData)){
		       update_user_klavio_blance_stars($kavio_id,$arrayData);
		           
		       }
		    }
}


function klavio_wc_after_order_complete($order_id ){
    $order = new WC_Order( $order_id );
    klavio_update($order);
}

add_action('init',function(){
if(!empty($_GET['user_update_klavio'])){ 
    
$user_file = get_template_directory().'/user_updates.json';
 var_dump($user_file);
if(!is_file($user_file)){
$user_list = [];
  
}else {
	$user_data = file_get_contents($user_file);
	$user_list = json_decode($user_data,1);
}
	    
	$params = array(
		'number' => 100 
	);
	if(!empty($user_list )){
		$params['exclude'] = $user_list;
	}
		$params['meta_query'] = array(
		array(
			'key' => '_user_rewards',
			'value' => 0,
			'type' => 'numeric',
			'compare' => '>'
		)
	);
	 
	$uq = new WP_User_Query( $params );
	if ( ! empty( $uq->results ) ) {
		foreach ( $uq->results as $u ) {
		klavio_update_by_user($u);
			$user_list[] = $u->ID;
		}
		var_dump($user_list);
		$user_list = json_encode($user_list);
    file_put_contents($user_file, $user_list); 
	}
	 die;
}    
	if(!empty($_GET['user_points'])){
		$points_balance = WC_Points_Rewards_Manager::get_users_points( 132800 );
		var_dump($points_balance);
		klavio_update_by_user(132800);
		die;
	}
});

add_action('wp_footer', function(){
  if(is_checkout()){ ?>
    <script src="/wp-content/themes/flatsome-child/js/auto-open-show-order-summary.js"></script>
  <?php }
});

function jy_handle_after_register($user_id, $args){
  if (!isset($args['submitted']['register_subscribe_newsletter'])) return;

  // $first_name = $args['submitted']['first_name'];
  // $last_name = $args['submitted']['last_name'];
  $email = $args['submitted']['user_email'];

  jy_add_user_newsletter_klaviyo($email);

}
add_action('um_registration_complete', 'jy_handle_after_register', 10, 2);

function jy_add_user_newsletter_klaviyo($email){
  $list_id = 'Nt82q3';
  $endpoint = 'profile-subscription-bulk-create-jobs';
  $api_key = 'pk_4eaa46ccc84fe14b8ba8fc80ae823b07cf';
  $current_date = date("Y-m-d");
  $url = "https://a.klaviyo.com/api/{$endpoint}/";

  $data = array(
    'data' => array(
      'type' => 'profile-subscription-bulk-create-job',
      'attributes' => array(
        'list_id' => $list_id,
        'subscriptions' => array(
          array(
            'channels' => array('email' => ['MARKETING']),
            'email' => $email,
          )
        )
      )
    )
  );

  $post_args = array(
    'method' => 'POST',
    'headers' =>  array(
      'Authorization' => 'Klaviyo-API-Key ' . $api_key,
      'accept' => 'application/json',
      'content-type' => 'application/json',
      'revision' => $current_date,
    ),
    'body' => wp_json_encode( $data ),
  );

  $response = wp_remote_post($url, $post_args);

}

add_action('woocommerce_single_product_summary', 'add_discount_percentage_single_product_simple', 10);
function add_discount_percentage_single_product_simple(){
  global $product;

  if (!$product->is_on_sale() || !$product->is_type('simple') ) return;
  $regular_price = $product->get_regular_price();
  $sale_price = $product->get_sale_price();

  $saved_amount = $regular_price - $sale_price;
  $percentage = round($saved_amount / $regular_price * 100);
  $formatted_percentage_text = "Save " . $percentage . "%";

  echo '<p style="color:red;font-weight:bolder;font-size:1.2rem;">' . $formatted_percentage_text . '</p>';
}

add_filter('woocommerce_available_variation', 'add_discount_percentage_single_product_variable', 10, 3);
function add_discount_percentage_single_product_variable($data, $product, $variation){
  
  if( $variation->is_on_sale() ) {
    $saved_amount  = $data['display_regular_price'] - $data['display_price'];
    $percentage    = round( $saved_amount / $data['display_regular_price'] * 100 );
    $formatted_percentage_text = "Save " . $percentage . "%";

    $data['price_html'] .= '<p style="color:red;font-weight:bolder;font-size:1.2rem;">' . $formatted_percentage_text . '</p>';
  }

  return $data;
}
error_reporting(0);