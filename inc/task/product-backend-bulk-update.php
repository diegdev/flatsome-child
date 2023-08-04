<?php 
/**
 * Task: https://green-society.monday.com/boards/1418964603/views/41399451/pulses/3380130155
 */

add_action('admin_footer', function() {
  if(!isset($_GET['post_type']) || $_GET['post_type'] != 'shop_order') return;
  ?>
  <script>
    ((w, $) => {
      const pushCheckbox = ($form, $selectedList) => {
        $form.find('.post-list-custom').remove();
        $form.append($(`<div>`, {
          class: 'post-list-custom',
          style: 'display: none',
        }).append($selectedList));
      }

      const fixOrderBulkAction = () => {
        const $form = $('#posts-filter');
        const $list = $('table.wp-list-table');

        $list.on('change', 'input[type=checkbox][name="post[]"], input#cb-select-all-1', e => {
          const $selected = $list.find('input[type=checkbox][name="post[]"]:checked');
          pushCheckbox($form, $selected.clone());
        })
      }

      $(() => {
        fixOrderBulkAction();
      })
    })(window, jQuery)
  </script>
  <?php
});