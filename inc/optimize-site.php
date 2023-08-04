<?php
//de enqueue scripts
add_action('wp_print_scripts','bt_remove_wp_enqueue_scripts',999);
function bt_remove_wp_enqueue_scripts(){
	$scripts = array();
	if(is_front_page() || is_product_category() || is_shop() ){
    $scripts = array(
      'flipclocksdsd-master-jsaaaa',
      'pw-gift-slider-jquery',
      'pw-gift-slightbx-jquery',
      'flash_sale_shortcodes_js',
      'wcpr-default-display-script',
      'woocommerce-photo-reviews-script',
      'woocommerce-photo-reviews-shortcode-script',
      'um_crop',
      'um_modal',
      'um_jquery_form',
      'um_fileupload',
      'um_datetime',
      'um_datetime_date',
      'um_datetime_time',
      'um_raty',
      'um_tipsy',
      'imagesloaded',
      'masonry',
      'um-gdpr',
      'jquery-masonry',
      'um_scrollbar',
      'um_functions',
      'um_responsive',
      'um_conditional',
      'um_scripts',
      'um_profile',
      'um_account',
      'wp-embed',
      'select2',
      'ivole-frontend-js',
      'cr-colcade',
      'pwb-functions-frontend',
  	);
  }
	foreach ($scripts as $script) {
		wp_dequeue_script($script);
		wp_deregister_script($script);
	}
}
//de enqueue styles
add_action('wp_print_styles','bt_remove_wp_enqueue_styles',999);
function bt_remove_wp_enqueue_styles(){
	$styles = array();
  if(is_front_page() || is_product_category() || is_shop()){
    $styles = array(
  		'pw-gift-layout-style',
  		'pw-gift-slider-style',
  		'pw-gift-grid-style',
  		'flipclock-master-cssss',
  		'pw-gift-lightbox-css',
  		'flash_sale_shortcodes',
  		'ivole-frontend-css',
  		'cr-badges-css',
  		'ivole-reviews-grid',
  		'cr-style',
  		'pwb-styles-frontend',
  		'wcpr-country-flags',
  		'gens-raf',
  		'wc-mnm-frontend',
  		'uap_public_style',
  		'uap_templates',
  		'um_fonticons_ii',
  		'um_fonticons_fa',
  		'select2',
  		'um_crop',
  		'um_modal',
  		'um_styles',
  		'um_profile',
  		'um_account',
  		'um_misc',
  		'um_fileupload',
  		'um_datetime',
  		'um_datetime_date',
  		'um_datetime_time',
  		'um_raty',
  		'um_scrollbar',
  		'um_tipsy',
  		'um_responsive',
  		'um_default_css',
  		'child-ultimate-member',
  	);
  }
	foreach ($styles as $style) {
		wp_dequeue_style($style);
	}
}
