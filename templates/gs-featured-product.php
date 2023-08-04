<?php
defined( 'ABSPATH' ) || exit; 
extract($args);
?>

<div class="daily-deal-container">
  <h3 class="daily-deal-title">
    <?php echo $title;  ?>
  </h3>
  <div class="daily-deal-flex">
    <a href="<?php echo $permalink; ?>"><?php echo $image; ?></a>
    <div class="daily-deal-content-wrapper">
      <div class="daily-deal-content">
        <h3 class="daily-daily-product-name">
            <a href="<?php echo $permalink; ?>"><?php echo $product_name; ?></a>
        </h3>
        <?php if($discount_amount){
          ?> <p class="daily-deal-discount-amount"> <?php
          echo $discount_amount; ?>% OFF
        </p>
        <?php } ?>
            <div class="product-info">
              <!-- <div class="price">$<?php //echo $price; ?></div> -->
              <?php echo $price; ?>
              <div class="category"><?php echo $category; ?></a>
      </div>
      <?php if($variable === true): ?>
        <a class="daily-deal-shop-now" href="<?php echo $permalink; ?>">
          View Options
        </a>
        <?php else: ?>
        <a class="daily-deal-shop-now non-var" data-product-id="<?php echo $product_id; ?>" href="<?php echo $permalink; ?>">
          Add to Cart
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
