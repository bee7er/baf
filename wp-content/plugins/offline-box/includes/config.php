<?php
defined('ABSPATH') or die();

/* ADD SETTINGS PAGE
------------------------------------------------------*/
if( !function_exists('offline_box_add_options_page') ){
	function offline_box_add_options_page() {
		add_options_page(
			'Offline Box Settings',
			'Offline Box',
			'manage_options',
			'offline-box-setting',
			'offline_box_setting_display'
		);
	}
}
add_action('admin_menu','offline_box_add_options_page');

if( !is_admin() ):
	
endif; // main_setup