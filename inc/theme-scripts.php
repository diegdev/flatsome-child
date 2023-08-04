<?php

// add script to frontend
add_action( 'wp_print_styles', 'bt_theme_enqueue_styles', 9999 );
function bt_theme_enqueue_styles() {
    $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    $theme = wp_get_theme();
    // parent theme js
    // wp_dequeue_script('flatsome-js');
    // wp_deregister_script('flatsome-js');
    // wp_enqueue_script( 'flatsome-js', get_stylesheet_directory_uri().'/dist/flatsome.js', array(
    //   'jquery',
    //   'hoverIntent',
    // ), $theme->get('Version'), true );
    //
    //
    //
  	// $sticky_height = get_theme_mod( 'header_height_sticky', 70 );
    //
  	// if ( is_admin_bar_showing() ) {
  	// 	$sticky_height = $sticky_height + 30;
  	// }
    //
  	// $lightbox_close_markup = apply_filters('flatsome_lightbox_close_button', '<button title="%title%" type="button" class="mfp-close"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>');
    //
  	// $localize_data = array(
  	// 	'theme'         => array( 'version' => $version ),
  	// 	'ajaxurl'       => admin_url( 'admin-ajax.php' ),
  	// 	'rtl'           => is_rtl(),
  	// 	'sticky_height' => $sticky_height,
  	// 	'assets_url'    => $uri . '/assets/js/',
  	// 	'lightbox'      => array(
  	// 		'close_markup'     => $lightbox_close_markup,
  	// 		'close_btn_inside' => apply_filters( 'flatsome_lightbox_close_btn_inside', false ),
  	// 	),
  	// 	'user'          => array(
  	// 		'can_edit_pages' => current_user_can( 'edit_pages' ),
  	// 	),
  	// 	'i18n'          => array(
  	// 		'mainMenu' => __( 'Main Menu', 'flatsome' ),
  	// 	),
  	// 	'options'       => array(
  	// 		'cookie_notice_version'          => get_theme_mod( 'cookie_notice_version', '1' ),
  	// 		'swatches_layout'                => get_theme_mod( 'swatches_layout' ),
  	// 		'swatches_box_select_event'      => get_theme_mod( 'swatches_box_select_event' ),
  	// 		'swatches_box_behavior_selected' => get_theme_mod( 'swatches_box_behavior_selected' ),
  	// 		'swatches_box_update_urls'       => get_theme_mod( 'swatches_box_update_urls', '1' ),
  	// 		'swatches_box_reset'             => get_theme_mod( 'swatches_box_reset' ),
  	// 		'swatches_box_reset_extent'      => get_theme_mod( 'swatches_box_reset_extent' ),
  	// 		'swatches_box_reset_time'        => get_theme_mod( 'swatches_box_reset_time', 300 ),
  	// 		'search_result_latency'          => get_theme_mod( 'search_result_latency', '0' ),
  	// 	),
  	// );
    //
  	// if ( is_woocommerce_activated() ) {
  	// 	$wc_localize_data = array(
  	// 		'is_mini_cart_reveal' => flatsome_is_mini_cart_reveal(),
  	// 	);
    //
  	// 	$localize_data = array_merge( $localize_data, $wc_localize_data );
  	// }
    // // Add variables to scripts
  	// wp_localize_script( 'flatsome-js', 'flatsomeVars', $localize_data );
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css',
        array(),  // if the parent theme code has a dependency, copy it to here
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array(),
        $theme->get('Version') // this only works if you have Version in the style header
    );
    wp_enqueue_style( 'child-style-custom', get_stylesheet_directory_uri().'/dist/app.css',
        array(),
        time(), // this only works if you have Version in the style header
    );

    wp_register_script( 'child-js-custom', get_stylesheet_directory_uri().'/dist/app.min.js',
        array( 'jquery' ),
        time() // this only works if you have Version in the style header
    );
    wp_localize_script( 'child-js-custom', 'pp_php_admin_data', apply_filters( 'pp/wp_localize_script/php_admin_data', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'lang' => [],
        ] ) );
    wp_enqueue_script( 'child-js-custom' );

}
/**
 * Load admin scripts
 *
 */
function bt_add_admin_scripts() {
    $theme = wp_get_theme();
    wp_enqueue_style( 'child-style-admin', get_stylesheet_directory_uri().'/dist/admin.css' );
    wp_register_script( 'child-js-admin', get_stylesheet_directory_uri().'/dist/admin.min.js',
        array( 'jquery' ),
        $theme->get('Version') // this only works if you have Version in the style header
    );
    wp_localize_script( 'child-js-admin', 'pp_php_admin_data', apply_filters( 'pp/wp_localize_script/php_admin_data', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'lang' => [],
        ] ) );
    wp_enqueue_script( 'jquery-ui-dialog' ); // jquery and jquery-ui should be dependencies, didn't check though...
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
    wp_enqueue_script( 'child-js-admin' );
    // barcode form
    $token = 'woocommerce_order_barcodes';
    wp_deregister_script( $token . '-frontend');
    wp_deregister_script( $token . '-frontend.min');
    wp_deregister_style( $token . '-frontend');
    wp_register_script( $token . '-frontend.min', get_stylesheet_directory_uri().'/dist/frontend.min.js',
        array( 'jquery' ),
        $theme->get('Version') // this only works if you have Version in the style header
    );
    wp_localize_script( $token . '-frontend.min', 'wc_order_barcodes', apply_filters( 'wc_order_barcodes', [
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'scan_nonce' => wp_create_nonce( 'scan-barcode' ),
        ] ) );
    wp_enqueue_style( $token . '-frontend', plugins_url().'/woocommerce-order-barcodes/assets/css/frontend.css' );
    wp_enqueue_script( $token . '-frontend.min' );

}
add_action( 'admin_enqueue_scripts', 'bt_add_admin_scripts' );

add_action('acf/input/admin_enqueue_scripts', 'bt_acf_admin_enqueue_scripts');
function bt_acf_admin_enqueue_scripts() {
    wp_enqueue_script( 'bt-acf-input-js', get_stylesheet_directory_uri() . '/js/bt-acf-input.js', false, time() );
}
// custom loading on checkout
add_action('wp_head', 'bt_custom_woocommerce_checkout_spinner_blogies', 1000 );
function bt_custom_woocommerce_checkout_spinner_blogies() {
    ?>
    <style>
    .woocommerce form.checkout > .blockUI.blockOverlay,
    .woocommerce form.checkout > .loader {
      background-image:url('/wp-content/themes/flatsome-child/images/output-onlinegiftools.gif') !important;
      background-size: 250px !important;
      z-index: 9999 !important;
      opacity: 0.9 !important;
    }
    .woocommerce form.checkout > .blockUI.blockMsg{
      width: 150px;
      height: 150px;
      z-index: 9999 !important;
      background-image:url('/wp-content/uploads/2021/12/cropped-Green-Society-Logo.png') !important;
      background-size: 150px !important;
      animation: lds-circle 8s cubic-bezier(0, 0.2, 0.8, 1) infinite;
      left: 40% !important;
      top: 38% !important;
      display: block !important;
    }
    .woocommerce form.checkout > .blockUI.blockOverlay:before {
      content: "Placing order.. ﻿Please Hold On";
      position: absolute;
      left: 50%;
      top: 51%;
      display: block !important;
      width: 100%;
      text-align: center;
      transform: translateX(-50%);
      color: #000000;
      font-size: 18px;
      font-weight: bold;
    }
    @keyframes lds-circle {
      0%, 100% {
        animation-timing-function: cubic-bezier(0.5, 0, 1, 0.5);
      }
      0% {
        transform: rotateY(0deg);
      }
      50% {
        transform: rotateY(1800deg);
        animation-timing-function: cubic-bezier(0, 0.5, 0.5, 1);
      }
      100% {
        transform: rotateY(3600deg);
      }
    }
    </style>
    <?php
}
