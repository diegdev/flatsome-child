<?php 
//add_filter( 'bulk_actions-edit-shop_order', ['WC_XR_Order_Actions', 'add_order_actions'] );

//add_action( 'admin_action_xero_manual_invoice', 'xero_custom_bulk_actions' );
//add_action( 'admin_action_xero_manual_payment', 'xero_custom_bulk_actions' );

// add_action( 'woocommerce_order_action_xero_manual_invoice', array( $this, 'manual_invoice' ) );
// add_action( 'woocommerce_order_action_xero_manual_payment', array( $this, 'manual_payment' ) );

function xero_custom_bulk_actions() {
  if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
  return;

  // var_dump($_REQUEST); die;
  $_action = $_REQUEST['action'];

  // Setup Settings.
  $settings = new WC_XR_Settings();
  $settings->setup_hooks();

  // Setup order actions.
  $order_actions = new WC_XR_Order_Actions( $settings );
  $order_actions->setup_hooks();

  foreach( $_REQUEST['post'] as $order_id ) {
    $order = new WC_Order($order_id);
    if($_action == 'xero_manual_invoice') {
      $order_actions->manual_invoice($order);
    } elseif($_action == 'xero_manual_payment') {
      $order_actions->manual_payment($order);
    }
  }
}

add_action('admin_footer', function() {
  ?>
  <script>
    ((w, $) => {
      'use strict';

      const removeRequireFieldsOrderFilter = () => {
        $('#barcode-scan-form').find('input[required], select[required]').removeAttr('required');
      }

      $(() => {
        removeRequireFieldsOrderFilter();
      });
    })(window, jQuery)
  </script>
  <?php
});
