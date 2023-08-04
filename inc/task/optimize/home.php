<?php 
/**
 * Home optimize
 */

function green_is_home_page() {
  return (is_front_page() && !is_user_logged_in()) ? true : false;
}

function green_denqueue_scripts_home_page() {
  $is_home = green_is_home_page();
  if($is_home != true) return;

  /**
   * metorik-helper
   */
  wp_deregister_style('metorik-css');
  wp_deregister_script('metorik-js');

  /**
   * woocommerce-product-bundles
   */
  wp_deregister_style('wc-pb-checkout-blocks');
  wp_deregister_style('wc-bundle-style');

  /**
   * if-menu
   */
  wp_deregister_style('if-menu-site-css');

  /**
   * free-shipping-label
   */
  wp_deregister_style('free-shipping-label-public');

  /**
   * products-visibility-by-user-roles
   */
  wp_deregister_style('afpvu-front');

  /**
   * woocommerce-waitlist
   */
  wp_deregister_style('wcwl_frontend');
}

add_action('wp_enqueue_scripts', 'green_denqueue_scripts_home_page', 999);