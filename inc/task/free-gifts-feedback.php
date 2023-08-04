<?php
/**
 * https://green-society.monday.com/boards/1418964603/pulses/3230084361/posts/1705189434?reply=reply-1872398350
 */

function green_is_gift_enable() {
  return get_field('enable_free_gifts', 'option');
}

function green_get_current_gift_in_cart() {
  // wp_send_json($cart_updated); die;
  $pid = 0;
  if ( ! WC()->cart->is_empty() ) {
    // Loop though cart items
    foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      if(isset($cart_item['free_gift'])) {
        $pid = $cart_item['product_id'];
        if(isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])) {
          $pid = $cart_item['variation_id'];
        }
      }
    }
  }

  return $pid;
}

function green_get_product_gifts() {
  $gift_tier = get_field('gift_tier', 'option');
  $totalCart = WC()->cart->get_cart_contents_total();
  $arr = [];
  if($gift_tier && count($gift_tier)) {
    foreach($gift_tier as $_index => $item) {
      foreach ($item['product'] as $_key => $_product){
        $_p = wc_get_product( $_product->ID );
        if($_p->get_type() == 'variation') {
          $parent_id = wp_get_post_parent_id( $_product->ID );
          $_product->parent_ID = $parent_id;
        }

        if(! isset($arr[$item['amount']])) {
          $arr[$item['amount']] = [];
        }
        $_product->available_for_gift = $item['amount'] <= $totalCart ? true : false;
        $_product->name_gift = $item['name'];
        array_push($arr[$item['amount']], $_product);
      }
    }
  }

  return $arr;
}

function green_product_gifts_html() {

  $totalCart = WC()->cart->get_cart_contents_total();
  //if(green_is_gift_enable() != true || $totalCart < 150) return;
  
  $gift_tier = get_field('gift_tier', 'option');

  $discount_cart = WC()->cart->discount_cart;

  $subtotal = WC()->cart->subtotal - $discount_cart;

  $amounts = wp_list_pluck( $gift_tier, 'amount' );

  $min = min($amounts);

  $max = max($amounts);

  $new_amounts = array_filter($amounts, function($n){

    $discount_cart = WC()->cart->discount_cart;

    $subtotal = WC()->cart->subtotal - $discount_cart;

    return $n > $subtotal;

  });

  $qualified_amounts = array_filter($amounts, function($n){

    $discount_cart = WC()->cart->discount_cart;

    $subtotal = WC()->cart->subtotal - $discount_cart;

    return $n <= $subtotal;

  });
  
  
  
  $gift_tier = green_get_product_gifts();
  $currentGift = green_get_current_gift_in_cart();
  ?>
  <div class="green-product-gifts__wrap">
    <div class="green-product-gifts green-product-gifts-container">
      <div class="green-product-gifts__gifts-heading">
        <?php
        $amount_qualified = 0;
        $next_gift_amount = $min;
        if(count($new_amounts)){
          $next_gift_amount = min($new_amounts);
        }
        $progressing = $subtotal*100/$next_gift_amount;
        if($subtotal <= $min && $subtotal > 0){
          echo "<h4 class='bt_gift_notice'>Add <b>$".floatval($min - $subtotal)."</b> more to your cart for a FREE gift!</h4>";
        }
        if($subtotal > $min){
          $amount_qualified = max($qualified_amounts);
          if($subtotal < $max){
            echo "<h4 class='bt_gift_notice'>Add <b>$".floatval($next_gift_amount - $subtotal)."</b> more to your cart for the next FREE gift!</h4>";
          }
        }
        ?>
        <div class="bt_progressing_wrap">
          <div class="bt_progressing" style="width:<?php echo $progressing.'%'; ?>"><?php echo $progressing.'%'; ?></div>
        </div>        
      </div>
      <div class="green-product-gifts__gift-tier__options">
        <?php foreach($gift_tier as $tier => $products ) : ?>
          <div class="green-product-gifts__tier-item" style="width: calc(100% / <?php echo count($gift_tier); ?>);">
            <h4><?php echo sprintf('$%s', $tier); ?></h4>
            <div class="green-product-gifts__item">
              <?php foreach($products as $p) :
                $product = wc_get_product($p->ID);
                $image_id  = $product->get_image_id();
                // echo json_encode($p);
                $checked = $currentGift == $p->ID ? 'checked' : '';
              ?>
                <div class="product-gift green-product-gifts__select-item <?php echo ($p->available_for_gift ? '' : '__not-available-for-gift') ?>" data="">
                  <label>
                    <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>

                    <div class="product-gift-content">
                      <div class="__radio-fake-ui">
                        <input
                          type="radio"
                          <?php echo $checked; ?>
                          name="gift_product_id"
                          data-parent-id="<?php echo (isset($p->parent_ID) ? $p->parent_ID : ''); ?>"
                          data-name-gift="<?php echo $p->name_gift; ?>"
                          value="<?php echo $p->ID; ?>">
                        <span class="__radio-fake-ui-target"></span>
                      </div>
                      <?php 
                            $product_price = number_format($product->get_price(), 2);
                      ?>
                      <h4><?php echo sprintf('<span class="green-product-gifts__product-price">$%s</span> - <span style="color:#046839;">FREE</span>', $product_price); ?></h4>
                      <div class="p-title" title="<?php echo $p->post_title; ?>">
                        <?php echo $p->post_title; ?>
                      </div>
                    </div>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="green-product-gifts__buttons">
        <button class="btn-add-gift"><?php _e('Add Free Gift', 'green') ?></button>
        <button class="btn-no-thanks"><?php _e('No Thanks', 'green') ?></button>
      </div>
    </div>

    <div class="free-gift-section">
      <p>
        You have a free gift waiting for you!<br />
        Are you sure you don't want to claim your free gift?<br />
        <button class="btn-switching-select-gift">Claim it now!</button>
      </p>
    </div>
  </div>
  <?php
}


add_action('wfacp_mini_cart_before_shipping', 'green_product_gifts_html', 10);

function green_gift_validate_init($POST_DATA) {
  // wp_send_json($cart_updated); die;
  if ( ! WC()->cart->is_empty() ) {
    // Loop though cart items
    foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      if(isset($cart_item['free_gift'])) {
        // WC()->cart->remove_cart_item($cart_item_key);
        // wp_send_json( [$cart_item['ProductID'], $cart_item] );

        $_pid = $cart_item['product_id'];
        if(isset($cart_item['variation_id']) && ! empty($cart_item['variation_id'])) {
          $_pid = $cart_item['variation_id'];
        }

        if(green_check_product_ready_for_gift($_pid) == false) {
          WC()->cart->remove_cart_item($cart_item_key);
        }
      }
    }
  }
}

add_action('woocommerce_checkout_update_order_review', 'green_gift_validate_init', 999);

function green_check_product_ready_for_gift($pid) {
  $gift_tier = get_field('gift_tier', 'option');
  $totalCart = WC()->cart->get_cart_contents_total();
  $pass = false;

  if(!$gift_tier && count($gift_tier) == 0) return false;

  foreach($gift_tier as $item) {
    foreach ($item['product'] as $_key => $_product){
      $_pid = $_product->ID;
      if($_pid == $pid && $item['amount'] <= $totalCart) {
        $pass = true;
        break;
      }
    }
  }

  return $pass;
}

function green_remove_all_gift_exist_in_cart() {
  $result = 0;
  if ( ! WC()->cart->is_empty() ) {
    // Loop though cart items
    foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      if(isset($cart_item['free_gift'])) {
        WC()->cart->remove_cart_item($cart_item_key);
      }
    }
  }
}

add_filter('woocommerce_update_order_review_fragments', function($fragments) {
  ob_start();
  green_product_gifts_html();
  $_html = ob_get_clean();

  $fragments['.green-product-gifts__wrap'] = $_html;
  return $fragments;
}, 1);

/**
 * Proccess
 * -> Prepare data
 * -> check product ready for gift
 * -> Remove all gift item in cart
 * -> Add gift
 *
 * @return
 */
function green_ajax_add_gift() {
  // wp_send_json($_POST);
  $variation_id = 0;
  $product_id = (int) $_POST['data']['product'];
  $name_gift = (int) $_POST['data']['name_gift'];

  if(isset($_POST['data']['parent_id'])) {
    $variation_id = (int) $_POST['data']['product'];
    $product_id = (int) $_POST['data']['parent_id'];
  }

  # Ready for gift
  if(green_check_product_ready_for_gift($_POST['data']['product']) != true) {
    wp_send_json([
      'success' => false,
      'message' => __('Product not ready for gift.')
    ]);
  }

  # Remove all gift item in cart
  green_remove_all_gift_exist_in_cart();

  // $existKey = bt_check_gift_exist_in_cart($product_id, $variation_id);
  // if($existKey) {
  //   # remove this
  //   WC()->cart->remove_cart_item($existKey);
  // }

  # Add gift
  WC()->cart->add_to_cart(
    $product_id,
    1,
    $variation_id,
    [],
    [
      'custom_price' => 0,
      'free_gift' => $name_gift,
      'update_qty' => false,
    ]
  );

  wp_send_json([
    'success' => true,
  ]);
}

add_action('wp_ajax_green_ajax_add_gift', 'green_ajax_add_gift');
add_action('wp_ajax_nopriv_green_ajax_add_gift', 'green_ajax_add_gift');

add_action('wp_footer', function() {
  ?>
  <script>
    ;((w, $) => {
      'use strict';
      const ajax_url = `<?php echo admin_url('admin-ajax.php'); ?>`;
      const addGiftHandle = async (pid, parentID, nameGift) => {

        const data = {
          product: pid,
          name_gift: nameGift
        }

        if(parentID) {
          data.parent_id = parentID
        }

        const result = await $.ajax({
          type: 'POST',
          url: ajax_url,
          data: {
            action: 'green_ajax_add_gift',
            data
          }
        });

        console.log(result);
        w.location.reload();
      }

      const switchingPanel = () => {
        $('body').on('click', '.green-product-gifts .btn-no-thanks', e => {
          $('.green-product-gifts').css('display', 'none');
          $('.free-gift-section').css('display', 'block');
        })

        $('body').on('click', '.btn-switching-select-gift', e => {
          $('.green-product-gifts').css('display', 'block');
          $('.free-gift-section').css('display', 'none');
        })
      }

      $(() => {
        switchingPanel();

        $('body').on('click', '.btn-add-gift', function(e) {
          e.preventDefault();
          const pid = $('input[name=gift_product_id]:checked').val();
          const parentID = $('input[name=gift_product_id]:checked').data('parent-id');
          const nameGift = $('input[name=gift_product_id]:checked').data('name-gift');
          if(!pid) return;

          $(this).css({
            opacity: .5,
            pointerEvents: 'none',
          });

          addGiftHandle(pid, parentID, nameGift);
        })
      });

    })(window, jQuery);
  </script>
  <style>
    .woocommerce-checkout .wfacp-comm-wrapper .wfacp-left-wrapper{
      padding-top: 0;
    }
    .wfacp_product_restore_wrap {
      display: none !important;
    }
    .free-gift-section {
      /* background: red; */
      text-align: center;
      box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
      border-radius: 4px;
      margin: 1rem 0;
      padding: 1rem;
      display: none;
    }
    .free-gift-section p {
      font-weight: bold;
      color: black;
    }
    .btn-switching-select-gift {
      margin: 0 10px;
      border: none;
      background: #046839;
      color: #fff;
      padding: 0.5rem 1rem;
      margin-top: 1em;
      border-radius: 4px;
      font-family: inherit;
      cursor: pointer;
    }

    .green-product-gifts {
      width: 100%;
      box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
      border-radius: 4px;
      margin-top: 1rem;
      margin-bottom: 1rem;
      padding: 1rem;
    }
    .green-product-gifts__gifts-heading {
      text-align: center;
      margin-bottom: 1em;
    }
    .green-product-gifts__gifts-heading h4{
      font-size: 0.9rem;
      color: #606060;
      font-weight: bold;
    }
    
    .gifts-heading__sub{
      font-size: 0.75rem;
      color: #606060;
    }

    .green-product-gifts__gift-tier__options {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -10px;
    }
    .green-product-gifts__tier-item {
      /* width: calc(100% / 4); */
      padding: 0 10px;
      margin-bottom: 10px;
      box-sizing: border-box;
      text-align: center;
    }
    .product-gift-content > h4 {
      font-size: 1.3em;
      font-weight: bold;
    }
    .green-product-gifts__item {
      /* display: flex; */
      justify-content: center;
    }
    .green-product-gifts__item img {
      /* width: 90%; */
      height: auto;
      border: solid 1px #eee;
      border-radius: 3px;
      margin: 5px auto;
      max-width: 100%;
    }
    .green-product-gifts__select-item {
      padding: 0 10px;
    }
    .green-product-gifts__select-item.__not-available-for-gift {
      opacity: .4;
      pointer-events: none;
    }
    .green-product-gifts__select-item label {
      cursor: pointer;
    }
    .green-product-gifts__select-item .p-title {
      font-size: 11px;
      font-weight: bold;
      line-height: normal;
      display: block;
    }
    .green-product-gifts__select-item input[type=radio]{
      position: initial !important;
      margin-top: 10px !important;
    }
    .green-product-gifts__buttons {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }
    .green-product-gifts__buttons button {
      margin: 0 10px;
      border: none;
      background: #046839;
      color: #fff;
      padding: 0.5rem 1rem;
      margin-top: 1em;
      border-radius: 4px;
      font-family: inherit;
      cursor: pointer;
    }
    .green-product-gifts__buttons button.btn-no-thanks {
      background: #666;
    }
    .__radio-fake-ui {
      display: inline-block;
      margin-top: 10px;
    }
    .__radio-fake-ui input[type=radio] {
      display: none !important;
    }
    .__radio-fake-ui .__radio-fake-ui-target {
      display: inline-block;
      width: 22px;
      height: 22px;
      background: #f6fff9;
      border: solid 2px #1d6839;
      border-radius: 5px;
      position: relative;
      transition: .3s ease;
      -webkit-transition: .3s ease;
    }
    .__radio-fake-ui .__radio-fake-ui-target:after {
      content: "";
      width: 66%;
      height: 40%;
      border: solid 1px #FFF;
      border-width: 2px 2px 0 0;
      position: absolute;
      left: 50%;
      top: 42%;
      transform: translate(-50%, -50%) rotate(132deg);
      -webkit-transform: translate(-50%, -50%) rotate(132deg);
      opacity: 0;
    }
    .__radio-fake-ui > input[type=radio]:checked + .__radio-fake-ui-target {
      background: #1d6839;
    }
    .__radio-fake-ui > input[type=radio]:checked + .__radio-fake-ui-target:after {
      opacity: 1;
    }

    .green-product-gifts__product-price{
      text-decoration: line-through;
      color: #777;
    }

    .green-product-gifts__tier-item > h4{
      font-size: 1.5em;
      font-weight: bold;
    }

    @media (max-width: 1920px){
      .green-product-gifts__select-item label {
        display: flex;
        flex-flow: row-reverse;
      }
      .green-product-gifts__item img {
        /* width: 20%; */
        flex: 0 0 50px;
        max-width: 60px;
      }
      .product-gift-content {
        flex: 1;
        text-align: left;
        padding-right: 10px;
      }
      .green-product-gifts__tier-item {
        width: 100% !important;
        border-bottom: 1px solid #d1d1d1;
        padding-bottom: 10px;
      }
    }

    /* free gift */
    .bt_free_gifts .bt_free_gifts_list {
      list-style: none;
    }

    .bt_free_gifts .bt_free_gifts_header span {
      display: block;
      text-align: center;
    }

    span.bt_gift_qualified {
      text-transform: uppercase;
      margin-bottom: 10px;
      font-weight: bolder;
      font-size: 0.75rem;
    }

    span.bt_gift_notice {
      color: #000;
      font-weight: bold;
      font-size: 0.8rem;
    }

    span.bt_gift_notice b {
      color: #078c4d;
    }

    li.bt_free_gift_item {
      flex-wrap: wrap;
      opacity: 0.7;
      margin-left: 0 !important;
    }
    li.bt_free_gift_item .bt_free_gift_item_product {
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
    }
    li.bt_free_gift_item .bt_free_gift_item_product + .bt_free_gift_item_product {
      margin-top: 30px;
    }
    li.bt_free_gift_item .bt_free_gift_item_product + .bt_free_gift_item_product:before {
      content: "OR";
      text-align: center;
      display: block;
      width: 100%;
      position: absolute;
      top: -30px;
      left: 0;
    }

    li.bt_free_gift_item .bt_free_gift_item_col2 {
      flex: 1;
      padding: 0 10px;
    }

    li.bt_free_gift_item h4 {
      margin-bottom: 0;
      color: #078c4d;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 12px;
      text-align: center;
    }

    .bt_free_gift_item_col2 {
      font-size: 0.75rem;
    }

    span.bt_gift_notice {
      color: var(--color-pecan);
    }

    li.bt_free_gift_item .bt_free_gift_item_col3 {
      color: #ffb92c;
      font-size: 22px;
    }

    li.bt_free_gift_item.bt_free_gift_item_qualified {
      opacity: 0.5;
    }

    li.bt_free_gift_item.bt_free_gift_item_qualified .bt_free_gift_item_col3 {
      color: #078c4d;
    }

    li.bt_free_gift_item img {
      width: 60px;
      border-radius: 5px;
      border: 1px solid #999;
    }

    .bt_progressing_wrap {
      height: 10px;
      font-size: 0;
      background: #eee;
      border-radius: 20px;
      margin: 30px 0;
      overflow: hidden;
    }

    .bt_progressing_wrap .bt_progressing {
      background: #078c4d;
      height: 10px;
      background-color: #046738;
      background-size: 30px 30px;
      background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
      -webkit-animation: animate-stripes 3s linear infinite;
      animation: animate-stripes 3s linear infinite;
      border-radius: 0.5rem;
    }

    .woocommerce-mini-cart-item .bt_free_gift_label {
      position: absolute;
      right: 0;
      bottom: 0;
    }

    .bt_free_gift_label {
      white-space: nowrap;
      color: #fff;
      background: #1ebb71;
      font-size: 0.6rem;
      font-weight: bold;
      padding: 2px;
      border-radius: 4px;
      display: block;
      width: fit-content;
      text-align: center;
    }

  </style>
  <script>
    ;((w, $) => {

    })(window, jQuery)
  </script>
  <?php
  
});