<?php 
use QuadLayers\WOOCCM\View\Frontend\Fields_Filter as Fields_Filter;
$field_filter = Fields_Filter::instance();
remove_filter( 'woocommerce_form_field_country', array( $field_filter, 'country_field' ), 10, 4 );
remove_filter( 'woocommerce_form_field_state', array( $field_filter, 'state_field' ), 10, 4 );