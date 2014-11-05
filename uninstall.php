<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function es1sc_delete_plugin() {
	global $wpdb;

	delete_option('es1sc_merchant_id');
	delete_option('es1sc_merchant_key');
	delete_option('es1sc_api_uri');
	delete_option('es1sc_no_image');
	delete_option('es1sc_cart_image');
	delete_option('es1sc_buynow_url');
	delete_option('es1sc_product_list_item_format');

}

es1sc_delete_plugin();

?>