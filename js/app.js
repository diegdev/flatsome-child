jQuery(document).ready(function($) {
    // trigger login popup
    const $body = $('body')
    const $window = $(window)
    $('.login-link').unbind('click').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        $('.account-item .button').trigger('click');
        $('.mfp-wrap').removeClass('off-canvas-right');
        $('.mfp-content').removeClass('off-canvas-cart');
    })
    // circle single product listing //
    $('.reset_variations').unbind('click').on('click', function(e) {
        $('.item-product-variation').removeClass('active-item');
    })
    $('.item-product-variation').unbind('click').on('click', function(e) {
        var $this = $(this),
            term = $this.attr('data-value'),
            title = $this.attr('title'),
            attr = $this.attr('data-attr_name'),
            $selectbox = $('select#' + attr);

        if ($this.hasClass('outstock')) {
            return false;
        }

        $selectbox.val(term).trigger('change');

        $this.addClass('active-item').siblings().removeClass('active-item');
        $('body').trigger('attr_selected', [attr, term, title]);

        e.preventDefault();
    })
    if ($('.item-product-variation:not(.outstock)').eq(0).length > 0) $('.item-product-variation:not(.outstock)').eq(0).trigger('click');
    // end circle single product listing //

    // From price

    $('.box .variable-item:not(.disabled)').click(function(){
      let value = $(this).data('value');
      let el_from_price = $(this).parents('.box-text-products').find('.bt_from_price');
      let prices = el_from_price.data('price_per_unit');
      let from_html = '<span class="bt_from_price_amount">' + prices[value] + '</span><span class="icon-g"> per gram<span>';
      el_from_price.html(from_html);
    })
    // trigger variation show/
    $( ".variations_form" ).on( "show_variation", function ( event, variation ) {
      $('.single_variation_wrap .points').remove();
      $('.bt_attr_weight').remove();
      let el_points = $('.sticky-add-to-cart-wrapper .sticky-add-to-cart__product');
      var selected_variation = [];
      if(variation.attributes){
        $( variation.attributes ).each(function(e,v){
          var attrs_names = Object.keys(v);
          var attrs_values = Object.values(v);
          $( attrs_names ).each(function(_e,_v){
            selected_variation.push($('.product-info select[name="'+_v+'"]').find('option[value="'+attrs_values[_e]+'"]').text());
          })
        })
      }
      if(selected_variation.length > 0){
        el_points.append('<span class="bt_attr_weight"> - ' + selected_variation.join() + '</span>');
      }
    });

    swatchesSingle = function() {
    if (!$body.hasClass('single-product')) {
      return
    }

    const $qty = $('form.cart .quantity')
    const $qtyInput = $qty.find('input[name="quantity"]')
    const $variationSelect = $('.variations_form select')
    const $addToCartButton = $('form.cart .single_add_to_cart_button')
    const $priceSingle = $('.product-page-price')
    const currencySymbol = $('.woocommerce-Price-currencySymbol')
      .first()
      .text()

    $('.cart:not(.cart_group)').on('updated_addons', function() {
        if($('#product-addons-total').find('.wc-pao-subtotal-line .amount').length > 0){
          let price = $('#product-addons-total').find('.wc-pao-subtotal-line .amount').html();
          $addToCartButton.html('<span>Add to cart</span><span class="divider">|</span><span>'+price+'</span>')
        }
    })
    $('.variations_form').on('show_variation', function(e, variation) {

      // Change price HTML.
      $priceSingle.html($(variation.price_html).filter(".price"))

      // Change add to cart text.
      $addToCartButton.attr('data-price', variation.display_price)
      $addToCartButton.text(`Add to cart`)

      /**
       * => Mike fixed NaN
       */
      let qtyNumber = parseInt($qtyInput.val(), 10) || 1;

      const subTotal = qtyNumber * parseFloat(variation.display_price)
      const formatSubTotal = subTotal.toFixed(2).replace(/(.)(?=(\d{3})+$)/g, '$1,')
      // console.log(subTotal, parseInt($qtyInput.val(), 10), variation.display_price);
      if ($addToCartButton.length && 0 < variation.display_price) {
        $addToCartButton.html('<span>Add to cart</span><span class="divider">|</span><span>'+currencySymbol+formatSubTotal+'</span>')
      }

      setTimeout(() => {
        if($qtyInput.val() == '') {
          $qtyInput.val(qtyNumber)
        }
      }, 50)

    })
    // mix and max product
    $('.mnm_form').on('wc-mnm-updated-totals', function(e,container) {
      const price = container.calculate_totals().totals.price;
      $addToCartButton.attr('data-price',price);
      const subTotal = parseInt(container.get_quantity(), 10) * parseFloat(price)
      const formatSubTotal = subTotal.toFixed(2).replace(/(.)(?=(\d{3})+$)/g, '$1,')

      if ($addToCartButton.length) {
        $addToCartButton.html('<span>Add to cart</span><span class="divider">|</span><span>'+currencySymbol+formatSubTotal+'</span>')
      }
    })

    $qtyInput.on('change', function() {
      const price = $addToCartButton.attr('data-price')
      const qty = parseInt($qtyInput.val(), 10)
      let pointEarned = price
      if ($addToCartButton.length && !$addToCartButton.is('.disabled') && 0 < price) {
        const subTotal = qty * parseFloat(price)
        const formatSubTotal = subTotal.toFixed(2).replace(/(.)(?=(\d{3})+$)/g, '$1,')
        $addToCartButton.html(`<span>Add to cart</span>
                              <span class="divider">|</span>
                              <span>${currencySymbol}${formatSubTotal}</span>`)
      }
    })

    let $mnm_form = $('.mnm_form.cart');
    let $mnm_quantity = $mnm_form.find('.mnm-quantity');
    let $mnm_min_size = $mnm_form.find('.mnm_cart').data('min_container_size');
    let $mnm_max_size = $mnm_form.find('.mnm_cart').data('max_container_size');
    var $mnm_qty_total;

    $mnm_quantity.on('change', function() {
      $mnm_qty_total = 0;
      $mnm_form.find('.mnm_item').each(function(){
        var $mnmQty = parseInt($(this).find('.mnm-quantity').val());

        if(!isNaN($mnmQty)) {
          $mnm_qty_total = $mnm_qty_total + $mnmQty;
        }
      })

      if($mnm_min_size <= $mnm_qty_total && $mnm_qty_total <= $mnm_max_size) {
        $('body').addClass('has-mnm-tabular');
        $('body').addClass('has-sticky-product-cart');
        $('.sticky-add-to-cart-wrapper .sticky-add-to-cart').addClass('sticky-add-to-cart--active');
      }
    });

    $(window).scroll(function() {
      if($('body').hasClass('has-mnm-tabular')) {
        $('body').addClass('has-sticky-product-cart');
        $('.sticky-add-to-cart-wrapper .sticky-add-to-cart').addClass('sticky-add-to-cart--active');
      }
    });

  }
  // remove product on order bump
  $('body').on('updated_checkout', function(data){
    var items = $('.wfob_bump .wfob_Box').length;
    let checked_items = 0;
    $('.wfob_bump .wfob_Box').each(function(){
      if($(this).find('.wfob_checkbox').is(':checked')){
        checked_items++;
        $(this).remove();
      }
    })
    if(items == checked_items){
      $('.wfob_bump_wrapper').remove();
    }
  })

  // rewards
  jQuery('body').on('click', '.claim_reward', function(e) {
      e.preventDefault();
      var _this = $(this);
      var _container = _this.parents('.claim_reward_form');
      _this.text('Processing...');
      var product_id = _container.find('input[name="product_id"]').val();
      var custom_price = _container.find('input[name="custom_price"]').val();
      var variation_id = _container.find('input[name="variation_id"]').val();
      var redeem_point = _container.find('input[name="redeem_point"]').val();
      var data = {
          action: "add_to_cart_reward",
          product_id: product_id,
          custom_price: custom_price,
          variation_id: variation_id,
          redeem_point: redeem_point,
      };
      console.log(data);
      $.ajax({
          type: "POST",
          url: pp_php_admin_data.ajax_url,
          data: data,
          success: function(data) {
            // console.log(data);
              _this.prop('disabled', true).text('Added to cart');
              // jQuery(document.body).trigger('wc_fragment_refresh');
              location.reload();
          }
      });
  })
  //
  swatchesSingle();

  function pg_show_price(id, variationid, price, pricepergram, regularprice = 0, e, virtual_variation = false){
  	e.closest('.product-small.col').querySelector('.add_to_cart_button').setAttribute('data-product_id', variationid);
  	e.closest('.product-small.col').querySelector('.add_to_cart_button').setAttribute('data-custom_id', '');
  	//jQuery('.product-small.col').removeClass('active');

  jQuery(e).closest('.product-small.col').addClass('active');
  jQuery(e).closest('.product-small.col').addClass('loaded');

  	if(virtual_variation){
  		e.closest('.product-small.col').querySelector('.add_to_cart_button').setAttribute('data-custom_id', virtual_variation);
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

  	Array.from(e.closest('.product-small.col').querySelectorAll('ul li')).forEach(function(el) {
  		el.classList.remove('active');
  	});
  	e.classList.add('active');
      jQuery(e).closest('.product-small.col').find('.col-inner span.price').fadeOut('fast').fadeIn('fast');
  	var	regularpriceHTML = '';
  	if(regularprice != '0'){
  		regularpriceHTML = '<del><span class="woocommerce-Price-amount amount"><bdi>$'+regularprice+'</bdi></span></del>';
  	}
  	e.closest('.product-small.col').querySelector('.col-inner span.price').innerHTML = regularpriceHTML+'<ins><span class="woocommerce-Price-amount amount"><bdi>$'+price+'</bdi></span></ins>';
    jQuery(e).closest('.product-small.col').find('.col-inner .bt_from_price').fadeOut('fast').fadeIn('fast');
  	e.closest('.product-small.col').querySelector('.col-inner .bt_from_price').innerHTML = '<strong>$'+pricepergram.toFixed(2)+'</strong> per gram';
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
})
