<?php
// Add Quantity Input in mini cart widget

add_filter( 'woocommerce_widget_cart_item_quantity', 'add_minicart_quantity_fields', 10, 3 );
function add_minicart_quantity_fields( $html, $cart_item, $cart_item_key ) {
    $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );
    $price = get_post_meta($cart_item['product_id'] , '_price', true);
    // $stock_qty = $cart_item['data']->get_stock_quantity();

    if(isset($cart_item['custom_price']) && isset($cart_item['redeem_point'])) {
        return $product_price;
    }
    if ((float)$price > 0.0){
        return '<div class="mini-cart-qty-wrapper">' . $product_price . woocommerce_quantity_input( array('input_value' => $cart_item['quantity']), $cart_item['data'], false ) . '</div>';    
    }
    return $product_price;
}

function ajax_update_mini_cart() {
    $cart_item_key = $_REQUEST["item_key"];
    $cart_item_qty = $_REQUEST["item_qty"];
    WC()->cart->set_quantity($cart_item_key, $cart_item_qty);
    echo wc_get_template( 'cart/mini-cart.php' );
    die();
}
add_filter( 'wp_ajax_nopriv_ajax_update_mini_cart', 'ajax_update_mini_cart' );
add_filter( 'wp_ajax_ajax_update_mini_cart', 'ajax_update_mini_cart' );



add_action('wp_footer', function(){
    ?>
    <script>
        jQuery(document).ready(function ($) {
          let triggered = false;
          $(document).on('change', '.widget_shopping_cart_content input.qty', function () {
            if (!triggered){
                $('#cart-popup .widget_shopping_cart_content').append('<div class="blockUI blockOverlay" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255); opacity: 0.6; cursor: wait; position: absolute;"></div><div class="blockUI blockMsg blockElement" style="z-index: 1011; display: none; position: absolute; left: 288px; top: 160px;"></div>'); 
                let item_key = $(this).closest('.woocommerce-mini-cart-item.mini_cart_item').find('.remove_from_cart_button').attr('data-cart_item_key');
                let item_qty = parseInt($(this).val());
                let stock_qty = parseInt($(this).attr("data-stock-qty"));
                let qty_input = $(this);
                if (item_qty > stock_qty){
                    $('#cart-popup .widget_shopping_cart_content .blockUI').remove();
                    $(this).closest('.woocommerce-mini-cart-item.mini_cart_item').append('<span class="not-enough-stock">Not enough stock</span>');
                    setTimeout(function(){
                        $('.not-enough-stock').remove();
                        qty_input.val(stock_qty).trigger('change');
                    }, 2400);
                }
                else{
                    // triggered=false;
                    $.post(
                      woocommerce_params.ajax_url,
                      {
                        action: 'ajax_update_mini_cart', 
                        "item_key": item_key,
                        "item_qty": item_qty
                      }, 
                      function (response) {
                        $('#cart-popup .widget_shopping_cart_content').html(response);
                        let total_price = $('.widget_shopping_cart p.total span.amount bdi').html();
                        $('header .header-nav .header-cart-title .cart-price bdi').html(total_price);
                      }
                    );
                }
            }
            
          });
        });
    </script>
    <style>
        .widget_shopping_cart_content .mini-cart-qty-wrapper .minus.button.is-form{
            width: 20px;
            margin: 0;
            border-radius: 15px !important;
            height: 2em;
            min-height: 20px;
            line-height: 1;
            max-height: 20px;
            color: white;
            background: #078C4D;
            border: none !important;
            font-size: 15px;
            text-shadow: none;
            padding: 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .widget_shopping_cart_content .mini-cart-qty-wrapper .quantity input[type=number]{
            min-height: 26px;
            color: #444;
            background: white;
            font-size: 16px;
            border: none !important;
            box-shadow: none;
            width: 24px;
            line-height: 1.3;
            display: block;
            height: 26px;
        }
        .widget_shopping_cart_content .mini-cart-qty-wrapper .plus.button.is-form{
            width: 20px;
            margin: 0;
            border-radius: 15px !important;
            height: 2em;
            min-height: 20px;
            line-height: 1;
            max-height: 20px;
            color: white;
            background: #078C4D;
            border: none !important;
            font-size: 15px;
            text-shadow: none;
            padding: 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .widget_shopping_cart_content ul.product_list_widget li .quantity{
            opacity: 1;
            display: flex !important;
            align-items: center;
            margin: 0 0 0 10px;
        }
        .mini-cart-qty-wrapper{
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .widget_shopping_cart_content ul.product_list_widget li{
            font-size: 0.9em;
            padding-bottom: 15px;
        }
        .widget_shopping_cart_content ul.product_list_widget li span.amount{
            font-weight: 500;
            color: #666;
        }
        .off-canvas .off-canvas-cart{
            width: 320px;
        }
        .not-enough-stock{
            position: absolute;
            opacity: 1;
            transition: all ease 0.3s;
            color: red;
            bottom: 0;
            right: 15px;
            font-size: 12px;
        }
        .widget_shopping_cart_content ul.product_list_widget .mini_cart_item.mnm_container_mini_cart_item .quantity.buttons_added{
            display: none !important;
        }
        .shop_table tr.cart_item.mnm_table_container .quantity, .wfacp_form_cart table.shop_table tr.cart_item.mnm_table_container .wfacp_quantity_selector{
            display: none !important;
        }
    </style>
    <?php
});


// Woocommerce Quantity Input product stock count function added

if ( ! function_exists( 'woocommerce_quantity_input' ) ) {

	function woocommerce_quantity_input( $args = array(), $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			$product = $GLOBALS['product'];
		}

		$defaults = array(
			'input_id'     => uniqid( 'quantity_' ),
			'input_name'   => 'quantity',
			'input_value'  => '1',
			'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
			'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
			'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
			'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
			'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
			'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
			'product_name' => $product ? $product->get_title() : '',
			'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
			'autocomplete' => apply_filters( 'woocommerce_quantity_input_autocomplete', 'off', $product ),
			'readonly'     => false,
		);

		$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );

		$args['min_value'] = max( $args['min_value'], 0 );
		$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

		if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
			$args['max_value'] = $args['min_value'];
		}

		$type = $args['min_value'] > 0 && $args['min_value'] === $args['max_value'] ? 'hidden' : 'number';
		$type = $args['readonly'] && 'hidden' !== $type ? 'text' : $type;


		$args['type'] = apply_filters( 'woocommerce_quantity_input_type', $type );
		$args['stock_qty'] = 9999;
		if ($product->get_stock_quantity() > 0){
		    $args['stock_qty'] = $product->get_stock_quantity();
		}

		ob_start();
		wc_get_template( 'global/quantity-input.php', $args );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}
}