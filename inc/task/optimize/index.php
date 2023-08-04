<?php 
/**
 * Optimize 
 */

require(__DIR__ . '/home.php');
require(__DIR__ . '/single-product.php');

/**
 * Remove lazyload first slide image homepage 
 */
add_action('wp_footer', function() {
  if(!is_front_page()) return;
  ?>
  <script>
    ;((w, $) => {
      'use strict';

      $('.jy-lazy-load-remove').find('img').removeAttr('loading');
      $('.jy-lazy-load-remove').find('img').each(function() {
        $(this).attr('src', $(this).data('src'));
      })
    })(window, jQuery);
  </script>
  <?php
});
/**
 * End remove lazyload first slide image homepage 
 */