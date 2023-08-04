<?php
defined( 'ABSPATH' ) || exit;
extract($args);
?>
<div class="scan_barcode_footer">
  <table class="table_scan_barcode_footer">
    <tr>
      <td colspan="3">
        <h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>

        <address>
          <?php echo wp_kses_post( $order->get_formatted_billing_address( __( 'N/A', 'woocommerce' ) ) ); ?>

          <?php if ( $order->get_billing_phone() ) : ?>
            <p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
          <?php endif; ?>

          <?php if ( $order->get_billing_email() ) : ?>
            <p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
          <?php endif; ?>
        </address>
      </td>
    </tr>
    <tr>
      <td>
        <button <?php echo $status=='completed'? 'disabled':''; ?> data-order_id="<?php echo $order_id; ?>" class="complete_order button button-primary"><?php echo $status=='completed'? 'Completed':'Complete'; ?></button>
      </td>
      <td>
        <button data-order_id="<?php echo $order_id; ?>" class="print_label_order button button-primary">Print Address Labels</button>
      </td>
      <td>
        <input value="<?php echo $tracking_number; ?>" name="tracking_number" placeholder="Enter tracking number" />
        <button data-order_id="<?php echo $order_id; ?>" class="save_tracking button button-primary">Save</button>
      </td>
    </tr>
  </table>
</div>
