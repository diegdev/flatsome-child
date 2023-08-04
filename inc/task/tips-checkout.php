<?php 
/**
 * https://green-society.monday.com/boards/1418964603/views/41399451/pulses/4332289022
 */
{
  /**
   * Define
   */
  define('TIPS_CHECKOUT_DEV', false);
}

if(TIPS_CHECKOUT_DEV == true && !isset($_GET['TIPS_CHECKOUT_DEV'])) return;

{
  /**
   * After payment success & Update tip metadata
   */


  function theme_update_tip_meta($order_id) {
    $the_order = wc_get_order( $order_id );
    $fee = $the_order->get_items('fee');
    
    foreach( $the_order->get_items('fee') as $item_id => $item_fee ){
      // The fee name
      $fee_name = $item_fee->get_name();
      // The fee total amount
      $fee_total = $item_fee->get_total();

      if($fee_name != 'Tip') return;
      update_post_meta($order_id, '__customer_tip', $fee_total);
    }
  }
  
  add_action( 'woocommerce_thankyou', function($order_id) {
    if ( ! $order_id ) return;
    theme_update_tip_meta($order_id);
  } );
  
  add_action( 'init', function() {
    if( isset($_GET['order_id']) && isset($_GET['key']) && $_GET['order_id'] ) {
      theme_update_tip_meta($_GET['order_id']);
    }
  } );
}


// {
//   add_filter( 'manage_edit-shop_order_columns', 'theme_custom_shop_order_column', 99 );
//   function theme_custom_shop_order_column($columns) {
//     $reordered_columns = array();
//     // Inserting columns to a specific location
//     foreach( $columns as $key => $column){
//       $reordered_columns[$key] = $column;
//       if( $key ==  'order_status' ){
//         // Inserting after "Status" column
//         $reordered_columns['__customer_tip'] = __( 'Tip','theme');
//       }
//     }
//     $reordered_columns['__customer_tip'] = __( 'Tip','theme');
//     return $reordered_columns;
//   }

//   add_action( 'manage_shop_order_posts_custom_column' , 'theme_custom_orders_list_column_content', 99, 2 );
//   function theme_custom_orders_list_column_content( $column, $post_id ) {
//     switch ( $column ) {
//       case '__customer_tip' :
//         $__customer_tip = get_field('__customer_tip', $post_id);
//         echo ($__customer_tip ? wc_price($__customer_tip) : '__');
//         break; 
//     }
//   }
// }

add_action( 'wpo_wcpdf_after_order_data', function($type, $order) {
  $__customer_tip = get_field('__customer_tip', $order->get_id());
  ?>
  <tr>
    <th>Tip</th>
    <td><?php echo ($__customer_tip ? 'YES' : 'NO'); ?></td>
  </tr>
  <?php
}, 20 , 2 );

add_action( 'woocommerce_review_order_before_order_total', function() {
  global $woocommerce;
  $cartfee = $woocommerce->cart->get_fees();
  $cart_total = (float) $woocommerce->cart->total;

  if(!$cartfee && count($cartfee) <= 0) return;
  $feeTotal = (float) $cartfee['tip']->total;
  $percent = round(($feeTotal / ( $cart_total - $feeTotal )) * 100);
  // print_r($cartfee['tip']);
  ?>
  <tr class="__customer-tip">
    <td><?php echo $cartfee['tip']->name ?></td>
    <td><?php echo wc_price($cartfee['tip']->total) . ' ('. $percent .'%)'; ?></td>
  </tr>
  <script>
    jQuery('tr.fee').each(function() {
      const self = jQuery(this);
      const feeName = jQuery(this).find('th').text();
      if(feeName == 'Tip') {
        self.css('display', 'none');
      }
    })
  </script>
  <?php 
} );

add_action( 'wp_head', function() {
  if(!is_checkout()) return; 
  $tip_amount = WC()->session->get('tip_amount');
  if($tip_amount || $tip_amount === 0 || $tip_amount === "0") return;

  global $woocommerce;  
  $cart_total = $woocommerce->cart->total;
  //$__10percent = theme_get_percent_by_total($cart_total, 10);
  //WC()->session->set('tip_amount', $__10percent);
} );

add_action( 'woocommerce_cart_calculate_fees', 'theme_add_tips', 99, 1);
if ( ! function_exists( 'theme_add_tips' ) ) {
  function theme_add_tips( $cart ) {
    $tip_amount = WC()->session->get('tip_amount');

    if($tip_amount && $tip_amount != 0) {
      $cart_contents_total = $cart->get_cart_contents_total();
      $name      = 'Tip';
      $amount    = (float) $tip_amount;
      $taxable   = false;
      $tax_class = '';
      $cart->add_fee( $name, $amount, $taxable, $tax_class );
    }
  }
}

function theme_ajax_add_tips() {
  // wp_send_json( $_POST );
  WC()->session->set( 'tip_amount', $_POST['data']['tip_amount'] );
  exit(); 
}

add_action( 'wp_ajax_theme_ajax_add_tips', 'theme_ajax_add_tips' );
add_action( 'wp_ajax_nopriv_theme_ajax_add_tips', 'theme_ajax_add_tips' );

function theme_get_percent_by_total($total, $percent) {
  return number_format(($total / 100) * $percent, 2, '.', '');
}

function theme_tips_checkout_form_html() {
  $tips_percent = [5, 10, 15];
  global $woocommerce;  
  $cart_total = $woocommerce->cart->total;
  $tip_amount_ss = WC()->session->get('tip_amount');
  
  if($tip_amount_ss) {
    $cart_total -= (float) $tip_amount_ss;
  }
  ?>
  <div class="tips-checkout">
    <div class="tips-checkout__inner">
      <h4><?php _e('Add tip', 'theme') ?></h4>
      <div class="tips-checkout__box">
        <p><?php _e('Show your support for the team', 'theme') ?></p>
        <div class="tips-checkout__options">
          <ul>
            <li>
              <label>
                <input type="radio" name="tip" value="0" />
                <div>
                  <span>None</span>
                </div>
              </label>
            </li>
            <?php foreach($tips_percent as $index => $num) : ?>
            <?php
            $tip_amount = theme_get_percent_by_total($cart_total, $num);
            $checked = ($tip_amount == $tip_amount_ss) ? 'checked' : '';
            ?>
            <li>
              <label>
                <input type="radio" name="tip" value="<?php echo $tip_amount; ?>" <?php echo $checked; ?> />
                <div>
                  <span><?php echo $num . '%' ?></span>
                  <span><?php echo wc_price($tip_amount); ?></span>
                </div>
              </label>
            </li>
            <?php endforeach; ?>
          </ul>
          <div class="tips-checkout__custom-amount">
            <input 
              name="tip-custom" 
              type="number" 
              min="0" 
              placeholder="<?php _e('Custom tip', 'theme') ?>" 
              value="<?php echo $tip_amount_ss; ?>" />
            <button id="BUTTON_ADD_TIP" class="button" type="submit"><?php _e('Update Tip', 'theme') ?></button>
          </div>
        </div> <!-- .tips-checkout__options -->
      </div>
    </div>
  </div> <!-- .tips-checkout -->
  <?php
}

// add_action('woocommerce_before_checkout_form', 'theme_tips_checkout_form_html');
add_action('wfacp_template_before_payment', 'theme_tips_checkout_form_html');

add_filter( 'woocommerce_update_order_review_fragments', function($fragments) {
  ob_start();
  theme_tips_checkout_form_html();
  $_html = ob_get_clean();
  $fragments['.tips-checkout'] = $_html;
  return $fragments;
}, 999 , 1 );

add_action( 'wp_footer', function() {
  ?>
  <script>
    ;((w, $) => {
      'use strict';

      const ajaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

      const __request = async (data) => {
        return await $.ajax({
          type: 'POST',
          url: ajaxUrl,
          data,
          error: (e) => {
            console.log(e);
          }
        })
      }

      const TipCheckoutAction = () => {
        const addTip = async (e, amount, callback) => {
          const result = await __request({
            action: 'theme_ajax_add_tips',
            data: {
              tip_amount: amount,
            }
          });
          (callback ? callback.call('', result) : '')
        }
        $(document.body).on('tipCheckout:addTip', addTip)
      }

      const TipCheckoutHandleUI = () => {
        const tipBox = $('.tips-checkout__box');
        const tipCustomInput = $('input[name=tip-custom');
        const opts = $('input[name=tip');
        const buttonAddTip = $('button#BUTTON_ADD_TIP');

        opts.on('change', function() {
          tipCustomInput.val(this.value);
          buttonAddTip.trigger('click');
        })

        buttonAddTip.on('click', function(e) {
          e.preventDefault();
          const tipAmount = tipCustomInput.val();

          tipBox.css({
            opacity: '.4',
            pointerEvents: 'none'
          })

          buttonAddTip.text('Update Tip...');

          $('body').trigger('tipCheckout:addTip', [tipAmount, (result) => {
            $(document.body).trigger('update_checkout');
            tipBox.css({
              opacity: '',
              pointerEvents: ''
            });

            buttonAddTip.text('Update Tip');
          }])
        })
      }

      const afterUpdateCart = () => {
        $( document.body ).on( 'updated_checkout', function(e, r) {
          // console.log(e, r);
          TipCheckoutHandleUI();
        });
      }

      $(() => {
        TipCheckoutAction();
        TipCheckoutHandleUI();
        afterUpdateCart();
      })

    })(window, jQuery)
  </script>
  <?php 
} );

add_action( 'wp_head', function() {
  ?>
  <style>
    .tips-checkout {
      display: inline-block;
      margin: 1.5em 0;
      width: 100%;
      /* display: none; */
    }

    .tips-checkout__inner h4 {
      font-size: 1.5em;
      color: black;
      margin-bottom: 0.6em;
      text-transform: uppercase;
      font-weight: bold;
    }

    .tips-checkout__box {
      border: solid 1px #eee;
      border-radius: 3px;
    }

    .tips-checkout__box p {
      padding: 1em 1.5em;
      margin: 0;
    }

    .tips-checkout__options {
      padding: 1.5em;
      background: #efefef;
      border-top: solid 1px #eee;
    }

    .tips-checkout__options ul {
      display: flex;
      flex-wrap: wrap;
      border: solid #ddd;
      border-width: 1px;
      border-radius: 3px;
      overflow: hidden;
    }

    .tips-checkout__options input[name=tip] {
      display: none !important;
    }

    .tips-checkout__options input[name=tip]:checked + div:after {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      border: solid 1px green;
      box-shadow: 0 0 3px -2px green;
    }

    .tips-checkout__options input[name=tip]:checked + div span {
      color: green;
    }

    .tips-checkout__options ul li {
      width: calc(100% / 4);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      background: white;

      -webkit-user-select: none; /* Safari */
      -ms-user-select: none; /* IE 10 and IE 11 */
      user-select: none; /* Standard syntax */
    }

    .tips-checkout__options ul li > label {
      padding: .7em 0;
      line-height: normal;
      display: flex;
      width: 100%;
      height: 100%;
      margin: 0;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .tips-checkout__options ul li div > span {
      display: block;
    }

    .tips-checkout__options ul li div > span:first-child {
      color: black;
    }

    .tips-checkout__options ul li:not(:last-child) {
      border: solid #eee;
      border-width: 0 1px 0 0;
    }

    .tips-checkout__custom-amount {
      margin-top: 1em;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }

    .tips-checkout__custom-amount input[name="tip-custom"] {
      width: 70% !important;
      padding: 10px 20px !important;
      color: black !important;
      border-radius: 3px !important;
      border: solid 1px #dbdbdb;
      line-height: normal !important;
    }

    .tips-checkout__custom-amount button {
      width: calc(30% - 1em);
      background-color: #1d6839 !important;
      line-height: normal;
    }
  </style>
  <?php 
} );