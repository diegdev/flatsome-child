<?php
defined( 'ABSPATH' ) || exit; 
extract($args);
?>

<div class="daily-deal-container">
  <h3 class="daily-deal-title">
    Today's Daily Deal
  </h3>
  <div class="daily-deal-flex">
    <a href="<?php echo $link; ?>"><img src="<?php echo $image_src; ?>" class="daily-deal-image" /></a>
    <div class="daily-deal-content-wrapper">
      <div class="daily-deal-content">
        <h3 class="daily-daily-product-name">
            <a href="<?php echo $link; ?>"><?php echo $title; ?></a>
        </h3>
        <p class="daily-deal-discount-amount">
        <?php echo $discount_amount; ?>% OFF
        </p>
        <a class="daily-deal-shop-now" href="<?php echo $link; ?>">
        Shop Now
        </a>
      </div>
    </div>
  </div>
</div>
