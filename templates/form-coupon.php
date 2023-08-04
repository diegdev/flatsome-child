<?php
defined( 'ABSPATH' ) || exit;
?>
<form class="checkout_coupon mb-0" method="post">
  <div class="coupon">
    <h3 class="widget-title"><?php echo get_flatsome_icon( 'icon-tag' ); ?> <?php esc_html_e( 'Coupon', 'woocommerce' ); ?></h3><input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <input type="submit" class="is-form expand" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>" />
    <?php do_action( 'woocommerce_cart_coupon' ); ?>
  </div>
</form>
