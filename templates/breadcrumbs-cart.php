<?php
defined( 'ABSPATH' ) || exit;
?>
<nav class="breadcrumbs-cart">
  <a href="<?php echo wc_get_cart_url(); ?>">SHOPPING CART</a>
  <span>></span>
  <a style="color:black;" href="<?php echo wc_get_checkout_url(); ?>">CHECKOUT DETAILS</a>
  <span>></span>
  <a style="color:black;" href="#">ORDER COMPLETE</a>
</nav>
