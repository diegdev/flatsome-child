
<?php 
/**
 * @since Sep 22
 * Mike
 */

/**
 * Style inline
 */
add_action('wp_head', function() {
  ?>
  <style data-src="inc/task/override-template-hooks">
    .woocommerce-mini-cart-item .bt_free_gift_label, 
    .wfacp_mini_cart_items .bt_free_gift_label {
      position: initial !important;
      right: auto !important;
      bottom: 15px;
      /* float: left; */
    }

    ul.product_list_widget li .quantity {
      display: inline-block !important;
    }

    /**
     * Rating count
     */
    .prod-rating-wrap { display: flex;  }
    .prod-rating-wrap .__count { display: none; }
    body:not(.single.single-product) .prod-rating-wrap,
    .related-products-wrapper .prod-rating-wrap { justify-content: center; align-items: center; }
    body:not(.single.single-product) .__count,
    .related-products-wrapper .__count { display: block; font-family: inherit; color: #515151; font-size: 13px; margin-left: 3px; line-height: normal; }
    .widget_recent_reviews .__count { display: none !important; }
    .widget_recent_reviews .prod-rating-wrap { justify-content: start !important; }
    /**
     * End rating count
     */

    /**
     * Checkout google login button
     */
    .__checkout-google-login {
      /* margin-bottom: 1em; */
      background: #4285f4;
      color: white !important;
      width: 50% !important;
      /* margin-right: auto; */
      /* margin-left: 0; */
      margin: 0;
    }

    .__checkout-google-login .um-sso-icon-google {
      background: url(/wp-content/plugins/um-social-login/assets/images/btn_google_dark_normal_ios.svg);
      background-size: cover;
      background-repeat: no-repeat;
      display: inline-block;
      width: 40px;
      height: 40px !important;
      position: absolute;
      left: 1%;
      top: 50%;
      transform: translateY(-50%);
    }

    /**
     * UX swatches fake 
     */
    .ux-swatches-fake {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: .5em;
    }
    .ux-swatches-fake + .ux-swatches {
      display: none !important;
    }
    .ux-swatches-fake .fake-s-item {
      border-radius: 3px;
      /* font-family: Arial; */
      font-size: 12px;
      color: #7b7b7b;
      padding: 2px;
      border: 1px solid #c6c6c6;
      margin: 0 1px 4px;
      cursor: pointer;
      white-space: nowrap;
      width: 100%;
      /* background-color: #046748; */
      /* font-weight: bold; */
      transition: 0.5s;
    }
    .ux-swatches-fake .fake-s-item.selected {
      border-color: #046748;
      box-shadow: 0 0 2px 1px #03a25799;
      background-color: #046748;
      color: #fff;
    }
    .ux-swatches-fake .fake-s-item:hover {
      background: #046738;
      color: #fff;
      border: 1px solid #046738;
    }
    .ux-swatches-fake .fake-s-item.__out-stock {
      opacity: .4;
      text-decoration: line-through;
      pointer-events: none;
    }

    /**
     * Update style product list style
     */
    .product-small .col-inner {
      position: relative;
    }

    .product-small .col-inner .add-to-cart-button {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      height: auto !important;
    }

    .product-small .col-inner .box-text.box-text-products {
      padding-bottom: 40px;
    }

    .product-small .col-inner .add-to-cart-button > a {
      width: 100%;
      background: #046738;
      border-color: #046738;
      color: white;
      border-radius: 0px 0px 7px 7px;
    }
    body.archive main .product-small.box .add_to_cart_button {
      border-radius: 0px 0px 7px 7px !important;
    }

    .related-products-wrapper .product-small .col-inner {
      border: solid 1px #f3f3f3;
      border-radius: 3px;
    }
  </style>
  <?php
});

/**
 * Add total number rating after star(*)
 * Desc task => https://green-society.monday.com/boards/1418964603/pulses/3268816712/posts/1718914411
 */
add_filter('woocommerce_product_get_rating_html', function($html, $rating, $count) {
  global $product;
  if(!$product) return $html;
  if(get_post_type($product->id) != 'product') return $product;

  $comments = get_comments([
    'post_type' => 'product', 
    'post_id' => $product->id
  ]);
  $total = count($comments);
  
  ob_start();
  ?>
  <div class="prod-rating-wrap">
    <div class="__start"><?php echo $html; ?></div>
    <?php echo ($total > 0 ? '<div class="__count" title="total review(s)">('. $total .')</div>' : ''); ?>
  </div><!-- .prod-rating-wrap -->
  <?php 
  return ob_get_clean();
}, 20, 3); 

/**
 * Shows society rewars item label on mini-cart, NOT cart details.
 */
add_filter('woocommerce_widget_cart_item_quantity', function($__html, $cart_item, $cart_item_key) {
  if(isset($cart_item['custom_price']) && isset($cart_item['redeem_point'])):
    return "<span class='bt_free_gift_label'>SOCIETY REWARD</span>" . $__html;
  endif;

  return $__html;
}, 20, 3);

/**
 * Shows society rewards item label on cart details. NOT mini-cart
 */
add_action('woocommerce_after_cart_item_name', 'green_cart_item_society_reward_label', 20, 2);
function green_cart_item_society_reward_label($cart_item, $cart_item_key){
  if(isset($cart_item['custom_price']) && isset($cart_item['redeem_point'])) {
    echo "<span class='bt_free_gift_label'>SOCIETY REWARD</span>";
  }
};

/**
 * Move ux-swatches
 * https://green-society.monday.com/boards/1418964603/pulses/3496501660/posts/1803747875
 */
add_action('wp_head', function() {
  ?>
  <style>
    .ux-swatches-in-loop {
      opacity: 0;
    }

    .text-center .ux-swatches-in-loop .ux-swatch, 
    .text-center .ux-swatch+.ux-swatches__limiter {
      border-radius: 3px;
      font-family: Arial;
      font-size: 12px;
      color: black;
      padding: 2px;
    }

    .box-text .ux-swatches {
      display: flex;
      flex-wrap: nowrap;
    }

    .ux-swatches-in-loop .ux-swatch {
      box-shadow: none;
      border: solid 1px gray;
    }

    .ux-swatches-in-loop .ux-swatch.selected {
      box-shadow: none !important;
      border: solid 1px #078B4D;
    }

    @media(max-width: 414px) {
      .box-text .ux-swatches {

      }

      .text-center .ux-swatches-in-loop .ux-swatch, 
      .text-center .ux-swatch+.ux-swatches__limiter {
        font-size: 12px;
        margin-left: 2px;
        margin-right: 2px;
      }
    }
  </style>
  <script>
    ;((w, $) => {
      'use strict';

      $(() => {
        $('.ux-swatches-in-loop').each(function() {
          let $self = $(this);
          $self.css('opacity', 1);
          $self.parents('.product-small').find('.add-to-cart-button').before($self);
        })
      })
    })(window, jQuery)
  </script>
  <?php
});

// add_action('woocommerce_before_shop_loop_item_title',  function() {
//   global $product;
//   if(!$product->is_type( 'variable' )) return;
//   print_r($product->get_available_variations());
// });

function green_get_variation_first_term_data($variation) {
  $attributes = $variation->get_attributes();
  if( $attributes ){
    foreach ( $attributes as $key => $value) {
        $tmp = explode('-', $key);
        $variation_key =  end($tmp);
        $variation_names[] = ucfirst($variation_key) .' : '. $value;
        $term = get_term_by( 'slug', $value, $variation_key );
        
        return [
          'name' => apply_filters( 'woocommerce_variation_option_name', $term->name ),
          'value' => $value,
          'key' => $variation_key,
          'is_in_stock' => $variation->is_in_stock()
        ];
    }
  }

  return false;
}
function green_render_fake_swatches_html($id) {
  $product = wc_get_product($id);
  if(!$product->is_type('variable')) return;
  echo '<div class="ux-swatches-fake">';
  foreach($product->get_children() as $index => $pid) {
    $variation = wc_get_product($pid);
    $attr_data = green_get_variation_first_term_data($variation);
    
    if($attr_data['key'] != 'pa_weight') continue;
    // echo '<div class="hidden">';
    // print_r($pid);
    // echo '</div>';
    // echo '<div class="hidden">';
    // print_r($variation);
    // echo '</div>';
    $classes = $attr_data['is_in_stock'] ? '' : '__out-stock';
    $single_variation = new WC_Product_Variation($pid);
    $price = $single_variation->get_price();
    $sale_price = $single_variation->get_sale_price() ? $single_variation->get_sale_price() : 0;

    $attributes = $single_variation->get_data()['attributes'];
    $first_key = array_key_first($attributes);
    $value = isset($attributes[$first_key]) ? $attributes[$first_key] : '';
    $weight = $single_variation->get_weight();
    $price_per_unit_value = 0;
    if($weight) {
        $price = $single_variation->get_price();
        $price_per_unit_value = $weight !== 0 ? $price/$weight : 0;
    }
    $price_per_unit_value = number_format((float) $price_per_unit_value, 1, '.', '');

    $sale_price = $single_variation->get_sale_price();
    $regular_price = $single_variation->get_regular_price();

    if($sale_price) {
        $price = number_format((float) $sale_price, 2, '.', '');
        $sale_price = number_format((float) $regular_price, 2, '.', '');
    } else {
        $price = number_format((float) $price, 2, '.', '');
        $sale_price = number_format((float) 0, 2, '.', ''); 
    }
    
    ?>
    <div 
      class="fake-s-item __key-<?php echo $attr_data['key'] ?> <?php echo $classes; ?>" 
      data-value="<?php echo $attr_data['value'] ?>" data-id="<?php echo $id; ?>" data-vid="<?php echo $pid; ?>" data-price="<?php echo $price; ?>" data-unit="<?php echo $price_per_unit_value; ?>" data-sale_price="<?php echo $sale_price ?>">
      <span><?php echo $attr_data['name']; ?></span>
    </div>
    <?php
  }
  echo '</div> <!-- .ux-swatches-fake -->';
}

add_action('wp_footer', function() {
  ?>
  <script>
    ((w, $) => {
      'use strict';

      const fake_swatches_handle = () => {
        $('body').on('click', '.fake-s-item', function() {
          const $el = $(this);
          const $realSwatches = $el.parents('.product').find('.ux-swatches');

          const value = $el.data('value');
          $el.addClass('selected').siblings().removeClass('selected');
          $realSwatches.find(`.ux-swatch[data-value="${ value }"]`).click()
          // console.log(value);
        })
      }

      $(fake_swatches_handle)

    })(window, jQuery);
  </script>
  <?php
});

add_action('flatsome_product_box_after', function() {
  global $product;
  green_render_fake_swatches_html($product->get_id());
});

// add_action('init', function() {
  // if(! isset($_GET['_dev'])) return;
  // $id = 1405124; // Do-si-dos
  // green_render_fake_swatches_html($id);
// });

/*
<a href="?oauthWindow=true&amp;provider=google" title="Sign in with Google" data-redirect-url="?oauthWindow=true&amp;provider=google" class="um-button um-alt um-button-social um-button-google" onclick="um_social_login_oauth_window( this.href,'authWindow', 'width=600,height=600,scrollbars=yes' );return false;">
<i class="um-sso-icon-google"></i>
<span>Sign in with Google</span>
</a>
*/


// function get_available_variations( $return = 'array' ) {
//   $variation_ids        = $this->get_children();
//   $available_variations = array();

//   if ( is_callable( '_prime_post_caches' ) ) {
//     _prime_post_caches( $variation_ids );
//   }

//   foreach ( $variation_ids as $variation_id ) {

//     $variation = wc_get_product( $variation_id );

//     // Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
//     if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
//       continue;
//     }

//     // Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
//     if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $this->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
//       continue;
//     }

//     if ( 'array' === $return ) {
//       $available_variations[] = $this->get_available_variation( $variation );
//     } else {
//       $available_variations[] = $variation;
//     }
//   }

//   if ( 'array' === $return ) {
//     $available_variations = array_values( array_filter( $available_variations ) );
//   }

//   return $available_variations;
// }

// add_action('green/before_login_form', function() {

add_filter( 'flatsome_swatches_cache_enabled', '__return_false' );

add_filter( 'wp_get_attachment_image_attributes', 'green_add_lazy_load', 10, 3 );
function green_add_lazy_load($attr, $attachment, $size) {
  if (!array_key_exists('loading', $attr)) {
    $attr['loading'] = 'lazy';
  }
  return $attr;
}

/**
 * Update position review order posisiton shipping 
 */
add_action('wp_footer', function() {
  ?>
  <script>
    ;((w, $) => {
      'use strict';

      const updateShippingPos = () => {
        setInterval(() => {
          const reviewOrder = $('.wfacp_mini_cart_reviews');
          const cartSubtotal = reviewOrder.find('tr.cart-subtotal');
          const shipping_total_fee = reviewOrder.find('tr.shipping_total_fee');
          // console.log(reviewOrder.length, cartSubtotal.length, shipping_total_fee.length);
          if(reviewOrder.length == 0 || cartSubtotal.length == 0 || shipping_total_fee.length == 0) return;

          if(!cartSubtotal.next().hasClass('shipping_total_fee'))
            cartSubtotal.after(shipping_total_fee);
        }, 100);
      }

      $(updateShippingPos)

    })(window, jQuery);
  </script>
  <style>
    body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr.tax-rate th, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr.tax-rate td, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr.cart-subtotal.tax-rate td, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr.cart-subtotal.tax-rate th, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr:nth-last-child(2) th, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9 tfoot tr:nth-last-child(2) td, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9:not(.wfacp_tax_enabled) tfoot tr.shipping_total_fee th, body .wfacp_form_cart table.shop_table.woocommerce-checkout-review-order-table_layout_9:not(.wfacp_tax_enabled) tfoot tr.shipping_total_fee td {
      padding-bottom: 0 !important;
    }
  </style>
  <?php 
});

