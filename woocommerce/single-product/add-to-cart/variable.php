<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.1
 */

defined( 'ABSPATH' ) || exit;

global $product;
$attribute_keys = array_keys( $attributes );
$show_template_variation_new = get_field('variation_template_new_custom','option');
$class = '';
$term_id = array(31,9859,13080);
$show_circle = 0;

if(has_term( $term_id, 'product_cat' ) && $show_template_variation_new):
    $class = 'hidden';
    $show_circle = 1;
endif;

do_action( 'woocommerce_before_add_to_cart_form' );

?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ); // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php esc_html_e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
	<?php else :
        $sort = array();
        foreach ($available_variations as $key => $variation) {
          $sort[$key]  = $variation['weight'];
        }
        array_multisort($sort, SORT_ASC, $available_variations);
        ?>
		<table class="variations <?php echo $show_circle? 'show_circle' : ''; ?>" cellspacing="0">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<td class="label"><label class="<?php echo esc_attr($class); ?>" for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></td>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options( array(
									'options'   => $options,
									'attribute' => $attribute_name,
									'product'   => $product,
                                    'class' => $class
								) );
								echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations aaa '.$class.'" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
                                if($show_circle): ?>
                                <div class="value-product-id-variation-single-custom">
                                    <?php
                                    foreach($available_variations as $option) {
										$value = isset($option['attributes']) && isset($option['attributes']['attribute_'.sanitize_title($attribute_name)]) ? $option['attributes']['attribute_'.sanitize_title($attribute_name)] : '';
										if(!$value) continue;
										$disable_pr = !$option['is_in_stock'] ? 'outstock' : '';
										$variation_id = $option['variation_id'];
										$single_variation = new WC_Product_Variation($variation_id);
										$name_variation = explode(': ',$single_variation->get_attribute_summary());
										$title_variation = str_replace(' Grams','G',$name_variation[1]);
										?>
										<div 
											data-attr_name="<?php echo sanitize_title($attribute_name); ?>" 
											data-item-pr="<?php echo $name_variation[1]; ?>" 
											class="item-product-variation <?php echo $disable_pr; ?> " 
											price-sale="<?php echo $option['display_price']; ?>" 
											price-regular="<?php echo $option['display_regular_price']; ?>" 
											data-value="<?php echo $value?>"
											title="">
											<div class="product-variant-inner">
										        <!--<span class="variant-select-hover">Selected:</span>-->
										        <div class="product-variant-price-select">
										            <span class="title_variation">
        												<?php echo $title_variation; ?>
        											</span>
        											<div class="per-g-item-product">
        												<span class="price-custom">
        													<?php
        														echo strip_tags(wc_price($option['display_price']));
        													?>
        												</span>
                                                        <?php if($option['weight']): ?>
          												<span class="per-g-custom">
          													<?php
          														echo strip_tags(wc_price($option['display_price']/(float)$option['weight'], array('decimals' => 2))).'<span class="icon-g">/g<span>';
          													?>
          												</span>
                                                        <?php endif; ?>
        											</div>
										        </div>
										    </div>
										</div>
										<?php
                                    }
                                ?>
                                </div>
                                <?php
                                endif;
							?>
						</td>
					</tr>
					<div id="dropdown-raido"></div>
				<?php endforeach; ?>
			</tbody>
		</table>


		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
