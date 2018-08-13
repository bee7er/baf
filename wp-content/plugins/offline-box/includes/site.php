<?php
defined('ABSPATH') or die('<meta http-equiv="refresh" content="0;url='.WP_PB_URL_AUTHOR.'">');


function offline_box_setup(){
	if( !is_admin() && preg_match('/wp-login|wp-admin/i', $_SERVER['REQUEST_URI']) == false ){
		// var_export($_SERVER);
		
		extract(shortcode_atts(array(
			'enable' => 'no',
			'theme' => 'default',
		), (array)get_option('offline_box_display')));
		
		$file 		= WP_OB_PATH_THEMES.'/'.$theme.'/index.php';
		
		$theme_url 	= WP_OB_URL_THEMES.$theme.'/';
		
		$current_user = wp_get_current_user();
		if( $enable == 'yesforguest' ){
			// Yes for Guest
			if( empty($current_user) || intval($current_user->ID) == 0 ){
				$enable = 'yes';
			}
		} else if( $enable == 'yesbutnotadmin' ){
			// Yes for all but exclude Admin
			if ( empty( $current_user->roles ) || !is_array( $current_user->roles ) || !in_array( 'administrator', $current_user->roles ) ) {
				$enable = 'yes';
			}
		}
		
		
		if( $enable == 'yes' ){
			
			if( file_exists( $file ) ){
				include $file;				
			} else {
				echo 'Not Theme !!! Please choose a theme in The Offline Box Plugin now.';
			}
			
			die();
		}
	}
} add_action( 'after_setup_theme', 'offline_box_setup' );