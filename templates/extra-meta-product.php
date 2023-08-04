<?php
defined( 'ABSPATH' ) || exit;
extract($args);
?>
<div class="bt_product_extra_meta">
  <?php if(!empty($thc)): ?>
    <div title="THC Levels" class="custom-thc-fr">
      <?php echo $thc ?>
    </div>
  <?php endif;
  if(!empty($cbd)): ?>
    <div title="CBD Levels" class="custom-cbd-fr">
      <?php echo $cbd ?>
    </div>
  <?php endif;
  if(!empty($psilocybin_Levels)): ?>
    <div title="Psilocybin Levels" class="custom-psilocybin_Levels-fr">
      <?php echo $psilocybin_Levels. " psilocybin" ?>
    </div>
  <?php endif; ?>
</div>
