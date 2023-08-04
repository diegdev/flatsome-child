jQuery(document).ready(function($) {
  /*
   * Add Show more/less term description mobile
   */
  var eleDesc = $('.tax-product_cat .term-description, .woocommerce-shop .page-description, .single-product .product-short-description');

  var moreText ='Read More';
  var lessText ='Show Less';

  if( eleDesc.height() > 100 ) {
    eleDesc.addClass('has-show-more');
    eleDesc.append('<a class="show-more-btn" href="#">'+ moreText +'</a>');
  }

  eleDesc.find('.show-more-btn').click(function(event){
    event.preventDefault();

    if( $(this).parent().hasClass('show-full') ) {
      $(this).html(moreText);
      $(this).parent().removeClass('show-full');
    } else {
      $(this).html(lessText);
      $(this).parent().addClass('show-full');
    }
  });
})
