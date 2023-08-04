jQuery(document).ready(function ($) {
	$('.daily-deal-container a.daily-deal-shop-now.non-var').click(function (e) {
		e.preventDefault();
		var addLink = $(this);
		var product_id = $(this)
			.closest('.daily-deal-container')
			.find('a.daily-deal-shop-now')
			.data('product-id');
		addLink.css('opacity', '0.5');
		// console.log('Adding', product_id);

		addLink.text('Adding to Cart');

		$.ajax({
			type: 'POST',
			url: ajax_object.ajax_url,
			data: {
				action: 'jy_featured_add_to_cart',
				product_id: product_id,
			},
			success: function (response) {
				// console.log('Success, added', product_id);

				$(document.body).trigger('wc_fragment_refresh');
			},
			error: function (error) {
				addLink.text('Error Adding to Cart');
			},
			complete: function () {
				setTimeout(function () {
					openMiniCart();
					addLink.css('opacity', '1');
					addLink.text('Add to Cart');
				}, 2500);
			},
		});
	});
});

function openMiniCart() {
	// Open mini cart
	var openButton = jQuery('a[data-open="#cart-popup"]');
	if (openButton.length) {
		// console.log('opening mini cart');
		openButton.trigger('click');
	}
}
