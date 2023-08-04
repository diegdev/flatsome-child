<?php
add_action( 'wp_enqueue_scripts', 'bt_woo_custom_enqueue_styles' );
function bt_woo_custom_enqueue_styles() {
  $parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
  $theme = wp_get_theme();

  wp_enqueue_style( 'child-woo-custom', get_stylesheet_directory_uri().'/dist/woo-custom.css',
      array( $parenthandle ),
      $theme->get('Version') // this only works if you have Version in the style header
  );

  wp_enqueue_script( 'child-woo-custom', get_stylesheet_directory_uri().'/dist/woo-custom.min.js',
      array( 'jquery' ),
      $theme->get('Version') // this only works if you have Version in the style header
  );
}

/*
 * Add custom CSS
 */
function bt_add_custom_css() {
  ob_start();
  ?>
  <style id="custom-css" type="text/css">
  <?php if(get_theme_mod('type_nav_bottom_color')){ ?>
    #header .menu-mobile-menu-container ul.menu li a{
      color: <?php echo get_theme_mod('type_nav_bottom_color'); ?>;
    }
  <?php } ?>

  <?php if(get_theme_mod('type_nav_bottom_color_hover')){ ?>
    #header .menu-mobile-menu-container ul.menu li a:hover,
    #header .menu-mobile-menu-container ul.menu li li.active > a,
    #header .menu-mobile-menu-container ul.menu li li.current > a,
    #header .menu-mobile-menu-container ul.menu li.current-menu-item a,
    #header .menu-mobile-menu-container ul.menu li a.active,
    #header .menu-mobile-menu-container ul.menu li a.current{
      color: <?php echo get_theme_mod('type_nav_bottom_color_hover'); ?>;
    }
  <?php } ?>
  </style>
  <?php
  $buffer = ob_get_clean();
  echo flatsome_minify_css($buffer);
}
add_action( 'wp_head', 'bt_add_custom_css', 100 );

/*
 * Add mobile menu after header bottom
 */
add_action( 'init', 'bt_add_mobile_menu_location' );
function bt_add_mobile_menu_location() {
  register_nav_menu('top-menu-mobile',__( 'Top Menu - Mobile', 'flatsome' ));
}

add_action( 'flatsome_after_header_bottom', 'bt_add_mobile_menu_after_header_bottom' );
function bt_add_mobile_menu_after_header_bottom() {
  if ( has_nav_menu( 'top-menu-mobile' ) ) {
    wp_nav_menu( array(
      'theme_location' => 'top-menu-mobile',
    ) );
  }
}

/*
 * Add shipping deadline timer to cart page
 */
add_action('woocommerce_before_cart', 'bt_add_shipping_deadline_timer_to_cart_page', 20);
function bt_add_shipping_deadline_timer_to_cart_page() {
  $enable = get_field('sdt_on_off_feature', 'option');
  $text = get_field('sdt_text', 'option');
  $break_text = explode("{timer}", $text);
  $break_before_text = isset($break_text[0]) ? $break_text[0] : '';
  $break_after_text = isset($break_text[1]) ? $break_text[1] : '';
  $hours = get_field('sdt_hours', 'option');

  $today = current_time('Y-m-d', true);
  $time = current_time('H:m', true);

  $break_end_date = explode("-", $today);
  $end_date_year = $break_end_date[0];
  $end_date_month = $break_end_date[1];
  $end_date_day = $break_end_date[2];

  $local_time  = current_datetime();

  if(strtotime($hours) + $local_time->getOffset() <= strtotime($time)) {
    $end_date_day = $end_date_day + 1;
  }

  if( !$enable ) {
    return;
  }

  // if the date is fri, sat, sun, dont't show
  if(date('D') == 'Fri' || date('D') == 'Sat' || date('D') == 'Sun') {
    return;
  }

  $message='<div class="cart-shipping-deadline-timer">';
    $message.='<div class="sdt-text">' . $break_before_text . ' <div class="timer-wrap">' . do_shortcode('[ux_countdown year="' . $end_date_year . '" month="' . $end_date_month . '" day="' . $end_date_day . '" time="' . $hours . '" t_hour_p="h" t_min_p="m" t_sec_p="s" t_hour = "h" t_min="m" t_sec="s"]') . '</div> ' . $break_after_text . '</div>';
    $message.='<div class="sdt-icon"><i class="fa fa-shipping-fast"></i></div>';
  $message.='</div>';

  echo $message;
}

/*
 * Add total savings on cart page
 */
add_action('woocommerce_after_cart_totals', 'bt_add_total_savings_on_cart_page');
function bt_add_total_savings_on_cart_page() {
  global $woocommerce;
  $discount_total = 0;

  foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values) {
    $_product = $values['data'];
    if ( $_product->is_on_sale() ) {
      $regular_price = (float)$_product->get_regular_price();
      $sale_price = (float)$_product->get_sale_price();
      $discount = ($regular_price - $sale_price) * $values['quantity'];
      $discount_total += $discount;
    }
  }

  $points_earned = 0;

  foreach ( WC()->cart->get_cart() as $item_key => $item ) {
  	$points_earned += apply_filters( 'woocommerce_points_earned_for_cart_item', WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $item['data'] ), $item_key, $item ) * $item['quantity'];
  }

  if ( version_compare( WC_VERSION, '2.3', '<' ) ) {
  	$discount = WC()->cart->discount_cart + WC()->cart->discount_total;
  } else {
  	$discount = ( wc_prices_include_tax() ) ? WC()->cart->discount_cart + WC()->cart->discount_cart_tax : WC()->cart->discount_cart;
  }

  $discount_amount = min( WC_Points_Rewards_Manager::calculate_points( $discount ), $points_earned );

  // Apply a filter that will allow users to manipulate the way discounts affect points earned.
  $points_earned = apply_filters( 'wc_points_rewards_discount_points_modifier', $points_earned - $discount_amount, $points_earned, $discount_amount, $discount );

  // Check if applied coupons have a points modifier and use it to adjust the points earned.
  $coupons = WC()->cart->get_applied_coupons();

  $points_earned = WC_Points_Rewards_Manager::calculate_points_modification_from_coupons( $points_earned, $coupons );

  $points_earned = WC_Points_Rewards_Manager::round_the_points( $points_earned );

  $discount_total = $discount_total + WC_Points_Rewards_Manager::calculate_points_value($points_earned);

  echo '<div class="wc-cart-total-savings">' . __('Your total savings today ', 'flatsome') . wc_price( $discount_total + $woocommerce->cart->discount_cart ) . '</div>';
}

function get_points_earned_for_purchase() {
  $points_earned = 0;

  foreach ( WC()->cart->get_cart() as $item_key => $item ) {
  	$points_earned += apply_filters( 'woocommerce_points_earned_for_cart_item', WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $item['data'] ), $item_key, $item ) * $item['quantity'];
  }

  /*
   * Reduce by any discounts.  One minor drawback: if the discount includes a discount on tax and/or shipping
   * it will cost the customer points, but this is a better solution than granting full points for discounted orders.
   */
  if ( version_compare( WC_VERSION, '2.3', '<' ) ) {
  	$discount = WC()->cart->discount_cart + WC()->cart->discount_total;
  } else {
  	$discount = ( wc_prices_include_tax() ) ? WC()->cart->discount_cart + WC()->cart->discount_cart_tax : WC()->cart->discount_cart;
  }

  $discount_amount = min( WC_Points_Rewards_Manager::calculate_points( $discount ), $points_earned );

  // Apply a filter that will allow users to manipulate the way discounts affect points earned.
  $points_earned = apply_filters( 'wc_points_rewards_discount_points_modifier', $points_earned - $discount_amount, $points_earned, $discount_amount, $discount );

  // Check if applied coupons have a points modifier and use it to adjust the points earned.
  $coupons = WC()->cart->get_applied_coupons();

  $points_earned = WC_Points_Rewards_Manager::calculate_points_modification_from_coupons( $points_earned, $coupons );

  $points_earned = WC_Points_Rewards_Manager::round_the_points( $points_earned );
  return apply_filters( 'wc_points_rewards_points_earned_for_purchase', $points_earned, WC()->cart );
}

/*
 * Add message login cart sidebar
 */
//add_action('flatsome_cart_sidebar', 'bt_add_message_login_cart_sidebar', 20);
function bt_add_message_login_cart_sidebar() {
  if( is_user_logged_in() ) {
    return;
  }

  echo '<div class="cart-sidebar-message-login">' .
          __('Not logged in? ', 'flatsome') . '
          <a href="'. um_get_core_page( "login" ) .'" class="login--link" data--open="#login-form-popup">' . __('Click here', 'flatsome') . '</a>' .
          __(' to log in', 'flatsome') . '
        </div>';
}

// Hide product category (based on backend options)
add_filter( 'woocommerce_product_query_tax_query', 'gs_hide_shop_categories_by_role', 99);
function gs_hide_shop_categories_by_role($tquery) {
	$user = wp_get_current_user();
  $show_on_user_roles = get_field('user_role_can_see_product_categories', 'options');
  $hidden_categories = get_field('product_categories_need_to_hidden', 'options');

  if ( count($show_on_user_roles) > 0 && count($hidden_categories) > 0 ) {
      if ( !is_user_logged_in() || (is_user_logged_in() && !count(array_intersect($show_on_user_roles, $user->roles)) > 0) ) {
      		$tquery[] =
      			array(
      				'taxonomy' => 'product_cat',
      				'terms'    => $hidden_categories,
      				'field'    => 'id',
      				'operator' => 'NOT IN'
      			);
    	}
  }
	return $tquery;
}
// Hide shroom product category (only show for CUSTOMERS, Site Admins)
add_filter( 'get_terms', 'gs_get_subcategory_terms', 10, 3 );
function gs_get_subcategory_terms( $terms, $taxonomies, $args ) {
    $new_terms = array();
    $user = wp_get_current_user();
  	$show_on_user_roles = get_field('user_role_can_see_product_categories', 'options');
    $hidden_categories = get_field('product_categories_need_to_hidden', 'options');

    if ( count($show_on_user_roles) > 0 && count($hidden_categories) > 0 ) {
        if ( ( is_shop() || is_product_category() ) && in_array( 'product_cat', $taxonomies )
             && !count(array_intersect($show_on_user_roles, $user->roles)) > 0 ) {

            foreach( $terms as $key => $term ) {
                if ( !in_array( $term->term_id, $hidden_categories ) ) {
                    $new_terms[] = $term;
                }
            }
            $terms = $new_terms;
        }
    }
    return $terms;
}

// Checks current user's role. Handles multiple roles per user.
function gs_is_current_user_role( $roles_to_check ) {
    $current_user       = wp_get_current_user();
    $current_user_roles = ( empty( $current_user->roles ) ? array( '' ) : $current_user->roles );
    $roles_intersect    = array_intersect( $current_user_roles, $roles_to_check );
    return ( !count($roles_intersect) > 0 );
}
// Checks product's category.
function gs_is_product_cat( $product_id, $product_cats ) {
    $current_cats = get_the_terms( $product_id, 'product_cat' );
    if ( $current_cats && ! is_wp_error( $current_cats ) ) {
        $current_cats   = wp_list_pluck( $current_cats, 'term_id' );
        $cats_intersect = array_intersect( $current_cats, $product_cats );
        return ( ! empty( $cats_intersect ) );
    }
    return false;
}
// Checks if the product needs to be hidden.
function gs_do_hide_product( $product_id_to_check ) {
    $show_on_user_roles = get_field('user_role_can_see_product_categories', 'options');
    $hidden_categories = get_field('product_categories_need_to_hidden', 'options');

    if ( !count($show_on_user_roles) > 0 || !count($hidden_categories) > 0 )
        return false;

    return (
        gs_is_product_cat( $product_id_to_check, $hidden_categories ) && // product category
        gs_is_current_user_role( $show_on_user_roles )                       // user role match
    );
}
// Hides product from shop and search results by user role.
add_filter( 'woocommerce_product_is_visible', 'gs_product_visible_by_user_role', PHP_INT_MAX, 2 );
function gs_product_visible_by_user_role( $visible, $product_id ) {
    return ( gs_do_hide_product( $product_id ) ? false : $visible );
}

/**
 * Redirect to homepage if user does not have one of the allowed roles.
 * 
 * Fires when user tries accessing single product page when they're not allowed.
 */
add_action( 'template_redirect', 'hidden_single_product_redirect' );
function hidden_single_product_redirect() {
    $product_id = get_queried_object_id();
    if ( is_product() && gs_do_hide_product( $product_id ) ) {
        wp_safe_redirect( home_url() );
        exit;
    }
}




// hide product in ajax search bar function

// Remove action of original search function

remove_action( 'wp_ajax_flatsome_ajax_search_products', 'flatsome_ajax_search' );
remove_action( 'wp_ajax_nopriv_flatsome_ajax_search_products', 'flatsome_ajax_search' );

// Add new ajax search function to hide products

add_action( 'wp_ajax_flatsome_ajax_search_products', 'flatsome_ajax_search_custom' );
add_action( 'wp_ajax_nopriv_flatsome_ajax_search_products', 'flatsome_ajax_search_custom' );
if ( ! function_exists( 'flatsome_ajax_search_custom' ) ) {
  function flatsome_ajax_search_custom()
  {
    // The string from search text field.
    $query = apply_filters('flatsome_ajax_search_query', $_REQUEST['query']);
    $wc_activated = is_woocommerce_activated();
    $products = array();
    $posts = array();
    $sku_products = array();
    $tag_products = array();
    $suggestions = array();

    $args = array(
        's' => $query,
        'orderby' => '',
        'post_type' => array(),
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'ignore_sticky_posts' => 1,
        'post_password' => '',
        'suppress_filters' => false,
    );

    if ($wc_activated) {
      $products = flatsome_ajax_search_get_products('product', $args);
      $sku_products = get_theme_mod('search_by_sku', 0) ? flatsome_ajax_search_get_products('sku', $args) : array();
      $tag_products = get_theme_mod('search_by_product_tag', 0) ? flatsome_ajax_search_get_products('tag', $args) : array();
    }

    if ((!$wc_activated || get_theme_mod('search_result', 1)) && !isset($_REQUEST['product_cat'])) {
      $posts = flatsome_ajax_search_posts($args);
    }

    $results = array_merge($products, $sku_products, $tag_products, $posts);

    foreach ($results as $key => $post) {
      if ($wc_activated && ($post->post_type === 'product' || $post->post_type === 'product_variation')) {
        $product = wc_get_product($post);

        if ($product->get_parent_id()) {
          $parent_product = wc_get_product($product->get_parent_id());
          $visible = $parent_product->get_catalog_visibility() === 'visible' || $parent_product->get_catalog_visibility() === 'search';
          if ($parent_product->get_status() !== 'publish' || !$visible) {
            unset($results[$key]);
            continue;
          }
        }

          if (gs_do_hide_product( $product->get_id() )) {
            unset($results[$key]);
            continue;
          }
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()));

        $suggestions[] = array(
            'type' => 'Product',
            'id' => $product->get_id(),
            'value' => $product->get_title(),
            'url' => $product->get_permalink(),
            'img' => $product_image ? $product_image[0] : '',
            'price' => $product->get_price_html(),
        );
      } else {
        $suggestions[] = array(
            'type' => 'Page',
            'id' => $post->ID,
            'value' => get_the_title($post->ID),
            'url' => get_the_permalink($post->ID),
            'img' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
            'price' => '',
        );
      }
    }

    if (empty($results)) {
      $no_results = $wc_activated ? __('No products found.', 'woocommerce') : __('No matches found', 'flatsome');

      $suggestions[] = array(
          'id' => -1,
          'value' => $no_results,
          'url' => '',
      );
    }

    $suggestions = flatsome_unique_suggestions(array($products, $sku_products, $tag_products), $suggestions);

    wp_send_json(array('suggestions' => $suggestions));
  }
}


/** 
 * Redirect to homepage if user does not have one of the allowed
 * roles and if user is accessing hidden product category.
 */
add_action('template_redirect', 'hidden_product_category_redirect');
function hidden_product_category_redirect(){
    if ( !is_product_category() ) return;
    
    $hidden_categories = function_exists('get_field') ? get_field('product_categories_need_to_hidden', 'options') : array();
    
    if ( empty($hidden_categories)) return;
    
    $is_user_allowed = can_user_see_hidden_categories();
    $term_id = get_queried_object_id();

    if ( is_hidden_category( $term_id ) && !$is_user_allowed ) {
        wp_safe_redirect( home_url() );
        exit;
      }
  
}

/**
 * Checks if the current user can see the hidden categories selected in Theme Options.
 * 
 * Returns true if the current user has atleast one role in the allowed roles theme options.
 */
function can_user_see_hidden_categories(){
  $allowed_roles = function_exists('get_field') ? get_field('user_role_can_see_product_categories', 'options') : array();

  $user = wp_get_current_user();
  $user_roles = $user->roles;

  return count(array_intersect($user_roles, $allowed_roles)) > 0;

}

/**
 * Checks if the category ID is a hidden category.
 * 
 * Returns true if the term ID is in the hidden categories selected in Theme Options.
 */
function is_hidden_category( $term_id ){
  $hidden_categories = function_exists('get_field') ? get_field('product_categories_need_to_hidden', 'options') : array();

  return in_array($term_id, $hidden_categories);

}


add_filter('manage_users_columns', function($columns) {
  $columns['user_stars'] = __( 'Stars' );
  return $columns;
}, 99999);

add_filter('manage_users_custom_column', function($val, $column, $user_id) {
  if($column == 'user_stars') {
    $root_stars = bt_get_user_rewards($user_id);
    return ($root_stars ? $root_stars * 10 : 0) . ' (★)';
  }

  return $val;
}, 99999, 3);

/*
 * WC points rewards cart checkout function
*/
function generate_redeem_points_message() {
  global $wc_points_rewards;
  // get the total discount available for redeeming points
  $discount_available = get_discount_for_redeeming_points( false, null, true );
  $message = get_option( 'wc_points_rewards_redeem_points_message' );

  // bail if no message set or no points will be earned for purchase
  if ( ! $message || ! $discount_available ) {
    return null;
  }

  // points required to redeem for the discount available
  $points  = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_available );
  $message = str_replace( '{points}', number_format_i18n( $points ), $message );

  // the maximum discount available given how many points the customer has
  $message = str_replace( '{points_value}', wc_price( $discount_available ), $message );

  // points label
  return str_replace( '{points_label}', $wc_points_rewards->get_points_label( $points ), $message );
}

function get_discount_for_cart_item( $item, $for_display ) {
  $product  = $item['data'];
  $quantity = $item['quantity'];

  if ( ! $product instanceof WC_Product ) {
    return 0;
  }

  $max_discount = WC_Points_Rewards_Product::get_maximum_points_discount_for_product( $product );

  if ( is_numeric( $max_discount ) ) {

    // multiple the product max discount by the quantity being ordered
    return $max_discount * $quantity;

    // Max should be product price. As this will be applied before tax, it will respect other coupons.
  } else {
    /*
     * Only exclude taxes when configured to in settings and when generating a discount amount for displaying in
     * the checkout message. This makes the actual discount money amount always tax inclusive.
     */
    if ( 'exclusive' === get_option( 'wc_points_rewards_points_tax_application', wc_prices_include_tax() ? 'inclusive' : 'exclusive' ) && $for_display ) {
      if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
        $max_discount = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
      } elseif ( method_exists( $product, 'get_price_excluding_tax' ) ) {
        $max_discount = $product->get_price_excluding_tax( $quantity );
      } else {
        $max_discount = $product->get_price( 'edit' ) * $quantity;
      }
    } else {
      if ( function_exists( 'wc_get_price_including_tax' ) ) {
        $max_discount = wc_get_price_including_tax( $product, array( 'qty' => $quantity ) );
      } elseif ( method_exists( $product, 'get_price_including_tax' ) ) {
        $max_discount = $product->get_price_including_tax( $quantity );
      } else {
        $max_discount = $product->get_price( 'edit' ) * $quantity;
      }
    }

    return $max_discount;
  }
}

function calculate_discount_modifier( $percentage ) {

  $percentage = str_replace( '%', '', $percentage ) / 100;

  if ( 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
    $discount = WC()->cart->subtotal_ex_tax;

  } else {
    $discount = WC()->cart->subtotal;

  }

  return $percentage * $discount;
}

function get_discount_for_redeeming_points( $applying = false, $existing_discount_amounts = null, $for_display = false, $code = null ) {
  // get the value of the user's point balance
  $available_user_discount = WC_Points_Rewards_Manager::get_users_points_value( get_current_user_id() );

  // no discount
  if ( $available_user_discount <= 0 ) {
    return 0;
  }

  if ( $applying && 'yes' === get_option( 'wc_points_rewards_partial_redemption_enabled' ) && WC()->session->get( 'wc_points_rewards_discount_amount' ) ) {
    $requested_user_discount = WC_Points_Rewards_Manager::calculate_points_value( WC()->session->get( 'wc_points_rewards_discount_amount' ) );
    if ( $requested_user_discount > 0 && $requested_user_discount < $available_user_discount ) {
      $available_user_discount = $requested_user_discount;
    }
  }

  // Limit the discount available by the global minimum discount if set.
  $minimum_discount = get_option( 'wc_points_rewards_cart_min_discount', '' );
  if ( $minimum_discount > $available_user_discount ) {
    return 0;
  }

  $discount_applied = 0;

  if ( ! did_action( 'woocommerce_before_calculate_totals' ) ) {
    WC()->cart->calculate_totals();
  }

  /*
   * Calculate the discount to be applied by iterating through each item in the cart and calculating the individual
   * maximum discount available.
   */
  foreach ( WC()->cart->get_cart() as $item ) {
    $discount = get_discount_for_cart_item( $item, $for_display );

    // if the discount available is greater than the max discount, apply the max discount
    $discount = ( $available_user_discount <= $discount ) ? $available_user_discount : $discount;

    // add the discount to the amount to be applied
    $discount_applied += $discount;

    // reduce the remaining discount available to be applied
    $available_user_discount -= $discount;
  }

  if ( is_null( $existing_discount_amounts ) ) {
    $existing_discount_amounts = version_compare( WC_VERSION, '3.0.0', '<' )
      ? WC()->cart->discount_total
      : WC()->cart->get_cart_discount_total();
  }

  /*
   * If during calculation process this discount was already applied then we need to remove its amount
   * from the total discounts in cart to not obscure the calculations.
   */
  if ( ! is_null( $code ) ) {
    $discount_from_the_coupon   = WC()->cart->get_coupon_discount_amount( $code );
    $existing_discount_amounts -= $discount_from_the_coupon;
  }
  

  // if the available discount is greater than the order total, make the discount equal to the order total less any other discounts
  if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
    if ( 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
      $discount_applied = max( 0, min( $discount_applied, WC()->cart->subtotal_ex_tax - $existing_discount_amounts ) );

    } else {
      $discount_applied = max( 0, min( $discount_applied, WC()->cart->subtotal - $existing_discount_amounts ) );

    }
  } else {
    if ( 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
      $discount_applied = max( 0, min( $discount_applied, WC()->cart->subtotal_ex_tax - $existing_discount_amounts ) );

    } else {
      $discount_applied = max( 0, min( $discount_applied, WC()->cart->subtotal - $existing_discount_amounts ) );
    }
  }

  // limit the discount available by the global maximum discount if set
  $max_discount = get_option( 'wc_points_rewards_cart_max_discount' );

  if ( false !== strpos( $max_discount, '%' ) ) {
    $max_discount = calculate_discount_modifier( $max_discount );
  }
  $max_discount = $max_discount;

  if ( $max_discount && $max_discount < $discount_applied ) {
    $discount_applied = $max_discount;
  }

  return $discount_applied;
}

/*
 * Add free gifts
 */
function bt_get_name_gift_by_amount($amount, $gift_tier){
  $name = '';
  if($gift_tier){
    foreach ($gift_tier as $key => $item) {
      if($amount == $item['amount']){
        return $item['name'];
      }
    }
  }
  return $name;
}

add_action('flatsome_cart_sidebar', 'jy_free_gift_first_order_message', 4);
function jy_free_gift_first_order_message() {
  $user_id = get_current_user_id();
  $total_order = wc_get_customer_order_count($user_id);
  if( $total_order === 0){
    echo "<div class='first-order-free-gift-message'>
            <p>Make Your First Order 😄</p>
            <span>↓</span>
            <p>Get a Free Gift 🎁</p>
            <small><a class='how-to-order-link' href='". home_url("/how-to-order") . "'>Learn How To Order</a></small>
          </div>";
  }
}


add_action('flatsome_cart_sidebar', 'bt_add_free_gifts_cart_sidebar', 5);
//add_action('wfacp_mini_cart_before_shipping', 'bt_add_free_gifts_cart_sidebar');
function bt_add_free_gifts_cart_sidebar() {
  if(!wp_doing_ajax() && is_checkout())
  {
      return;
  }

  $enable_free_gifts = get_field('enable_free_gifts','option');
  if(!$enable_free_gifts) return;
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
  if($gift_tier): ?>
    <?php 
    if (is_checkout()){
    ?>
    <!--      <style>-->
<!--        @font-face {-->
<!--          font-family: "fl-icons";-->
<!--          font-display: block;-->
<!--          src: url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.eot');-->
<!--          src:-->
<!--              url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.eot#iefix') format("embedded-opentype"),-->
<!--              url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.woff2') format("woff2"),-->
<!--              url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.ttf') format("truetype"),-->
<!--              url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.woff') format("woff"),-->
<!--              url('/wp-content/themes/flatsome//assets/css/icons/fl-icons.svg') format("svg");-->
<!--        }-->
<!--      </style>-->
<!--      <link rel="stylesheet" id="flatsome-main-css" href="https://greensociety.cc/wp-content/themes/flatsome/assets/css/flatsome.css" type="text/css" media="all">-->
    <?php
    }
    ?>
    <div class="bt_free_gifts">
      <div class="bt_free_gifts_header">
        <?php
        $amount_qualified = 0;
        $next_gift_amount = $min;
        if(count($new_amounts)){
          $next_gift_amount = min($new_amounts);
        }
        $progressing = $subtotal*100/$next_gift_amount;
        if($subtotal <= $min && $subtotal > 0){
          echo "<span class='bt_gift_notice'>Add <b>$".floatval($min - $subtotal)."</b> more to unlock the next FREE gift!</span>";
        }
        if($subtotal > $min){
          $amount_qualified = max($qualified_amounts);
          $name_gift = bt_get_name_gift_by_amount($amount_qualified, $gift_tier);
          echo "<span class='bt_gift_qualified'>CONGRATS!<br>YOU QUALIFIED FOR ".$name_gift."<br>Claim it at checkout!</span>";
          if($subtotal < $max){
            echo "<span class='bt_gift_notice'>Add <b>$".floatval($next_gift_amount - $subtotal)."</b> more to unlock the next FREE gift!</span>";
          }
        }
        ?>
        <div class="bt_progressing_wrap">
          <div class="bt_progressing" style="width:<?php echo $progressing.'%'; ?>"><?php echo $progressing.'%'; ?></div>
        </div>
      </div>
      <ul class="bt_free_gifts_list"> 
        <?php foreach ($gift_tier as $key => $item): ?>
          <?php
          $item_class = 'bt_free_gift_item';
          $icon_class = 'icon-gift';
          if( $item['amount'] <= $amount_qualified ):
            $item_class .= ' bt_free_gift_item_qualified';
            $icon_class = 'icon-checkmark';
          endif; ?>
          <li class="<?php echo $item_class; ?>">
            <h4><?php echo $item['name']; ?></h4>
            <?php foreach ($item['product'] as $_key => $_product): ?>
              <?php $product = wc_get_product($_product->ID); ?>
                <div class="bt_free_gift_item_product">
                  <div class="bt_free_gift_item_col1">
                    <?php echo $product->get_image('thumbnail'); ?>
                  </div>
                  <div class="bt_free_gift_item_col2">
                    <div><?php echo $_product->post_title; ?></div>
                  </div>
                  <div class="bt_free_gift_item_col3">
                    <i class="<?php echo $icon_class; ?>"></i>
                  </div>
                </div>
            <?php endforeach; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif;
}
/* Update cart free gifts */
function bt_header_add_to_cart_fragment_update( $fragments ) {
  ob_start();
  echo bt_add_free_gifts_cart_sidebar();
  $fragments['.bt_free_gifts'] = ob_get_clean();

  return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'bt_header_add_to_cart_fragment_update');

function bt_check_gift_exist_in_cart($product_id, $variation_id){
  $result = 0;
  if ( ! WC()->cart->is_empty() ) {
    // Loop though cart items
    foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      // Handle a simple product Id (int or string) or an array of product Ids
      if( $product_id == $cart_item['product_id'] && $variation_id == $cart_item['variation_id'] ){
        return $cart_item_key;
      }
    }
  }
  return $result;
}
/* Automate add gift to cart */
function bt_automate_add_gift_to_cart(){

  /**
   * Feedback => https://green-society.monday.com/boards/1418964603/pulses/3230084361/posts/1705189434?reply=reply-1872398350
   */
  return;
  /**
   * End
   */

  $gift_tier = get_field('gift_tier', 'option');
  $log = new WC_Logger();
  if($gift_tier):
    $amounts = wp_list_pluck( $gift_tier, 'amount' );
    $discount_cart = WC()->cart->discount_cart;
    $subtotal = WC()->cart->subtotal - $discount_cart;
    $qualified_amounts = array_filter($amounts, function($n){
      $discount_cart = WC()->cart->discount_cart;
      $subtotal = WC()->cart->subtotal - $discount_cart;
      return $n <= $subtotal;
    });
    $amount_qualified = $qualified_amounts ? max($qualified_amounts) : 0;
    foreach ($gift_tier as $key => $item):
      $product = wc_get_product($item['product']->ID);
      $product_id = $product->get_parent_id() ? $product->get_parent_id(): $product->get_id();
      $variation_id = 0;
      if('variation' == $product->get_type()){ $variation_id = $product->get_id(); }
      if($amount_qualified == $item['amount']){
        $name_gift = bt_get_name_gift_by_amount($amount_qualified, $gift_tier);
        $cart_item_key = bt_check_gift_exist_in_cart($product_id, $variation_id);
        if( !$cart_item_key ){
          remove_action( 'woocommerce_add_cart_item', 'bt_automate_add_gift_to_cart', 2 );
          WC()->cart->add_to_cart(
            $product_id,
            1,
            $variation_id,
            array(),
            array(
              'custom_price' => 0,
              'free_gift' => $name_gift,
              'update_qty' => false,
            )
          );
          // $log->log( 'woocommerce_add_cart_item', '$product_id:'.$product_id.',$variation_id:'.$variation_id);
          add_action( 'woocommerce_add_cart_item', 'bt_automate_add_gift_to_cart', 2 );
        }
      }else{
        $cart_item_key = bt_check_gift_exist_in_cart($product_id, $variation_id);
        //var_dump($cart_item_key);die;
        if ( $cart_item_key ) {
          remove_action( 'woocommerce_cart_item_removed', 'bt_automate_add_gift_to_cart_when_updated', 2 );
          WC()->cart->remove_cart_item($cart_item_key);
          // $log->log( 'woocommerce_remove_cart_item', '$product_id:'.$product_id.',$variation_id:'.$variation_id);
          add_action( 'woocommerce_cart_item_removed', 'bt_automate_add_gift_to_cart', 2 );
        }
      }
    endforeach;
  endif;
}
add_action('wp_head', 'bt_automate_add_gift_to_cart');
add_action('woocommerce_before_mini_cart', 'bt_automate_add_gift_to_cart');

//add free gift label
add_filter('woocommerce_widget_cart_item_quantity', 'bt_woocommerce_widget_cart_item_quantity', 20, 3);
function bt_woocommerce_widget_cart_item_quantity($html, $cart_item, $cart_item_key){
  if(isset($cart_item['free_gift'])):
    // $html .= "<span class='bt_free_gift_label'>FREE GIFT</span><br>";
    return "<span class='bt_free_gift_label'>FREE GIFT</span>" . $html;
  endif;
  return $html;
}
add_filter('wfacp_allow_woocommerce_after_cart_item_name_mini_cart', '__return_true', 99);
add_action('woocommerce_after_cart_item_name', 'bt_woocommerce_after_cart_item_name_free_gift', 20, 2);
function bt_woocommerce_after_cart_item_name_free_gift($cart_item, $cart_item_key){
  if(isset($cart_item['free_gift'])):
    echo "<span class='bt_free_gift_label'>FREE GIFT</span><br>";
  endif;
}
// Add 10% fee for Cryptocurrency
add_action( 'woocommerce_cart_calculate_fees','bt_add_discount', 1, 1 );
function bt_add_discount( $cart_object ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $label_text = __("");

    $percent = 0;
    // Mention the payment method e.g. cod, bacs, cheque or paypal
    $cart_total = $cart_object->subtotal_ex_tax;

    $chosen_payment_method = WC()->session->get('chosen_payment_method');  //Get the selected payment method
    if( $chosen_payment_method == "gourlpayments" ){
       $label_text = __( "Cryptocurrency Discount" );
       // The percentage to apply
       $percent = 10; // 2%

       // Calculating percentage
       $discount = number_format(($cart_total / 100) * $percent, 2);

       // Adding the discount
       $cart_object->add_fee( $label_text, -$discount, false );

    }

}
add_action('wp_head', 'bt_add_free_gift_style', 99);
function bt_add_free_gift_style(){
  ob_start(); ?>
  <style>
  .wfacp_mini_cart_items .bt_free_gift_label{
      position: absolute!important;
      right: -40px!important;
      bottom: 15px;
  }
  .wfacp_mini_cart_items .product-name-area{
      position:relative;
  }
  .wfacp_mini_cart_items .product-name-area .wfacp_cart_title_sec{
      position:static;
  }
  .bt_free_gift_label{
  	white-space: nowrap;
  	color: #fff !important;
  	background: #ff5b5b;
    font-size: 0.6rem;
  	font-weight: bold;
  	padding: 5px;
  	border-radius: 10px;
  	display: block;
  	max-width: 100% !important;
  	text-align: center;
    font-family: sans-serif;
    -webkit-font-smoothing: auto;
  }
  .xlwcty_pro_list{
    position: relative;
  }
  .wc-item-meta .bt_free_gift_label{
    position: absolute;
    right: 20px;
    bottom: 30px;
  }
  </style>
  <?php echo ob_get_clean();
}
// $html = apply_filters( 'woocommerce_display_item_meta', $html, $item, $args );
add_filter('woocommerce_display_item_meta', 'bt_woocommerce_display_item_meta', 20, 3);
function bt_woocommerce_display_item_meta($html, $item, $args){
  $strings = array();
	$html    = '';
	$args    = wp_parse_args(
		$args,
		array(
			'before'       => '<ul class="wc-item-meta"><li>',
			'after'        => '</li></ul>',
			'separator'    => '</li><li>',
			'echo'         => true,
			'autop'        => false,
			'label_before' => '<strong class="wc-item-meta-label">',
			'label_after'  => ':</strong> ',
		)
	);
  $html = '<ul class="wc-item-meta">';
	foreach ( $item->get_all_formatted_meta_data() as $meta_id => $meta ) {
		$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
    if($meta->display_key == 'free_gift'){
      $html .= '<span class="bt_free_gift_label">FREE GIFT</span><br>';
    }else{
      $html .= '<li>'.$args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value.'</li>';
    }
	}
  $html .= '</ul>';
	if ( $strings ) {
		$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
	}
  return $html;
}
// forced pay on checkout 
add_filter( 'woocommerce_valid_order_statuses_for_payment', 'bt_woocommerce_valid_order_statuses_for_payment', 10, 2 );
function bt_woocommerce_valid_order_statuses_for_payment($status, $order){
  $status[] = 'on-hold';
  $status[] = 'cancelled';
  return $status;
}

/**
 * Move product category description under products
 */
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
add_action( 'flatsome_products_after', 'woocommerce_taxonomy_archive_description', 10 );
add_action( 'flatsome_products_after', 'woocommerce_product_archive_description', 10 );