<?php
/**
 * Examples
 */

/**
 * Run using http://example.com/wp-admin/index.php?wds_test_grabber=true
 */
add_action( 'admin_init', function() {
	if ( ! isset( $_GET['wds_test_grabber'] ) ) {
		return;
	}

	$url = 'http://www.amazon.com//Sales-Grocery/b?ie=UTF8&node=52129011&ref_=amb_link_353229922_2';
	$grabber = new WDS_Image_Grabber( $url );
	$images = $grabber->get_images();
	
	var_dump( $images );
	wp_die();
} );