<?php 
/**
 * Single product 
 */
// remove enqueue if we are on single product and not admin
function green_is_remove_enqueue() {
  return (is_product() && !is_user_logged_in()) ? true : false;
}

function green_denqueue_scripts_single_product() {
  $is_remove = green_is_remove_enqueue();
  if($is_remove != true) return; 

  //print_r([is_product(), is_user_logged_in(), $is_remove]);

  /**
   * Core dashicon
   */
  // wp_deregister_style('dashicons');

  /**
   * woocommerce-waitlist
   */
  wp_deregister_style('wcwl_frontend');
  wp_deregister_script('wcwl_frontend');

  /**
   * WC Photoswipe
   */
  wp_deregister_script('photoswipe');
  wp_deregister_style('photoswipe');
  wp_deregister_script('photoswipe-ui-default');  

  /**
   * woocommerce-photo-reviews
   */
  wp_deregister_script('wcpr-swipebox-js');
  // wp_deregister_script('woocommerce-photo-reviews-script');
  // wp_deregister_script('woocommerce-photo-reviews-shortcode-script');

  /**
   * customer-reviews-woocommerce
   */
  wp_deregister_script('ivole-frontend-js');

  /**
   * ultimate-member
   */
  wp_deregister_script('um_crop');
  wp_deregister_style('um_crop');
  wp_deregister_script('um_datetime');
  wp_deregister_script('um_datetime_date');
  wp_deregister_script('um_datetime_time');
  wp_deregister_script('um_functions');

  /**
   * business-reviews-bundle
   */
  // wp_deregister_style('brb-public-main-css');
}

// add_action('wp_enqueue_scripts', 'green_denqueue_scripts_single_product', 999);

// dequeue style not being used
function handle_single_product_styles(){
  if(is_product() && !is_admin()){
    $style_handles = array(
      'uap_templates',
      'ivole-frontend-css',
      'uap_public_style',
      'um_fonticons_ii',
      'um_fonticons_fa',
      'um_styles',
      'cr-badges-css',
      'select2',
      'wcpr-country-flags',
      'um_responsive',
      'um_profile',
      'child-ultimate-member',
      // 'photoswipe-default-skin', // creates a spinner below footer if included
      'um_default_css',
      'cr-style',
      'um_datetime_date',
      'wc-pb-checkout-blocks',
      'um_account',
      'um_crop',
      'um_scrollbar',
      'um_datetime',
      'um_fileupload',
      'um_modal',
      'free-shipping-label-public',
      'um_datetime_time',
      'wcpr-verified-badge-icon',
      'um_tipsy',
      'um_misc',
      'metorik-css',
      'um_raty',
      'if-menu-site-css',
      'wcpr-swipebox-css',
    );
    
    foreach ($style_handles as $key => $value){
      wp_dequeue_style($value);
      wp_deregister_style($value);
    }
  }; 
}
// add_action('wp_enqueue_scripts', 'handle_single_product_styles', 9999);

// removes lazy loading on main product image
function remove_lazy_load($attr, $attachment, $size){

  if(is_product()){
    if(strpos($attr['class'], 'wp-post-image') !== false){
      $attr['loading'] = "eager";
    }
  }

  return $attr;

}
add_filter("wp_get_attachment_image_attributes", 'remove_lazy_load', 10, 3);

add_filter('woocommerce_single_product_photoswipe_enabled', '__return_false');

add_action('wp_footer', function() {
  if(green_is_remove_enqueue() != true) return;
  ?>
  <style>
    .pswp {
      display: none;
    }
  </style>
  <?php
});