<?php
/**
 * The template for displaying the footer.
 *
 * @package flatsome
 */

global $flatsome_opt;
?>

</main>

<footer id="footer" class="footer-wrapper">

	<?php do_action('flatsome_footer'); ?>

</footer>

</div>

<?php wp_footer(); ?>
<!-- TrustBox script -->
<script  type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js"></script>
<!-- End TrustBox script -->
</script>
<style>
.ux-swatches.ux-swatches-in-loop .ux-swatch {
  min-width: 40px;
  font-size: 12px;
  background: #046738;
  color: #ffffff;
  border-radius: 2px;
  padding: 3px;
}
.ux-swatches.ux-swatches-in-loop .ux-swatch.selected {
  color: #046738;
  background: #ffffff;
}
.add-to-cart-button .add_to_cart_button.added{
	display: inline-block !important;
}
.add-to-cart-button .added_to_cart{
	display: none !important;
}
</style>
<script type="text/javascript" id="pg_show_price">

// fake swatch click function
    jQuery(document).ready(function($){
        function pg_show_price(id, variationid, price, pricepergram, regularprice = 0, e, virtual_variation = false){
            e.closest('.product-small.col').find('.add_to_cart_button').attr('data-product_id', variationid);
            e.closest('.product-small.col').find('.add_to_cart_button').attr('data-custom_id', '');
            //jQuery('.product-small.col').removeClass('active');

            jQuery(e).closest('.product-small.col').addClass('active');
            jQuery(e).closest('.product-small.col').addClass('loaded');

            if(virtual_variation){
                e.closest('.product-small.col').find('.add_to_cart_button').attr('data-custom_id', virtual_variation);
                //jQuery(e).closest('.product-small.col .pg_4oz').find('.label_save').fadeOut();
                jQuery(e).closest('.product-small.col').find('.pricePG-wrapper').addClass('pg_4oz');
            }
            else {
                jQuery(e).closest('.product-small.col .pg_4oz').find('.label_save').fadeIn();
                jQuery(e).closest('.product-small.col').find('.pricePG-wrapper').removeClass('pg_4oz');
            }

            if(jQuery(e).closest('.product-small').hasClass('pg_4oz')) { /*# contains 4oz*/
                jQuery('.product-small.col .pg_4oz').find('.label_save').hide();
            }

            Array.from(e.closest('.product-small.col').find('ul li')).forEach(function(el) {
                el.classList.remove('active');
            });
            e.addClass('active');
            jQuery(e).closest('.product-small.col').find('.col-inner span.price').fadeOut('fast').fadeIn('fast');
            var	regularpriceHTML = '';
            if(regularprice != '0.00'){
                regularpriceHTML = '<del><span class="woocommerce-Price-amount amount"><bdi>$'+ parseFloat(regularprice).toFixed(2) +'</bdi></span></del>';
            }
            else{
                //regularpriceHTML = '<del><span class="woocommerce-Price-amount amount"><bdi>$'+ parseFloat(price*10/9).toFixed(2) +'</bdi></span></del>';
            }
            e.closest('.product-small.col').find('.col-inner span.price').html(regularpriceHTML+'<ins><span class="woocommerce-Price-amount amount"><bdi>$'+ parseFloat(price).toFixed(2) +'</bdi></span></ins>');
            jQuery(e).closest('.product-small.col').find('.col-inner .bt_from_price').fadeOut('fast').fadeIn('fast');
            e.closest('.product-small.col').find('.col-inner .bt_from_price').html ('From <span class="bt_from_price_amount">$'+ parseFloat(pricepergram).toFixed(2)+'</span> per gram');
            jQuery(e).closest('.product-small.col').find('.add_to_cart_button').removeAttr('disabled');
            jQuery(e).closest('.product-small.col').find('.add_to_cart_button').removeClass('select_size');
            jQuery(e).closest('.product-small.col').find('.add_to_cart_button').addClass('ajax_add_to_cart');
            jQuery(e).closest('.product-small.col').find('.add_to_cart_button').html('ADD TO CART');
        }

        function pg_animate_add_to_cart_btn(e){
            e.classList.toggle('loading');
            e.classList.toggle('button');
            e.innerText = '';

            setTimeout(function(){
                e.classList.toggle('loading');
                e.classList.toggle('button');
                e.innerText = 'Add to cart';
            }, 1500);
        }

        /* UPDATE: PG SELECT VARIATION, PRICEPERGRAM */
        function PG_update_pricepergram(){
            setTimeout(function(){
                var selectedVariation = document.querySelector('input[name=variation_id]').value;
                if(selectedVariation != '' && selectedVariation > 0){
                let price = pricepergram_list[selectedVariation];
                    if(price > 0){
                    jQuery('.woocommerce-variation-availability .pricepergram').remove();
                    jQuery('.woocommerce-variation-availability').prepend("<div class='pricepergram' style='display:none;'><strong>$"+price+"</strong> per gram</div>");
                    jQuery('.woocommerce-variation-availability .pricepergram').fadeIn();

                    jQuery('.pricepergram_shorcode').each(function(i, obj) {
                        jQuery(obj).html('<strong>$'+pricepergram_firstPrice+'</strong> per gram');
                    });
                    }

                }
                }, 200);
        }
        $(document).on('click', '.ux-swatches-fake .fake-s-item', function(e){
            let id = $(this).data('id');
            let vid = $(this).data('vid');
            let price = $(this).data('price');
            let unit = $(this).data('unit');
            let sale_price = $(this).data('sale_price');
            pg_show_price(id, vid, price, unit, sale_price, $(this));
        });

        // Account detail page username disable function
        if ($('body.woocommerce-account #um_field_general_user_login #user_login').length > 0){
            $('body.woocommerce-account #um_field_general_user_login #user_login').attr('disabled', 'disabled');
        }
    });
</script>
</body>
</html>