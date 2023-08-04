<div class="shop-page-title category-page-title dark featured-title page-title <?php flatsome_header_title_classes() ?>">
	<?php
			if( is_product_category() ){
				$queried_object = get_queried_object(); 
				$taxonomy = $queried_object->taxonomy;
				$term_id = $queried_object->term_id;
				$parent_category_id = get_ancestors($term_id, 'product_cat');

				$current_cat_desktop_image = get_field( "product_category_header_banner_desktop",  $taxonomy . '_' . $term_id);
				$current_cat_mobile_image = get_field( "product_category_header_banner_mobile",  $taxonomy . '_' . $term_id);

				if($current_cat_desktop_image && $current_cat_mobile_image){
					
						echo '<img class="acf-category-banner acf-category-banner-mobile" src="' . $current_cat_mobile_image . '">';
			
						echo '<img class="acf-category-banner acf-category-banner-desktop test1" src="' . $current_cat_desktop_image . '">';

				} elseif($parent_category_id) {
					$parent_cat_mobile_image = get_field( "product_category_header_banner_mobile",  $taxonomy . '_' . $parent_category_id[0]);

					$parent_cat_desktop_image = get_field( "product_category_header_banner_desktop",  $taxonomy . '_' . $parent_category_id[0]);
					
					if($parent_cat_desktop_image && $parent_cat_mobile_image){

						echo '<img class="acf-category-banner acf-category-banner-mobile" src="' . $parent_cat_mobile_image . '">';
						echo '<img class="acf-category-banner acf-category-banner-desktop test2" src="' . $parent_cat_desktop_image . '">';
					} else {
						echo '<img class="acf-category-banner acf-category-banner-mobile" src="https://greensociety.cc/wp-content/uploads/2020/05/The-Green-Room-2-Category-Banner-Mobile.jpg">';
						echo '<img class="acf-category-banner acf-category-banner-desktop test3" src="https://greensociety.cc/wp-content/uploads/2020/05/The-Green-Room-2-Category-Banner-Desktop.jpg">';
					}
				} else {
					echo '<img class="acf-category-banner acf-category-banner-mobile" src="https://greensociety.cc/wp-content/uploads/2020/05/The-Green-Room-2-Category-Banner-Mobile.jpg">';
					echo '<img class="acf-category-banner acf-category-banner-desktop test4" src="https://greensociety.cc/wp-content/uploads/2020/05/The-Green-Room-2-Category-Banner-Desktop.jpg">';
				}
			}

			if( is_shop() ){
				echo '<img class="acf-category-banner acf-category-banner-mobile" src="https://greensociety.cc/wp-content/uploads/2020/04/Shop-Category-Banner-Mobile.jpg">';

				echo '<img class="acf-category-banner acf-category-banner-desktop" src="https://greensociety.cc/wp-content/uploads/2020/04/Shop-Category-Banner-Desktop.jpg">';
			}


	?>
	<div class="page-title-bg fill">
		<div class="title-bg fill bg-fill" data-parallax-fade="true" data-parallax="-2" data-parallax-background data-parallax-container=".page-title"></div>
		<div class="title-overlay fill"></div>
	</div>
	
	<div class="page-title-inner flex-row container medium-flex-wrap flex-has-center">
	  <div class="flex-col">
	  	&nbsp;
	  </div>
	  <div class="flex-col flex-center text-center">
	  	  <?php do_action('flatsome_category_title') ;?>
	  </div>
	  <div class="flex-col flex-right text-right medium-text-center form-flat">
	  	  <?php do_action('flatsome_category_title_alt') ;?>
	  </div>
	</div>
</div>