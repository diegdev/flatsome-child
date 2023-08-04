<?php
// woocommerce_add_to_cart
// woocommerce_cart_item_removed

/**
 * Auto apply coupon code based on cart amount and order amount
 */
function auto_apply_first_order_coupon() {
  
    $coupon_code = 'GS20'; 

    $cart = WC()->cart;

    $has_code = $cart->has_discount( $coupon_code );
    $meets_order_amount = $cart->get_cart_contents_total() > 50;
    $has_orders = customer_has_orders();
    $has_sale_items = false;

    // Check if cart contains sale items
    foreach ( $cart->get_cart() as $cart_item ) {
        if ( $cart_item['data']->is_on_sale() ) {
            $has_sale_items = true;
            break;
        }
    }

    if (!$has_code && $meets_order_amount && !$has_sale_items && !$has_orders){
        WC()->cart->apply_coupon( $coupon_code );
    };
  

}
// Apply the coupon if the user ends up on cart or checkout page
add_action( 'woocommerce_before_checkout_form', 'auto_apply_first_order_coupon', 11 );
add_action( 'woocommerce_before_cart', 'auto_apply_first_order_coupon' );


/**
 * Display message for auto applying the coupon code
 */
function display_coupon_auto_apply_message(){
    $coupon_code = "GS20";

    $cart = WC()->cart;
    $has_code = $cart->has_discount( $coupon_code );

    if(customer_has_orders() || !$has_code) return;
    
    echo "<div style='background:#43c670;color:#fff;border-radius:4px;box-shadow:rgba(0, 0, 0, 0.24) 0px 3px 8px;padding:0.5rem;margin-bottom:1rem;text-align:center;'><p style='margin:0;'>Thanks for making your first order with Green Society!<br>We've automatically added coupon code <strong>GS20</strong> to your order for <strong>20% off</strong>!</p><p>Restrictions - does not apply on sale or promotional items</p></div>";
}
add_action('woocommerce_before_checkout_form', 'display_coupon_auto_apply_message', 9);


/**
 * Helpers
 */
function customer_has_orders(){
    $current_user_id = get_current_user_id();
    $order_amount = wc_get_customer_order_count($current_user_id);

    if($order_amount > 0){
        return true;
    } else {
        return false;
    }
}
