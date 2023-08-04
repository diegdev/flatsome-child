<?php 

// Remove Query Strings from Static Resources

// add_action('init', 'remove_query_strings');

function remove_query_strings() {
   if(!is_admin()) {
       add_filter('script_loader_src', 'remove_query_strings_split', 999);
       add_filter('style_loader_src', 'remove_query_strings_split', 999);
   }
}

function remove_query_strings_split($src){
   $output = preg_split("/(&ver|\?ver)/", $src);
   return $output[0];
}



// This will fix it and removes the Pagespeed warning

add_action('wp_enqueue_scripts', 'be_make_event_listeners_passive');

function be_make_event_listeners_passive(){
    wp_add_inline_script('jquery', 'try{jQuery.event.special.touchstart={setup:function(e,t,s){this.addEventListener("touchstart",s,{passive:!t.includes("noPreventDefault")})}},jQuery.event.special.touchmove={setup:function(e,t,s){this.addEventListener("touchmove",s,{passive:!t.includes("noPreventDefault")})}},jQuery.event.special.wheel={setup:function(e,t,s){this.addEventListener("wheel",s,{passive:!0})}},jQuery.event.special.mousewheel={setup:function(e,t,s){this.addEventListener("mousewheel",s,{passive:!0})}};}catch(e){}');
}




// Order max limit function

add_action( 'woocommerce_check_cart_items', 'required_max_cart_subtotal_amount' );

function required_max_cart_subtotal_amount(){
    $max_amount = 2500;
    $cart_subtotal = WC()->cart->subtotal;
    if( $cart_subtotal > $max_amount  ) {
        wc_add_notice( '<strong>Order limit $2500 reached. Contact support to complete your order.<strong><br><small>support@greensociety.cc</small>', 'error' );
        
        // Don't display proceed to cart button on cart page
        remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
    }
}
add_action('woocommerce_widget_shopping_cart_before_buttons', function(){
    $max_amount = 2500;
    $cart_subtotal = WC()->cart->subtotal;
    if( $cart_subtotal > $max_amount  ) {
        echo '<div class="cart_over_limit_wrapper"><h4>ORDER LIMIT $2500 REACHED</h4><p>Contact support to complete your order.</p><small>support@greensociety.cc</small></div>'; 
        ?>
        <script>
            jQuery(document).ready(function($){
                $('.woocommerce-mini-cart__buttons .button.checkout').css('background-color', '#777').css('pointer-events', 'none');
            });
        </script>
        <?php
    }
});



// Custom Product Addon

// Add custom add on in the green rooms products

// add_action( 'woocommerce_before_add_to_cart_button', 'product_custom_add_on' );

function product_custom_add_on(){
    global $product;
    // if( $product->is_type('variable') ) return;
    $product_id = $product->get_id();

    if ( has_term( 'green-room', 'product_cat', $product_id ) ) {
        ?>
        <div class="product_custom_addon_wrap" style="display: none;">
            <h2>Boveda Packs (Add Ons)</h2>
            <div class="product_addon_selection">
                <select class="custom_add_on_select" name="boveda_packs">
                    <option value="">None</option>
                    <option data-raw-price="3.00" data-price="3" data-price-type="quantity_based" value="3.00" data-label="1x Boveda Pack 4g">1x Boveda Pack 4g (+$3.00) </option>
                    <option data-raw-price="5.00" data-price="5" data-price-type="quantity_based" value="5.00" data-label="2x Boveda Pack 4g">2x Boveda Pack 4g (+$5.00) </option>
                    <option data-raw-price="8.50" data-price="8.5" data-price-type="quantity_based" value="8.50" data-label="4x Boveda Pack 4g">4x Boveda Pack 4g (+$8.50) </option>
                </select>
            </div>
        </div>
        <style>
            .product_custom_addon_wrap{
                display: flex;
                align-items: center;
                flex-wrap: wrap;
            }
            .product_custom_addon_wrap h2{
                font-size: 1.25rem;
                margin-right: 20px;
                margin-bottom: 0;
                width: fit-content;
            }
            .product_addon_selection{
                margin: 10px 0;
            }
            .product_addon_selection select{
                margin: 0;
            }
            .sticky-add-to-cart--active .product_custom_addon_wrap{
                display: none;
            }
            dl.variation dt{
                float: none;
            }

        </style>
        <?php
    }
}


// Add on price in cart item

// add_filter( 'woocommerce_add_cart_item_data', 'product_add_on_cart_item_data', 100, 3 );

function product_add_on_cart_item_data( $cart_item, $product_id, $variation_id ){
    if( isset( $_POST['boveda_packs'] ) && $_POST['boveda_packs'] != "" ) {
        $cart_item['boveda_packs_price'] = (float)$_POST['boveda_packs'];
        if ($cart_item['boveda_packs_price'] == 3){
            $cart_item['boveda_packs_label'] = "1x Boveda Pack 4g (+$3.00)";
        }
        else if ($cart_item['boveda_packs_price'] == 5){
            $cart_item['boveda_packs_label'] = "2x Boveda Pack 4g (+$5.00)";
        }
        else{
            $cart_item['boveda_packs_label'] = "4x Boveda Pack 4g (+$8.50)";
        }


        $pid = ($variation_id ? $variation_id : $product_id);
        $product = wc_get_product($pid);
        $sale_price = $product->get_sale_price();
        $regular_price = $product->get_regular_price();
        $price = 0;
        if ($sale_price > 0){
            $price = $sale_price;
        }
        else{
            $price = $regular_price;
        }
        $new_price = (float)$product->get_price() + $cart_item['boveda_packs_price'];
        $cart_item['boveda_update_price'] = $new_price;
        $cart_item['unique_key'] = md5( microtime() . rand() );

        // $cart_item['data']->set_price( $price + $cart_item['boveda_packs_price'] );
    }
    return $cart_item;
}


// Set conditionally a Product add on item price

// add_action('woocommerce_before_calculate_totals', 'set_cutom_cart_item_price', 50, 1);

function set_cutom_cart_item_price( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    // if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        // return;
    foreach (  $cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['boveda_update_price'] ) ){
            // $updated_price = number_format( ((float)$cart_item['data']->get_price() + (float)$cart_item['boveda_packs_price']), 2, '.', '');
            $cart_item['data']->set_price($cart_item['boveda_update_price']);
            $cart_item['data']->save();
        }
    }
}

// Display product add on value @ Cart

// add_filter( 'woocommerce_get_item_data', 'product_add_on_display_cart', 100, 2 );

function product_add_on_display_cart( $data, $cart_item ) {
    if ( isset( $cart_item['boveda_update_price'] ) ){
        $cart_item['data']->set_price($cart_item['boveda_update_price']);
        echo '<div class="zzzzzzz" style="display: none;">';
        print_r($cart_item['data']);
        echo '</div>';
    }
    if ( isset( $cart_item['boveda_packs_price'] ) && isset( $cart_item['boveda_packs_label'] ) ){
        $data[] = array(
            'name' => 'BOVEDA PACKS',
            'value' => $cart_item['boveda_packs_label']
        );
    }
    return $data;
}

// Save product add on field value into order item meta

add_action( 'woocommerce_add_order_item_meta', 'product_add_on_order_item_meta', 100, 2 );

function product_add_on_order_item_meta( $item_id, $values ) {
    if ( ! empty( $values['boveda_packs_price'] ) && ! empty( $values['boveda_packs_label'] ) ) {
        // wc_add_order_item_meta( $item_id, 'boveda_packs_price', $values['boveda_packs_price'], true );
        wc_add_order_item_meta( $item_id, 'BOVEDA PACKS', $values['boveda_packs_label'], true );
    }
}

// Display product add on field value into order table

// add_filter( 'woocommerce_order_item_product', 'product_add_on_display_order', 100, 2 );

function product_add_on_display_order( $cart_item, $order_item ){
    if( isset( $order_item['boveda_packs_price'] ) && isset( $order_item['boveda_packs_label'] ) ){
        $cart_item['boveda_packs_price'] = $order_item['boveda_packs_price'];
        $cart_item['boveda_packs_label'] = $order_item['boveda_packs_label'];
        $cart_item['boveda_update_price'] = $order_item['boveda_update_price'];
    }
    return $cart_item;
}

// Update cart add on price update

// add_filter('woocommerce_cart_item_price', 'set_cutom_cart_item_price', 50, 1);


// Force cart re-calculation when displaying the mini-cart.

function force_cart_calculation() {
	if ( is_cart() || is_checkout() ) {
		return;
	}

	if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
		define( 'WOOCOMMERCE_CART', true );
	}

	WC()->cart->calculate_totals();
}
// add_action( 'woocommerce_before_mini_cart', __NAMESPACE__ . '\\force_cart_calculation' );

// Cart force calculate

function wpse64458_force_recalculate_wc_totals() {
    // Calculate totals
    WC()->cart->calculate_totals();
    // Save cart to session
    WC()->cart->set_session();
    // Maybe set cart cookies
    WC()->cart->maybe_set_cart_cookies();
}

// add_action( 'woocommerce_before_mini_cart_contents', 'wpse64458_force_recalculate_wc_totals' );
// add_action( 'woocommerce_before_cart_totals', 'wpse64458_force_recalculate_wc_totals' );
// add_action( 'wfacp_mini_cart_before_order_total', 'wpse64458_force_recalculate_wc_totals', 100 );

// Footer custom product add on section changes

add_action( 'wp_footer', 'custom_addon_section' );

function custom_addon_section(){
    ?>
    <script>
        // jQuery(document).ready(function($){
        //   $('.custom_add_on_select').on('change', function(){
        //         let price = 0.0;
        //         if ( $('.product-info .price-wrapper .price ins').length > 0 ){
        //             price = parseFloat( $('.product-info .price-wrapper .price ins .woocommerce-Price-amount.amount').text().replace('$', '') ).toFixed(2);    
        //         }
        //         else{
        //             price = parseFloat( $('.product-info .price-wrapper .price .woocommerce-Price-amount.amount').text().replace('$', '') ).toFixed(2);    
        //         }
        //         let qty = parseFloat($('.quantity input[type=number]').val());
        //         console.log('QTY: '+qty);
        //         if ( $(this).val() ){
        //             let extra_price = parseFloat( $(this).val() );
        //             let updated_price =  parseFloat( (parseFloat(extra_price) + parseFloat(price))*qty ).toFixed(2);
        //             $('.single-product .single_add_to_cart_button > span:nth-child(3)').text( '$' + updated_price );
        //         }
        //         else{
        //             $('.single-product .single_add_to_cart_button > span:nth-child(3)').text( '$' + parseFloat(price * qty).toFixed(2) );
        //         }
        //     });

        //     $('.value-product-id-variation-single-custom .item-product-variation, .product-summary .quantity .button.is-form').click(function(){
        //         setTimeout(function(){
        //             $('.custom_add_on_select').trigger('change');    
        //         }, 200);
                
        //     });
        // });
    </script>
    <?php
}



// Free Shipping Label PLugin custom Code


function theme_freeshipping_message_checkout_page() {
//   if(is_user_logged_in()) return;
  $cartTotal = WC()->cart->get_subtotal(); 
  $freeship = 149;

  if(!$cartTotal || $cartTotal == 0) return;

  $helpIcon = '<svg widht="18px" height="18px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12 9C11.7015 9 11.4344 9.12956 11.2497 9.33882C10.8843 9.75289 10.2523 9.79229 9.83827 9.42683C9.4242 9.06136 9.3848 8.42942 9.75026 8.01535C10.2985 7.3942 11.1038 7 12 7C13.6569 7 15 8.34315 15 10C15 11.3072 14.1647 12.4171 13 12.829V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V12.5C11 11.6284 11.6873 11.112 12.2482 10.9692C12.681 10.859 13 10.4655 13 10C13 9.44772 12.5523 9 12 9ZM12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17H12.01C12.5623 17 13.01 16.5523 13.01 16C13.01 15.4477 12.5623 15 12.01 15H12Z"/> </svg>';

  if($cartTotal >= $freeship) {
    echo '<div class="devnet_fsl-free-shipping qualified-message" style=""><h4 class="title">Congratulations!<br>You have received Free Shipping!'. '<span class="__help-icon-freeship" title="Available in Canada">'. $helpIcon .'</span>' .'</h4></div>';
    return;
  }

  $percent = ($cartTotal / $freeship) * 100;
  ob_start();
  ?>
  <div class="devnet_fsl-free-shipping bar-type-linear __custom-for-nonlogin" style="">
    <h4 class="fsl-title title">
      Free Shipping 
      <?php echo wc_price($freeship) . "+" ?> 
      <span class="__help-icon-freeship" title="Available in Canada"><?php echo $helpIcon; ?></span>
    </h4>

    <div class="fsl-progress-bar progress-bar shine stripes" style="--fsl-percent:60.402684563758; background-color:#ffffff; border-color:#ffffff;">
      <span class="fsl-progress-amount progress-amount" style="width:<?php echo $percent; ?>%; height:12px; background-color:#046738;">
      </span>
    </div>
      <span class="fsl-notice notice">Add
        <span style="color:#13824d;"><?php echo wc_price($freeship - $cartTotal); ?></span>
        For Free Shipping!
      </span>
  </div>
  <?php
  $content = ob_get_clean();
  echo $content;
}

// add_action('woocommerce_widget_shopping_cart_before_buttons', 'theme_freeshipping_message_checkout_page', 8);
add_action('woocommerce_before_mini_cart_contents', 'theme_freeshipping_message_checkout_page', 8);

add_action('wp_footer', function() {
  ?>
  <style>
    .__help-icon-freeship {
      display: inline-block;
      vertical-align: middle;
    }

    .__help-icon-freeship svg {
      fill: #39805a;
    }

    .devnet_fsl-free-shipping .fsl-notice,.devnet_fsl-free-shipping .notice,.devnet_fsl-free-shipping .fsl-title,.devnet_fsl-free-shipping .title {
      width: 100%;
      display: block;
      text-align: center
    }
    
    .devnet_fsl-free-shipping {
      width: 100%;
      margin: 1rem 0 2rem;
      padding: 1rem 2rem;
      box-shadow: 0 0 2rem -1rem #000;
      box-sizing: border-box
    }
    
    .devnet_fsl-free-shipping .fsl-title,.devnet_fsl-free-shipping .title {
      margin: 2rem auto;
      font-size: 1.1em
    }
    
    .devnet_fsl-free-shipping .fsl-notice .woocommerce-Price-amount.amount,.devnet_fsl-free-shipping .notice .woocommerce-Price-amount.amount {
      font-weight: bold
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar,.devnet_fsl-free-shipping .progress-bar {
      width: 100%;
      justify-content: flex-start;
      margin: 1rem 0;
      border: .0625rem solid #000;
      border-radius: .5rem;
      box-shadow: 0 .3rem 1rem -0.5rem #000
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar .fsl-.progress-amount,.devnet_fsl-free-shipping .fsl-.progress-bar .progress-amount,.devnet_fsl-free-shipping .progress-bar .fsl-.progress-amount,.devnet_fsl-free-shipping .progress-bar .progress-amount {
      position: relative;
      display: block;
      border-radius: .5rem
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar span,.devnet_fsl-free-shipping .progress-bar span {
      display: inline-block;
      height: 100%;
      border-radius: 3px;
      box-shadow: 0 1px 0 rgba(255,255,255,.5) inset;
      transition: width .4s ease-in-out
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar.shine span,.devnet_fsl-free-shipping .progress-bar.shine span {
      position: relative
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar.shine span::after,.devnet_fsl-free-shipping .progress-bar.shine span::after {
      content: "";
      opacity: 0;
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background: #fff;
      border-radius: 3px;
      -webkit-animation: fsl-animate-shine 2s ease-out infinite;
      animation: fsl-animate-shine 2s ease-out infinite
    }
    
    .devnet_fsl-free-shipping .fsl-.progress-bar.stripes span,.devnet_fsl-free-shipping .progress-bar.stripes span {
      background-size: 30px 30px;
      background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
      -webkit-animation: fsl-animate-stripes 3s linear infinite;
      animation: fsl-animate-stripes 3s linear infinite;
      margin: 0;
    }
    
    .devnet_fsl-free-shipping.qualified-message .title {
      margin: 0;
      padding: 0
    }
    
    @-webkit-keyframes fsl-animate-stripes {
      0% {
          background-position: 0 0
      }
    
      100% {
          background-position: 60px 0
      }
    }
    
    @keyframes fsl-animate-stripes {
      0% {
          background-position: 0 0
      }
    
      100% {
          background-position: 60px 0
      }
    }
    
    @-webkit-keyframes fsl-animate-shine {
      0% {
          opacity: 0;
          width: 0
      }
    
      50% {
          opacity: .5
      }
    
      100% {
          opacity: 0;
          width: 100%
      }
    }
    
    @keyframes fsl-animate-shine {
      0% {
          opacity: 0;
          width: 0
      }
    
      50% {
          opacity: .5
      }
    
      100% {
          opacity: 0;
          width: 100%
      }
    }
    
    .devnet_fsl-no-shadow {
      border: none;
      box-shadow: none
    }
    
    .devnet_fsl-label {
      display: block !important;
      margin: 1rem auto;
      padding: .3rem .5rem;
      font-size: .8em;
      font-weight: bold;
      text-align: center;
      box-shadow: 0 5px 16px -8px #000
    }
    
    .devnet_fsl-no-animation .shine span.progress-amount {
      -webkit-animation: none;
      animation: none
    }
    
    .devnet_fsl-no-animation .shine span.progress-amount::after {
      -webkit-animation: none;
      animation: none
    }
    
    .summary .devnet_fsl-label {
      max-width: -webkit-max-content;
      max-width: -moz-max-content;
      max-width: max-content;
      margin: inherit;
      margin: .5rem 0 1rem
    }
    
    .devnet_fsl-free-shipping .title,.devnet_fsl-free-shipping .notice {
      color: var(--fsl-text-color)
    }
    
    .fsl-circular-bar-wrapper {
      width: var(--fsl-circle-size);
      height: var(--fsl-circle-size);
      margin: 0 auto 1rem;
      position: relative
    }
    
    .fsl-circular-bar-wrapper:nth-child(3n+1) {
      clear: both
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar {
      width: 100%;
      height: 100%;
      clip: rect(0, var(--fsl-circle-size), var(--fsl-circle-size), var(--fsl-circle-size-half));
      left: 0;
      position: absolute;
      top: 0
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar .fsl-half-circle {
      width: 100%;
      height: 100%;
      color: inherit;
      color: var(--fsl-circle-bar-inner-color, inherit);
      border-color: currentColor;
      border: var(--fsl-circle-size-tenth) solid currentColor;
      border-radius: 50%;
      clip: rect(0, var(--fsl-circle-size-half), var(--fsl-circle-size), 0);
      left: 0;
      position: absolute;
      top: 0
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar .fsl-left-side {
      transform: rotate(var(--fsl-circle-left-rotation))
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar.less-than-50 .fsl-right-side {
      display: none
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar.more-than-50 {
      clip: rect(auto, auto, auto, auto)
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar.more-than-50 .fsl-right-side {
      transform: rotate(180deg)
    }
    
    .fsl-circular-bar-wrapper .fsl-inner-circle {
      background-color: transparent;
      background-color: var(--fsl-circle-bg-color, transparent);
      position: absolute;
      top: var(--fsl-circle-size-tenth);
      left: var(--fsl-circle-size-tenth);
      right: var(--fsl-circle-size-tenth);
      bottom: var(--fsl-circle-size-tenth);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      color: currentColor;
      color: var(--fsl-text-color, currentColor);
      text-align: center;
      padding: var(--fsl-circle-size-tenth);
      cursor: default
    }
    
    .fsl-circular-bar-wrapper .fsl-inner-circle .fsl-svg-icon>*,.fsl-circular-bar-wrapper .fsl-inner-circle .fsl-svg-icon g path {
      fill: var(--fsl-circular-bar-icon-color)
    }
    
    .fsl-circular-bar-wrapper .fsl-inner-circle .fsl-svg-icon>rect {
      fill: transparent
    }
    
    .fsl-circular-bar-wrapper .fsl-circular-bar-background {
      width: 100%;
      height: 100%;
      border: var(--fsl-circle-size-tenth) solid transparent;
      border: var(--fsl-circle-size-tenth) solid var(--fsl-circle-bar-bg-color, transparent);
      border-radius: 50%
    }
    
    .fsl-circular-bar-wrapper.animation.puls .fsl-half-circle {
      -webkit-animation: fsl-animate-invert-puls 2s infinite;
      animation: fsl-animate-invert-puls 2s infinite
    }
    
    @-webkit-keyframes fsl-animate-invert-puls {
      0% {
          filter: invert(0)
      }
    
      50% {
          filter: invert(35%)
      }
    
      100% {
          filter: invert(0)
      }
    }
    
    @keyframes fsl-animate-invert-puls {
      0% {
          filter: invert(0)
      }
    
      50% {
          filter: invert(35%)
      }
    
      100% {
          filter: invert(0)
      }
    }
    
    @-webkit-keyframes fsl-animate-opacity-puls {
      0% {
          opacity: 1
      }
    
      50% {
          opacity: .5
      }
    
      100% {
          opacity: 1
      }
    }
    
    @keyframes fsl-animate-opacity-puls {
      0% {
          opacity: 1
      }
    
      50% {
          opacity: .5
      }
    
      100% {
          opacity: 1
      }
    }
    
    .devnet_fsl-free-shipping.notice-bar {
      position: fixed;
      max-width: 320px;
      margin: 0;
      background-color: #fff;
      z-index: 9999
    }
    
    .devnet_fsl-free-shipping.notice-bar .fsl-close-notice-bar {
      position: absolute;
      top: -12px;
      right: -12px;
      border-radius: 100%;
      background-color: #d3d3d3;
      width: 1rem;
      height: 1rem;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-sizing: content-box;
      cursor: pointer
    }
    
    .devnet_fsl-free-shipping.notice-bar .fsl-close-notice-bar:hover {
      background-color: #a9a9a9
    }
    
    .devnet_fsl-free-shipping.notice-bar.autohide.top-left {
      top: 32px;
      top: var(--fsl-notice-bar-margin-y, 32px);
      left: -100vw;
      -webkit-animation: noticeBarFadeInOutLeft 5s;
      animation: noticeBarFadeInOutLeft 5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.autohide.top-right {
      top: 32px;
      top: var(--fsl-notice-bar-margin-y, 32px);
      right: -100vw;
      -webkit-animation: noticeBarFadeInOutRight 5s;
      animation: noticeBarFadeInOutRight 5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.autohide.bottom-left {
      bottom: 32px;
      bottom: var(--fsl-notice-bar-margin-y, 32px);
      left: -100vw;
      -webkit-animation: noticeBarFadeInOutLeft 5s;
      animation: noticeBarFadeInOutLeft 5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.autohide.bottom-right {
      bottom: 32px;
      bottom: var(--fsl-notice-bar-margin-y, 32px);
      right: -100vw;
      -webkit-animation: noticeBarFadeInOutRight 5s;
      animation: noticeBarFadeInOutRight 5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.hold.top-left {
      top: 32px;
      top: var(--fsl-notice-bar-margin-y, 32px);
      left: 32px;
      left: var(--fsl-notice-bar-margin-x, 32px);
      -webkit-animation: noticeBarFadeInLeft .5s;
      animation: noticeBarFadeInLeft .5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.hold.top-right {
      top: 32px;
      top: var(--fsl-notice-bar-margin-y, 32px);
      right: 32px;
      right: var(--fsl-notice-bar-margin-x, 32px);
      -webkit-animation: noticeBarFadeInRight .5s;
      animation: noticeBarFadeInRight .5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.hold.bottom-left {
      bottom: 32px;
      bottom: var(--fsl-notice-bar-margin-y, 32px);
      left: 32px;
      left: var(--fsl-notice-bar-margin-x, 32px);
      -webkit-animation: noticeBarFadeInLeft .5s;
      animation: noticeBarFadeInLeft .5s
    }
    
    .devnet_fsl-free-shipping.notice-bar.hold.bottom-right {
      bottom: 32px;
      bottom: var(--fsl-notice-bar-margin-y, 32px);
      right: 32px;
      right: var(--fsl-notice-bar-margin-x, 32px);
      -webkit-animation: noticeBarFadeInRight .5s;
      animation: noticeBarFadeInRight .5s
    }
    
    @-webkit-keyframes noticeBarFadeInLeft {
      0% {
          left: -100vw;
          opacity: 0
      }
    
      100% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    }
    
    @keyframes noticeBarFadeInLeft {
      0% {
          left: -100vw;
          opacity: 0
      }
    
      100% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    }
    
    @-webkit-keyframes noticeBarFadeInRight {
      0% {
          right: -100vw;
          opacity: 0
      }
    
      100% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    }
    
    @keyframes noticeBarFadeInRight {
      0% {
          right: -100vw;
          opacity: 0
      }
    
      100% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    }
    
    @-webkit-keyframes noticeBarFadeInOutLeft {
      0% {
          left: -100vw;
          opacity: 0
      }
    
      5% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      90% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      100% {
          left: -100vw;
          opacity: 0
      }
    }
    
    @keyframes noticeBarFadeInOutLeft {
      0% {
          left: -100vw;
          opacity: 0
      }
    
      5% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      90% {
          left: 32px;
          left: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      100% {
          left: -100vw;
          opacity: 0
      }
    }
    
    @-webkit-keyframes noticeBarFadeInOutRight {
      0% {
          right: -100vw;
          opacity: 0
      }
    
      5% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      90% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      100% {
          right: -100vw;
          opacity: 0
      }
    }
    
    @keyframes noticeBarFadeInOutRight {
      0% {
          right: -100vw;
          opacity: 0
      }
    
      5% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      90% {
          right: 32px;
          right: var(--fsl-notice-bar-margin-x, 32px);
          opacity: 1
      }
    
      100% {
          right: -100vw;
          opacity: 0
      }
    }
    
    .devnet_fsl-label-image .fsl-label-image {
      width: 100px !important;
      width: var(--fsl-label-image-width, 100px) !important;
      height: auto !important;
      -o-object-fit: contain !important;
      object-fit: contain !important
    }
    
    .devnet_fsl-label-over-image {
      display: block;
      position: absolute;
      z-index: 5
    }
    
    .devnet_fsl-label-over-image.top-left {
      top: 0px;
      top: var(--fsl-label-margin-y, 0px);
      left: 0px;
      left: var(--fsl-label-margin-x, 0px)
    }
    
    .devnet_fsl-label-over-image.top-right {
      top: 0px;
      top: var(--fsl-label-margin-y, 0px);
      right: 0px;
      right: var(--fsl-label-margin-x, 0px)
    }
    
    .devnet_fsl-label-over-image.bottom-left {
      bottom: 0px;
      bottom: var(--fsl-label-margin-y, 0px);
      left: 0px;
      left: var(--fsl-label-margin-x, 0px)
    }
    
    .devnet_fsl-label-over-image.bottom-right {
      bottom: 0px;
      bottom: var(--fsl-label-margin-y, 0px);
      right: 0px;
      right: var(--fsl-label-margin-x, 0px)
    }
    
    .devnet_fsl-label-over-image .devnet_fsl-label {
      margin: 0
    }

  </style>
  <?php
});


function theme_freeshipping_message_checkout_page_html() {
  global $woocommerce;
  $freeshipping_amount = 149;
  $subtotal = $woocommerce->cart->subtotal;
  $missing = $freeshipping_amount - $subtotal;

  if($missing > 0) {
    $percent_bar = ($subtotal / $freeshipping_amount) * 100;
    ?>
    <div class="devnet_fsl-free-shipping bar-type-linear check_cart_page" style="">
      <h4 class="fsl-title title">
        Free Shipping <?php echo wc_price($freeshipping_amount) . "+" ?>
      </h4>

      <div 
        class="fsl-progress-bar progress-bar shine stripes" 
        style="--fsl-percent:<?php echo $percent_bar ?>; background-color:#ffffff; border-color:#ffffff;">
        <span 
          class="fsl-progress-amount progress-amount" 
          style="width:<?php echo $percent_bar ?>%; height:12px; background-color:#046738;">
        </span>
      </div>
      <span 
        class="fsl-notice notice">
        Add <span style="color:#046738;"><?php echo wc_price($missing); ?></span> For Free Shipping!
      </span>
    </div>
    <?php
  } else {
    ?>
    <div class="devnet_fsl-free-shipping qualified-message" >
      <h4 class="title">Congratulations!<br>You have received Free Shipping!</h4>
    </div>
    <?php
  }
}

add_action('woocommerce_before_cart_totals', 'theme_freeshipping_message_checkout_page_html');
add_action('wfacp_before_form', 'theme_freeshipping_message_checkout_page_html', 1);

// add_filter( 'woocommerce_update_order_review_fragments', function($fragments) {
//   ob_start();
//   theme_freeshipping_message_checkout_page_html();
//   $_html = ob_get_clean();
//   $fragments['.devnet_fsl-free-shipping'] = $_html;
//   return $fragments;
// }, 999 , 1 );


// Product page sticky cart dropdown function added

add_action('wp_footer', function(){ 
    if (is_product()){
        global $product;
        if ( $product->is_type( 'variable' ) ) {
        ?>
        <script>
            jQuery(document).ready(function($){
                let sticky_variant = "<select class='pa_weight custom_sticky_variant_select'>" + $('.variations #pa_weight').html() + "</select>";
                $('.sticky-variant-wrapper').html(sticky_variant);
                $('.sticky-variant-wrapper .custom_sticky_variant_select > option:first-child').remove();
                
                
                let flag1 = false;
                let flag2 = false;
                
                $(document).on('click', '.item-product-variation', function(){
                    if(flag2)
                    {
                        flag2 = false;
                        return;
                    }
                    let term = $(this).attr('data-value');
                    flag1 = true;
                    $('.custom_sticky_variant_select').val(term).trigger('change');
                    let swatch_triggered = true;
                    
                });
                
                $(document).on('change', '.custom_sticky_variant_select', function(){
                    if(flag1)
                    {
                        flag1 = false;
                        return;
                    }
                    let term = $(this).val();
                    flag2= true;
                    $('.item-product-variation[data-value="' + term + '"]').click();
                });
            });
        </script>
        <style>
            .sticky-add-to-cart--active .quantity{
                display: none !important;
            }
        </style>
        <?php
        }
        if ( $product->is_type( 'mix-and-match' ) ) {
        ?>
        <style>
            .sticky-add-to-cart-wrapper .sticky-add-to-cart--active{
                display: block;
                position: relative;
                z-index: auto !important;
            }
            .has-sticky-product-cart{
                padding-bottom: 0 !important;
            }
            .sticky-add-to-cart--active .sticky-add-to-cart__product{
                display: none !important;
            }
        </style>
        <?php    
        }
    }
});

