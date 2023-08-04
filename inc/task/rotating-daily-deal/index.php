<?php 
/**
 * Rotating Daily Deal
 * task: https://green-society.monday.com/boards/1418964603/views/41399451/pulses/3275525827
 */

{
  /**
   * inc
   */
  require_once(__DIR__ . '/options.php');
  require_once(__DIR__ . '/helpers.php');
}

function green_get_deal_by_day($weekday) {
  return get_field('rdd_' . strtolower($weekday), 'option');
}

function green_is_product_daily_deal($product_id = 0) {
  $weekday = current_time('l');
  $productDealByDay = green_get_deal_by_day($weekday);
  // var_dump($productDealByDay['deal']);

  $products_daily_deal = [];
  if($productDealByDay['products']) {
    foreach($productDealByDay['products'] as $k => $_p) {
      array_push($products_daily_deal, $_p->ID);
    }
  } else {
    return false;
  }

  if(in_array($product_id, $products_daily_deal) && $productDealByDay['enable'] == true ) {
    return $productDealByDay['deal'];
  } else {
    return false;
  }
}

add_action('init', function() {
  // green_is_product_daily_deal();
});

/**
 * deal for product type variation 
 */
add_filter('woocommerce_product_variation_get_sale_price', 'green_custom_sale_price_variation', 10, 2);
function green_custom_sale_price_variation($sale_price, $product) {
  // echo ' / ' . $product->get_parent_id() . ' / ';
  $enable = get_field('rdd_enable', 'option');
  $parent_product_id = $product->get_parent_id();

  // is disable daily deal variation product
  $disable_daily_deal = get_post_meta($product->get_id(), 'disable_daily_deal', true);
  if($disable_daily_deal == true) {
    return $sale_price;
  }

  $result = green_is_product_daily_deal($parent_product_id);
  if($result == false && empty($result)) return $sale_price;

  $deal = 1 - ($result / 100);
  // var_dump($sale_price);
  // echo $product->get_price() . '/' . $product->get_regular_price();
  
  
  if(!$sale_price) {
    $sale_price = $product->get_regular_price('edit') * $deal; 
  }
  // var_dump($sale_price);
  
  // $product->daily_deal_status = true; 

  // if($product->get_parent_id() == 1583797) {
  //   $sale_price = $product->get_regular_price() * 0.5; 
  // }

  return $sale_price;
}

/**
 * Sale Price by Qty
 */
add_action('woocommerce_product_variation_get_sale_price', function($sale_price, $product) {

  // var_dump($sale_price);

  return $sale_price;
}, 999, 2);

add_filter('woocommerce_product_get_sale_price', 'green_custom_sale_price', 10, 2);
// add_filter('woocommerce_product_variation_get_sale_price', 'green_custom_sale_price_for_category', 10, 2);

function green_custom_sale_price($sale_price, $product) {
  $enable = get_field('rdd_enable', 'option');
  if($enable != true) return $sale_price;

  if($product->is_type('variable')) {
    $result = green_is_product_daily_deal($product->get_id());

    if($result == false && empty($result)) return $sale_price;

    $product->daily_deal_status = true;  
    $product->daily_deal_percent = $result;  
    return $sale_price;
  }

  if(! $product->is_type('simple')) {
    return $sale_price;
  }
  // $product_apply_deal = 1644862;
  // $deal = 0.9; // 10%
  
  $result = green_is_product_daily_deal($product->get_id());
  if($result == false && empty($result)) return $sale_price;

  $deal = 1 - ($result / 100);
  $sale_price = $product->get_regular_price() * $deal; 

  $product->daily_deal_status = true;  
  $product->daily_deal_percent = $result;
  // if($product->get_id() == $product_apply_deal) {
  //   $sale_price = $product->get_regular_price() * $deal; 
  // }
 
  return $sale_price;
}

// add_filter('woocommerce_product_get_regular_price', 'green_custom_dynamic_regular_price', 10, 2);
// add_filter('woocommerce_product_variation_get_regular_price', 'green_custom_dynamic_regular_price', 10, 2);
function green_custom_dynamic_regular_price($regular_price, $product) {
  // var_dump($product->get_sale_price('edit'));
  // return $regular_price;

  if( empty($regular_price) || $regular_price == 0 )
    return $product->get_price();
  else
    return $regular_price;
}

// Displayed formatted regular price + sale price
// add_filter( 'woocommerce_get_price_html', 'green_custom_dynamic_sale_price_html', 20, 2 );
function green_custom_dynamic_sale_price_html($price_html, $product) {
  // if(!$product->is_type('simple')) return $price_html;

  if($product->is_type('variable')) { 
    
  }

  if( $product->is_type('simple') ) {

    if( $product->get_sale_price() && $product->get_sale_price() < $product->get_regular_price() ) {
      $price_html = wc_format_sale_price( 
        wc_get_price_to_display($product, array('price' => $product->get_regular_price())), 
        wc_get_price_to_display($product, array('price' => $product->get_sale_price())) 
        ) . $product->get_price_suffix();
    }
  }

  return $price_html;
}

add_action('woocommerce_product_get_price', 'green_change_price_regular_member', 10, 2);
add_action('woocommerce_product_variation_get_price', 'green_change_price_regular_member', 10, 2);
function green_change_price_regular_member($price, $product) {
  if($product->get_sale_price()) {
    return $product->get_sale_price();
  }
  return $price;
}



// add_filter('woocommerce_add_cart_item', function($cart_item_data) {
//   // echo json_encode($cart_item_data);

//   // if(isset($cart_item_data['before-dynamic-pricing'])) {
//   //   $cart_item_data['addons_sale_price_before_calc'] = number_format((float) $cart_item_data['before-dynamic-pricing'], 2, '.', '');
//   // } 
//   $cart_item_data['addons_sale_price_before_calc'] = 1; 
//   return $cart_item_data;
// }, 999);

add_action('woocommerce_single_product_summary', 'green_count_down_daily_deal_html', 11);
function green_count_down_daily_deal_html() {
  global $product;
  if(empty($product->daily_deal_status)) return;
  // echo $product->daily_deal_status . ' --- ' . $product->get_id();
  $now = current_time('Y-m-d');
  $next_date = new DateTime("$now + 1 day");
  $next_date_format = $next_date->format('Y-m-d');
  $deal_percent = $product->daily_deal_percent;
  // $next_date = date('Y-m-d', strtotime(' +1 day'));
  // print_r($product);
  ?>
  <div 
    class="__daily-deal-countdown" 
    data-deal-percent="<?php echo $deal_percent; ?>"
    data-now="<?php echo current_time('Y-m-d H:i:s'); ?>"
    data-end-date="<?php echo $next_date_format; ?> 00:00:00"></div>
  <?php
}

add_action('wp_footer', function() {
  ?>
  <style>
    .__daily-deal-countdown {
      color: #fff;
      text-align: center;
      margin-bottom: 1em;
      font-size: 1.3em;
      border-radius: 4px;
      box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
      background: rgb(76, 171, 93);
      background: linear-gradient(90deg, rgb(76, 171, 93) 0%, rgb(78, 194, 83) 50%, rgb(76, 171, 93) 100%);
    }
    .__daily-deal-countdown p {
      font-size: 1rem;
    }
  </style>
  <script>
    ((w, $) => {
      'use strict';

      const countDownHandle = (countDownDate, _now, elem) => {
        var s = 1;
        let x = setInterval(function() {

          // Get today's date and time
          let now = new Date(_now).getTime();
          now = new Date(now + s*1000);

          // Find the distance between now and the count down date
          let distance = countDownDate - now;

          // Time calculations for days, hours, minutes and seconds
          let days = Math.floor(distance / (1000 * 60 * 60 * 24));
          let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          let seconds = Math.floor((distance % (1000 * 60)) / 1000);

          // Display the result in the element with id="demo"
          elem.innerHTML = `<p>Get 10% off this daily deal, ends ${hours}h ${minutes}m ${seconds}s</p>`;

          // If the count down is finished, write some text
          if (distance < 0) {
            clearInterval(x);
            elem.innerHTML = "EXPIRED";
          }

          s += 1
        }, 1000);

        return x;
      }

      const dailyDealCountdown = () => {
        if($('.__daily-deal-countdown').length == 0) return;

        $('.__daily-deal-countdown').each(function() {
          const self = this;
          const now = $(self).data('now');
          const endDate = $(self).data('end-date');
          
          countDownHandle(
            new Date(endDate).getTime(), 
            now,
            self
          );
        })
      }

      /**
       * DOM Ready
       */
      $(() => {
        dailyDealCountdown();
      })

    })(window, jQuery);
  </script>
  <?php
});

/**
 * Backend 
 */

/**
 * Add custom field on/off each variation product
 */
add_action('woocommerce_variation_options', function($loop, $variation_data, $variation) {
  ob_start();
  woocommerce_wp_checkbox([
    'id' => 'disable_daily_deal[' . $loop . ']',
    'label' => __('Disable Daily Deal', 'green'),
    'wrapper_class' => '__custom-field-tips',
    'value' => get_post_meta($variation->ID, 'disable_daily_deal', true)
  ]);
  $content = ob_get_clean();

  $array_replace = [
    '<p' => '<span ',
    '</p>' => '</span>',
  ];

  echo str_replace(array_keys($array_replace), array_values($array_replace), $content);
}, 10, 3);

/**
 * Save custom field
 */
add_action('woocommerce_save_product_variation', function($variation_id, $i) {
  $custom_field = $_POST['disable_daily_deal'][$i];
  if (isset($custom_field)) 
    update_post_meta($variation_id, 'disable_daily_deal', esc_attr($custom_field));
}, 10, 2);

/**
 * Store custom field value into variation data
 */
add_filter('woocommerce_available_variation', function($variations) {
  $variations['disable_daily_deal'] = '<div class="woocommerce_custom_field">Disable Daily Deal: <span>' . get_post_meta( $variations[ 'variation_id' ], 'disable_daily_deal', true ) . '</span></div>';
  return $variations;
}, 10);

add_action('admin_footer', function() {
  ?>
  <style>
    .__custom-field-tips {
      display: inline-block; 
      vertical-align: top; 
      width: auto; 
      padding: 4px 1em 2px 0;
    }
    .__custom-field-tips label {
      padding: 0 !important;
    }
  </style>
  <?php
});
/**
 * End backend
 */

/**
 * Fix Cart Price
 */

// add_filter('woocommerce_cart_item_price', function($price, $cart_item, $cart_item_key) {
//   /**
//    * Addons
//    */
//   if(
//     (isset($cart_item['addons']) && count($cart_item['addons']) > 0) && 
//     isset($cart_item['before-dynamic-pricing'])
//   ) {
//     // print_r($cart_item);
//     $price_num = number_format((float) $cart_item['before-dynamic-pricing'], 2, '.', '');
//     foreach($cart_item['addons'] as $index => $a) {
//       $price_num += (float) $a['price'];
//     }

//     return wc_price($price_num);
//   }

//   return $price;
// }, 999, 3);

// add_action('woocommerce_before_calculate_totals', function($cart_object) {
//   if ( is_admin() && ! defined( 'DOING_AJAX' ) )
//     return;

//   if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
//     return;

//   // Loop through cart items
//   foreach ( $cart_object->cart_contents as $hash => $cart_item ) {
//     $cart_item['data']->price = 1;
//     continue;

//     /**
//      * Addons
//      */
//     if(
//       (isset($cart_item['addons']) && count($cart_item['addons']) > 0) && 
//       isset($cart_item['before-dynamic-pricing'])
//     ) {
//       // print_r($cart_item);
//       $price_num = number_format((float) $cart_item['before-dynamic-pricing'], 2, '.', '');
//       foreach($cart_item['addons'] as $index => $a) {
//         $price_num += (float) $a['price'];
//       }
      
//       // $cart_item['data']->set_price( 1 );
//       // return wc_price($price_num);
//       // print_r(json_encode($cart_item));
//     }
//   }

// }, 99999);

// add_action('woocommerce_add_to_cart', function() {
//   remove_filter('woocommerce_product_variation_get_sale_price', 'green_custom_sale_price_variation', 10);
// });

// add_filter( 'woocommerce_product_addons_update_product_price', function($updated_product_prices, $cart_item_data, $prices) {
//   if(isset($cart_item_data['before-dynamic-pricing'])) {
//     $price_num = number_format((float) $cart_item_data['before-dynamic-pricing'], 2, '.', '');
//     $updated_product_prices['price'] = ($price_num + $updated_product_prices['sale_price']);
//     $updated_product_prices['regular_price'] = ($price_num + $updated_product_prices['sale_price']);
//   }
//   return $updated_product_prices;
// }, 20, 3 );

add_filter('woocommerce_get_cart_item_from_session', function($cart_item, $values) {
  // echo json_encode($cart_item);

  if(isset($cart_item['custom_price']) && isset($cart_item['redeem_point'])) {
    // var_dump($cart_item);
    // echo json_encode($cart_item);
    $cart_item['data']->set_price(0);
    $cart_item['data']->set_sale_price(0);
    return $cart_item;
  }

  if(isset($cart_item['before-dynamic-pricing'])) {
    $price_num = number_format((float) $cart_item['before-dynamic-pricing'], 2, '.', '');

    // if(isset($cart_item['addons']) && count($cart_item['addons']) > 0) {
    //   foreach($cart_item['addons'] as $index => $a) {
    //     $price_num += (float) $a['price'];
    //   }
    // }
    
    $cart_item['data']->set_price($price_num);
    $cart_item['data']->set_sale_price($price_num);
  }
  return $cart_item;
}, 20, 2);

/**
 * Fix Dynamic Pricing
 */
add_filter('wc_dynamic_pricing_apply_cart_item_adjustment', function($adjusted_price, $cart_item_key, $original_price, $module) {
  return $adjusted_price;
}, 20, 4);
/**
 * End fix dynamic pricing
 */

/**
 * End fix cart price
 */

add_action('wp_footer', function() {
  ?>
  <script>
    ((w, $) => {
      'use strict';

      /**
       * Product variation update
       */
      const pVariationUpdate = function(e) {
        console.log(this, e);
      }

      /**
       * DOM ready
       */
      $(() => {
        // $('.variations_form').on('woocommerce_variation_select_change', pVariationUpdate );
      })

    })(window, jQuery);
  </script>
  <?php
});