<?php
use \Milon\Barcode\DNS1D;
add_action('woocommerce_proceed_to_checkout', 'bt_show_coupon', 6);
function bt_show_coupon(){
  if ( wc_coupons_enabled() )
    get_template_part( 'templates/form-coupon' );
}

// wrap print_r in preformatted text tags
// usage: print_pre($value)
function print_pre($value) {
    echo "<pre>",print_r($value, true),"</pre>";
}

function enqueue_jeff_scripts(){

  $daily_deal_version = "1.0.1";
  wp_enqueue_style("gs-featured-product-styles", get_stylesheet_directory_uri() . '/css/gs-featured-product-style.css', array(), $daily_deal_version);

  //For Society Deals page
  if(is_page(1568345)){
    wp_enqueue_style("gs-society-deals-styles", get_stylesheet_directory_uri() . '/dist/gs-society-deals.css', array(), "1.0.0");
    wp_enqueue_script("gs-society-deals-script", get_stylesheet_directory_uri() . '/dist/gs-society-deals.js', array(), "1.0.0", true);
  }

  /**
   * Ounce badge styles.
   * Does not get loaded onto the checkout page because there is no product loop.
   */
  if(!is_checkout()){
    wp_enqueue_style("custom-sale-badge-css", get_stylesheet_directory_uri() . '/dist/custom-sale-badge-css.css', array(), "4.0");
  }

  /**
   * Enable mobile off-canvas cart
   */
  $off_canvas_script_file = '/js/enable-off-canvas-mob.js';
  // $off_canvas_script_path = get_template_directory() . $off_canvas_script_file;
  $off_canvas_script_url = get_stylesheet_directory_uri() . $off_canvas_script_file;
  // $off_canvas_version = filemtime($off_canvas_script_path);
  $off_canvas_version = "1.0.1";
  wp_enqueue_script( 'enable-off-canvas-mob', $off_canvas_script_url, array(), $off_canvas_version, true );

    
  

}
add_action('wp_enqueue_scripts', 'enqueue_jeff_scripts');


function jy_add_to_cart_ajax_featured_product(){
  wp_enqueue_script('jy-add-to-cart-featured-product', get_stylesheet_directory_uri() . '/dist/jy-ajax-add-to-cart-featured-product.js', array('jquery'), '1.0', true );
  wp_localize_script( 'jy-add-to-cart-featured-product', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'jy_add_to_cart_ajax_featured_product' );

function jy_add_to_cart_ajax_handler(){
  $product_id = absint( $_POST['product_id'] );
  // $quantity = absint( $_POST['quantity'] );
  WC()->cart->add_to_cart( $product_id);
  wp_die();
}
add_action( 'wp_ajax_jy_featured_add_to_cart', 'jy_add_to_cart_ajax_handler' );
add_action( 'wp_ajax_nopriv_jy_featured_add_to_cart', 'jy_add_to_cart_ajax_handler' );


function change_default_edibles_orderby( $sort_by ) {
  return 'menu_order';
  $parent_id = 26;
  $termchildren = get_terms('product_cat',array('child_of' => $parent_id));


  $all_edibles_cats = array($parent_id);

  foreach ($termchildren as $term){
    $all_edibles_cats[] = $term->term_id;
  }

  if( is_product_category($all_edibles_cats) ) {
      return 'menu_order';
  }
  return $sort_by;
}
add_filter( 'woocommerce_default_catalog_orderby', 'change_default_edibles_orderby' );


/***
 * DEPRECATED: Not in use anymore
 * 
 * Free eighth is not offered anymore
 */
function limit_free_eighth_once($valid, $product_id){
  $current_user = wp_get_current_user();

  $product = 1424449;

  if($product === $product_id){
    if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id)) {
      wc_add_notice( __( 'This product is only purchaseable once per customer. Thanks for supporting!', 'woocommerce' ), 'error' );
      $valid = false;
    }
  }

  return $valid;
}
// add_filter('woocommerce_add_to_cart_validation','limit_free_eighth_once',20, 2);

function bt_render_base64_barcode($barcode) {
	$upload_dir  = wp_upload_dir();
	$dns1d       = new DNS1D();
	$dns1d->setStorPath( $upload_dir['path'] . '/cache/' );
	return $dns1d->getBarcodePNG($barcode, 'C128', 1, 48);
}

