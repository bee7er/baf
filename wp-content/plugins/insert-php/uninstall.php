<?php

	// if uninstall.php is not called by WordPress, die
	if( !defined('WP_UNINSTALL_PLUGIN') ) {
		die;
	}

	// remove plugin options
	global $wpdb;

	$snippets = get_posts(array(
		'post_type' => 'wbcr-snippets',
		'numberposts' => -1
	));

	if( !empty($snippets) ) {
		foreach((array)$snippets as $snippet) {
			wp_delete_post($snippet->ID, true);
		}
	}

	$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'wbcr_inp_%';");