<?php 
/**
 * Product like, count
 * https://green-society.monday.com/boards/1418964603/views/41399451/pulses/3340768603
 */

if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array(
    'key' => 'group_636da724a56bc',
    'title' => 'Like / Hearts',
    'fields' => array(
      array(
        'key' => 'field_636da7522ebc8',
        'label' => 'Enable',
        'name' => 'like_hearts_enable',
        'type' => 'true_false',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '', 
          'id' => '',
        ),
        'message' => '',
        'default_value' => 0,
        'ui' => 0,
        'ui_on_text' => '',
        'ui_off_text' => '',
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'theme-notice-page-checkout-settings',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'field',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
  ));
  
endif;

function green_create_db_plike() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE if not exists `{$wpdb->base_prefix}plike` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `post_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`ID`)
  ) $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

add_action('wp_head', function() {
  if(isset($_GET['create_table_plike'])) {
    green_create_db_plike(); // create database;
    echo 'create table success';
  }
});

/**
 * Check liked by user
 */
function green_plike_is_liked($pID, $uID) { 
  global $wpdb;
  $result = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(*) FROM `{$wpdb->base_prefix}plike` 
      WHERE `post_id` = %d AND `user_id` = %d",
      $pID, $uID
    )
  );

  return (($result && (int) $result > 0) ? true : false);
}

function green_plike_get_list_by_user($uID = 0) {
  global $wpdb;
  $q = "SELECT * FROM `{$wpdb->base_prefix}plike` WHERE `user_id` = {$uID}";
  $result = $wpdb->get_results($q, ARRAY_A);
  return $result;
}

/**
 * Add like
 */
function green_plike_add($pID, $uID) {
  global $wpdb;

  if(green_plike_is_liked($pID, $uID)) {
    return true;
  }

  $table = $wpdb->prefix.'plike';
  $data = ['post_id' => $pID, 'user_id' => $uID];
  $format = ['%d','%d'];
  $wpdb->insert($table, $data, $format);
  return $wpdb->insert_id;
}

/**
 * Remove like
 */
function green_plike_remove($pID, $uID) {
  global $wpdb;
  return $wpdb->delete($wpdb->prefix . 'plike', ['post_id' => $pID, 'user_id' => $uID], ['%d', '%d']);
}

/**
 * Get total liked
 */
function green_plike_total_like($pID) {
  global $wpdb;
  $result = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(*) FROM `{$wpdb->base_prefix}plike` 
      WHERE post_id = %d",
      $pID
    )
  );

  return (int) $result;
}

function green_plike_is_enable() {
  $enable = get_field('like_hearts_enable', 'option');
  return $enable ? true : false;
}

function green_plike_ui($pID = 0) {

  if(green_plike_is_enable() == false) return;

  global $post;
  $_pID = $pID ? $pID : $post->ID;
  // $totalLiked = green_plike_total_like($_pID);
  $totalLiked = green_get_like_total($_pID);
  $currentUserLiked = green_plike_current_user_liked($_pID);
  ob_start();
  ?>
  <div 
    id="plike-<?php echo $_pID; ?>" 
    class="plike <?php echo apply_filters('plike/classes', '', $_pID); ?>" 
    data-plike 
    data-pid="<?php echo $_pID ?>">
    <div class="plike__inner">
      <?php if($totalLiked > 0) : ?>
      <span class="__total-liked"><?php echo sprintf(
        '%s %s', 
        $totalLiked, 
        _n('Like', 'Likes', $totalLiked)); ?></span>
      <?php endif; ?>
      <span class="__like-icon"><?php echo green_plike_icon($currentUserLiked ? 'heartBold' : 'heartBold'); ?></span>
    </div>
  </div> <!-- .plike -->
  <?php 
  return ob_get_clean();
}

function green_plike_current_user_liked($pID) {
  if(!is_user_logged_in()) return false;
  
  $uID = get_current_user_id();
  if(green_plike_is_liked($pID, $uID)) {
    return true;
  }

  return false;
}

add_filter('plike/classes', function($classes, $pID) {
  if(green_plike_current_user_liked($pID)) { 
    return $classes . ' ' . '__liked __removable';
  }

  return $classes;
}, 10, 2);

add_filter('plike/classes', function($classes, $pID) {
  if(!is_user_logged_in()) { 
    return $classes . ' ' . '__non-logged';
  }

  return $classes;
}, 5, 2);

add_action('woocommerce_before_single_product_summary', function() {
  echo green_plike_ui();
}, 5);

add_action('wp_footer', function() {
  ?>
  <script>
    ((w, $) => {
      'use strict';
      const ajax_url = `<?php echo admin_url('admin-ajax.php'); ?>`;
      const user_logged_in = `<?php echo is_user_logged_in() ?>`;

      const _request = async (data) => {
        const result = await $.ajax({
          type: 'POST',
          url: ajax_url,
          data,
          error: (e) => {
            console.log(e);
          }
        });
        return result;
      }

      const bodyTriggerHandle = () => {
        $('body').on('plike::addLike', async (_, pID, callback) => {
          const result = await _request({
            action: 'green_ajax_plike_add',
            postID: pID,
          });

          // Update UI
          const { success, fragments } = result;
          if(success == true) {
            updateUI(fragments);
          } else {

          }

          callback ? callback.call('', result) : '';
        })

        $('body').on('plike::removeLike', async (_, pID, callback) => {
          const result = await _request({
            action: 'green_ajax_plike_remove',
            postID: pID,
          });

          const { success, fragments } = result;
          if(success == true) {
            updateUI(fragments);
          } else {

          }

          callback ? callback.call('', result) : '';
        })
      }

      const updateUI = (fragments) => { 
        $.each(fragments, (selector, temp) => {
          const oldElem = $(selector);
          if(oldElem.length <= 0) return;
          oldElem.after(temp);
          oldElem.remove();
        })
      }

      const clickLikeHandle = () => { 
        $('body').on('click', '*[data-plike]', function(e) {
          e.preventDefault();
          const self = $(this);
          const pid = $(this).data('pid');

          if(user_logged_in != '1') {
            w.location.href = '/account/';
            return;
          }

          self.addClass('__handle');

          /**
           * Remove liked
           */
          if(self.hasClass('__removable')) {
            $('body').trigger('plike::removeLike', [pid, (result) => {
              self.removeClass('__handle');
            }]);
            return;
          }

          /**
           * Add like
           */
          $('body').trigger('plike::addLike', [pid, (result) => {
            self.removeClass('__handle');
          }]);
        })
      }

      const pLike = () => {
        bodyTriggerHandle();
        clickLikeHandle();
      }

      $(() => {
        pLike();
      })
    })(window, jQuery);
  </script>
  <?php 
});

function green_plike_fragments($pid) {
  return apply_filters('plike::fragments', [
    '#plike-' . $pid => green_plike_ui($pid),
  ]);
}

function green_plike_handle($action, $pID, $uID) {
  // wp_send_json([$action, $pID, $uID]);
  switch($action) {
    case 'add': 
      return green_plike_add($pID, $uID);
      break;

    case 'remove': 
      return green_plike_remove($pID, $uID);
      break;
  }
}

function green_ajax_plike_add() {
  if(!is_user_logged_in()) {
    wp_send_json([
      'success' => false,
      'message' => __('Please login...!', 'green'),
    ]);
  }

  $uID = get_current_user_id();
  $result = green_plike_handle('add', (int) $_POST['postID'], $uID);

  wp_send_json([
    'success' => true,
    'result' => $result,
    'fragments' => green_plike_fragments((int) $_POST['postID']),
  ]);
}

add_action('wp_ajax_green_ajax_plike_add', 'green_ajax_plike_add');
add_action('wp_ajax_nopriv_green_ajax_plike_add', 'green_ajax_plike_add');

add_action('wp_ajax_green_ajax_plike_remove', function() {
  if(!is_user_logged_in()) {
    wp_send_json([
      'success' => false,
      'message' => __('Please login...!', 'green'),
    ]);
  }

  $uID = get_current_user_id();
  $result = green_plike_handle('remove', (int) $_POST['postID'], $uID);

  wp_send_json([
    'success' => true,
    'result' => $result,
    'fragments' => green_plike_fragments((int) $_POST['postID']),
  ]);
});

add_action('wp_ajax_green_ajax_plike_fragments', function() {
  wp_send_json(green_plike_fragments($pid));
});

add_action('wp_head', function() {
  ?>
  <style>
  .badge-container.absolute.left {
    left: 12px;
  } 
  .plike {
    font-family: Arial;
    color: black;
    cursor: pointer;
    margin-bottom: 1em;
    position: absolute;
    z-index: 9;
    right: 5px;
    top: 28px;
  }
  .plike.__non-logged {
    /* pointer-events: none; */
  }
  .plike.__handle {
    opacity: .4;
    pointer-events: none;
  }
  .plike__inner {
    display: inline-flex;
    align-items: center;
    position: relative;

    padding: 5px 10px;
    box-sizing: border-box;
    border-radius: 4px;
    box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
    background: white;

    /* border: solid 1px #ececec; */
  }
  .__liked .plike__inner {
    /* background: #078c4d;
    color: white; */
  }

  .plike__inner:hover svg,
  .__liked .plike__inner svg {
    /* fill: white; */
    fill: #ff0000 !important;
  }
  .plike__inner .__like-icon {
    width: 18px;
    height: 18px;
  }
  .plike__inner .__like-icon svg {
    width: 100%;
    line-height: 0;
    fill: #a0a0a0;
    transition: .3s ease;
    -webkit-transition: .3s ease;
  }
  .plike__inner .__total-liked {
    margin-right: 6px;
    font-family: 'Poppins';
    font-weight: 300;
    font-size: 13px;
    line-height: 0;
  }

  /**
   * My Account
   */
  .plike-tab-container {
    font-family: Arial;
    color: black;
    padding: 1.5em 0;
  }
  .myaccount-plike-loop {
    display: flex;
    flex-wrap: wrap;
    margin: 0 15px;
  }
  .myaccount-plike-loop > * {
    width: calc(100% / 3);
    padding: 0 15px;
    box-sizing: border-box;
    margin-bottom: 30px;
  }
  .myaccount-plike-loop > * > .col-inner {
    border: solid 1px #eee;
    border-radius: 3px;
    overflow: hidden;
    background: #fcfcfc;
    height: 100%;
    position: relative;
  }
  .myaccount-plike-loop .box-text.text-center {
    box-sizing: border-box;
  }
  .myaccount-plike-loop .add_to_cart_button {
    border-bottom: solid 2px #046738 !important;
  }
  .myaccount-plike-loop .plike {
    position: absolute;
    right: 5px;
    top: 28px;
    z-index: 9;
  }
  .myaccount-plike-loop .plike__inner .__like-icon {
    width: 18px;
    height: 18px;
  }
  @media(max-width: 970px) {
    .myaccount-plike-loop > * {
      width: calc(100% / 2);
    }
  }
  @media(max-width: 425px) {
    .myaccount-plike-loop > * {
      width: calc(100% / 1);
    }
  }
  .related-products-wrapper .plike,
  .products .plike {
    position: absolute;
    right: 5px;
    top: 5px;
    z-index: 9;
  }
  .related-products-wrapper .plike .plike__inner .__like-icon,
  .products .plike .plike__inner .__like-icon {
    width: 18px;
    height: 18px;
  }
  .badge-container {
    margin-top: 42px;
  }
  .product-gallery .plike {
    position: absolute;
    right: 25px;
    top: 10px;
    z-index: 9;
  }
  .equalize-box > .product .plike,
  .flickity-slider > .product .plike {
    position: absolute;
    z-index: 9;
    right: 5px;
    top: 28px;
  }

  /* .equalize-box .bt_product_extra_meta,
  .flickity-slider .bt_product_extra_meta,
  .related .bt_product_extra_meta,
  .products .bt_product_extra_meta {
    bottom: 5px !important;
    top: auto !important;
    right: 5px !important;
  } */

  .myaccount-plike-loop .__total-liked,
  .equalize-box .__total-liked,
  .related .__total-liked,
  .products .__total-liked,
  .flickity-slider .__total-liked {
    display: none;
  }

  .equalize-box .bt_product_extra_meta .custom-thc-fr,
  .flickity-slider .bt_product_extra_meta .custom-thc-fr,
  .related .bt_product_extra_meta .custom-thc-fr,
  .products .bt_product_extra_meta .custom-thc-fr {
    background: #046738 !important;
    color: white !important;
    /* border-radius: 3px 0 0 0; */
  }
  .equalize-box .bt_product_extra_meta .custom-cbd-fr,
  .flickity-slider .bt_product_extra_meta .custom-cbd-fr,
  .related .bt_product_extra_meta .custom-cbd-fr,
  .products .bt_product_extra_meta .custom-cbd-fr {
    background: #31ae73 !important;
    color: white !important;
    /* border-radius: 0 0 3px 0; */
  }
  .equalize-box .badge-container,
  .flickity-slider .badge-container,
  .related .badge-container,
  .products .badge-container { 
    margin-top: 5px !important;
    font-weight: 300 !important;
    font-size: 13px;
    font-family: inherit;
  }

  .box-image .bt_product_extra_meta {
    position: absolute;
    top: 0;
    right: 0px;
    z-index: 9;
    width: 100%!important;
    display: -webkit-box!important;
    display: -ms-flexbox!important;
    display: flex!important;
  }
  .box-image .bt_product_extra_meta .custom-thc-fr {
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px 6px;
    color: blue;
    font-size: 10px;
    width: 100%!important;
    
    background: #046738 !important;
    color: white !important;
    border-radius: 3px 0 0 0;
  }
  .box-image .bt_product_extra_meta .custom-cbd-fr {
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px 6px;
    color: #cacf42;
    font-size: 10px;
    width: 100%!important;
    white-space: nowrap;

    background: #31ae73 !important;
    color: white !important;
    border-radius: 0 3px 0 0;
  }

  .myaccount-plike-loop p {
    margin: 0 !important;
  }

  .myaccount-plike-loop .product-small.box  {
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .myaccount-plike-loop .product-small.box .box-text {
    position: initial;
    padding-bottom: 50px;
  }

  .myaccount-plike-loop .add-to-cart-button {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    margin: 0;
    padding: 0;
  }

  .myaccount-plike-loop .add-to-cart-button > a {
    width: 100%;
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    background: #046738;
    border-color: #046738;
    color: white;
    border-radius: 0;
  }

  .myaccount-plike-loop .badge-container.absolute.left.top.z-1 {
    left: 12px !important;
    top: -18px !important;
  }
  </style>
  <?php
});

function green_plike_icon($name) {
  $icons = [
    'likeBold' => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <g> <g> <path d="M512,231.509c0-21.515-17.441-38.956-38.957-38.956H339.478v-1.113c26.381-40.5,39.45-91.473,39.45-144.683 c0-21.52-17.445-38.963-38.964-38.963c-21.52,0-38.964,17.444-38.964,38.963c0,74.859-53.968,136.529-126.259,144.833v312.616 h250.435c21.515,0,38.957-17.44,38.957-38.956c0-21.515-17.441-38.957-38.957-38.957h15.957c21.515,0,38.957-17.44,38.957-38.956 c0-21.515-17.441-38.957-38.957-38.957h15.957c21.515,0,38.956-17.44,38.956-38.957c0-21.515-17.441-38.956-38.956-38.956h15.955 C494.559,270.466,512,253.024,512,231.509z"/> </g> </g> <g> <g> <rect x="107.954" y="192.555" width="33.391" height="311.652"/> </g> </g> <g> <g> <rect y="192.555" width="74.566" height="311.652"/> </g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>',
    'likeLine' => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"> <g> <g> <path d="M497.02,306.152c0-10.83-3.313-20.9-8.979-29.253C502.442,267.586,512,251.399,512,233.01 c0-28.808-23.437-52.245-52.245-52.245h-99.093c17.343-35.287,26.412-76.656,26.412-121.197c0-28.812-23.44-52.251-52.252-52.251 c-28.811,0-52.251,23.44-52.251,52.251c0,67.96-52.064,121.195-118.53,121.195H0v323.92h414.817 c28.808,0,52.245-23.437,52.245-52.245c0-10.83-3.314-20.9-8.979-29.253c14.401-9.313,23.958-25.501,23.958-43.89 c0-10.83-3.314-20.901-8.979-29.253C487.463,340.728,497.02,324.541,497.02,306.152z M85.674,473.336H31.347V212.11h54.327 V473.336z M148.368,473.336h-31.347V212.11h31.347V473.336z M459.755,253.908h-55.387v31.347h40.407 c11.523,0,20.898,9.375,20.898,20.898c0,11.523-9.375,20.898-20.898,20.898h-40.407v31.347h25.429 c11.523,0,20.898,9.375,20.898,20.898c0,11.523-9.375,20.898-20.898,20.898h-25.429v31.347h10.449 c11.523,0,20.898,9.375,20.898,20.898c0,11.523-9.375,20.898-20.898,20.898H179.715v-262.03 c34.656-3.553,66.527-18.869,91.008-44.027c27.855-28.626,43.196-66.878,43.196-107.711c0-11.526,9.378-20.904,20.904-20.904 c11.527,0,20.905,9.378,20.905,20.904c0,48.618-11.927,92.628-34.493,127.271l-2.541,3.9v21.373h141.061 c11.523,0,20.898,9.375,20.898,20.898C480.653,244.533,471.278,253.908,459.755,253.908z"/> </g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>',
    'heartBold' => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 343.422 343.422" style="enable-background:new 0 0 343.422 343.422;" xml:space="preserve"> <g> <g id="Artwork_15_"> <g id="Layer_5_15_"> <path d="M254.791,33.251c-46.555,0-76.089,51.899-83.079,51.899c-6.111,0-34.438-51.899-83.082-51.899 c-47.314,0-85.947,39.021-88.476,86.27c-1.426,26.691,7.177,47.001,19.304,65.402c24.222,36.76,130.137,125.248,152.409,125.248 c22.753,0,127.713-88.17,152.095-125.247c12.154-18.483,20.731-38.711,19.304-65.402 C340.738,72.272,302.107,33.251,254.791,33.251"/> </g> </g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>',
    'heartLine' => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 297 297" style="enable-background:new 0 0 297 297;" xml:space="preserve"> <g> <path d="M148.5,273.96c-1.572,0-3.145-0.36-4.589-1.083c-1.455-0.728-36.028-18.149-71.1-47.375 c-20.824-17.353-37.475-35.289-49.488-53.31C7.847,148.979,0,125.504,0,102.421C0,58.65,35.61,23.04,79.381,23.04 c29.604,0,55.474,16.286,69.119,40.372c13.645-24.086,39.516-40.372,69.119-40.372c43.77,0,79.381,35.61,79.381,79.381 c0,23.083-7.847,46.558-23.323,69.771c-12.014,18.021-28.664,35.957-49.488,53.311c-35.071,29.226-69.645,46.647-71.1,47.374 C151.645,273.6,150.072,273.96,148.5,273.96z M79.381,43.564c-32.453,0-58.856,26.403-58.856,58.856 c0,75.731,104.584,136.931,127.972,149.665c23.379-12.75,127.979-74.044,127.979-149.665c0-32.453-26.403-58.856-58.856-58.856 c-32.454,0-58.856,26.403-58.856,58.856c0,5.667-4.596,10.263-10.263,10.263s-10.263-4.596-10.263-10.263 C138.237,69.968,111.835,43.564,79.381,43.564z"/> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>',
  ];
  return $icons[$name];
}

function green_shortcode_plike_fav_list($atts) {
  $a = shortcode_atts([
    'user_id' => 0,
    'class' => '',
  ], $atts);

  $items = green_plike_get_list_by_user($a['user_id']);
  $products = [];

  if($items && count($items) > 0) {
    $products = array_map(function($item) {
      return (int) $item['post_id']; // wc_get_product($item['post_id']);
    }, $items);
  } else {

  }

  ob_start();
  ?>
  <div class="post-likes">

    <?php do_action('plike/before_list', $products, $a); ?>

    <?php 
    if(count($products) == 0) {
      echo '<div class="myaccount-plike-no-item">';
        echo '<p>You have no liked products, check out the shop to start saving your favourites!</p>';
        echo '<a style="display: inline-block;background:#046839;padding:0.5rem 1rem;color:#fff;font-weight:bold;border-radius:4px;margin-top:12px;" href="https://greensociety.cc/shop">View Shop</a>';
      echo '</div>';
    } else {
      $args = [
        'post_type' => 'product',
			  'posts_per_page' => -1,
        'post__in' => $products,
      ];
      
      $loop = new WP_Query($args);

      if ( $loop->have_posts() ) {
        echo '<div class="myaccount-plike-loop">';
        while ($loop->have_posts()) : $loop->the_post();
          wc_get_template_part('content', 'product');
        endwhile;
        echo '</div>';
      } else {
        echo __('No products found');
      }

      wp_reset_postdata();
    }
    ?>

    <?php do_action('plike/after_list', $products, $a); ?>

  </div>
  <?php
  return ob_get_clean();
}

add_shortcode('green_post_like', 'green_shortcode_plike_fav_list');

add_action('woocommerce_before_shop_loop_item', function() {
  global $post;
  echo green_plike_ui($post->ID);
});

add_action('flatsome_account_links', function() {
  ob_start();
  ?>
  <li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--plike">
    <a href="/account/post_like/"><?php _e('Likes', 'green') ?></a>
  </li>
  <?php
  echo ob_get_clean();
});

{
  /**
   * Add likes tab in my-account
   */
  function green_um_plike_tab_register($tabs) {
    $tabs[100]['post_like']['icon'] = 'um-faicon-thumbs-up';
    $tabs[100]['post_like']['title'] = __('Likes', 'green');
    $tabs[100]['post_like']['custom'] = true;
    $tabs[100]['post_like']['show_button'] = false;
    
    return $tabs;
  }

  add_filter('um_account_page_default_tabs_hook', 'green_um_plike_tab_register', 99999, 1);

  function green_um_account_tab__post_like($info) {
    global $ultimatemember;
    extract($info);
    $output = $ultimatemember->account->get_tab_output('post_like');
    if ($output) { echo $output; }
  }

  add_action('um_account_tab__post_like', 'green_um_account_tab__post_like');

  function green_um_account_content_hook_post_like($output){
    $current_user_id = get_current_user_id();
    ob_start();
    ?>
    <div class="plike-tab-container">
      <?php echo do_shortcode("[green_post_like user_id={$current_user_id}]"); ?>
    </div>
    <?php
    return $output . ob_get_clean();
  }

  add_filter('um_account_content_hook_post_like', 'green_um_account_content_hook_post_like');
  /**
   * End likes tab
   */
}

{
  /**
   * Backend count number likes product column
   * 
   */
  add_filter( 'manage_product_posts_columns', 'green_product_add_likes_columns' );
  function green_product_add_likes_columns($columns) {
    $columns['likes'] = __('Likes', 'green');
    return $columns;
  }

  function green_product_like_label($num = 0, $post_id) {
    return '<span class="number-label __plike-label-'. $post_id .'">' . $num . ' ' . _n('like', 'likes', $num, 'green') . '</span>';
  }

  function green_get_like_total($pid = 0) {
    $realLike = green_plike_total_like($pid);
    $fakeLike = get_post_meta((int) $pid, '__like_number_start', true);

    return (int) $realLike + (int) $fakeLike;
  }

  add_action('manage_product_posts_custom_column' , 'green_product_add_likes_value_columns', 10, 2);
  function green_product_add_likes_value_columns($column, $post_id) {
    switch ($column) {
      case 'likes':
        $totalLike = green_get_like_total($post_id);
        echo green_product_like_label($totalLike, $post_id);
        do_action('green/after_total_like_value', $post_id, $totalLike);
        break;
    }
  }

  /**
   * Button set like value init
   */
  add_action('green/after_total_like_value', function($post_id, $totalLike) {
    ?>
    <div class="like-number-start">
      <?php _e('Start with', 'green') ?> 
      <input 
        class="like-number-start-field" 
        type="number" 
        min=0 
        placeholder="0" 
        value="<?php echo get_post_meta((int) $post_id, '__like_number_start', true); ?>"
        data-product="<?php echo $post_id; ?>"> 
        <?php _e('like(s)', 'green') ?>
    </div>
    <?php 
  }, 20, 2);
  
  function green_ajax_update_like_number_start() {
    // wp_send_json($_POST);
    $pid = $_POST['pid'];
    $number = $_POST['number'];
    update_post_meta((int) $pid, '__like_number_start', (int) $number);

    $totalLike = green_get_like_total($pid);
    wp_send_json([
      'success' => true,
      'pid' => $pid,
      '__html' => green_product_like_label($totalLike, $pid),
    ]);
  }

  add_action('wp_ajax_green_ajax_update_like_number_start', 'green_ajax_update_like_number_start');
  add_action('wp_ajax_nopriv_green_ajax_update_like_number_start', 'green_ajax_update_like_number_start');

  /**
   * Backend script modal
   */
  add_action('admin_footer', function() {
  ?>
  <script>
    ((w, $) => {
      const ajaxUrl = `<?php echo admin_url('admin-ajax.php'); ?>`;
      
      const updateLikeNumberStartRequest = async (pid, number) => {
        const result = await $.ajax({
          type: 'POST',
          url: ajaxUrl,
          data: {
            action: 'green_ajax_update_like_number_start',
            pid, 
            number
          }
        });

        return result;
      }

      const updateLikeNumberStart = async (_pid, num, callback) => {
        const { success, pid, __html } = await updateLikeNumberStartRequest(_pid, num);
        
        if(true == success) {
          const selector = $(`.__plike-label-${ pid }`);
          selector.after(__html);
          selector.remove();
        }

        return { success, pid, __html }
      }

      const likeNumberStartChange = () => {
        $('body').on('change', 'input.like-number-start-field', async function(e) {
          let num = this.value;
          const pid = $(this).data('product');

          $(this).parent('.like-number-start').css({
            opacity: .4,
            pointerEvents: 'none',
          });

          const result = await updateLikeNumberStart(pid, num);

          $(this).parent('.like-number-start').css({
            opacity: '',
            pointerEvents: '',
          });
        })
      }

      $(likeNumberStartChange)
    })(window, jQuery)
  </script>
  <?php
  });

  /**
   * CSS backend
   */
  add_action('admin_head', function() {
  ?>
  <style>
  .number-label {
    background: #007cba;
    color: white;
    border-radius: 3px;
    min-width: 12px;
    display: inline-block;
    text-align: center;
    padding: 9px 3px;
    height: auto;
    line-height: 0;
    font-size: 11px;
  }
  .like-number-start {

  }
  .like-number-start .like-number-start-field {
    width: 55px;
    border: none;
    border-bottom: solid 1px #b3b3b3;
    border-radius: 0;
    background: no-repeat;
    padding: 0 0 0 6px;
    min-height: auto;
    color: black;
  }

  .column-likes {
    width: 160px;
  }
  </style>
  <?php
  });
}

/**
 * Fix product title display wrong in liked my-account page
 */
// add_filter('the_title', function($post_title, $post_id) {
//   if(get_post_type($post_id) == 'product') {
//     // return get_the_title($post_id);
//   } else {
//     return $post_title;
//   }
//   // return $post_title . ' -- test hook' . ' ' . $post_id;
// }, 20, 2);