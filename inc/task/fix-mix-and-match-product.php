<?php 
/**
 * Task: https://green-society.monday.com/boards/1418964603/pulses/3530803432?reply=reply-1816103092
 */

add_action('wp_footer', function() {
  // return;
  ?>
  <script>
    ((w, $) => {
      'use strict';

      const reCalculatorPriceButtonAddToCart = () => {

        $('body').on('change', 'select.wc-pao-addon-field', function() {
          let $select = $(this);
          let val = $select.val();
          if(val == '') {
            $('.input-text.qty').trigger('change');
          }
        })

        $('.cart').on('updated_addons', function(e) {
          // console.log(this, e);
          setTimeout(() => {
            if($('#product-addons-total .price .woocommerce-Price-amount.amount').length) {
              let price = $('#product-addons-total .price .woocommerce-Price-amount.amount').text();
              let currentPrice = $('.add_to_cart_button_wrap.mnm_button button.single_add_to_cart_button span:nth-child(3)').text();
              // console.log(price);
              let newPrice = parseFloat(price.replace('$', '')) + parseFloat(currentPrice.replace('$', ''));
              console.log(newPrice);

              let $nth3Span = $('.add_to_cart_button_wrap.mnm_button button.single_add_to_cart_button span:nth-child(3)')
              let $nth4Span = $('.add_to_cart_button_wrap.mnm_button button.single_add_to_cart_button span:nth-child(4)')
              
              if($nth4Span.length) {
                $nth4Span.html(`$${ newPrice }`)
              } else {
                $nth3Span.after(`<span>$${ newPrice }</span>`)
              }

              if($('.add_to_cart_button_wrap.mnm_button button.single_add_to_cart_button span:nth-child(4)').length) {
                $nth3Span.css('display', 'none');
              } else {
                $nth3Span.css('display', '');
              }
            }
          }, 90)
        })
      }

      $(reCalculatorPriceButtonAddToCart); 

    })(window, jQuery);
  </script>
  <?php
});