<?php
/**
 * The template for displaying the GS Club Deals page
 *
 * @package flatsome
 */

get_header();
do_action( 'flatsome_before_page' ); ?>
	
	
<div class="gs-page-content-wrapper">
	
	<div id="content" class="gs-club-page" role="main">
		<?php
		$mobile_image = get_field("gs_club_page_banner_mobile");
		$desktop_image = get_field("gs_club_page_banner_desktop");
		$size = "full";
		if($mobile_image){
			echo "<a href='https://greensociety.cc/account/'><div class='gs-club-page-mobile'>" . wp_get_attachment_image($mobile_image, $size) . "</div></a>"; 
		}
		if($desktop_image){
			echo "<a href='https://greensociety.cc/account/'><div class='gs-club-page-desktop'>" . wp_get_attachment_image($desktop_image, $size) . "</div></a>"; 
		}
		?>

		<!-- 30%+ deals -->
		<?php if(is_user_logged_in()) {?>
				<div class="gs-club-product-row">
					<?php } else { ?>
				<div class="gs-club-product-row  user-logged-out">
					<?php } ?>
				<h2>30%+ Deals</h2>
				<?php echo do_shortcode('[products columns="5" tag="30off" limit="10"]'); ?>
				<!-- gs-club-product-row -->
				</div>


				<!-- 20% deals -->
			<?php if(is_user_logged_in()) {?>
				<div class="gs-club-product-row">
					<?php } else { ?>
				<div class="gs-club-product-row  user-logged-out">
					<?php } ?>
					<h2>20%+ Deals</h2>
					<?php echo do_shortcode('[products columns="5" tag="20off" limit="10"]'); ?>
				<!-- gs-club-product-row -->
				</div>

			<!-- 10% deals -->
			<?php if(is_user_logged_in()) {?>
				<div class="gs-club-product-row">
					<?php } else { ?>
				<div class="gs-club-product-row  user-logged-out">
							<?php } ?>
							<h2>10%+ Deals</h2>
							<?php echo do_shortcode('[products columns="5" tag="15off" limit="10"]'); ?>
							<!-- gs-club-product-row -->
				</div>
						
			
						
						
			


		<!-- content	 -->
		</div>

	<!-- gs-page-content-wrapper -->
</div>
<?php if(!is_user_logged_in()){ ?>
		<div class="gs-page-overlay">
			<div class="gs-page-modal">
				<div class="gs-page-modal-content">
					<div class="modal-header">
						<h2>Join the GS Club</h2>
						<p>Start taking advantage of these deals by signing up or <a href="https://greensociety.cc/account/">logging in</a></p>
					</div>
					<?php echo do_shortcode('[ultimatemember form_id="1571299"]'); ?>
				</div>
			</div>
		</div>
<?php } ?>

<?php
do_action( 'flatsome_after_page' );
get_footer();

?>