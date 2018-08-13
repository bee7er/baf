<?php
/*
Plugin Name: Offline Box
Plugin URI: http://wordpress.org/plugins/offline-box/
Description: Site offline, coming soon, development site, security. Easily activate maintenance mode for your website.
Version: 1.0.5
Author: PB One
Author URI: http://photoboxone.com/
License: GPL2
*/
define('WP_OB_URL_AUTHOR', 'http://photoboxone.com' ); 
define('WP_OB_AUTHOR_NAME', 'PB One' ); 
define('WP_OB_AUTHOR_URL', WP_OB_URL_AUTHOR );
defined('WP_PB_URL_AUTHOR') or define('WP_PB_URL_AUTHOR', WP_OB_URL_AUTHOR );

defined('ABSPATH') or die('<meta http-equiv="refresh" content="0;url='.WP_OB_URL_AUTHOR.'">');

define('WP_OB_PATH', dirname(__FILE__) ); 
define('WP_OB_PATH_INCLUDES', dirname(__FILE__).'/includes' ); 
define('WP_OB_PATH_THEMES', dirname(__FILE__).'/themes' ); 
define('WP_OB_URL', plugins_url('', __FILE__).'/' ); 
define('WP_OB_URL_THEMES', WP_OB_URL.'themes/' ); 
define('WP_OB_URL_IMAGES', WP_OB_URL.'images/' ); 
define('WP_OB_URL_MEDIA', WP_OB_URL.'media/' );

if( is_admin() ){
	
	
	require WP_OB_PATH_INCLUDES.'/config.php';
	
	$action = isset($_GET['action'])?$_GET['action']:'';	
	$page 	= isset($_GET['page'])?$_GET['page']:'';	
	$plugins = preg_match('/plugins.php/i',$_SERVER['REQUEST_URI']);
	$options = preg_match('/options/i',$_SERVER['REQUEST_URI']);
	if( $plugins ){
		function offline_box_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
			$url_setting = admin_url('options-general.php?page=offline-box-setting');
			
			// array_unshift($actions, "<a href=\"http://photoboxone.com/download\" target=\"_blank\">".__("Full Version")."</a>");			
			array_unshift($actions, "<a href=\"http://photoboxone.com/documents\" target=\"_blank\">".__("Documents")."</a>");			
			array_unshift($actions, "<a href=\"$url_setting\">".__("Settings")."</a>");			return $actions;		
		}add_filter("plugin_action_links_".plugin_basename(__FILE__), "offline_box_plugin_actions", 10, 4);	
	}
	
	if( $action == 'edit' ){
		//require WP_OB_PATH_INCLUDES.'/admin.php';	
	} else if( $options ){
		require WP_OB_PATH_INCLUDES.'/setting.php';	
	}
} else {
	require WP_OB_PATH_INCLUDES.'/site.php';
}