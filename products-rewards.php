<?php
defined( 'ABSPATH' ) || exit;
$rewards_items = 8;
extract($args);
$_user_rewards = (int)$_user_rewards;
$rewards_able = bt_get_user_rewards();
?>
<div class="products-rewards tabbed-content">
  <h4 class="bt_rewards_heading text-center">You currently have <span class="bt_rewards_able"><?php echo $rewards_able*10; ?></span> Stars</h4>
  <ul class="nav nav-line-bottom nav-uppercase nav-size-normal nav-center">
    <?php for($i = 1; $i <= $rewards_items; $i++){ ?>
      <li class="tab <?php echo $i == 1 ? 'active' : ''; ?> has-icon <?php echo $i <= $_user_rewards ? 'rewards_item_active' : ''; ?>">
        <a href="#tab_tab-<?php echo $i; ?>-title"><span><?php echo $i; ?></span><i class="um-faicon-star"></i></a>
      </li>
    <?php } ?>
  </ul>
  <div class="tab-panels">
    <?php for($i = 1; $i <= $rewards_items; $i++){ ?>
      <div class="panel <?php echo $i == 1 ? 'active' : ''; ?> entry-content" id="tab_tab-<?php echo $i; ?>-title">
        <?php
        $reward = get_field('reward_'.$i,'option');
        if($reward['rw_product']): ?>

        <div class="single_product row">
          <?php
          $product = wc_get_product($reward['rw_product']);
          $product_id = $product->get_parent_id() ? $product->get_parent_id(): $product->get_id();
          $variation_id = 0;
          $offer_type = $reward['offer_type'];
          $offer_amount = (float)$reward['offer_amount'];
          $offer_description = $reward['offer_description'];
          $price_to_discount = $product->get_price();
          if($offer_type == 'fixed'){
            $discount = floor( $price_to_discount - $offer_amount );
          }else{
            $discount = floor( $price_to_discount - $price_to_discount * ( $offer_amount / 100 ) );
          }

          ?>
          <div class="single_product_left col medium-5 small-12 large-5">
            <?php echo $product->get_image('thumbnail'); ?>
          </div>
          <div class="single_product_right col medium-5 small-12 large-5">
            <h4><?php echo get_the_title($product_id); ?></h4>
            <?php if($offer_description){ ?>
              <div class="product_offer_description">
                <?php echo $offer_description; ?>
              </div>
            <?php } ?>
            <?php if('variation' == $product->get_type()){ $variation_id = $product->get_id(); ?>
              <div class="product_attribute_summary">
                <?php echo($product->get_attribute_summary()); ?>
              </div>
            <?php } ?>
            <span class="woocommerce-Price-amount">
              <?php echo $product->get_price_html(); ?>
            </span>
            <div class="product_price_discount">
              <?php echo wc_price(max($discount,0)); ?>
            </div>
            <?php if($i <= $_user_rewards): ?>
              <div method="POST" action="/account/welcome/" class="claim_reward_form">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                <input type="hidden" name="custom_price" value="<?php echo $discount; ?>" />
                <input type="hidden" name="variation_id" value="<?php echo $variation_id; ?>" />
                <input type="hidden" name="redeem_point" value="<?php echo $i; ?>" />
                <?php if(!bt_matched_cart_items($product_id)): ?>
                  <button class="btn button primary claim_reward" name="claim_reward" type="submit">Claim Reward</button>
                <?php else: ?>
                  <button class="btn button primary claim_reward" disabled name="claim_reward">Added to cart</button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    <?php } ?>
  </div>
</div>
