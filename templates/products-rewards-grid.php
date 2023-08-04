<?php
defined( 'ABSPATH' ) || exit; 

$rewards_items = 8;

?>
<?php if(!is_user_logged_in()): ?>
  <div class="rewards-product-grid-message">
    <h4><strong>Log in to view the rewards!</strong></h4>
  </div>
  <?php endif; ?>
<div class="rewards-products-grid">
    <?php for($i = 1; $i <= $rewards_items; $i++){ 

    $reward = get_field('reward_'.$i,'option'); 
    
    if($reward['rw_product']){
        $product = wc_get_product($reward['rw_product']);
  
        $product_name = $product->get_name();
        $product_image = $product->get_image('thumbnail');
        $offer_description = $reward['offer_description'];
        $product_permalink = $product->get_permalink();
        $star_amount = $i . 0 . "<i class='um-faicon-star'></i>"; ?>

  <div class="rewards-product">
    <div class="rewards-product-image">
        <?php echo $product_image; ?>
    </div>
    <div class="rewards-product-description">
      <h2><?php echo $product_name; ?></h2>
      <h4><?php echo $star_amount; ?></h4>
      <p><?php echo $offer_description; ?></p>
      <a href="<?php echo $product_permalink; ?>">View Reward</a>
    </div>
  </div>
  <?php } 
     } ?>
</div>
