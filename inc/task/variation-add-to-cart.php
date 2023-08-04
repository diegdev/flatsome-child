<?php 
/**
 * Task: https://green-society.monday.com/boards/1418964603/views/41399451
 */

add_action( 'wp_footer', function() {

  ?>
  <script>
    ((w, $) => {
      $(w).on('load', () => {
        let count = 0;  
        const activeFirstProductVariant = () => {
            $('.ux-swatches-fake').each(function() {
              if($(this).find('.selected').length > 0) {
                clearInterval(interval);
                return;
              }
              $(this).find('.fake-s-item:not(.__out-stock)').first().click();
            });
            count ++;
            if (count > 8){
                clearInterval(interval);    
            }
        }
        let interval = setInterval(() => {
          activeFirstProductVariant();
        }, 1000);
        
      })

    })(window, jQuery);
  </script>
  <?php
} );