<?php
/**
 * Dynamic pricing func
 *
 */

{
  /**
   * Dynamic pricing custom PHP
   * Custom feature
   */

  function isProductDynamicQty($product_id) {
    $settings = get_post_meta($product_id, '_pricing_rules', true);
    if(empty($settings) || count($settings) <= 0) return false;
    $firstSettings = array_values($settings)[0];

    $conditions_type = $firstSettings['conditions_type'];
    $collector_type = $firstSettings['collector']['type'];
    $mode = $firstSettings['mode'];
    $date_from = strtotime($firstSettings['date_from']);
    $date_to = strtotime($firstSettings['date_to']);
    $rules = $firstSettings['rules'];

    $current_date = strtotime(current_time('mysql'));
    $pass_time = (
      (($date_from <= $current_date) && empty($firstSettings['date_to'])) ||
      (($date_from <= $current_date) && ($current_date <= $date_to))
    );

    if(
      $conditions_type == 'all' &&
      $collector_type == 'product' &&
      $mode == 'continuous' &&
      $pass_time == true &&
      count($rules) > 0
    ) {
      return [
        'dynamic_qty' => true,
        'settings' => $settings,
        'pass_settings' => $firstSettings,
        'pass_rules' => $rules,
      ];
    }

    return false;
  }

  function DP__qtyNumText($from, $to) {
    if($from == $to) {
      return $from;
    } else {
      return $to ? "{$from} - {$to}" : "{$from}+";
    }
  }

  function DP__amountText($amount = 0, $type = 'percentage_discount') {
    switch(trim($type)) {
      case 'percentage_discount':
        return "save {$amount}%";
        break;
      case 'fixed_price':
        return 'fixed price';
        break;
      default:
        return 'save $' . $amount . ' each';
    }
  }

  add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    $dynamicQty = isProductDynamicQty($product->get_id());
    // var_dump($dynamicQty); die;
    if(empty($dynamicQty) || $dynamicQty == false) return;
    $pass_settings = $dynamicQty['pass_settings'];
    $rules = $dynamicQty['pass_rules'];
    ?>
    <div class="dynamic-pricing-qty-options" >

      <ul class="qty-options">
        <?php foreach($rules as $index => $r) :
          $label_template = 'Buy {%QTY_NUM%} {%AMOUNT%}';
          $replace_map = [
            '{%QTY_NUM%}' => DP__qtyNumText($r['from'], $r['to']),
            '{%AMOUNT%}' => DP__amountText($r['amount'], $r['type']),
          ];
          // var_dump($replace_map);
        ?>
        <li class="qty-opt-item">
          <label>
            <span>
              <input
                type="radio"
                name="__dynamic_pricing_qty"
                data-qty-from="<?php echo $r['from']; ?>"
                data-qty-to="<?php echo $r['to']; ?>"
                data-type="<?php echo $r['type']; ?>"
                data-amount="<?php echo $r['amount']; ?>"
                value="<?php echo $r['from']; ?>" />
              <?php echo str_replace(array_keys($replace_map), array_values($replace_map), $label_template); ?>
            </span>
            <span class="__price-per">_</span>
          </label>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="__subtotal">
        <h4><?php _e('Subtotal', 'green') ?></h4>
        <div class="__subtotal--price">_</div>
      </div>
    </div>
    <?php
    // var_dump($rules);
  }, 30);

  /**
   * Script
   */
  add_action('wp_head', function() {
    ?>
    <script>
      ((w, $) => {
        'use strict';

        const dynamicPricingCaclPrice_Func = () => {
          const dynamicQty = $('.dynamic-pricing-qty-options');
          if(dynamicQty.length <= 0) return;

          $('body').addClass('__product-has-dynamic-pricing');

          const updatePricePerOpt = (priceNumber, qtyNumber) => {
            let activeOptElem = null;
            $('.dynamic-pricing-qty-options input[name=__dynamic_pricing_qty]').each((_index, elem) => {
              // console.log(elem, priceNumber);
              let customPrice = parseFloat(priceNumber);
              const { amount, qtyFrom, qtyTo, type } = elem.dataset;

              if(parseInt(qtyNumber) >= parseFloat(qtyFrom)) {
                activeOptElem = elem;
              }

              let _amount = parseFloat(amount);

              switch(type) {
                case 'percentage_discount':

                  if(_amount > 0) {
                    let sale = (customPrice / 100) * _amount;
                    customPrice = customPrice - sale;
                  }
                  break;

                case 'price_discount':
                  if(_amount > 0) {
                    customPrice -= _amount;
                  }
                  break;

                case 'fixed_price':
                  customPrice = _amount
                  break;

                default:
              }

              elem.dataset.currentprice = customPrice;
              $(elem)
                .parents('li.qty-opt-item')
                .find('.__price-per')
                .html(`$${parseFloat(customPrice).toFixed(2)} each`);
            })

            activeOpt_Func(activeOptElem);
            calcSubtotal(parseInt(qtyNumber), activeOptElem);
          }

          // console.log($('.wc-pao-addon-field.wc-pao-addon-select').length);
          $(w).on('load', () => {
            if($('.wc-pao-addon-field.wc-pao-addon-select').length) {
              $('.wc-pao-addon-field.wc-pao-addon-select').trigger('change');
            }
          })

          $('.cart:not(.cart_group)').on('updated_addons', (e) => {
            if($('#product-addons-total').find('.wc-pao-subtotal-line .amount').length > 0){

              setTimeout(() => {
                const basePriceTotal = $('#product-addons-total').find('.wc-pao-subtotal-line .amount').text();
                const priceNumberTotal = parseFloat(basePriceTotal.replace(/,/g, '').replace('$', ''));
                const qtyNumber = parseInt($('.quantity input[name=quantity]').val());

                $('input[name=before-dynamic-pricing]').remove();
                $('form.cart').append(`<input type="hidden" name="before-dynamic-pricing" value="${ priceNumberTotal / qtyNumber }" />`);

                updatePricePerOpt(priceNumberTotal / qtyNumber, qtyNumber);
              }, 35);

            }
          })

          $(".variations_form").on("woocommerce_variation_select_change", (e) => {
            setTimeout(() => {
              $('.wc-pao-addon-field.wc-pao-addon-select').trigger('change');
            }, 100);
          });

          const findDynamicQtyOptActive = (qty) => {
            let activeInputElem = null;
            $(`input[name=__dynamic_pricing_qty]`).each((_index, input) => {
              const { amount, qtyFrom, qtyTo, type } = input.dataset;
              let qtyFromNum = parseFloat(qtyFrom);
              let qtyToNum = parseFloat(qtyTo);

              if(qty >= qtyFrom) {
                activeInputElem = input;
              }
            })
            return activeInputElem;
          }

          const activeOpt_Func = (elem) => {
            $(`input[name=__dynamic_pricing_qty]`).prop('checked', false);
            $('.dynamic-pricing-qty-options .qty-opt-item').removeClass('__active');

            if(elem) {
              $(elem)
                .prop('checked', true)
                .parents('li.qty-opt-item')
                .addClass('__active');
            }
          }

          const calcSubtotal = (qty, activeOpt) => {
            const $subtotalElem = $('.dynamic-pricing-qty-options .__subtotal--price')
            if(qty && activeOpt) {
              let currentPrice = activeOpt.dataset?.currentprice;
              let subtotal = parseFloat(currentPrice) * parseInt(qty);
              let priceFormat = `$${ parseFloat(subtotal).toFixed(2) }`
              $subtotalElem.html(priceFormat);
              $('body').trigger('dynamic_pricing:subtotal_price_update', [priceFormat]);
            } else {
              $subtotalElem.empty();
            }
          }

          $('body').on('change', 'input[name=__dynamic_pricing_qty]', function(e) {
            let qty = this.value;
            $('.quantity input[name=quantity]').val(qty).trigger('change');
          })

          $('body').on('change', '.quantity input[name=quantity]', function(e) {
            let qty = this.value;
            let activeOpt = findDynamicQtyOptActive(parseInt(qty));
            activeOpt_Func(activeOpt);
            calcSubtotal(qty, activeOpt)
          })

          $('body').on('dynamic_pricing:subtotal_price_update', (e, price) => {
            $('button.single_add_to_cart_button').html(`<span>Add to cart</span><span class="divider">|</span><span>${ price }</span>`);
          })
        }

        const updateQtyDynamicPricingMiniCart_Func = () => {
          $('body').on('change', 'input.__dynamic_pricing_qty_field--mini-cart', async e => {
            let value = e.target.value;
            const { cartItemKey, qtyFrom } = e.target.dataset;

            $('.widget_shopping_cart_content').addClass('__handling')

            const result = await $.ajax({
              type: 'POST',
              url: '<?php echo admin_url('admin-ajax.php') ?>',
              data: {
                action: 'green_update_product_qty_in_cart',
                p_key: cartItemKey,
                qty: qtyFrom,
              },
              error: (e) => {
                console.log(e)
              }
            });

            /**
             * trigger refresh mini cart
             */

            // $('.widget_shopping_cart_content').removeClass('__handling');
            $(document.body).trigger('wc_fragment_refresh');
            return;

            // const { fragments } = result;
            // jQuery.each(fragments, (selector, content) => {
            //   const _selector = $(selector);
            //   _selector.after(content);
            //   _selector.remove();
            // })
          })
        }

        $(() => {
          dynamicPricingCaclPrice_Func();
          updateQtyDynamicPricingMiniCart_Func();
        });
      })(window, jQuery);
    </script>
    <?php
  });

  add_action('wp_head', function() {
    ?>
    <style>
      *.__handling {
        opacity: .4;
        pointer-events: none;
      }

      .dynamic-pricing-qty-options {
        margin-bottom: 1.5em;
        font-family: Arial;
      }
      .dynamic-pricing-qty-options ul.qty-options {
        margin: 0;
        padding: 0;
        border: solid #eee;
        border-width: 1px;
        border-radius: 3px;
        margin-bottom: 1em;
      }
      .dynamic-pricing-qty-options ul.qty-options li {
        list-style: none;
        border-bottom: solid 1px #eee;
        margin: 0;
      }
      .dynamic-pricing-qty-options ul.qty-options li.__active {
        background: #f5f5f5;
      }
      .dynamic-pricing-qty-options ul.qty-options li.__active label {
        font-weight: bold;
      }
      .dynamic-pricing-qty-options ul.qty-options li label {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        margin: 0;
        cursor: pointer;
        font-family: Arial;
        font-weight: normal;
      }
      .dynamic-pricing-qty-options ul.qty-options input[name=__dynamic_pricing_qty] {
        margin: 0 6px 0 0;
      }

      .dynamic-pricing-qty-options .__subtotal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
      }

      .dynamic-pricing-qty-options .__subtotal h4 {
        font-family: Arial;
        color: black;
        font-weight: bold;
        margin: 0;
      }

      .dynamic-pricing-qty-options .__subtotal .__subtotal--price {
        font-family: Arial;
        color: black;
        font-weight: bold;
        font-size: 1.5em;
      }

      body.__product-has-dynamic-pricing #product-addons-total {
        display: none;
      }

      .sticky-add-to-cart.sticky-add-to-cart--active .dynamic-pricing-qty-options {
        display: none;
      }

      /** Minicart style */
      .dynamic-pricing-qty-options--mini-cart {
        font-family: Arial;
        font-size: 13px;
        width: calc(100% + 75px);
        margin-left: -75px;
        margin-top: 10px;
        margin-bottom: 6px;
      }

      .dynamic-pricing-qty-options--mini-cart .qty-options--mini-cart {
        margin: 0;
        padding: 0;
        background: white;
        border-radius: 3px;
        border: solid 1px #bad7b8;
        padding: 4px 10px;
      }

      .qty-opt-item--mini-cart {
        width: 100%;
        padding: 5px 0px !important;
        margin: 0 !important;
        min-height: auto !important;
      }

      .qty-opt-item--mini-cart label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: normal;
        margin-bottom: 0;
        padding: 3px 0;
      }

      .qty-opt-item--mini-cart label input.__dynamic_pricing_qty_field--mini-cart {
        margin: 0 6px 0 0;
        vertical-align: text-top;
      }

      .off-canvas-right .mfp-content,
      .off-canvas-left .mfp-content {
        background-color: rgb(249 249 249);
      }

      /** Other dev update CSS */
      .dynamic-pricing-qty-options {
        max-width: 500px;
      }
      .dynamic-pricing-qty-options ul.qty-options {
        border-radius: 7px;
      }
      .dynamic-pricing-qty-options ul.qty-options li {
        transition: 0.5s;
      }
      .dynamic-pricing-qty-options ul.qty-options li.__active {
        background-color: #046738;
      }
      .dynamic-pricing-qty-options ul.qty-options li.__active label {
        color: #fff;
      }
      .dynamic-pricing-qty-options--mini-cart {
        font-size: 12px;
        width: calc(100% + 100px);
      }
      .dynamic-pricing-qty-options--mini-cart .qty-options--mini-cart {
        border-radius: 7px;
      }
      /** End other dev update CSS */

      /** Jeff Yun CSS
       Media queries for mobile view
      */
      @media all and (max-width: 850px){
          #cart-popup .quantity {
          font-size: 0.77rem;
        }

          .dynamic-pricing-qty-options--mini-cart .qty-options--mini-cart{
          width: 200px;
        }
      }
    </style>
    <?php
  });

  function green_get_base_price($post_id = 0) {
    $product = wc_get_product($post_id);
    // $product->get_regular_price();
    // $product->get_sale_price();
    return $product->get_price();
  }

  add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id, $variation_id) {
    if(isset($_REQUEST['before-dynamic-pricing'])) {
      $cart_item_data['before-dynamic-pricing'] = $_REQUEST['before-dynamic-pricing'];
    } else {
      $pid = ($variation_id ? $variation_id : $product_id);
      if(!isset($_REQUEST['mnm_quantity'])){
        $cart_item_data['before-dynamic-pricing'] = green_get_base_price($pid);
      }
    }

    return $cart_item_data;
  }, 10, 3);

  function green_calc_dynamic_pricing($base_price, $dynamic_data) {
    $type = isset($dynamic_data['type']) ? $dynamic_data['type'] : '';
    $customPrice = $base_price;
    $amount = isset($dynamic_data['amount']) ? $dynamic_data['amount'] : 0;
    $_amount = floatval($amount);

    switch($type) {
      case 'percentage_discount':

        if($_amount > 0) {
          $sale = ($customPrice / 100) * $_amount;
          $customPrice = $customPrice - $sale;
        }
        break;

      case 'price_discount':
        if($_amount > 0) {
          $customPrice -= $_amount;
        }
        break;

      case 'fixed_price':
        $customPrice = $_amount;
        break;

      default:
    }

    return wc_price($customPrice);
  }

  add_filter('woocommerce_widget_cart_item_quantity', function($_html, $cart_item, $cart_item_key) {
    // echo json_encode($cart_item);
    $prod_id = $cart_item['product_id'];
    $dynamicQty = isProductDynamicQty($prod_id);
    $before_dynamic_pricing = (isset($cart_item['before-dynamic-pricing']) ? floatval($cart_item['before-dynamic-pricing']) : null);
    if(empty($dynamicQty) || $dynamicQty == false || $before_dynamic_pricing == null) return $_html;
    $pass_settings = $dynamicQty['pass_settings'];
    $rules = $dynamicQty['pass_rules'];
    $available_quantity = (int) $cart_item['quantity'];

    $currentOptIndex = 0;
    foreach($rules as $_index => $_r) {
      if($available_quantity >= floatval($_r['from'])) {
        $currentOptIndex = $_index;
      }
    }
    $rules[$currentOptIndex]['__active'] = true;

    ob_start();
    // echo $before_dynamic_pricing;
    // echo json_encode($cart_item);
    // echo json_encode($cart_item);
    ?>
    <div class="dynamic-pricing-qty-options--mini-cart">
      <ul class="qty-options--mini-cart">
        <?php foreach($rules as $index => $r) :
          $label_template = 'Buy {%QTY_NUM%} {%AMOUNT%}';
          $from = isset($r['from']) ? $r['from'] : '';
          $to = isset($r['to']) ? $r['to'] : '';
          $amount = isset($r['amount']) ? $r['amount'] : 0;
          $type = isset($r['type']) ? $r['type'] : '';

          $replace_map = [
            '{%QTY_NUM%}' => DP__qtyNumText($from, $to),
            '{%AMOUNT%}' => DP__amountText($amount, $type),
          ];

          $__active = isset($r['__active']) ? 'checked' : '';

          // echo json_encode($r);
          // echo json_encode($cart_item);
          ?>
        <li class="qty-opt-item--mini-cart">
          <label>
            <span>
              <input
                type="radio"
                <?php echo $__active; ?>
                class="__dynamic_pricing_qty_field--mini-cart"
                name="__dynamic_pricing_qty--mini-cart-<?php echo $cart_item_key; ?>"
                data-cart-item-key="<?php echo $cart_item_key; ?>"
                data-before-dynamic-pricing="<?php echo $before_dynamic_pricing; ?>"
                data-qty-from="<?php echo $from; ?>"
                data-qty-to="<?php echo $to; ?>"
                data-type="<?php echo $type; ?>"
                data-amount="<?php echo $amount; ?>"
                value="<?php echo $from; ?>" />
              <?php echo str_replace(array_keys($replace_map), array_values($replace_map), $label_template); ?>
            </span>
            <span class="__price-per--mini-cart"><?php echo green_calc_dynamic_pricing($before_dynamic_pricing, $r); ?> each</span>
          </label>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php
    $dynamic_pricing_html = ob_get_clean();
    return $_html . $dynamic_pricing_html;
  }, 999, 3);

  function green_update_product_qty_in_cart() {
    global $woocommerce;
    WC()->cart->set_quantity($_POST['p_key'], $_POST['qty'], true);
    WC_AJAX::get_refreshed_fragments();

    // $ajax_event = 'get_refreshed_fragments';
    // do_action('wp_ajax_woocommerce_' . $ajax_event, ['WC_AJAX', $ajax_event]);
    // do_action('wp_ajax_nopriv_woocommerce_' . $ajax_event, ['WC_AJAX', $ajax_event]);
    // do_action('wc_ajax_' . $ajax_event, ['WC_AJAX', $ajax_event]);
  }

  add_action('wp_ajax_green_update_product_qty_in_cart', 'green_update_product_qty_in_cart');
  add_action('wp_ajax_nopriv_green_update_product_qty_in_cart', 'green_update_product_qty_in_cart');
}
