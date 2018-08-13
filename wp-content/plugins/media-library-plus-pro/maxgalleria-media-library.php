<?php
/*
Plugin Name: Media Library Plus Pro
Plugin URI: http://maxgalleria.com
Description: Gives you the ability to adds folders and move files in the WordPress Media Library.
Version: 3.2.8
Author: Max Foundry
Author URI: http://maxfoundry.com

Copyright 2015 Max Foundry, LLC (http://maxfoundry.com)
*/

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {	
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

require_once 'maxgalleria-video-sc.php';


class MaxGalleriaMediaLibPro {

  public $upload_dir;
  public $wp_version;
  public $theme_mods;
	public $uploads_folder_name;
	public $uploads_folder_name_length;
	public $uploads_folder_ID;
	public $image_sizes;
	public $shortcode_video;
	public $blog_id;
	public $base_url_length;

  public function __construct() {
		$this->blog_id = 0;
		$this->set_global_constants();
		$this->set_activation_hooks();
		$this->setup_hooks();       
		$this->upload_dir = wp_upload_dir();  
    $this->wp_version = get_bloginfo('version'); 
	  $this->base_url_length = strlen($this->upload_dir['baseurl']) + 1;
    
    //convert theme mods into an array
    $theme_mods = get_theme_mods();
    $this->theme_mods = json_decode(json_encode($theme_mods), true);
		
		$this->image_sizes = get_intermediate_image_sizes();
	  $this->image_sizes[] = 'full';
		
		if(class_exists('MaxGalleria'))
		  $this->shortcode_video = new MaxGalleriaVideoShortcode();
        
    add_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER, '0' );    
    add_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY, 'on' );    
	}

	public function set_global_constants() {	
		define('MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY', 'maxgalleria_media_library_version');
		define('MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM', '3.2.8');
		define('MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE', 'maxgalleria_media_library_ignore_notice');
		define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
		define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME);
		define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL', plugin_dir_url('') . MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_NAME);
    define("MAXGALLERIA_MEDIA_LIBRARY_NONCE", "mgmlp_nonce");
    define("MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE", "mgmlp_media_folder");
    define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME", "mgmlp_upload_folder_name");
    define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID", "mgmlp_upload_folder_id");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE'))
      define("MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE", "mgmlp_folders");
    define("MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER", "mgmlp_sort_order");
    define("NEW_MEDIA_LIBRARY_VERSION", "4.0.0");
    define("MAXGALLERIA_MLP_REVIEW_NOTICE", "maxgalleria_mlp_review_notice");
		if(!defined('MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX'))
      define("MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX", "mgmlp_src_fix");
    
		define('MLPP_EDD_SHOP_URL', 'http://maxgalleria.com/');		
	  define('EDD_MLPP_NAME', 'media-library-plus-pro' ); 
    define("MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY", "mgmlp_move_or_copy");
    define("MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO", "mgmlp_image_seo");
    define("MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT", "mgmlp_default_alt");
    define("MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT", "mgmlp_default_title");
    define("MAXGALLERIA_MEDIA_LIBRARY_BACKUP_TABLE", "mgmlp_old_posts");
		define("MAXGALLERIA_MEDIA_LIBRARY_POSTMETA_UPDATED", "mgmlp_postmeta_updated");
		
		
		// Bring in all the actions and filters
		require_once 'maxgalleria-media-library-hooks.php';
	}
    	
 	public function set_activation_hooks() {
		register_activation_hook(__FILE__, array($this, 'do_activation'));
		register_deactivation_hook(__FILE__, array($this, 'do_deactivation'));
	}
  
  public function do_activation($network_wide) {
		if ($network_wide) {
			$correct_mulisite = $this->check_for_old_multisite();			
			if($correct_mulisite === false )
				wp_die ( _e("This multisite was create with a version earlier than Wordpress 3.5 an is not compatible with Media Library Plus Pro. To continue, click the Back Arrow.", 'maxgalleria-media-library' ));
			$this->call_function_for_each_site(array($this, 'activate'));
		}
		else {
			$this->activate();
		}
	}
	
	public function do_deactivation($network_wide) {	
		if ($network_wide) {
			$this->call_function_for_each_site(array($this, 'deactivate'));
		}
		else {
			$this->deactivate();
		}
	}
  
	public function activate() {
		
		if(class_exists('MaxGalleriaMediaLib')) {
			add_action( 'admin_notices', array($this, 'runing_mlp_error_notice'));
			exit();
		}
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY, MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM);
    //update_option('uploads_use_yearmonth_folders', 1);    
    $this->add_folder_table();
    if ( 'impossible_default_value_1234' === get_option( MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, 'impossible_default_value_1234' ) ) {
      $this->scan_attachments();
      $this->admin_check_for_new_folders(true);
		  update_option(MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX, true);
    } else if ( 'impossible_default_value_3579' === get_option( MAXGALLERIA_MEDIA_LIBRARY_POSTMETA_UPDATED, 'impossible_default_value_3579' ) ) {
			$this->update_folder_postmeta();
		}
			
//    if ( ! wp_next_scheduled( 'new_folder_check' ) )
//      wp_schedule_event( time(), 'daily', 'new_folder_check' );
    
	}
	
	public function check_for_old_multisite() {
		
		$upload_sites = get_home_path() . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "uploads";
		
		if(!file_exists($upload_sites)) 
			return false;
		else
			return true;
	}
	
	public function update_achachment_data() {
		
    global $wpdb;

		// get all the attachment IDs
    $sql = "select ID from $wpdb->prefix" . "posts where post_type = 'attachment' order by ID";

    $rows = $wpdb->get_results($sql);
		if($rows) {
			foreach($rows as $row) {

				// get the file location and meta data location
        $uploads_location = get_post_meta( $row->ID, '_wp_attached_file', true );
        $attachment_data = get_post_meta( $row->ID, '_wp_attachment_metadata', true );
				
				// check for valid offsets
				if(isset($attachment_data[0])) {
					if(isset($attachment_data[0]['file'])) {
						$meta_file = $uploads_location;
						$meta_location = $attachment_data[0]['file'];
						
						// update the meta data location if it does not match
						if($meta_location !== $meta_file) {
						  $attachment_data[0]['file'] = $meta_file;
						  update_post_meta( $row->ID, '_wp_attachment_metadata', $attachment_data );												
						}
				  }					
				}	else {
					if(isset($attachment_data['file'])) {
						$meta_file = $uploads_location;
						$meta_location = $attachment_data['file'];
						
						// update the meta data location if it does not match
						if($meta_location !== $meta_file) {
						  $attachment_data['file'] = $meta_file;
						  update_post_meta( $row->ID, '_wp_attachment_metadata', $attachment_data );												
						}
					}										
				}	
			}			
		}
		// never repeat this process
		update_option(MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX, true);

	}
	
	public function update_folder_postmeta() {
    global $wpdb;
		
    $uploads_path = wp_upload_dir();
					
		$sql = "select ID, guid from {$wpdb->prefix}posts where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE . "'";

		$rows = $wpdb->get_results($sql);

		if($rows) {
			foreach($rows as $row) {
				$relative_path = substr($row->guid, $this->base_url_length);
				$relative_path = ltrim($relative_path, '/');
				update_post_meta($row->ID, '_wp_attached_file', $relative_path);
			}				
			update_option(MAXGALLERIA_MEDIA_LIBRARY_POSTMETA_UPDATED, 'on');				
		}	
		
	}
	  
  public function deactivate() {
    wp_clear_scheduled_hook('new_folder_check');
	}
  
  public function call_function_for_each_site($function) {
		global $wpdb;
		
		// Hold this so we can switch back to it
		$current_blog = $wpdb->blogid;
		
		// Get all the blogs/sites in the network and invoke the function for each one
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
		  $this->blog_id = $blog_id;
			call_user_func($function);
		}
		$this->blog_id = 0;
		
		// Now switch back to the root blog
		switch_to_blog($current_blog);
	}
    
  public function enqueue_admin_print_styles() {		
    global $pagenow;
    if(isset($_REQUEST['page'])) {
			
      if($_REQUEST['page'] === 'media-library' 
				|| $_REQUEST['page'] === 'search-library' ) {
//				|| $_REQUEST['page'] === 'view-nextgen'	) {
        wp_enqueue_style('thickbox');
        wp_enqueue_style('maxgalleria-media-library', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/maxgalleria-media-library.css');
        wp_enqueue_style('foundation', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/libs/foundation/foundation.min.css');    
        //wp_enqueue_style('jstree', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.min.css');    
      }
			
      if($_REQUEST['page'] === 'mlp-regenerate-thumbnails' ||
				 $_REQUEST['page'] === 'mlp-support' ||
				 $_REQUEST['page'] === 'image-seo') {
        wp_enqueue_style('maxgalleria-media-library', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/maxgalleria-media-library.css');
				
				wp_register_script('jquery-ui', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jquery-ui-1.11.4.custom/jquery-ui.min.js', array('jquery'));
				wp_enqueue_script('jquery-ui');
			}
						
    }  
		
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) {
			wp_enqueue_style('maxgalleria-media-library', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/maxgalleria-media-library.css');				
		}
		
		wp_enqueue_style('jstree', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.min.css');    		
		
    wp_enqueue_style('mlp-notice', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/mlp-notice.css');
		
		wp_register_script('jquery-ui', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jquery-ui-1.11.4.custom/jquery-ui.min.js', array('jquery'));
		wp_enqueue_script('jquery-ui');
		
		wp_register_script('jstree', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/jstree.min.js', array('jquery'));
		wp_enqueue_script('jstree');
		
		
}
  
  public function enqueue_admin_print_scripts() {
    global $pagenow;
		
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php', 'uploads.php', 'admin.php'))) {
		
        wp_register_script( 'loader-folders', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mgmlp-loader.js', array( 'jquery' ), '', true );

        wp_localize_script( 'loader-folders', 'mgmlp_ajax', 
              array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
                     'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE))
                   ); 

        wp_enqueue_script('loader-folders');
			
		}
//    if(isset($_REQUEST['page'])) {
//      if($_REQUEST['page'] === 'media-library') {
//        wp_register_script( 'loader-folders', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mgmlp-loader.js', array( 'jquery' ), '', true );
//
//        wp_localize_script( 'loader-folders', 'mgmlp_ajax', 
//              array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
//                     'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE))
//                   ); 
//
//        wp_enqueue_script('loader-folders');
//      }
//    }    		
  }
 
  public function setup_hooks() {
		add_action('init', array($this, 'load_textdomain'));
	  add_action('init', array($this, 'register_mgmlp_post_type'));
		add_action('init', array($this, 'show_mlp_admin_notice'));
	  add_action('admin_init', array($this, 'ignore_notice'));
    
		add_action('admin_print_styles', array($this, 'enqueue_admin_print_styles'));
		add_action('admin_print_scripts', array($this, 'enqueue_admin_print_scripts'));
    add_action('admin_menu', array($this, 'setup_mg_media_plus'));
		
		add_action('media_buttons_context', array($this, 'mlp_media_button'));		
		add_action('admin_footer', array($this, 'mlp_button_admin_footer'));
		
		add_action('media_buttons_context', array($this, 'mlp_gallery_button'));					
        
    add_action('wp_ajax_nopriv_create_new_folder', array($this, 'create_new_folder'));
    add_action('wp_ajax_create_new_folder', array($this, 'create_new_folder'));
    
    add_action('wp_ajax_nopriv_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
    add_action('wp_ajax_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
    
    add_action('wp_ajax_nopriv_upload_attachment', array($this, 'upload_attachment'));
    add_action('wp_ajax_upload_attachment', array($this, 'upload_attachment'));
    
    add_action('wp_ajax_nopriv_copy_media', array($this, 'copy_media'));
    add_action('wp_ajax_copy_media', array($this, 'copy_media'));
        
    add_action('wp_ajax_nopriv_move_media', array($this, 'move_media'));
    add_action('wp_ajax_move_media', array($this, 'move_media'));
    
    add_action('wp_ajax_nopriv_add_to_max_gallery', array($this, 'add_to_max_gallery'));
    add_action('wp_ajax_add_to_max_gallery', array($this, 'add_to_max_gallery'));
    
    add_action('wp_ajax_nopriv_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
    add_action('wp_ajax_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
        
    add_action('wp_ajax_nopriv_sort_contents', array($this, 'sort_contents'));
    add_action('wp_ajax_sort_contents', array($this, 'sort_contents'));
		
    add_action('wp_ajax_nopriv_mgmlp_move_copy', array($this, 'mgmlp_move_copy'));
    add_action('wp_ajax_mgmlp_move_copy', array($this, 'mgmlp_move_copy'));		
        
    add_action( 'new_folder_check', array($this,'admin_check_for_new_folders'));
    
    add_action( 'add_attachment', array($this,'add_attachment_to_folder'));
    
    add_action( 'delete_attachment', array($this,'delete_folder_attachment'));
		
    add_action('wp_ajax_nopriv_max_sync_contents', array($this, 'max_sync_contents'));
    add_action('wp_ajax_max_sync_contents', array($this, 'max_sync_contents'));		
		
    add_action('wp_ajax_nopriv_mlp_tb_load_folder', array($this, 'mlp_tb_load_folder'));
    add_action('wp_ajax_mlp_tb_load_folder', array($this, 'mlp_tb_load_folder'));		
		
    add_action('wp_ajax_nopriv_mlp_load_folder', array($this, 'mlp_load_folder'));
    add_action('wp_ajax_mlp_load_folder', array($this, 'mlp_load_folder'));		
				
    add_action('wp_ajax_nopriv_mlp_get_image_info', array($this, 'mlp_get_image_info'));
    add_action('wp_ajax_mlp_get_image_info', array($this, 'mlp_get_image_info'));		
						
    add_action('wp_ajax_nopriv_mlp_image_add_caption', array($this, 'mlp_image_add_caption'));
    add_action('wp_ajax_mlp_image_add_caption', array($this, 'mlp_image_add_caption'));		
		
    add_action('wp_ajax_nopriv_mlp_update_description', array($this, 'mlp_update_description'));
    add_action('wp_ajax_mlp_update_description', array($this, 'mlp_update_description'));		
		
		add_filter( 'admin_post_thumbnail_html', array( $this, 'mlp_admin_post_thumbnail'), 10, 2 );
		
    add_action('wp_ajax_nopriv_mlp_add_featured_image', array($this, 'mlp_add_featured_image'));
    add_action('wp_ajax_mlp_add_featured_image', array($this, 'mlp_add_featured_image'));		
		
		add_action('wp_ajax_nopriv_mlp_display_folder_ajax', array($this, 'mlp_display_folder_contents_ajax'));
    add_action('wp_ajax_mlp_display_folder_contents_ajax', array($this, 'mlp_display_folder_contents_ajax'));		
		
    add_action('wp_ajax_nopriv_mlp_display_folder_contents_images_ajax', array($this, 'mlp_display_folder_contents_images_ajax'));
    add_action('wp_ajax_mlp_display_folder_contents_images_ajax', array($this, 'mlp_display_folder_contents_images_ajax'));		

    add_action('wp_ajax_nopriv_mlpp_hide_template_ad', array($this, 'mlpp_hide_template_ad'));
    add_action('wp_ajax_mlpp_hide_template_ad', array($this, 'mlpp_hide_template_ad'));				
		
    add_action('wp_ajax_nopriv_mlpp_create_new_ng_gallery', array($this, 'mlpp_create_new_ng_gallery'));
    add_action('wp_ajax_mlpp_create_new_ng_gallery', array($this, 'mlpp_create_new_ng_gallery'));				
			
    add_action('wp_ajax_nopriv_mg_add_to_ng_gallery', array($this, 'mg_add_to_ng_gallery'));
    add_action('wp_ajax_mg_add_to_ng_gallery', array($this, 'mg_add_to_ng_gallery'));				
		
    add_action('wp_ajax_nopriv_mgmlp_add_to_gallery', array($this, 'mgmlp_add_to_gallery'));
    add_action('wp_ajax_mgmlp_add_to_gallery', array($this, 'mgmlp_add_to_gallery'));				
		
		
		//add_action('save_post', array($this, 'mlp_save_featured_image_id'), 20, 2);
		
		add_action('admin_init', array($this, 'edd_mlpp_plugin_updater'), 0 );
		
    add_action('admin_init', array($this, 'edd_mlpp_activate_license'));
		
    add_action('admin_init', array($this, 'edd_mlpp_deactivate_license'));
		
	  add_action('admin_init', array($this, 'edd_mlpp_register_option'));		
		
    add_action('wp_ajax_nopriv_regen_mlp_thumbnails', array($this, 'regen_mlp_thumbnails'));
    add_action('wp_ajax_regen_mlp_thumbnails', array($this, 'regen_mlp_thumbnails'));				
		
		add_action( 'wp_ajax_regeneratethumbnail', array( $this, 'ajax_process_image' ) );
		$this->capability = apply_filters( 'regenerate_thumbs_cap', 'manage_options' );

    add_action('wp_ajax_nopriv_mlp_image_seo_change', array($this, 'mlp_image_seo_change'));
    add_action('wp_ajax_mlp_image_seo_change', array($this, 'mlp_image_seo_change'));				

    add_action('wp_ajax_nopriv_mgmlp_fix_bad_urls', array($this, 'mgmlp_fix_bad_urls'));
    add_action('wp_ajax_mgmlp_fix_bad_urls', array($this, 'mgmlp_fix_bad_urls'));				
				
    add_action('wp_ajax_nopriv_mgmlp_restore_backup', array($this, 'mgmlp_restore_backup'));
    add_action('wp_ajax_mgmlp_restore_backup', array($this, 'mgmlp_restore_backup'));				
		
    add_action('wp_ajax_nopriv_mgmlp_remove_backup', array($this, 'mgmlp_remove_backup'));
    add_action('wp_ajax_mgmlp_remove_backup', array($this, 'mgmlp_remove_backup'));				
				
    add_action('wp_ajax_nopriv_mlp_get_attachment_image_src', array($this, 'mlp_get_attachment_image_src'));
    add_action('wp_ajax_mlp_get_attachment_image_src', array($this, 'mlp_get_attachment_image_src'));				
		
    add_action('wp_ajax_nopriv_hide_maxgalleria_media', array($this, 'hide_maxgalleria_media'));
    add_action('wp_ajax_hide_maxgalleria_media', array($this, 'hide_maxgalleria_media'));						
		
  }
	
	function runing_mlp_error_notice() {
    ?>
    <div class="error notice">
        <p><?php _e( 'Please deactivate Media Library Plus. It should not running when Media Library Plus Pro is activated', 'maxgalleria-media-library' ); ?></p>
    </div>
    <?php
  }
	
	function edd_mlpp_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'mg_edd_mlpp_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( MLPP_EDD_SHOP_URL, __FILE__, array(
				'version' 	=> MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM, 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => EDD_MLPP_NAME, 	// name of this plugin
				'author' 	=> 'MaxFoundry INC'  // author of this plugin
			)
		);
	}
		
	public function edd_mlpp_activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['edd_mlpp_license_activate'] ) ) {

			// run a quick security check
			if( ! check_admin_referer( 'edd_mlpp_nonce', 'edd_mlpp_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'mg_edd_mlpp_license_key' ) );

			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $license,
				'item_name' => urlencode( EDD_MLPP_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( MLPP_EDD_SHOP_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
			$output = print_r($response, true);
			error_log("license activate response $output");

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'mg_edd_mlpp_license_status', $license_data->license );
			//$info = print_r($license_data, true);
			//update_option( 'mg_edd_mlpp_license_response', $info );

		}
	}
	
	public function edd_mlpp_deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['edd_mlpp_license_deactivate'] ) ) {

			// run a quick security check
			if( ! check_admin_referer( 'edd_mlpp_nonce', 'edd_mlpp_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'mg_edd_mlpp_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $license,
				'item_name' => urlencode( EDD_MLPP_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( MLPP_EDD_SHOP_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' )
				delete_option( 'mg_edd_mlpp_license_status' );

		}
	}
	
	public function edd_mlpp_register_option() {
		// creates our settings in the options table
		register_setting('edd_mlpp_license', 'mg_edd_mlpp_license_key', array($this, 'edd_sanitize_mlpp_license' ));
	}
	
	public function edd_sanitize_mlpp_license( $new ) {
		$old = get_option( 'mg_edd_mlpp_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'mg_edd_mlpp_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}	
	
	public function mlp_media_button($context) {
		global $pagenow, $wp_version;
		$output = '';
		
		// Only run in post/page creation and edit screens
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) {
			$title = __('Add MLPP Media', 'maxgalleria-media-library');
			$icon = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/images/mlp-icon-16.png';
			$img = '<span class="wp-media-buttons-icon" style="background-image: url(' . $icon . '); width: 16px; height: 16px; margin-top: 1px;"></span>';
			//$output = '<a href="#TB_inline?height=450&amp;width=1153&amp;inlineId=select-mlp-container" id="mlp-show-library" class="thickbox button" title="' . $title . '" style="padding-left: .4em;">' . $img . ' ' . $title . '</a>';
			$output = '<a href="#TB_inline?height=450&amp;width=750&amp;inlineId=select-mlp-container" id="mlp-show-library" class="thickbox button" title="' . $title . '" style="padding-left: .4em;">' . $img . ' ' . $title . '</a>';
			$output .= '<script>' . PHP_EOL;
			$output .= '	jQuery(document).ready(function(){' . PHP_EOL;
			$output .= '	  jQuery("#mlp-show-library").click(function(){' . PHP_EOL;      
			$output .= '      window.hide_checkboxes = true;' . PHP_EOL;
			$output .= '	    jQuery("#insert_wp_gallery").hide();' . PHP_EOL;
			//$output .= '	    jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_insert_row").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_featured").val("");' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_custom_link_label").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_custom_link").show();' . PHP_EOL;			
			$output .= '	    jQuery("#mlp_tb_align_label").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_alignment").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_size_label").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_size").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_link_to_label").show();' . PHP_EOL;
			$output .= '	    jQuery("#mlp_tb_link_select").show();' . PHP_EOL;
			$output .= '	    jQuery("#insert_mlp_media").val("Insert");' . PHP_EOL;
			$output .= '	    jQuery("a.tb-media-attachment").css("cursor", "pointer");' . PHP_EOL;
			$output .= '      window.hide_checkboxes = true;' . PHP_EOL;
			$output .= '	  });' . PHP_EOL;
			
			$output .= '	});' . PHP_EOL;
			$output .= '</script>' . PHP_EOL;
		}

		return $context . $output;
	}
	
		public function mlp_gallery_button($context) {
		global $pagenow, $wp_version;
		$output = '';

		// Only run in post/page creation and edit screens
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) {
			$title = __('JetPack / WP Gallery', 'maxgalleria-media-library');
			$icon = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/images/mlp-icon-16.png';
			$img = '<span class="wp-media-buttons-icon" style="background-image: url(' . $icon . '); width: 16px; height: 16px; margin-top: 1px;"></span>';
			$output = '<a href="#TB_inline?height=450&amp;width=753&amp;inlineId=select-mlp-container" id="mlp-gallery-insert" class="thickbox button" title="' . $title . '" style="padding-left: .4em;">' . $img . ' ' . $title . '</a>';
			$output .= '<script>' . PHP_EOL;
			$output .= '	jQuery(document).ready(function(){' . PHP_EOL;
			$output .= '	  jQuery("#mlp-gallery-insert").click(function(){' . PHP_EOL;      
			$output .= '      window.hide_checkboxes = false;' . PHP_EOL;
			$output .= '	    jQuery("#mlp_insert_row").hide();' . PHP_EOL;
			$output .= '	    jQuery("#insert_wp_gallery").show();' . PHP_EOL;
      $output .= '	    jQuery("#insert_mlp_wp_gallery").prop("disabled", false);' . PHP_EOL;			
      $output .= '	    jQuery("#select_all_mlp_wp_gallery").prop("disabled", false);' . PHP_EOL;			
      $output .= '	    jQuery("#insert_mlp_pe_images").prop("disabled", false);' . PHP_EOL;			
      $output .= '	    jQuery("#remove_mlp_pe_images").prop("disabled", false);' . PHP_EOL;			
      $output .= '	    jQuery("#clear_mlp_pe_images").prop("disabled", false);' . PHP_EOL;			
			$output .= '	    jQuery("div#mgmlp-tb-container input.mgmlp-media").show();' . PHP_EOL;
			$output .= '	    jQuery("a.tb-media-attachment").css("cursor", "default");' . PHP_EOL;
			$output .= '      window.hide_checkboxes = false;' . PHP_EOL;

			
			//$output .= '	    jQuery("#insert_mlp_wp_gallery").show();' . PHP_EOL;						
			//$output .= '	    jQuery("#insert_mlp_media").val("Insert");' . PHP_EOL;
			$output .= '	  });' . PHP_EOL;
			
			$output .= '	});' . PHP_EOL;
			$output .= '</script>' . PHP_EOL;
		}

		return $context . $output;
	}

	
	public function mlp_button_admin_footer() {
		require_once 'mlp-media-button.php';
	}
	
	  
  public function delete_folder_attachment ($postid) {    
    global $wpdb;
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    $where = array( 'post_id' => $postid );
    $wpdb->delete( $table, $where );    
  }

    // in case an image is uploaded in the WP media library we
  // need to add a record to the mgmlp_folders table
  public function add_attachment_to_folder ($post_id) {
    
    $folder_id = $this->get_default_folder();
    if($folder_id !== false) {
      $this->add_new_folder_parent($post_id, $folder_id);
    }  
  }
  
  public function get_default_folder() {
    global $wpdb;
    		
		if( get_option('uploads_use_yearmonth_folders') === false)
			return $this->uploads_folder_ID;

    $base_url = $this->upload_dir['baseurl'];
    $year_month = date("m");    
    $year = date("Y");    
    //$guid = $base_url . '/' . $year . '/' . $year_month;
    $relative_dir = $year . '/' . $year_month;
    
    if($this->is_windows())
      $relative_dir = str_replace('\\', '/', $relative_dir);      
    
    //$sql = "select ID from $wpdb->prefix" . "posts where guid = '$guid'";
		$sql = "SELECT ID FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = ID
WHERE pm.meta_value = '$relative_dir' 
and pm.meta_key = '_wp_attached_file'";

    $row = $wpdb->get_row($sql);
    if($row) {
      return $row->ID;
    }
    else {
			$this->write_log("This folder was not found: $relative_dir");
      return false;
    }
    
  }

  public function register_mgmlp_post_type() {
    
		$args = apply_filters(MGMLP_FILTER_POST_TYPE_ARGS, array(
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
      'show_in_nav_menus' => false,
      'show_in_admin_bar' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'hierarchical' => true,
			'supports' => false,
			'exclude_from_search' => true
		));
		
		register_post_type(MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE, $args);
    
  }
  
  public function add_folder_table () {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    $sql = "CREATE TABLE IF NOT EXISTS " . $table . " ( 
  `post_id` bigint(20) NOT NULL,
  `folder_id` bigint(20) NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";	
 
    dbDelta($sql);
    
  }
    
  public function upload_attachment () {
                  
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    }
    
    $uploads_path = wp_upload_dir();
    
    if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(stripslashes(strip_tags($_POST['folder_id'])));
    else
      $folder_id = 0;
    
    if ((isset($_POST['title_text'])) && (strlen(trim($_POST['title_text'])) > 0))
      $title_text = trim(stripslashes(strip_tags($_POST['title_text'])));
    else
      $title_text = "";
		
    if ((isset($_POST['alt_text'])) && (strlen(trim($_POST['alt_text'])) > 0))
      $alt_text = trim(stripslashes(strip_tags($_POST['alt_text'])));
    else
      $alt_text = "";
		
    $destination = $this->get_folder_path($folder_id);
				    
    if ( 0 < $_FILES['file']['error'] ) {
      echo 'Error: ' . $_FILES['file']['error'] . '<br>';
    }
    else {
      
      // insure it has a unique name
      $new_filename = wp_unique_filename( $destination, $_FILES['file']['name'], null );
      
      $filename = $destination . DIRECTORY_SEPARATOR . $new_filename;
      if( move_uploaded_file($_FILES['file']['tmp_name'], $filename) ) {
        
        // Set correct file permissions.
	      $stat = stat( dirname( $filename ));
        $perms = $stat['mode'] & 0000644;
        @ chmod( $filename, $perms );
        
        $this->add_new_attachment($filename, $folder_id, $title_text, $alt_text);

        $this->display_folder_contents ($folder_id);
        
      }
    }
        
    die();
  }
      
  public function add_new_attachment($filename, $folder_id, $title_text="", $alt_text="") {

    $parent_post_id = 0;
    remove_action( 'add_attachment', array($this,'add_attachment_to_folder'));

    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype( basename( $filename ), null );

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();
    
    $file_url = $this->get_file_url_for_copy($filename);
		
		$image_seo = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');
		
		if($image_seo === 'on') {
			
			$folder_name = $this->get_folder_name($folder_id);
			
			$file_name = basename( $filename );
			
			$new_file_title = $title_text;
			
			$new_file_title = str_replace('%foldername', $folder_name, $new_file_title );			
			
			$new_file_title = str_replace('%filename', $file_name, $new_file_title );			
									
		  //$default_alt = get_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT);
			$default_alt = $alt_text;
			
			$default_alt = str_replace('%foldername', $folder_name, $default_alt );			
			
			$default_alt = str_replace('%filename', $file_name, $default_alt );			
						
		} else {
      $new_file_title	= preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
		}		
		            
    // Prepare an array of post data for the attachment.
    $attachment = array(
      'guid'           => $file_url, 
      'post_mime_type' => $filetype['type'],
      'post_title'     => $new_file_title,
  		'post_parent'    => 0,
      'post_content'   => '',
      'post_status'    => 'inherit'
    );
    
    // Insert the attachment.
    $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );    

		if($image_seo === 'on') 
		  update_post_meta($attach_id, '_wp_attachment_image_alt', $default_alt);			
		
    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        
    wp_update_attachment_metadata( $attach_id, $attach_data );

    if($this->is_windows()) {
      
      // get the uploads dir name
      $basedir = $this->upload_dir['baseurl'];
      $uploads_dir_name_pos = strrpos($basedir, '/');
      $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);
    
      //find the name and cut off the part with the uploads path
      $string_position = strpos($filename, $uploads_dir_name);
      $uploads_dir_length = strlen($uploads_dir_name) + 1;
      $uploads_location = substr($filename, $string_position+$uploads_dir_length);
      $uploads_location = str_replace('\\','/', $uploads_location);   
			$uploads_location = ltrim($uploads_location, '/');
      
      // put the short path into postmeta
	    $media_file = get_post_meta( $attach_id, '_wp_attached_file', true );
    
      if($media_file !== $uploads_location )
        update_post_meta( $attach_id, '_wp_attached_file', $uploads_location );
    }

    $this->add_new_folder_parent($attach_id, $folder_id );
    add_action( 'add_attachment', array($this,'add_attachment_to_folder'));
    
    return $attach_id;
    
  }
	  
  public function scan_attachments () {
    
    global $wpdb;
            
    $uploads_path = wp_upload_dir();
    
    if(!$uploads_path['error']) {
			
			//echo "<p id='scanning-message'>Scanning the Media Library for existing folders...Please wait.</p>";
      
      //find the uploads folder
      $base_url = $uploads_path['baseurl'];
      $last_slash = strrpos($base_url, '/');
      $uploads_dir = substr($base_url, $last_slash+1);
			$this->uploads_folder_name = $uploads_dir;
			$this->uploads_folder_name_length = strlen($uploads_dir);
      
      update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, $uploads_dir);
                              
      //create uploads parent media folder      
      $uploads_parent_id = $this->add_media_folder($uploads_dir, 0, $base_url);
      update_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID, $uploads_parent_id);
      
      $sql = "SELECT ID, pm.meta_value as attached_file 
FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
WHERE post_type = 'attachment' 
AND pm.meta_key = '_wp_attached_file'
ORDER by ID";
			
      $rows = $wpdb->get_results($sql);
      
      $current_folder = "";
            
      $parent_id = $uploads_parent_id;
            
      if($rows) {
        foreach($rows as $row) {
					
				if( strpos($row->attached_file, "http:") !== false || strpos($row->attached_file, "https:") !== false)  {
					error_log("bad file path: $row->ID $row->attached_file");						
				} else {
									
					  //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
						$baseurl = $this->upload_dir['baseurl'];
						$baseurl = rtrim($baseurl, '/') . '/';
						$image_location = $baseurl . ltrim($row->attached_file, '/');
																          
          //if(strpos($image_location, $uploads_dir)) {
										                    
            $sub_folders = $this->get_folders($image_location);
            $attachment_file = array_pop($sub_folders);  

            $uploads_length = strlen($uploads_dir);
            $new_folder_pos = strpos($image_location, $uploads_dir ); 
            $folder_path = substr($image_location, 0, $new_folder_pos+$uploads_length );

            foreach($sub_folders as $sub_folder) {
              
              // check for URL path in database
              $folder_path = $folder_path . '/' . $sub_folder;

              $new_parent_id = $this->folder_exist($folder_path);														
              if($new_parent_id === false) {
                if($this->is_new_top_level_folder($uploads_dir, $sub_folder, $folder_path)) {
                  $parent_id = $this->add_media_folder($sub_folder, $uploads_parent_id, $folder_path); 
                }  
                else {
                  $parent_id = $this->add_media_folder($sub_folder, $parent_id, $folder_path); 
                }  
              }  
              else
                $parent_id = $new_parent_id;
            }          

            $this->add_new_folder_parent($row->ID, $parent_id );
          //} //test for ?
				  } // test for http
        } //foreach         
        
      } //rows  
			if ( ! wp_next_scheduled( 'new_folder_check' ) )
				wp_schedule_event( time(), 'daily', 'new_folder_check' );
            
    }
		
//		echo "done";
//		die();
        
  }
     
  private function is_new_top_level_folder($uploads_dir, $folder_name, $folder_path) {
    
    $needle = $uploads_dir . '/' . $folder_name;
    if(strpos($folder_path, $needle))
      return true;
    else
      return false;   
  }

  private function get_folders($path) {
    $sub_folders = explode('/', $path);
    while( $sub_folders[0] !== $this->uploads_folder_name )
      array_shift($sub_folders);
    
    if($sub_folders[0] === $this->uploads_folder_name) 
      array_shift($sub_folders);
      
    return $sub_folders;
  }
  
  private function folder_exist($folder_path) {
    
    global $wpdb;    
		
		$relative_path = substr($folder_path, $this->base_url_length);
		$relative_path = ltrim($relative_path, '/');
						    
//		$sql = "select post_id
//from {$wpdb->prefix}postmeta 
//where meta_key = '_wp_attached_file' 
//and meta_value = '$relative_path'";

		$sql = "SELECT ID FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = ID
WHERE pm.meta_value = '$relative_path' 
and pm.meta_key = '_wp_attached_file'";

    $row = $wpdb->get_row($sql);
    if($row === null)
      return false;
    else
      return $row->ID;
             
  }
  
  private function add_media_folder($folder_name, $parent_folder, $base_path ) {
    
    global $wpdb;    
    $table = $wpdb->prefix . "posts";	    
		
    $new_folder_id = $this->mpmlp_insert_post(MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE, 
    $folder_name, $base_path, 'publish' );

		$attachment_location = substr($base_path, $this->base_url_length);
		$attachment_location = ltrim($attachment_location, '/');
				
		update_post_meta($new_folder_id, '_wp_attached_file', $attachment_location);
        		
    $this->add_new_folder_parent($new_folder_id, $parent_folder);
        
    return $new_folder_id;
        
  }
  
  private function add_new_folder_parent($record_id, $parent_folder) {
    
    global $wpdb;    
    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    
      $new_record = array( 
			  'post_id'   => $record_id, 
			  'folder_id' => $parent_folder 
			);
      
      $wpdb->insert( $table, $new_record );
      
  }
    
  public function setup_mg_media_plus() {
    add_menu_page(__('Media Library + Pro','maxgalleria-media-library'), __('Media Library + Pro','maxgalleria-media-library'), 'upload_files', 'media-library', array($this, 'media_library'), 'dashicons-admin-media', 11 );				
    add_submenu_page(null, 'Check For New Folders', 'Check For New Folders', 'upload_files', 'check-for-new-folders', array($this, 'check_for_new_folders'));
    add_submenu_page(null, 'Search Library', 'Search Library', 'upload_files', 'search-library', array($this, 'search_library'));
    add_submenu_page('media-library', __('Add New Folders','maxgalleria-media-library'), __('Add New Folders','maxgalleria-media-library'), 'upload_files', 'admin-check-for-new-folders', array($this, 'admin_check_for_new_folders'));
		add_submenu_page(null, '', '', 'manage_options', 'mlp-review-later', array($this, 'mlp_set_review_later'));
		add_submenu_page(null, '', '', 'manage_options', 'mlp-review-notice', array($this, 'mlp_set_review_notice_true'));    		
    add_submenu_page('media-library', __('Regenerate Thumbnails','maxgalleria-media-library'), __('Regenerate Thumbnails','maxgalleria-media-library'), 'upload_files', 'mlp-regenerate-thumbnails', array($this, 'regenerate_interface'));
    add_submenu_page('media-library', __('Image SEO','maxgalleria-media-library'), __('Image SEO','maxgalleria-media-library'), 'upload_files', 'image-seo', array($this, 'image_seo'));
    add_submenu_page('media-library', __('Support','maxgalleria-media-library'), __('Support','maxgalleria-media-library'), 'upload_files', 'mlp-support', array($this, 'mlp_support'));
		
    add_submenu_page('media-library', __('Settings','maxgalleria-media-library'), __('Settings','maxgalleria-media-library'), 'upload_files', 'mlpp-settings', array($this, 'mlpp_settings'));
				
    //add_submenu_page('media-library', __('Scan','maxgalleria-media-library'), __('Scan','maxgalleria-media-library'), 'upload_files', 'scan-attachments', array($this, 'scan_attachments'));
  }
  
	public function load_textdomain() {
		load_plugin_textdomain('maxgalleria-media-library', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}
  
	public function ignore_notice() {
		if (current_user_can('install_plugins')) {
			global $current_user;
			
			if (isset($_GET['maxgalleria-media-library-ignore-notice']) && $_GET['maxgalleria-media-library-ignore-notice'] == 1) {
				add_user_meta($current_user->ID, MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE, true, true);
			}
		}
	}

	public function show_mlp_admin_notice() {
    global $current_user;
    
    $review = get_user_meta( $current_user->ID, MAXGALLERIA_MLP_REVIEW_NOTICE, true );
    if( $review !== 'off') {
      if($review === '') {
				//show review notice after three days
        $review_date = date('Y-m-d', strtotime("+3 days"));        
        update_user_meta( $current_user->ID, MAXGALLERIA_MLP_REVIEW_NOTICE, $review_date );
				
				//show notice if not found
        //add_action( 'admin_notices', array($this, 'mlp_review_notice' ));            
			} else {
        $now = date("Y-m-d"); 
        $review_time = strtotime($review);
        $now_time = strtotime($now);
        if($now_time > $review_time)
          add_action( 'admin_notices', array($this, 'mlp_review_notice' ));
      }
    }          
	}
    
  public function media_library() {
    
    global $wpdb;
    global $pagenow;

		if(is_multisite()) {
			$table_name = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {		
			  $this->activate();
			}	
		}
		
		if(get_option('mlpp_show_template_ad', "on") == "on")
			$show_temp_ad = true;
		else
			$show_temp_ad = false;

    ?>      
<!--      <div id="fb-root"></div>
      <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=636262096435499";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>-->
    <?php
    
    $sort_order = get_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER );    
    $move_or_copy = get_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY );    
        
    if ((isset($_GET['media-folder'])) && (strlen(trim($_GET['media-folder'])) > 0)) {
      $current_folder_id = trim(stripslashes(strip_tags($_GET['media-folder'])));
      if(!is_numeric($current_folder_id)) {
        $current_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
        $current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );        
	      $this->uploads_folder_name = $current_folder;
	      $this->uploads_folder_name_length = strlen($current_folder);
	      $this->uploads_folder_ID = $current_folder_id;				
      }
      else {
        $current_folder = $this->get_folder_name($current_folder_id);
			}	
    } else {             
      if(get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "none") !== 'none') { 
				$current_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
				$current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
				$this->uploads_folder_name = $current_folder;
				$this->uploads_folder_name_length = strlen($current_folder);
				$this->uploads_folder_ID = $current_folder_id;				
			}
    }  
		
    if ( 'impossible_default_value_3579' === get_option( MAXGALLERIA_MEDIA_LIBRARY_POSTMETA_UPDATED, 'impossible_default_value_3579' ) ) {
			$this->update_folder_postmeta();
		}
		            
    ?>


      <div id="wp-media-grid" class="wrap">                
        <!--empty h2 for where WP notices will appear--> 
				<h1></h1>
        <div class="media-plus-toolbar"><div class="media-toolbar-secondary">  
            
				<div id="mgmlp-header">		
					<div id='mgmlp-title-area'>
						<h2 class='mgmlp-title'><?php _e('Media Library Plus Pro', 'maxgalleria-media-library' ); ?> </h2>  

					</div> <!-- mgmlp-title-area -->
					<div id="new-top-promo">
						<a id="mf-top-logo" target="_blank" href="http://maxfoundry.com"><img alt="maxfoundry logo" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/mf-logo.png" width="140" height="25" ></a>
						<p class="center-text"><?php _e('Makers of', 'maxgalleria-media-library' ); ?> <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></p>
						<p class="center-text-no-ital">Need help? Click here for <a href="https://maxgalleria.com/forums/forum/media-library-plus-pro/" target="_blank">Awesome Support!</a></p>
						<p class="center-text-no-ital">Or Email Us at <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></p>
					</div>
					
				</div><!--mgmlp-header-->
        <div class="clearfix"></div>  
        <!--<p id='mlp-more-info'><a href='http://maxgalleria.com/media-library-plus/' target="_blank"><?php _e('Click here to learn about the Media Library Plus', 'maxgalleria-media-library' ); ?></a></p>-->
                                      
        <!--<div class="clearfix"></div>-->
				          
					<div id="mgmlp-outer-container"> 
						
				  <div id="scan-results"></div>				
						
					<?php 
																									
						$phpversion = phpversion();		
						if($phpversion < '5.6')		
							echo "<br><div>" . __('Current PHP version, ','maxgalleria-media-library') . $phpversion . __(', is outdated. Please upgrade to version 5.6.','maxgalleria-media-library') . "</div>";
										
            $folder_location = $this->get_folder_path($current_folder_id);

            $folders_path = "";
            $parents = $this->get_parents($current_folder_id);

            $folder_count = count($parents);
            $folder_counter = 0;        
            $current_folder_string = site_url() . "/wp-content";
            foreach( $parents as $key => $obj) { 
              $folder_counter++;
              if($folder_counter === $folder_count)
                $folders_path .= $obj['name'];      
              else
                $folders_path .= '<a folder="' . $obj['id'] . '" class="media-link">' . $obj['name'] . '</a>/';      
              $current_folder_string .= '/' . $obj['name'];
            }
					
					
					echo "<h3 id='mgmlp-breadcrumbs'>" . __('Location:','maxgalleria-media-library') . " $folders_path</h3>"; 
					
					?>
						
						<div id="folder-tree-container">
							<div id="alwrapnav">
								<div style="display:none" id="ajaxloadernav"></div>
						  </div>
							
							<div id="above-toolbar">

								<?php

								echo '  <a id="add-new_attachment" help="' . __('Upload new files.','maxgalleria-media-library') . '" class="gray-blue-link" href="javascript:slideonlyone(\'add-new-area\');">' . __('Add File','maxgalleria-media-library') . '</a>' . PHP_EOL;

								echo '  <a id="add-new-folder" help="' . __('Create a new folder. Type in a folder name (do not use spaces, single or double quote marks) and click Create Folder.','maxgalleria-media-library') . '"  class="gray-blue-link" href="javascript:slideonlyone(\'new-folder-area\');">' .  __('Add Folder','maxgalleria-media-library') . '</a>' . PHP_EOL;

								?>

							</div>
						
							<ul id="folder-tree">
								
							</ul>
							<p><?php _e('When moving/copying to a new folder place your pointer, not the image, on the folder where you want the file(s) to go.','maxgalleria-media-library')?></p>
							<p><?php _e('To drag multiple images, check the box under the files you want to move and then drag one of the images to the desired folder.','maxgalleria-media-library')?></p>
							<p><?php _e('To move/copy to a folder nested under the top level folder click the triangle to the left of the folder to show the nested folder that is your target.','maxgalleria-media-library')?></p>		
							<p><?php _e('To delete a folder, right click on the folder and a popup menu will appear. Click on the option, "Delete this folder?" If the folder is empty, it will be deleted.','maxgalleria-media-library')?></p>
							<p><?php _e('To hide a folder and all its sub folders and files, right click on a folder, On the popup menu that appears, click "Hide this folder?" and those folders and files will be removed from the Media Library, but not from the server.','maxgalleria-media-library')?></p>
							<p><?php _e('If you click on an image and end up in WordPress Media Library please backclick two times to return to MLP','maxgalleria-media-library')?></p>														
						</div>				
          <div id="mgmlp-library-container">
            <div id="alwrap">
              <div style="display:none" id="ajaxloader"></div>
            </div>
            <?php 
            
            echo '<div id="mgmlp-toolbar">' . PHP_EOL;
            $move_or_copy = ($move_or_copy === 'on') ? true : false;
						echo '  <div class="onoffswitch" help="' . __('Move/Copy Toggle. Move or copy selected files to a different folder. <span class=\'mlp-warning\'>Images already in existing pages or blog posts will not display if they are moved from their current location unless you deleted and reinsert them after they have been moved.</span>','maxgalleria-media-library') . '">' . PHP_EOL;
						//echo '    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="move-copy-switch" >' . PHP_EOL;
						echo '    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="move-copy-switch" ' . checked($move_or_copy, true, false)  . '>' . PHP_EOL;
						echo '    <label class="onoffswitch-label" for="move-copy-switch">' . PHP_EOL;
						echo '      <span class="onoffswitch-inner"></span>' . PHP_EOL;
						echo '      <span class="onoffswitch-switch"></span>' . PHP_EOL;
						echo '    </label>' . PHP_EOL;
						echo '  </div>' . PHP_EOL;
						
            
						echo '  <a id="rename-file" help="' . __('Rename a file; select only one file. Folders cannot be renamed. Type in a new name with no spaces and without the extention and click Rename.','maxgalleria-media-library') . '" class="gray-blue-link" href="javascript:slideonlyone(\'rename-area\');">' .  __('Rename','maxgalleria-media-library') . '</a>' . PHP_EOL;
            														
            echo '  <a id="delete-media" help="' . __('Delete selected files.','maxgalleria-media-library') . '" class="gray-blue-link" >' .  __('Delete','maxgalleria-media-library') . '</a>' . PHP_EOL;
						
						echo '  <a id="select-media" help="' . __('Select or unselect all files in the folder.','maxgalleria-media-library') . '" class="gray-blue-link" >' .  __('Select/Unselect All','maxgalleria-media-library') . '</a>' . PHP_EOL;

						echo '  <a id="sync-media" help="' . __('Sync the contents of the current folder with the server','maxgalleria-media-library') . '" class="gray-blue-link" >' .  __('Sync','maxgalleria-media-library') . '</a>' . PHP_EOL;            
						
                        
            echo '  <div id="sort-wrap"><select id="mgmlp-sort-order">' . PHP_EOL;
            echo '    <option value="1" ' . ($sort_order === '1' ? 'selected="selected"' : ''  ). '>' . __('Sort by Name','maxgalleria-media-library') . '</option>' . PHP_EOL;
            echo '    <option value="0" ' . ($sort_order === '0' ? 'selected="selected"' : ''  ). '>' . __('Sort by Date','maxgalleria-media-library') . '</option>' . PHP_EOL;
            echo '  </select></div>' . PHP_EOL;
                                    
            echo '  <input type="search" placeholder="Search" id="mgmlp-media-search-input" class="search">' . PHP_EOL;            
            //echo '  <div id="search-wrap"><input type="search" placeholder="Search" id="mgmlp-media-search-input" class="search"></div>' . PHP_EOL;            
                        						
            echo '</div>' . PHP_EOL;           
						
            echo '  <div id="mgmlp-toolbar">' . PHP_EOL;
							
            echo '  <a id="mgmlp-regen-thumbnails" help="' . __('Regenerates the thumbnails of selected images','maxgalleria-media-library') . '" class="gray-blue-link" >' .  __('Regenerate Thumbnails','maxgalleria-media-library') . '</a>' . PHP_EOL;            						

						if(class_exists('MaxGalleria')) {
              echo '  <a id="add-images-to-gallery" help="' . __('Add images to an existing MaxGalleria gallery. Folders can not be added to a gallery. Images already in the gallery will not be added. ','maxgalleria-media-library') . '" class="gray-blue-link" href="javascript:slideonlyone(\'gallery-area\');">' .  __('Add to MaxGalleria Gallery','maxgalleria-media-library') . '</a>' . PHP_EOL;
						}
														
						if(class_exists('C_NextGEN_Bootstrap')) {
              echo '    <a id="new-ng-gallery" help="' . __('Create a new NextGEN Gallery','maxgalleria-media-library') . '" class="gray-blue-link" href="javascript:slideonlyone(\'new-gallery-area\');">' .  __('New NextGen Gallery','maxgalleria-media-library') . '</a>' . PHP_EOL;            
							
              echo '    <a id="add_to-ng-gallery" help="' . __('Add images to a NextGEN Gallery','maxgalleria-media-library') . '" class="gray-blue-link" href="javascript:slideonlyone(\'add-to-gallery-area\');">' .  __('Add Images to NextGen Gallery','maxgalleria-media-library') . '</a>' . PHP_EOL;            
						}
						
							$filter_output = "";
						
		          echo  apply_filters(MGMLP_FILTER_ADD_TOOLBAR_BUTTONS, $filter_output);
			
													
							echo '  </div>' . PHP_EOL;
						
            
            echo '  <div id="folder-message">' . PHP_EOL;
            echo '  </div>' . PHP_EOL;
            
						$image_seo = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');
						if($image_seo === 'on') {
							$seo_file_title = get_option(MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT);
							$seo_alt_text = get_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT);
						}
            echo '<div id="add-new-area" class="input-area">' . PHP_EOL;
            echo '  <div id="dragandrophandler">' . PHP_EOL;
            echo '    <div>Drag & Drop Files Here</div>' . PHP_EOL;
            echo '    <div id="upload-text">or select an image to upload:</div>' . PHP_EOL;
            echo '    <input type="file" name="fileToUpload" id="fileToUpload">' . PHP_EOL;  
            echo '    <input type="hidden" name="folder_id" id="folder_id" value="' . $current_folder_id . '">' . PHP_EOL;
            echo '    <input type="button" value="Upload Image" id="mgmlp_ajax_upload" name="submit_image">' . PHP_EOL;
            echo '  </div>' . PHP_EOL;
						if($image_seo === 'on') {
						  echo '  <label class="mlp-seo-label" for="mlp_title_text">Image Title Text:&nbsp;</label><input class="seo-fields" type="text" name="mlp_title_text" id="mlp_title_text" value="' . $seo_file_title .'">' . PHP_EOL;
						  echo '  <label class="mlp-seo-label" for="mlp_alt_text">Image ALT Text:&nbsp;</label><input class="seo-fields" type="text" name="mlp_alt_text" id="mlp_alt_text" value="' . $seo_alt_text . '">' . PHP_EOL;
						}
            echo '</div>' . PHP_EOL;
            echo '<div class="clearfix"></div>' . PHP_EOL;
            
            echo '<div id="rename-area" class="input-area">' . PHP_EOL;
            echo '  <div id="rename-box">' . PHP_EOL;
            echo __('File Name: ','maxgalleria-media-library') . '<input type="text" name="new-file-name" id="new-file-name", value="" />' . PHP_EOL;
            echo '<div class="btn-wrap"><a id="mgmlp-rename-file" class="gray-blue-link" >'. __('Rename','maxgalleria-media-library') .'</a></div>' . PHP_EOL;
            echo '  </div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '<div class="clearfix"></div>' . PHP_EOL;
                                               
						if(class_exists('MaxGalleria')) {
						
							echo '<div id="gallery-area" class="input-area">' . PHP_EOL;
							echo '  <div id="gallery-box">' . PHP_EOL;
							$sql = "select ID, post_title 
	from $wpdb->prefix" . "posts 
	LEFT JOIN $wpdb->prefix" . "postmeta ON($wpdb->prefix" . "posts.ID = $wpdb->prefix" . "postmeta.post_id)
	where post_type = 'maxgallery' 
	and $wpdb->prefix" . "postmeta.meta_key = 'maxgallery_type'
	and $wpdb->prefix" . "postmeta.meta_value = 'image'
	order by post_name";
							//echo $sql;
							$gallery_list = "";
							$rows = $wpdb->get_results($sql);

							if($rows) {
								foreach ($rows as $row) {
									$gallery_list .='<option value="' . $row->ID . '">' . $row->post_title . '</option>' . PHP_EOL;
								}
							}
							echo '    <select id="gallery-select">' . PHP_EOL;
							echo        $gallery_list;
							echo '    </select>' . PHP_EOL;
							echo '<div class="btn-wrap"><a id="add-to-gallery" class="gray-blue-link" >'. __('Add Images','maxgalleria') .'</a></div>' . PHP_EOL;

							echo '  </div>' . PHP_EOL;
							echo '</div>' . PHP_EOL;
							echo '<div class="clearfix"></div>' . PHP_EOL;            
						}
                        						
            echo '<div id="new-folder-area" class="input-area">' . PHP_EOL;
            echo '  <div id="new-folder-box">' . PHP_EOL;
            echo '<input type="hidden" id="current-folder-id" value="' . $current_folder_id . '" />' . PHP_EOL;
            echo __('Folder Name: ','maxgalleria-media-library') . '<input type="text" name="new-folder-name" id="new-folder-name", value="" />' . PHP_EOL;
            echo '<div class="btn-wrap"><a id="mgmlp-create-new-folder" class="gray-blue-link" >'. __('Create Folder','maxgalleria-media-library') .'</a></div>' . PHP_EOL;
            echo '  </div>' . PHP_EOL;                        
            echo '</div>' . PHP_EOL;
            echo '<div class="clearfix"></div>' . PHP_EOL;
						
            if(class_exists('C_NextGEN_Bootstrap')) {
							echo '<div id="new-gallery-area" class="input-area">' . PHP_EOL;
							echo '  <div id="new-gallery-box">' . PHP_EOL;
              echo '<input type="hidden" id="ng-current-folder-id" value="' . $current_folder_id . '" />' . PHP_EOL;
							echo __('Gallery Name: ','maxgalleria-media-library') . '<input type="text" name="new-gallery-name" id="new-gallery-name", value="" />' . PHP_EOL;
							echo '<div class="btn-wrap"><a id="mgmlp-create-new-gallery" class="gray-blue-link" >'. __('Create NG Gallery','maxgalleria-media-library') .'</a></div>' . PHP_EOL;
							echo '  </div>' . PHP_EOL;                        
							echo '</div>' . PHP_EOL;
							echo '<div class="clearfix"></div>' . PHP_EOL;
							
							echo '<div id="add-to-gallery-area" class="input-area">' . PHP_EOL;
							echo '  <div id="images-to-gallery-box">' . PHP_EOL;
							
              $sql = "SELECT gid, title FROM {$wpdb->prefix}ngg_gallery ORDER BY name";
							//echo $sql;
							$ng_gallery_list = "";
							$rows = $wpdb->get_results($sql);

							if($rows) {
								foreach ($rows as $row) {
									$ng_gallery_list .='<option value="' . $row->gid . '">' . $row->title . '</option>' . PHP_EOL;
								}
							}
							echo __('NextGen Galleries: ','maxgalleria-media-library');
							echo '    <select id="ng-gallery-select">' . PHP_EOL;
							echo        $ng_gallery_list;
							echo '    </select>' . PHP_EOL;
							echo '<div class="btn-wrap"><a id="mlpp-add-to-ng-gallery" class="gray-blue-link" >'. __('Add Images','maxgalleria') .'</a></div>' . PHP_EOL;
							
							echo '  </div>' . PHP_EOL;                        
							echo '</div>' . PHP_EOL;
							echo '<div class="clearfix"></div>' . PHP_EOL;
														
						}	
						
						$filter_output = "";
						echo  apply_filters(MGMLP_FILTER_ADD_TOOLBAR_AREAS, $filter_output);													
                        
            echo '<div id="mgmlp-file-container">' . PHP_EOL;
              $this->display_folder_contents ($current_folder_id);
            echo '</div>' . PHP_EOL;
                        
            ?>
            <script>

            jQuery(document).on("click", ".media-link", function () {

              var folder = jQuery(this).attr('folder');

              var home_url = "<?php echo site_url(); ?>"; 

              window.location.href = home_url + '/wp-admin/admin.php?page=media-library&' + 'media-folder=' + folder;

            });
            
            
            jQuery('#mgmlp-media-search-input').keydown(function (e){
              if(e.keyCode == 13){
                
                var search_value = jQuery('#mgmlp-media-search-input').val();
                
                var home_url = "<?php echo site_url(); ?>"; 

                window.location.href = home_url + '/wp-admin/admin.php?page=search-library&' + 's=' + search_value;
                
              }  
            })    
            
            </script>  

          </div> <!-- mgmlp-library-container -->
          </div> <!-- mgmlp-outer-container -->
        </div>
          
          <div class="clearfix"></div>
					<?php //if($show_temp_ad) { ?>
<!--          <div id="mlpp-ad" class="large-12">
            <div class="mg-promo">
		        <a id="ad-close-btn">x</a>							
            <p class="mg-promo-title"><a target="_blank" href="http://maxgalleria.com/shop/category/addons/?utm_source=mlefree&utm_medium=tout&utm_campaign=tout ">Try these terrific MaxGalleria Addons<br>Every Addon for $49 or any single Addon for $29 for 1 site</a></p>
            <div class="small-6 medium-6 large-6 columns sources">
            <p class="section-title"><span>Layout Addons</span></p>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-image-carousel/?utm_source=mlefree&amp;utm_medium=image-carousel&amp;utm_campaign=image-carousel"><img width="200" height="200" title="MaxGalleria Image Carousel Addon" alt="MaxGalleria Image Carousel Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-image-carousel-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-image-carousel/?utm_source=mlefree&amp;utm_medium=image-carousel&amp;utm_campaign=image-carousel">Image Carousel</a></h3><p>Turn your galleries into carousels</p>
              </div>
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-albums/?utm_source=mlefree&amp;utm_medium=albums&amp;utm_campaign=albums"><img width="200" height="200" title="MaxGalleria Albums Addon" alt="MaxGalleria Albums Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-albums-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-image-carousel/?utm_source=mlefree&amp;utm_medium=albums&amp;utm_campaign=albums">Albums</a></h3><p>Organize your galleries into albums</p>
              </div>
            </div>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-image-showcase/?utm_source=mlefree&utm_medium=imageshowcase&utm_campaign=imageshowcase"><img width="200" height="200" title="MaxGalleria Image Showcase Addon" alt="MaxGalleria Image Showcase Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-image-showcase-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-image-showcase/?utm_source=mlefree&utm_medium=imageshowcase&utm_campaign=imageshowcase">Image Showcase</a></h3><p>Showcase image with thumbnails</p>
              </div>
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-video-showcase/?utm_source=mlefree&utm_medium=videoshowcase&utm_campaign=videoshowcase"><img width="200" height="200" title="" alt="" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-video-showcase-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-video-showcase/?utm_source=mlefree&utm_medium=videoshowcase&utm_campaign=videoshowcase">Video Showcase</a></h3><p>Showcase video with thumbnails</p>
              </div>
            </div>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-masonry/?utm_source=mlefree&utm_medium=masonry&utm_campaign=masonry"><img width="200" height="200" title="Maxgalleria Masonry" alt="Maxgalleria Masonry" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-masonry-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-masonry/?utm_source=mlefree&utm_medium=masonry&utm_campaign=masonry">Masonry</a></h3><p>Display Images in a Masonry Grid</p>
              </div>
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-image-slider/?utm_source=mlefree&utm_medium=imageslider&utm_campaign=imageslider"><img width="200" height="200" title="MaxGalleria Image Slider Addon" alt="MaxGalleria Image Slider Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-image-slider-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-image-slider/?utm_source=mlefree&utm_medium=imageslider&utm_campaign=imageslider">Image Slider</a></h3><p>Turn your galleries into sliders</p>
              </div>
            </div>
           </div>
           <div class="small-6 medium-6 large-6 columns sources">
            <p class="section-title"><span>Media Sources</span></p>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-facebook/?utm_source=mlefree&utm_medium=facebook&utm_campaign=facebook"><img width="200" height="200" title="MaxGalleria Facebook Addon" alt="MaxGalleria Facebook Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-facebook-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-facebook/?utm_source=mlefree&utm_medium=facebook&utm_campaign=facebook">Facebook</a></h3><p>Add Facebook photos to galleries</p>
              </div>
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-slick-for-wordpress/?utm_source=mlefree&utm_medium=slick&utm_campaign=slick"><img width="200" height="200" title="Slick for WordPress" alt="Slick for WordPress" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-slick-for-wordpress-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-slick-for-wordpress/?utm_source=mlefree&utm_medium=slick&utm_campaign=slick">Slick for WordPress</a></h3><p>The Last Carousel You'll ever need!</p>
              </div>
            </div>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-instagram/?utm_source=mlefree&utm_medium=instagram&utm_campaign=instagram"><img width="200" height="200" title="MaxGalleria Instagram Addon" alt="MaxGalleria Instagram Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-instagram-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-instagram/?utm_source=mlefree&utm_medium=instagram&utm_campaign=instagram">Instagram</a></h3><p>Add Instagram images to galleries</p>
              </div>
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-flickr/?utm_source=mlefree&utm_medium=flickr&utm_campaign=flickr"><img width="200" height="200" title="MaxGalleria Flickr Addon" alt="MaxGalleria Flickr Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-flickr-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-flickr/?utm_source=mlefree&utm_medium=flickr&utm_campaign=flickr">Flickr</a></h3><p>Pull In Images from your Flickr stream</p>
              </div>
            </div>
            <div class="row top-margin">
              <div class="medium-6 large-6 columns addon-item">
                <a href="http://maxgalleria.com/shop/maxgalleria-vimeo/?utm_source=mlefree&utm_medium=vimeo&utm_campaign=vimeo"><img width="200" height="200" title="MaxGalleria Vimeo Addon" alt="MaxGalleria Vimeo Addon" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/maxgalleria-vimeo-cover.png"></a><h3><a href="http://maxgalleria.com/shop/maxgalleria-vimeo/?utm_source=mlefree&utm_medium=vimeo&utm_campaign=vimeo">Vimeo</a></h3><p>Use Vimeo videos in your galleries</p>
              </div>
            </div>
           </div>
           </div>
          </div> large-12
        <div class="clearfix"></div>          -->
			<?php // } ?>
      </div>
			<script>
				jQuery("#ad-close-btn").click(function() {
					jQuery.ajax({
						type: "POST",
						async: true,
						data: { action: "mlpp_hide_template_ad",  nonce: "<?php echo wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE); ?>" },
						url: "<?php echo admin_url('admin-ajax.php') ?>",
						dataType: "html",
						success: function (data) {
							jQuery("#mlpp-ad").hide();          
						},
						error: function (err)
							{ alert(err.responseText);}
					});

				});		
			</script>
    <?php
  }
  public function display_folder_contents ($current_folder_id, $image_link = true, $folders_path = '') {
				
    $folders_found = false;
    $images_found = false;
    
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = 'post_date DESC';
        break;
      
      case '1': //order by name
        $order_by = 'post_title';
        break;      
    }
		
		if($image_link)
			$image_link = "1";
		else				
			$image_link = "0";
								
		echo '<script type="text/javascript">' . PHP_EOL;
    echo '	jQuery(document).ready(function() {' . PHP_EOL;
		
		echo '    jQuery.ajax({' . PHP_EOL;
		echo '      type: "POST",' . PHP_EOL;
		echo '      async: true,' . PHP_EOL;
		echo '      data: { action: "mlp_display_folder_contents_ajax", current_folder_id: "' . $current_folder_id . '", image_link: "' . $image_link . '", nonce: mgmlp_ajax.nonce },' . PHP_EOL;
    echo '      url: mgmlp_ajax.ajaxurl,' . PHP_EOL;
		echo '      dataType: "html",' . PHP_EOL;
		echo '      success: function (data) ' . PHP_EOL;
		echo '        {' . PHP_EOL;
		//echo '				  console.log(window.hide_checkboxes);' . PHP_EOL;
		echo '				  if(window.hide_checkboxes) {' . PHP_EOL;
		echo '					  jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();' . PHP_EOL;
		echo '	          jQuery("a.tb-media-attachment").css("cursor", "pointer");' . PHP_EOL;
		echo '				  } else {' . PHP_EOL;
		echo '					  jQuery("div#mgmlp-tb-container input.mgmlp-media").show();' . PHP_EOL;
		echo '	          jQuery("a.tb-media-attachment").css("cursor", "default");' . PHP_EOL;
		echo '				  }' . PHP_EOL;
		echo '          jQuery("#mgmlp-file-container").html(data);' . PHP_EOL;		
		echo '          jQuery("li a.media-attachment").draggable({' . PHP_EOL;
		echo '          	cursor: "move",' . PHP_EOL;
		echo '          helper: function() {' . PHP_EOL;
		echo '          	var selected = jQuery(".mg-media-list input:checked").parents("li");' . PHP_EOL;
		echo '          	if (selected.length === 0) {' . PHP_EOL;
		echo '          		selected = jQuery(this);' . PHP_EOL;
		echo '          	}' . PHP_EOL;
		echo '          	var container = jQuery("<div/>").attr("id", "draggingContainer");' . PHP_EOL;
		echo '          	container.append(selected.clone());' . PHP_EOL;
		echo '          	return container;' . PHP_EOL;
		echo '          }' . PHP_EOL;
		
		echo '          });' . PHP_EOL;
		
		echo '          jQuery(".media-link").droppable( {' . PHP_EOL;
		echo '          	  accept: "li a.media-attachment",' . PHP_EOL;
		echo '          		hoverClass: "droppable-hover",' . PHP_EOL;
		echo '          		drop: handleDropEvent' . PHP_EOL;
		echo '          });' . PHP_EOL;
		
    echo '        },' . PHP_EOL;
		echo '          error: function (err)' . PHP_EOL;
		echo '	      { alert(err.responseText)}' . PHP_EOL;
		echo '	   });' . PHP_EOL;
		
		if($folders_path !== '') {
		  echo '   jQuery("#mgmlp-breadcrumbs").html("'. __('Location:','maxgalleria-media-library') . " " . addslashes($folders_path) .'");' . PHP_EOL;
		}
				
    echo '	});' . PHP_EOL;
    echo '</script>' . PHP_EOL;
		

				
	}
  	
	public function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}
    return false;
  }


	public function mlp_display_folder_contents_ajax() {
		
    global $wpdb;
		    
    //$folders_found = false;
    
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = 'post_date DESC';
        break;
      
      case '1': //order by name
        $order_by = 'attached_file';
        break;      
    }
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0))
      $current_folder_id = trim(stripslashes(strip_tags($_POST['current_folder_id'])));
		else
			$current_folder_id = 0;
		
    if ((isset($_POST['image_link'])) && (strlen(trim($_POST['image_link'])) > 0))
      $image_link = trim(stripslashes(strip_tags($_POST['image_link'])));
		else
			$image_link = "0";
		
    if ((isset($_POST['display_type'])) && (strlen(trim($_POST['display_type'])) > 0))
      $display_type = trim(stripslashes(strip_tags($_POST['display_type'])));
		else
			$display_type = 0;
		
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
				
		$this->display_folder_nav($current_folder_id, $folder_table);
		
		$this->display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by );
		
		die();
		
	}
	
	public function mlp_display_folder_contents_images_ajax() {
	
    global $wpdb;
		        
    $sort_order = get_option(MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER);
    
    switch($sort_order) {
      default:
      case '0': //order by date
        $order_by = 'post_date DESC';
        break;
      
      case '1': //order by name
        $order_by = 'post_title';
        break;      
    }
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['current_folder_id'])) && (strlen(trim($_POST['current_folder_id'])) > 0))
      $current_folder_id = trim(stripslashes(strip_tags($_POST['current_folder_id'])));
		else
			$current_folder_id = 0;
		
    if ((isset($_POST['image_link'])) && (strlen(trim($_POST['image_link'])) > 0))
      $image_link = trim(stripslashes(strip_tags($_POST['image_link'])));
		else
			$image_link = "0";
		
    if ((isset($_POST['display_type'])) && (strlen(trim($_POST['display_type'])) > 0))
      $display_type = trim(stripslashes(strip_tags($_POST['display_type'])));
		else
			$display_type = 0;
		
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
			
		$this->display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by );
		
		die();
		
	}
	
	public function display_folder_nav($current_folder_id, $folder_table) {
	
    global $wpdb;
		
// we used to use this to display the folders		
//    $sql = "select ID, guid, post_title, $folder_table.folder_id
//from $wpdb->prefix" . "posts
//LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
//where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE ."' 
//and folder_id = $current_folder_id 
//order by $order_by";		
//            $rows = $wpdb->get_results($sql);
		
						$folder_parents = $this->get_parents($current_folder_id);
            $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
						
			$sql = "select ID, post_title, $folder_table.folder_id
from {$wpdb->prefix}posts
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE ."' 
order by folder_id";
						

					$add_child = array();
					$folders = array();
					$first = true;
					$rows = $wpdb->get_results($sql);            
					if($rows) {
						foreach($rows as $row) {
							
								$max_id = -1;
							
								if($row->ID > $max_id)
									$max_id = $row->ID;
								$folder = array();
								$folder['id'] = $row->ID;
								if($row->folder_id === '0')
									$folder['parent'] = '#';
								else
									$folder['parent'] = $row->folder_id;

								$folder['text'] = $row->post_title;
								$state = array();
							if($row->folder_id === '0') {
								$state['opened'] = true;
								$state['disabled'] = true;
							} else if($this->in_array_r($row->ID, $folder_parents))	{
								$state['opened'] = true;
							}	else {
								$state['opened'] = false;
							}	
							if($row->ID === $current_folder_id)
							  $state['selected'] = true;
							else
							  $state['selected'] = false;
							$state['disabled'] = false;
							$folder['state'] = $state;
							
							$a_attr  = array();
							$a_attr['href'] = site_url() . "/wp-admin/admin.php?page=media-library&media-folder=" . $row->ID;
							$a_attr['target'] = '_self';
							
							$folder['a_attr'] = $a_attr;
							
							$add_child[] = $row->ID;
							$child_index = array_search($row->folder_id, $add_child);
							if($child_index !== false)
								unset($add_child[$child_index]);
							
							$folders[] = $folder;
					  }
						
						$max_id += 99999;
						foreach($add_child as $child) {
					    $max_id++;
							$folder = array();
							$folder['id'] = $max_id;
							$folder['parent'] = $child;
							$folder['text'] = "empty node";
							$state = array();
						  $state['opened'] = false;
							$state['disabled'] = true;
							$state['selected'] = false;
							$folder['state'] = $state;
							$folders[] = $folder;							
						}
					}
		            
		?>
			
<script>
	var mlp_busy = false;
  var folders = <?php echo json_encode($folders); ?>;
	jQuery(document).ready(function(){		
		jQuery("#scanning-message").hide();		
		jQuery("#ajaxloadernav").show();		
    jQuery('#folder-tree').jstree({ 'core' : {
				'data' : folders,
				'check_callback' : true
			},
			'force_text' : true,
			'themes' : {
				'responsive' : false,
				'variant' : 'small',
				'stripes' : true
			},		
			'types' : {
				'default' : { 'icon' : 'folder' },
        'file' : { 'icon' :'folder'},
				'valid_children' : {'icon' :'folder'}	 
 				//'file' : { 'valid_children' : [], 'icon' : 'file' }
			},
			'sort' : function(a, b) {
				return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
			},			
				"contextmenu":{
				  "select_node":false,
					"items": function($node) {
						 var tree = jQuery("#tree").jstree(true);
						 return {
							 "Remove": {
								 "separator_before": false,
								 "separator_after": false,
								 "label": "Delete this folder?",
								 "action": function (obj) { 
										var delete_ids = new Array();
										delete_ids[delete_ids.length] = jQuery($node).attr('id');
										
										var folder_id = jQuery('#folder_id').val();      
										var to_delete = jQuery($node).attr('id');
//										if(folder_id === to_delete ) {
//											alert("<?php _e('You cannot delete the currently open folder.','maxgalleria-media-library'); ?>")
//											return true;
//										}	

										if(confirm("Are you sure you want to delete the selected folder?")) {
											var serial_delete_ids = JSON.stringify(delete_ids.join());
											jQuery("#ajaxloader").show();
											jQuery.ajax({
												type: "POST",
												async: true,
												data: { action: "delete_maxgalleria_media", serial_delete_ids: serial_delete_ids, nonce: mgmlp_ajax.nonce },
												url : mgmlp_ajax.ajaxurl,
												dataType: "html",
												success: function (data) {
													jQuery("#ajaxloader").hide();            
													jQuery("#folder-message").html(data);
												},
												error: function (err)
													{ alert(err.responseText);}
											});
									} 
								}
							},
							 "Hide": {
								 "separator_before": false,
								 "separator_after": false,
								 "label": "Hide this folder?",
								 "action": function (obj) { 
										//var hide_id = jQuery($node).attr('id');										
										var folder_id = jQuery('#folder_id').val();      
										var to_hide = jQuery($node).attr('id');
										//if(folder_id === to_hide ) {
										//	alert("<?php _e('You cannot hide the currently open folder.','maxgalleria-media-library'); ?>")
										//	return true;
										//}	

										if(confirm("Are you sure you want to hide the selected folder and all its sub folders and files?")) {
											//var serial_delete_ids = JSON.stringify(delete_ids.join());
											jQuery("#ajaxloader").show();
											jQuery.ajax({
												type: "POST",
												async: true,
												data: { action: "hide_maxgalleria_media", folder_id: to_hide, nonce: mgmlp_ajax.nonce },
												url : mgmlp_ajax.ajaxurl,
												dataType: "html",
												success: function (data) {
													jQuery("#ajaxloader").hide();            
													jQuery("#folder-message").html(data);
												},
												error: function (err)
													{ alert(err.responseText);}
											});
									} 
								}
							}
						}; // end context menu
					}					
			},						
			'plugins' : [ 'sort','types', "contextmenu" ],
		});
		
		// for changing folders
		if(!jQuery("ul#folder-tree.jstree").hasClass("bound")) {
      jQuery("#folder-tree").addClass("bound").on("select_node.jstree", show_mlp_node);		
		}	
				
		jQuery('#folder-tree').droppable( {
				accept: 'li a.media-attachment',
				hoverClass: 'jstree-anchor',
				//hoverClass: 'droppable-hover',
				drop: handleTreeDropEvent
		});
	
		jQuery('#folder-tree').on('copy_node.jstree', function (e, data) {
			 //console.log(data.node.data.more); 
		});
		
		jQuery("#ajaxloadernav").hide();		
	});  
	
	
function show_mlp_node (e, data) {

  var thickbox_shown = (jQuery('#TB_window').is(':visible')) ? true : false;
	if(!window.mlp_busy) {
		window.mlp_busy = true;
		if(thickbox_shown) {

			// opens the closed node
			jQuery("#folder-tree").jstree("toggle_node", data.node.id);
			
			var folder = data.node.id;

			jQuery("#ajaxloader").show();

			jQuery.ajax({
				type: "POST",
				async: true,
				data: { action: "mlp_load_folder", folder: folder, nonce: mgmlp_ajax.nonce },
				url : mgmlp_ajax.ajaxurl,
				dataType: "html",
				success: function (data) {
					jQuery("#ajaxloader").hide();          
					jQuery("#mgmlp-file-container").html(data);						
					if(window.hide_checkboxes) {
						jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
						jQuery("a.tb-media-attachment").css("cursor", "pointer");
					} else {
						jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
						jQuery("a.tb-media-attachment").css("cursor", "default");
					}	
				},
				error: function (err)
					{ alert(err.responseText);}
			});

		} else {	
			window.location.href = data.node.a_attr.href;
		}
		window.mlp_busy = false;
	}	
}
	
function handleTreeDropEvent(event, ui ) {
		
	var target=event.target || event.srcElement;
	//console.log(target);
	
	var move_ids = new Array();
	var items = ui.helper.children();
	items.each(function() {  
		move_ids[move_ids.length] = jQuery(this).find( "a.media-attachment" ).attr("id");
	});
	
	if(move_ids.length < 2) {
	  move_ids = new Array();
		move_ids[move_ids.length] =  ui.draggable.attr("id");
	}	
		
	var serial_copy_ids = JSON.stringify(move_ids.join());
	var folder_id = jQuery(target).attr("aria-activedescendant");	
	var destination = '';
	var current_folder = jQuery("#current-folder-id").val();      
	
	var action_name = 'move_media';
	var operation_type = jQuery('#move-copy-switch:checkbox:checked').length > 0;
	if(operation_type)
		action_name = 'move_media';
	else
		action_name = 'copy_media';

	jQuery("#ajaxloader").show();

	jQuery.ajax({
		type: "POST",
		async: true,
		data: { action: action_name, current_folder: current_folder, folder_id: folder_id, destination: destination, serial_copy_ids: serial_copy_ids, nonce: mgmlp_ajax.nonce },
		url : mgmlp_ajax.ajaxurl,
		dataType: "html",
		success: function (data) {
			jQuery("#ajaxloader").hide();
			jQuery(".mgmlp-media").prop('checked', false);
			jQuery(".mgmlp-folder").prop('checked', false);
			jQuery("#folder-message").html(data);
		},
		error: function (err)
		{ 
			jQuery("#ajaxloader").hide();
			alert(err.responseText);
		}
	
	});
	
} 

function delete_current_folder(node) {
	var folder_id = jQuery(target).attr("aria-activedescendant");	
	//console.log(folder_id);
					
	
}
</script>
  <?php
							
	}
	
	public function display_files($image_link, $current_folder_id, $folder_table, $display_type, $order_by) {
		
    global $wpdb;
    $images_found = false;
		
		if($image_link === "1")
			$image_link = true;
		else
			$image_link = false;
						
	            $sql = "select COUNT(*)  
from $wpdb->prefix" . "posts 
LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
where post_type = 'attachment' 
and folder_id = '$current_folder_id'";
							
    $row_count = $wpdb->get_var($sql);
		if($row_count > 40 && $display_type === 0) {
			 ?>
				<p class="center-text"><?php echo $row_count; ?> files were found. Choose to display the images or just the file names?</p>
				<div class="center-text">
		      <a id="display_mlpp_images" folder_id="<?php echo $current_folder_id; ?>" image_link="<?php echo $image_link; ?>" class="gray-blue-link">Display images</a>
			    <a id="display_mlpp_titles" folder_id="<?php echo $current_folder_id; ?>" image_link="<?php echo $image_link; ?>" class="gray-blue-link">Display image file names only</a>				
				</div>	
			<?php
      die();		
		}
		
				
            echo '<ul class="mg-media-list">' . PHP_EOL;              
            
//            $sql = "select ID, guid, post_title, $folder_table.folder_id 
//from $wpdb->prefix" . "posts 
//LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
//where post_type = 'attachment' 
//and folder_id = '$current_folder_id'
//order by $order_by";
						
            $sql = "select ID, post_title, $folder_table.folder_id, pm.meta_value as attached_file 
from {$wpdb->prefix}posts 
LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where post_type = 'attachment' 
and folder_id = '$current_folder_id'
AND pm.meta_key = '_wp_attached_file' 
order by $order_by";

            $rows = $wpdb->get_results($sql);            
            if($rows) {
              $images_found = true;
              foreach($rows as $row) {
                $thumbnail = wp_get_attachment_thumb_url($row->ID);                
                if($thumbnail === false || $display_type == 2) {									
									$ext = pathinfo($row->attached_file, PATHINFO_EXTENSION);										
                  //$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/default.png";
									$thumbnail = $this->get_file_thumbnail($ext);
                }  
                                
                $checkbox = sprintf("<input type='checkbox' class='mgmlp-media' id='%s' value='%s' />", $row->ID, $row->ID );
								if($image_link)
                  $class = "media-attachment"; 
								else
                  $class = "tb-media-attachment"; 
                
								// for WP 4.6 use /wp-admin/post.php?post=
								if( version_compare($this->wp_version, NEW_MEDIA_LIBRARY_VERSION, ">") )
                  $media_edit_link = "/wp-admin/post.php?post=" . $row->ID . "&action=edit";
								else									error_log('old link');
                  $media_edit_link = "/wp-admin/upload.php?item=" . $row->ID;
									
					      //$image_location = $this->check_for_attachment_id($row->guid, $row->ID);
							  //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
								$baseurl = $this->upload_dir['baseurl'];
								$baseurl = rtrim($baseurl, '/') . '/';
								$image_location = $baseurl . ltrim($row->attached_file, '/');
								                
                $filename = pathinfo($image_location, PATHINFO_BASENAME);
                                
                echo "<li>" . PHP_EOL;
								if($image_link)
									echo "   <a id='$row->ID' class='$class' href='" . site_url() . $media_edit_link . "' title='$filename'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
								else
									echo "   <a id='$row->ID' class='$class' title='$filename'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
                echo "   <div class='attachment-name'><span class='image_select'>$checkbox</span>$filename</div>" . PHP_EOL;
                echo "</li>" . PHP_EOL;              
              }      
            }
            echo '</ul>' . PHP_EOL;

						
						echo '      <script>' . PHP_EOL;
						echo '				jQuery(document).ready(function(){' . PHP_EOL;
//						echo '				  console.log(window.hide_checkboxes);' . PHP_EOL;
						echo '				  if(window.hide_checkboxes) {' . PHP_EOL;
						echo '					  jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();' . PHP_EOL;
						echo '	          jQuery("a.tb-media-attachment").css("cursor", "pointer");' . PHP_EOL;
						echo '				  } else {' . PHP_EOL;
						echo '					  jQuery("div#mgmlp-tb-container input.mgmlp-media").show();' . PHP_EOL;
						echo '	          jQuery("a.tb-media-attachment").css("cursor", "default");' . PHP_EOL;
						echo '				  }' . PHP_EOL;
						echo '          jQuery("li a.media-attachment").draggable({' . PHP_EOL;
						echo '          	cursor: "move",' . PHP_EOL;
						echo '            helper: function() {' . PHP_EOL;
						echo '          	  var selected = jQuery(".mg-media-list input:checked").parents("li");' . PHP_EOL;
						echo '          	  if (selected.length === 0) {' . PHP_EOL;
						echo '          		  selected = jQuery(this);' . PHP_EOL;
						echo '          	  }' . PHP_EOL;
						echo '          	  var container = jQuery("<div/>").attr("id", "draggingContainer");' . PHP_EOL;
						echo '          	  container.append(selected.clone());' . PHP_EOL;
						echo '          	  return container;' . PHP_EOL;
						echo '            }' . PHP_EOL;
						echo '          });' . PHP_EOL;
						echo '        });' . PHP_EOL;
						echo '      </script>' . PHP_EOL;
						
    
            if(!$images_found)
              echo "<p style='text-align:center'>" . __('No files were found.','maxgalleria-media-library')  . "</p>";
						
		
		
	}
  
  private function get_folder_path($folder_id) {
      
    global $wpdb;    
   $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $folder_id
AND meta_key = '_wp_attached_file'";
				
    $row = $wpdb->get_row($sql);
		
    //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
    $absolute_path = $this->get_absolute_path($image_location);
		
    return $absolute_path;
      
  }
  
  private function get_subfolder_path($folder_id) {
      
    global $wpdb;    
		
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $folder_id    
AND meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
		
	  //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
			
    $postion = strpos($image_location, $this->uploads_folder_name);
    $path = substr($image_location, $postion+$this->uploads_folder_name_length );
    return $path;
      
  }
  
  private function get_folder_name($folder_id) {
    global $wpdb;    
    $sql = "select post_title from $wpdb->prefix" . "posts where ID = $folder_id";    
    $row = $wpdb->get_row($sql);
    return $row->post_title;
  }
    
  private function get_parents($current_folder_id) {

    global $wpdb;    
    $folder_id = $current_folder_id;    
    $parents = array();
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    
    while($folder_id !== '0') {    
      
      $sql = "select post_title, ID, $folder_table.folder_id 
from $wpdb->prefix" . "posts 
LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
where ID = $folder_id";    
      
      $row = $wpdb->get_row($sql);
      
      $folder_id = $row->folder_id;
      
      $new_folder = array();
      $new_folder['name'] = $row->post_title;
      $new_folder['id'] = $row->ID;
      
      $parents[] = $new_folder;      
                    
    }
    
    $parents = array_reverse($parents);
        
    return $parents;
    
  }  

  private function get_parent($folder_id) {
    
    global $wpdb;    
    $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
    
    $sql = "select folder_id from $folder_table where post_id = $folder_id";    
    
    $row = $wpdb->get_row($sql);
		if($row)        
      return $row->folder_id;
    else
			return $this->uploads_folder_ID;
  }
  
  public function create_new_folder() {
    
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 

    if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder_id = trim(stripslashes(strip_tags($_POST['parent_folder'])));
    
    
    if ((isset($_POST['new_folder_name'])) && (strlen(trim($_POST['new_folder_name'])) > 0))
      $new_folder_name = trim(stripslashes(strip_tags($_POST['new_folder_name'])));
    
      $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $parent_folder_id    
AND meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
		
		error_log("folder location " . $row->attached_file);
		
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($row->attached_file, '/');
		
		error_log("function create_new_folder");
		        
    $absolute_path = $this->get_absolute_path($image_location);
		$absolute_path = rtrim($absolute_path, '/') . '/';
		$this->write_log("absolute_path $absolute_path");
        
    $new_folder_path = $absolute_path . $new_folder_name ;
		$this->write_log("new_folder_path $new_folder_path");
    
    $new_folder_url = $this->get_file_url_for_copy($new_folder_path);
		$this->write_log("new_folder_url $new_folder_url");
		
		$this->write_log("Trying to create directory at $new_folder_path");
    
    if(!file_exists($new_folder_path)) {
      if(mkdir($new_folder_path)) {
        if($this->add_media_folder($new_folder_name, $parent_folder_id, $new_folder_url)){
          $location = 'window.location.href = "' . home_url() . '/wp-admin/admin.php?page=media-library&media-folder=' . $parent_folder_id .'";';
          echo __('The folder was created.','maxgalleria-media-library');
          echo "<script>$location</script>";
        }  
        else
          echo __('There was a problem creating the folder.','maxgalleria-media-library');
      }
    }
    else
      echo __('The folder already exists.','maxgalleria-media-library');
    die();
  }

  public function get_absolute_path($url) {
		
		global $blog_id;
		
		$baseurl = $this->upload_dir['baseurl'];
		
		error_log("starting url: $url");
		
		if(is_multisite()) {
			$url_slug = "site" . $blog_id . "/";
			$baseurl = str_replace($url_slug, "", $baseurl);
			if(strpos($url, 'wp-content') === false)
			  $url = str_replace($url_slug, "wp-content/uploads/sites/" . $blog_id . "/" , $url);
			else
			  $url = str_replace($url_slug, "", $url);
		}
		
    //$file_path = str_replace( $this->upload_dir['baseurl'], $this->upload_dir['basedir'], $url ); 
    $file_path = str_replace( $baseurl, $this->upload_dir['basedir'], $url ); 
		
		$this->write_log("url $url");
		$this->write_log("baseurl "  . $this->upload_dir['baseurl']);
		$this->write_log("basedir " . $this->upload_dir['basedir']);
		$this->write_log("file_path $file_path");
				
		//first attempt failed; try again
		if(strpos($file_path, "http") !== false) {	
			$this->write_log("absolute path, second attempt $file_path");
			$baseurl = $this->upload_dir['baseurl'];
			$base_length = strlen($baseurl);
			//compare the two urls
			$url_stub = substr($url, 0, $base_length);
			if(strcmp($url_stub, $baseurl) === 0) {			
				$non_base_file = substr($url, $base_length);
				$file_path = $this->upload_dir['basedir'] . DIRECTORY_SEPARATOR . $non_base_file;			
			} else {
				$this->write_log("url_stub $url_stub");
				$this->write_log("baseurl $baseurl");
				$new_msg = "The URL to the folder or image is not correct: $url";
				$this->write_log($new_msg);
				echo $new_msg;
			}
		}
		    
    // are we on windows?
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $file_path = str_replace('/', '\\', $file_path);
    }
		
		$this->write_log("file_path 2 $file_path");
				
    return $file_path;
  }
  
  public function is_windows() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
      return true;
    else
      return false;      
  }
  
  public function get_file_url($path) {
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      
      $base_url = $this->upload_dir['baseurl'];
      // replace any slashes in the dir path when running windows
      $base_upload_dir1 = $this->upload_dir['basedir'];
      $base_upload_dir2 = str_replace('\\', '/', $base_upload_dir1);      
      $file_url = str_replace( $base_upload_dir2, $base_url, $path ); 
    }
    else {
      $file_url = str_replace( $this->upload_dir['basedir'], $this->upload_dir['baseurl'], $path );          
    }
    return $file_url;    
  }
  
  public function get_file_url_for_copy($path) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      
      $base_url = $this->upload_dir['baseurl'];
      
      // replace any slashes in the dir path when running windows
      $base_upload_dir1 = $this->upload_dir['basedir'];
      $base_upload_dir2 = str_replace('/','\\', $base_upload_dir1);      
      $file_url = str_replace( $base_upload_dir2, $base_url, $path ); 
      $file_url = str_replace('\\',   '/', $file_url);      
      
    }
    else {
      $file_url = str_replace( $this->upload_dir['basedir'], $this->upload_dir['baseurl'], $path );          
    }
    return $file_url;    
  
  }
  
  public function delete_maxgalleria_media() {
    global $wpdb;
    $delete_ids = array();
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_delete_ids'])) && (strlen(trim($_POST['serial_delete_ids'])) > 0)) {
      $delete_ids = trim(stripslashes(strip_tags($_POST['serial_delete_ids'])));
      $delete_ids = str_replace('"', '', $delete_ids);
		  $this->write_log("delete_ids $delete_ids");
      $delete_ids = explode(",",$delete_ids);
    }  
    else
      $delete_ids = '';
		            
    foreach( $delete_ids as $delete_id) {
			
			if(is_numeric($delete_id)) {

        $sql = "select post_title, post_type, pm.meta_value as attached_file 
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where ID = $delete_id 
AND pm.meta_key = '_wp_attached_file'";

				$row = $wpdb->get_row($sql);

				$baseurl = $this->upload_dir['baseurl'];
				$baseurl = rtrim($baseurl, '/') . '/';
				$image_location = $baseurl . ltrim($row->attached_file, '/');
				
				$folder_path = $this->get_absolute_path($image_location);
				$table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
				$del_post = array('post_id' => $delete_id);                        

				if($row->post_type === MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE) { //folder

					$sql = "SELECT COUNT(*) FROM $wpdb->prefix" . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE . " where folder_id = $delete_id";
					$row_count = $wpdb->get_var($sql);

					if($row_count > 1) {
						echo __('The folder, ','maxgalleria-media-library'). $row->post_title . __(', is not empty. Please delete or move files form the folder','maxgalleria-media-library') . PHP_EOL;      
						die();
					}  
					
			    $parent_folder =  $this->get_parent($delete_id);					

					if(file_exists($folder_path)) {
						if(is_dir($folder_path)) {  //folder
							@chmod($folder_path, 0777);
							$this->write_log("Deleting $folder_path");
							//unlink($folder_path. "/.DS_Store");
							if(rmdir($folder_path))
								$this->write_log(__('The folder was deleted.','maxgalleria-media-library'));
							else
								$this->write_log(__('The folder could not be deleted.','maxgalleria-media-library'));
						}          
					}                          
					wp_delete_post($delete_id, true);
					$wpdb->delete( $table, $del_post );
					
					$location = 'window.location.href = "' . home_url() . '/wp-admin/admin.php?page=media-library&media-folder=' . $parent_folder .'";';
					echo __('The folder was deleted.','maxgalleria-media-library');
					echo "<script>$location</script>";
					
					die();
				}
				else {
					if( wp_delete_attachment( $delete_id, true ) !== false ) {
						$wpdb->delete( $table, $del_post );
					}  
				} 
			}
    }
    echo "<script>location.reload(true);</script>";

    die();
  }  
    
  public function copy_media() {
    $this->modify_media(true);
  }
    
  public function move_media() {
    $this->modify_media(false);    
  }
  
  public function modify_media($copy=true) {
    global $wpdb;
        
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_copy_ids'])) && (strlen(trim($_POST['serial_copy_ids'])) > 0))
      $serial_copy_ids = trim(stripslashes(strip_tags($_POST['serial_copy_ids'])));
    else
      $serial_copy_ids = "";
		
    $serial_copy_ids = str_replace('"', '', $serial_copy_ids);    
    
    $serial_copy_ids = explode(',', $serial_copy_ids);
        
    if ((isset($_POST['destination'])) && (strlen(trim($_POST['destination'])) > 0))
      $destination = trim(strip_tags($_POST['destination']));
    else
      $destination = '';
    
    if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(stripslashes(strip_tags($_POST['folder_id'])));
    else
      $folder_id = 0;
    
    if ((isset($_POST['current_folder'])) && (strlen(trim($_POST['current_folder'])) > 0))
      $current_folder = trim(stripslashes(strip_tags($_POST['current_folder'])));
    else
      $current_folder = 0;
		
		if($destination === '' && $folder_id !== '' ) {
      $destination = $this->get_folder_path($folder_id);			
		}
				            
    if($destination !== "" || $folder_id !== 0 ) {
      
      foreach( $serial_copy_ids as $copy_id) {
				        
        $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta 
where post_id = $copy_id    
AND meta_key = '_wp_attached_file'";

        $row = $wpdb->get_row($sql);
				
        //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
				$baseurl = $this->upload_dir['baseurl'];
				$baseurl = rtrim($baseurl, '/') . '/';
				$image_location = $baseurl . ltrim($row->attached_file, '/');
				
        $image_path = $this->get_absolute_path($image_location);
								
        $destination_path = $this->get_absolute_path($destination);
				
				$folder_basename = basename($destination_path);

        $destination_name = $destination_path . DIRECTORY_SEPARATOR . pathinfo($image_path, PATHINFO_BASENAME);
                
        $copy_status = true;
                                
        if(file_exists($image_path)) {
          if(!is_dir($image_path)) {
            if(file_exists($destination_path)) {
              if(is_dir($destination_path)) {
                
                if($copy) {
                  if(copy($image_path, $destination_name )) {                                          
                         
                    $destination_url = $this->get_file_url($destination_name);
										$title_text = get_the_title($copy_id);
										$alt_text = get_post_meta($copy_id, '_wp_attachment_image_alt');										
                    $attach_id = $this->add_new_attachment($destination_name, $folder_id, $title_text, $alt_text);
                    if($attach_id === false){
                      $copy_status = false; 
                    }  
                  }
                  else {
                    echo __('Unable to copy the file; please check the folder and file permissions.','maxgalleria-media-library') . PHP_EOL;
                    $copy_status = false; 
                    break;
                  }
                  //move
                } else {
                        
                  if(rename($image_path, $destination_name )) {
                    
                    // check current theme customizer settings for the file
                    // and update if found
                    $update_theme_mods = false;
                    $move_image_url = $this->get_file_url_for_copy($image_path);
                    $move_destination_url = $this->get_file_url_for_copy($destination_name);
                    $key = array_search ($move_image_url, $this->theme_mods);
                    if($key !== false ) {
                      set_theme_mod( $key, $move_destination_url);
                      $update_theme_mods = true;                      
                    }
                    if($update_theme_mods) {
                      $theme_mods = get_theme_mods();
                      $this->theme_mods = json_decode(json_encode($theme_mods), true);
                      $update_theme_mods = false;
                    }
                    
                    $image_path = str_replace('.', '*.', $image_path );

                    foreach (glob($image_path) as $source_path) {
                      $thumbnail_file = pathinfo($source_path, PATHINFO_BASENAME);
                      $thumbnail_destination = $destination_path . DIRECTORY_SEPARATOR . $thumbnail_file;
                      rename($source_path, $thumbnail_destination);
                                            
                      // check current theme customizer settings for the fileg
                      // and update if found
                      $update_theme_mods = false;
                      $move_source_url = $this->get_file_url_for_copy($source_path);
                      $move_thumbnail_url = $this->get_file_url_for_copy($thumbnail_destination);
                      $key = array_search ($move_source_url, $this->theme_mods);
                      if($key !== false ) {
                        set_theme_mod( $key, $move_thumbnail_url);
                        $update_theme_mods = true;                      
                      }
                      if($update_theme_mods) {
                        $theme_mods = get_theme_mods();
                        $this->theme_mods = json_decode(json_encode($theme_mods), true);
                        $update_theme_mods = false;
                      }
                      
                    }                    
                      
                    $destination_url = $this->get_file_url($destination_name);
                    
                    // update posts table
                    $table = $wpdb->prefix . "posts";
                    $data = array('guid' => $destination_url );
                    $where = array('ID' => $copy_id);
                    $wpdb->update( $table, $data, $where);
                    
                    // update folder table
                    $table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
                    $data = array('folder_id' => $folder_id );
                    $where = array('post_id' => $copy_id);
                    $wpdb->update( $table, $data, $where);

                    // get the uploads dir name
                    $basedir = $this->upload_dir['baseurl'];
                    $uploads_dir_name_pos = strrpos($basedir, '/');
                    $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);
                        
                    //find the name and cut off the part with the uploads path
                    $string_position = strpos($destination_name, $uploads_dir_name);
                    $uploads_dir_length = strlen($uploads_dir_name) + 1;
                    $uploads_location = substr($destination_name, $string_position+$uploads_dir_length);
                    if($this->is_windows()) 
                      $uploads_location = str_replace('\\','/', $uploads_location);      
                    
                    // update _wp_attached_file
										
										$uploads_location = ltrim($uploads_location, '/');
                    update_post_meta( $copy_id, '_wp_attached_file', $uploads_location );
										
										// update _wp_attachment_metadata
                    $attach_data = wp_generate_attachment_metadata( $copy_id, $destination_name );										
                    wp_update_attachment_metadata( $copy_id,  $attach_data );										
                                                                                                 
                  }                                   
                  else {
                    echo __('Unable to move the file(s); please check the folder and file permissions.','maxgalleria-media-library') . PHP_EOL;
                    $copy_status = false; 
                    break;
                  }
                } 
              }
              else {
                echo __('The destination is not a folder: ','maxgalleria-media-library') . $destination_path . PHP_EOL;
                $copy_status = false; 
                break;
              }
            }
            else {
              echo __('Cannot find destination folder: ','maxgalleria-media-library') . $destination_path . PHP_EOL;
              $copy_status = false; 
              break;
            }
          }   
          else {
            echo __('Coping or moving a folder is not allowed.','maxgalleria-media-library') . PHP_EOL;
            $copy_status = false; 
            break;
          }
        }
        else {
          echo __('Cannot find the file: ','maxgalleria-media-library') . $image_path . ". " . PHP_EOL;
					error_log("Cannot find the file: $image_path");
          $copy_status = false; 
        }        
      }
      if($copy) {
        if($copy_status)
          echo __('The file(s) were copied to ','maxgalleria-media-library') . $folder_basename . PHP_EOL;      
        else
          echo __('The file(s) were not copied.','maxgalleria-media-library') . PHP_EOL;      
      }
      else {
        if($copy_status)
          echo __('The file(s) were moved to ','maxgalleria-media-library') . $folder_basename . PHP_EOL;      
        else
          echo __('The file(s) were not moved.','maxgalleria-media-library') . PHP_EOL;              
      }
      
      if(!$copy) {
        $location = "window.location.href = '" . site_url() . "/wp-admin/admin.php?page=media-library&media-folder=" . $current_folder . "'";
        echo "<script>$location</script>";
      }
      
    }        
    die();
        
  }
  
  public function get_image_sizes() {
    global $_wp_additional_image_sizes;
    $sizes = array();
    $rSizes = array();
    foreach (get_intermediate_image_sizes() as $s) {
      $sizes[$s] = array(0, 0);
      if (in_array($s, array('thumbnail', 'medium', 'large'))) {
        $sizes[$s][0] = get_option($s . '_size_w');
        $sizes[$s][1] = get_option($s . '_size_h');
      } else {
        if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$s]))
          $sizes[$s] = array($_wp_additional_image_sizes[$s]['width'], $_wp_additional_image_sizes[$s]['height'],);
      }
    }
		
		foreach ($sizes as $size => $atts) {
			$rSizes[] = implode('x', $atts);
		}

    return $rSizes;
  }  
    
  public function add_to_max_gallery () {
    
    global $wpdb, $maxgalleria;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_gallery_image_ids'])) && (strlen(trim($_POST['serial_gallery_image_ids'])) > 0))
      $serial_gallery_image_ids = trim(stripslashes(strip_tags($_POST['serial_gallery_image_ids'])));
    else
      $serial_gallery_image_ids = "";
    
    $serial_gallery_image_ids = str_replace('"', '', $serial_gallery_image_ids);    
    
    $serial_gallery_image_ids = explode(',', $serial_gallery_image_ids);
        
    if ((isset($_POST['gallery_id'])) && (strlen(trim($_POST['gallery_id'])) > 0))
      $gallery_id = trim(stripslashes(strip_tags($_POST['gallery_id'])));
    else
      $gallery_id = 0;
    
    foreach( $serial_gallery_image_ids as $attachment_id) {
      
      // check for image already in the gallery
      $sql = "SELECT ID FROM $wpdb->prefix" . "posts where post_parent = $gallery_id and post_type = 'attachment' and ID = $attachment_id";
      
      $row = $wpdb->get_row($sql);
      
      if($row === null) {

        $menu_order = $maxgalleria->common->get_next_menu_order($gallery_id);      

        $attachment = get_post( $attachment_id, ARRAY_A );

        // assign a new value for menu_order
        //$menu_order = $maxgalleria->common->get_next_menu_order($gallery_id);
        $attachment[ 'menu_order' ] = $menu_order;

        //If the attachment doesn't have a post parent, simply change it to the attachment we're working with and be done with it      
        // assign a new value for menu_order
        if( empty( $attachment[ 'post_parent' ] ) ) {
          wp_update_post(
            array(
              'ID' => $attachment[ 'ID' ],
              'post_parent' => $gallery_id,
              'menu_order' => $menu_order
            )
          );
          $result = $attachment[ 'ID' ];
        } else {
          //Else, unset the attachment ID, change the post parent and insert a new attachment
          unset( $attachment[ 'ID' ] );
          $attachment[ 'post_parent' ] = $gallery_id;
          $new_attachment_id = wp_insert_post( $attachment );
          //$new_attachment_id = $this->mpmlp_insert_post( $attachment );
          

          //Now, duplicate all the custom fields. (There's probably a better way to do this)
          $custom_fields = get_post_custom( $attachment_id );

          foreach( $custom_fields as $key => $value ) {
            //The attachment metadata wasn't duplicating correctly so we do that below instead
            if( $key != '_wp_attachment_metadata' )
              update_post_meta( $new_attachment_id, $key, $value[0] );
          }

          //Carry over the attachment metadata
          $data = wp_get_attachment_metadata( $attachment_id );
          wp_update_attachment_metadata( $new_attachment_id, $data );

          $result = $new_attachment_id;

        } 
      }
            
    }// foreach
        
    echo __('The images were added.','maxgalleria-media-library') . PHP_EOL;              
        
    die();
    
  }
  
  public function search_media () {
    
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['search_value'])) && (strlen(trim($_POST['search_value'])) > 0))
      $search_value = trim(stripslashes(strip_tags($_POST['search_value'])));
    else
      $search_value = "";
    
	$sql = $wpdb->prepare("select ID, post_title, post_name, pm.meta_value as attached_file from {$wpdb->prefix}posts 
			LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( {$wpdb->prefix}posts.ID = {$wpdb->prefix}mgmlp_folders.post_id) 
      LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID)
      where post_type= 'attachment' and pm.meta_key = '_wp_attached_file' and post_title like '%%%s%%'", $search_value);
    
    $rows = $wpdb->get_results($sql);
    
    if($rows) {
        foreach($rows as $row) {
          $thumbnail = wp_get_attachment_thumb_url($row->ID);
          if($thumbnail !== false)
            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
          else {
						
            //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
						$baseurl = $this->upload_dir['baseurl'];
						$baseurl = rtrim($baseurl, '/') . '/';
						$image_location = $baseurl . ltrim($row->attached_file, '/');
												
            $ext_pos = strrpos($image_location, '.');
            $ext = substr($image_location, $ext_pos+1);
            $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
          }

          $class = "media-attachment"; 
          echo "<li>" . PHP_EOL;
          echo "   <a class='$class' href='" . site_url() . "/wp-admin/upload.php?item=" . $row->ID . "'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
          echo "   <div class='attachment-name'>$row->post_title.$ext</div>" . PHP_EOL;
          echo "</li>" . PHP_EOL;              
        }      
      
    }
    else {
      echo __('No files were found matching that name.','maxgalleria-media-library') . PHP_EOL;                      
    }
    
    die();    
  }
  
  public function search_library() {
    
    global $wpdb;
    
    echo '<div id="wp-media-grid" class="wrap">' . PHP_EOL;
    //empty h2 for where WP notices will appear
    echo '  <h2></h2>' . PHP_EOL;
//    echo '  <div class="media-plus-toolbar wp-filter"><div class="media-toolbar-secondary">' . PHP_EOL;
    echo '  <div class="media-plus-toolbar wp-filter">' . PHP_EOL;
    echo '<div id="mgmlp-title-area">' . PHP_EOL;
    echo '  <h2 class="mgmlp-title">Media Library Plus Pro Search Results</h2>' . PHP_EOL;
    echo '  <div id="back-wraper"><a href="' . site_url() . '/wp-admin/admin.php?page=media-library">Back to Media Library Plus Folders</a></div>' . PHP_EOL;
    echo '  <div id="search-wrap"><input type="search" placeholder="Search" id="mgmlp-media-search-input" class="search"></div>' . PHP_EOL;            
    echo '</div>' . PHP_EOL;
		echo '<div style="clear:both;"></div>' . PHP_EOL;
    echo "<div id='search-instructions'>Click on an image to go to its folder or a on folder to view its contents.</div>";
    if ((isset($_GET['s'])) && (strlen(trim($_GET['s'])) > 0)) {
      $search_string = trim(stripslashes(strip_tags($_GET['s'])));
      echo "<h4>Search results for: $search_string</h4>" . PHP_EOL;
      
      echo '<ul class="mg-media-list">' . PHP_EOL;
            
      $folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
      $sql = $wpdb->prepare("select ID, post_title, $folder_table.folder_id
        from $wpdb->prefix" . "posts
        LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
        where post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE ."' and post_title like '%%%s%%'", $search_string);

      $rows = $wpdb->get_results($sql);

      $class = "media-folder"; 
      if($rows) {
        foreach($rows as $row) {
          $thumbnail = wp_get_attachment_thumb_url($row->ID);
          if($thumbnail !== false)
            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
          else {
						
            //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
						$baseurl = $this->upload_dir['baseurl'];
						$baseurl = rtrim($baseurl, '/') . '/';
						$image_location = $baseurl . ltrim($row->attached_file, '/');
												
            $ext_pos = strrpos($image_location, '.');
            $ext = substr($image_location, $ext_pos+1);
            $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
          }
          
          echo "<li>" . PHP_EOL;
          echo "   <a class='$class' href='" . site_url() . "/wp-admin/admin.php?page=media-library&media-folder=" . $row->ID . "'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
          echo "   <div class='attachment-name'>$row->post_title</div>" . PHP_EOL;
          echo "</li>" . PHP_EOL;              
          
        }
      }


		$sql = $wpdb->prepare("select ID, post_title, pm.meta_value as attached_file, folder_id from {$wpdb->prefix}posts 
        LEFT JOIN {$wpdb->prefix}mgmlp_folders ON( {$wpdb->prefix}posts.ID = {$wpdb->prefix}mgmlp_folders.post_id) 
        LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
        where post_type= 'attachment' and pm.meta_key = '_wp_attached_file' and post_title like '%%%s%%'", $search_string);

      $rows = $wpdb->get_results($sql);

      $class = "media-attachment"; 
      if($rows) {
        foreach($rows as $row) {
					
		      //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
					$baseurl = $this->upload_dir['baseurl'];
					$baseurl = rtrim($baseurl, '/') . '/';
					$image_location = $baseurl . ltrim($row->attached_file, '/');
					
          $thumbnail = wp_get_attachment_thumb_url($row->ID);
          if($thumbnail !== false)
            $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
          else {												
            $ext_pos = strrpos($image_location, '.');
            $ext = substr($image_location, $ext_pos+1);
            $thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
          }
          
          $filename =  pathinfo($image_location, PATHINFO_BASENAME);
          
          echo "<li>" . PHP_EOL;
          echo "   <a class='$class' href='" . site_url() . "/wp-admin/admin.php?page=media-library&media-folder=" . $row->folder_id . "'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
          echo "   <div class='attachment-name'>$filename</div>" . PHP_EOL;
          echo "</li>" . PHP_EOL;              
        }      

      }
      else {
        echo __('No files were found matching that name.','maxgalleria-media-library') . PHP_EOL;                      
      }
      echo "</ul>" . PHP_EOL;
    }
    //echo '  </div>' . PHP_EOL;
    echo '</div>' . PHP_EOL;    
    
    ?>
        
      <script>                        
      jQuery('#mgmlp-media-search-input').keydown(function (e){
        if(e.keyCode == 13){

          var search_value = jQuery('#mgmlp-media-search-input').val();

          var home_url = "<?php echo site_url(); ?>"; 

          window.location.href = home_url + '/wp-admin/admin.php?page=search-library&' + 's=' + search_value;

        }  
      })    
      </script>          
    <?php
  }
  
  public function maxgalleria_rename_image() {
    
    global $wpdb, $blog_id;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
      $file_id = trim(stripslashes(strip_tags($_POST['image_id'])));
    else
      $file_id = "";
    
    if ((isset($_POST['new_file_name'])) && (strlen(trim($_POST['new_file_name'])) > 0))
      $new_file_name = trim(stripslashes(strip_tags($_POST['new_file_name'])));
    else
      $new_file_name = "";
    
    if($new_file_name === '') {
      echo "Invalid file name.";
      die();
    }
    
    $new_file_name = strtolower($new_file_name);
    if(preg_match('/^[a-z0-9-]+\.ext$/', $new_file_name)) {
      echo "Invalid file name.";
      die();      
    }
          
    if (preg_match("/\\s/", $new_file_name)) {
      echo "The file name cannot contain spaces or tabs.";
      die();            
    }
          
    $sql = "select ID, pm.meta_value as attached_file, post_title, post_name 
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where ID = $file_id
AND pm.meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
    if($row) {
			
      //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
			$baseurl = $this->upload_dir['baseurl'];
			$baseurl = rtrim($baseurl, '/') . '/';
			$image_location = $baseurl . ltrim($row->attached_file, '/');
			
      $full_new_file_name = $new_file_name . '.' . pathinfo($image_location, PATHINFO_EXTENSION);
      $destination_path = $this->get_absolute_path(pathinfo($image_location, PATHINFO_DIRNAME));
						
      $new_file_name = wp_unique_filename( $destination_path, $full_new_file_name, null );
      
      $old_file_path = $this->get_absolute_path($image_location);
						
      $new_file_url = pathinfo($image_location, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $new_file_name;

			if(is_multisite()) {
				$url_slug = "site" . $blog_id . "/";
				$new_file_url = str_replace($url_slug, "", $new_file_url);
//				if(strpos($url, 'wp-content') === false)
//					$url = str_replace($url_slug, "wp-content/uploads/sites/" . $blog_id . "/" , $url);
//				else
//					$url = str_replace($url_slug, "", $url);
			}
									
      $new_file_path = $this->get_absolute_path($new_file_url);
                  
      if($this->is_windows()) {
        $old_file_path = str_replace('\\', '/', $old_file_path);      
        $new_file_path = str_replace('\\', '/', $new_file_path);      
      }
            
      if(rename($old_file_path, $new_file_path )) {

        $old_file_path = str_replace('.', '*.', $old_file_path );

        foreach (glob($old_file_path) as $source_path) {
          $thumbnail_file = pathinfo($source_path, PATHINFO_BASENAME);
          $thumbnail_destination = $destination_path . DIRECTORY_SEPARATOR . $thumbnail_file;
          unlink($source_path);
        }                    
              
        $table = $wpdb->prefix . "posts";
        $data = array('guid' => $new_file_url, 
                      'post_title' => $new_file_name,
                      'post_name' => $new_file_name                
                );
        $where = array('ID' => $file_id);
        $wpdb->update( $table, $data, $where);
        
        $table = $wpdb->prefix . "postmeta";
        $where = array('post_id' => $file_id);
        $wpdb->delete($table, $where);
                
        // get the uploads dir name
        $basedir = $this->upload_dir['baseurl'];
        $uploads_dir_name_pos = strrpos($basedir, '/');
        $uploads_dir_name = substr($basedir, $uploads_dir_name_pos+1);

        //find the name and cut off the part with the uploads path
        $string_position = strpos($new_file_url, $uploads_dir_name);
        $uploads_dir_length = strlen($uploads_dir_name) + 1;
        $uploads_location = substr($new_file_url, $string_position+$uploads_dir_length);
        if($this->is_windows()) 
          $uploads_location = str_replace('\\','/', $uploads_location);      
								
				$uploads_location = ltrim($uploads_location, '/');
        update_post_meta( $file_id, '_wp_attached_file', $uploads_location );
        $attach_data = wp_generate_attachment_metadata( $file_id, $new_file_path );
        wp_update_attachment_metadata( $file_id, $attach_data );

        echo "<script>window.location.reload(true);</script>";
      }
    }
    
    die();
  }
  
  // saves the sort selection
  public function sort_contents() {
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['sort_order'])) && (strlen(trim($_POST['sort_order'])) > 0))
      $sort_order = trim(stripslashes(strip_tags($_POST['sort_order'])));
    else
      $sort_order = "0";
    
    update_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER, $sort_order );  
    
    switch ($sort_order) {
      case '0':
      $msg = __('Sorting by date.','maxgalleria-media-library');
      break;  
    
      case '1':
      $msg = __('Sorting by name.','maxgalleria-media-library');
      break;        
    }
    
    echo $msg;
            
    die();
  }
	
	public function mgmlp_move_copy(){

    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['move_copy_switch'])) && (strlen(trim($_POST['move_copy_switch'])) > 0))
      $move_copy_switch = trim(stripslashes(strip_tags($_POST['move_copy_switch'])));
    else
      $move_copy_switch = 'on';
				    
    update_option( MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY, $move_copy_switch );  
		
		die();
		
	}
  
  public function run_on_deactivate() {
    wp_clear_scheduled_hook('new_folder_check');
  }
  
  public function admin_check_for_new_folders($noecho = null) {
        
		global $blog_id;
		$skip_path = "";
    $uploads_path = wp_upload_dir();
    
    if(!$uploads_path['error']) {
      
      $uploads_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
      $uploads_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
      $uploads_length = strlen($uploads_folder);
						
			$folders_to_hide = explode("\n", file_get_contents( MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR .'/folders_to_hide.txt'));
      
      //find the uploads folder
      $uploads_url = $uploads_path['baseurl'];
      //$upload_path = $this->get_absolute_path($uploads_url);
			$upload_path = $uploads_path['basedir'];
      $folder_found = false;
			
			//not sure if this is still needed
			//$this->mlp_remove_slashes();
      
      if(!$noecho)
        echo __('Scaning for new folders in ','maxgalleria-media-library') . " $upload_path<br>";      
      $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_path), RecursiveIteratorIterator::SELF_FIRST);
      foreach($objects as $name => $object){
        if(is_dir($name)) {
          $dir_name = pathinfo($name, PATHINFO_BASENAME);
          if ($dir_name[0] !== '.') { 
						if( empty($skip_path) || (strpos($name, $skip_path) === false)) {
							
							// no match, set it back to empty
							$skip_path = "";
            //$url = $this->get_file_url($name);
						//error_log("skip_path $skip_path, name $name");
						
            if(!is_multisite()) {
							$upload_pos = strpos($name, $uploads_folder);
							$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

							// fix slashes if running windows
							if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
								$url = str_replace('\\', '/', $url);      
							}

							if($this->folder_exist($url) === false) {
								if(!in_array($dir_name, $folders_to_hide)) {
		                if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
										$folder_found = true;
										if(!$noecho)
											echo __('Adding','maxgalleria-media-library') . " $url<br>";
										$parent_id = $this->find_parent_id($url);
										$this->add_media_folder($dir_name, $parent_id, $url);
									} else {
										$skip_path = $name;
									}
								} else {
									$skip_path = $name;									
								}
							}
						} else {
							if($blog_id === '1') {
								if(strpos($name,"uploads/sites") !== false)
									continue;
								
								$upload_pos = strpos($name, $uploads_folder);
								$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

								// fix slashes if running windows
								if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
									$url = str_replace('\\', '/', $url);      
								}

								if($this->folder_exist($url) === false) {
								  if(!in_array($dir_name, $folders_to_hide)) {
		                if(!file_exists($name . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
											$folder_found = true;
											if(!$noecho)
												echo __('Adding','maxgalleria-media-library') . " $url<br>";
											$parent_id = $this->find_parent_id($url);
											$this->add_media_folder($dir_name, $parent_id, $url);
										}
									} else {
										$skip_path = $name;									
									}
								}																
							} else {
								if(strpos($name,"uploads/sites/$blog_id") !== false) {
									$upload_pos = strpos($name, $uploads_folder);
									$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

									// fix slashes if running windows
									if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
										$url = str_replace('\\', '/', $url);      
									}

									if($this->folder_exist($url) === false) {
										$folder_found = true;
										if(!$noecho)
											echo __('Adding','maxgalleria-media-library') . " $url<br>";
										$parent_id = $this->find_parent_id($url);
										$this->add_media_folder($dir_name, $parent_id, $url);              
									}																
								}
							}
						}
          }  
				}
        }  
      }      
      if(!$folder_found) {
        if(!$noecho)
          echo __('No new folders were found.','maxgalleria-media-library') . "<br>";
      }  
    } 
    else {
      if(!$noecho)
        echo "error: " . $uploads_path['error'];
    }
  }
		
	public function new_folder_search($name, $uploads_folder, $uploads_length, $dir_name, $noecho) {
		$folder_found = false;
		$upload_pos = strpos($name, $uploads_folder);
		$url = $uploads_url . substr($name, ($upload_pos+$uploads_length));

		// fix slashes if running windows
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$url = str_replace('\\', '/', $url);      
		}

		if($this->folder_exist($url) === false) {
			$folder_found = true;
			if(!$noecho) {
				echo __('Adding','maxgalleria-media-library') . " $url<br>";
			}	
			$parent_id = $this->find_parent_id($url);
			$this->add_media_folder($dir_name, $parent_id, $url);              
		}
		return $folder_found;
	}
  
  private function find_parent_id($base_url) {
    
    global $wpdb;    
    $last_slash = strrpos($base_url, '/');
    $parent_dir = substr($base_url, 0, $last_slash);
		
		// get the relative path
		$parent_dir = substr($parent_dir, $this->base_url_length);		
		
    //$sql = "select ID from $wpdb->prefix" . "posts where guid = '$parent_dir'";
    $sql = "SELECT ID FROM {$wpdb->prefix}posts
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = ID
WHERE pm.meta_value = '$parent_dir' 
and pm.meta_key = '_wp_attached_file'";
		
    $row = $wpdb->get_row($sql);
    if($row) {
      $parent_id = $row->ID;
    }
    else
      $parent_id = $this->uploads_folder_ID; //-1;

    return $parent_id;
  }
    
  private function mpmlp_insert_post( $post_type, $post_title, $guid, $post_status ) {
    global $wpdb;
    
    $user_id = get_current_user_id();
    $post_date = current_time('mysql');
    
    $post = array(
      'post_content'   => '',
      'post_name'      => $post_title, 
      'post_title'     => $post_title,
      'post_status'    => $post_status,
      'post_type'      => $post_type,
      'post_author'    => $user_id,
      'ping_status'    => 'closed',
      'post_parent'    => 0,
      'menu_order'     => 0,
      'to_ping'        => '',
      'pinged'         => '',
      'post_password'  => '',
      'guid'           => $guid,
      'post_content_filtered' => '',
      'post_excerpt'   => '',
      'post_date'      => $post_date,
      'post_date_gmt'  => $post_date,
      'comment_status' => 'closed'
    );      
        
    
    $table = $wpdb->prefix . "posts";	    
    $wpdb->insert( $table, $post );
        
    return $wpdb->insert_id;  
  }
  
  public function mlp_set_review_notice_true() {
    
    $current_user_id = get_current_user_id(); 
    
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_REVIEW_NOTICE, "off" );
    
    $request = $_SERVER["HTTP_REFERER"];
    
    echo "<script>window.location.href = '" . $request . "'</script>";             
    
	}
  
	public function mlp_set_review_later() {
    
    $current_user_id = get_current_user_id(); 
    
    $review_date = date('Y-m-d', strtotime("+14 days"));
        
    update_user_meta( $current_user_id, MAXGALLERIA_MLP_REVIEW_NOTICE, $review_date );
    
    $request = $_SERVER["HTTP_REFERER"];
    
    echo "<script>window.location.href = '" . $request . "'</script>";             
    
	}
		
  public function mlp_review_notice() {
    if( current_user_can( 'manage_options' ) ) {  ?>
      <div class="updated notice maxgalleria-mlp-notice">         
        <div id='mlp_logo'></div>
        <div id='maxgalleria-mlp-notice-3'><p id='mlp-notice-title'><?php _e( 'Rate us Please!', 'maxgalleria-media-library' ); ?></p>
        <p><?php _e( 'Your rating is the simplest way to support Media Library Plus. We really appreciate it!', 'maxgalleria-media-library' ); ?></p>

        <ul id="mlp-review-notice-links">
          <li> <span class="dashicons dashicons-smiley"></span><a href="<?php echo admin_url(); ?>admin.php?page=mlp-review-notice"><?php _e( "I've already left a review", "maxgalleria-media-library" ); ?></a></li>
          <li><span class="dashicons dashicons-calendar-alt"></span><a href="<?php echo admin_url(); ?>admin.php?page=mlp-review-later"><?php _e( "Maybe Later", "maxgalleria-media-library" ); ?></a></li>
          <li><span class="dashicons dashicons-external"></span><a target="_blank" href="https://wordpress.org/support/plugin/media-library-plus/reviews/?filter=5"><?php _e( "Sure! I'd love to!", "maxgalleria-media-library" ); ?></a></li>
        </ul>
        </div>
        <a class="dashicons dashicons-dismiss close-mlp-notice" href="<?php echo admin_url(); ?>admin.php?page=mlp-review-notice"></a>          
      </div>
    <?php     
    }
  }
	
  public function check_for_attachment_id($guid, $post_id) {	
		global $blog_id;
		
		$attach_id_found = strpos($guid, 'attachment_id=');
		if($attach_id_found !== false)
			$location = wp_get_attachment_url($post_id);
		else
			$location = $guid;
						
//		if(is_multisite()) {
//			$url_slug = 'site' . $blog_id . '/';
//			$location = str_replace($location, $url_slug, "");			
//			return $location;
//		} else
			return $location;
	}
	
	public function max_sync_contents() {

    global $wpdb;
		
    $files_added = 0;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder = trim(stripslashes(strip_tags($_POST['parent_folder'])));
    else
      $parent_folder = "";

		$image_seo = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');
		
		if($image_seo === 'on') {
		
			if ((isset($_POST['alt_text'])) && (strlen(trim($_POST['alt_text'])) > 0))
				$alt_text = trim(stripslashes(strip_tags($_POST['alt_text'])));
			else
				$alt_text = "";

			if ((isset($_POST['title_text'])) && (strlen(trim($_POST['title_text'])) > 0))
				$default_title = trim(stripslashes(strip_tags($_POST['title_text'])));
			else
				$default_title = "";
																		
		} else {
			$default_alt = "";
		}
				    
    if(!is_numeric($parent_folder))
      die();
		
		// get the contents of the current folder from the database
		
		$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;    
      
    $sql = "select ID, pm.meta_value as attached_file, post_title, $folder_table.folder_id 
from $wpdb->prefix" . "posts 
LEFT JOIN $folder_table ON($wpdb->prefix" . "posts.ID = $folder_table.post_id)
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
where post_type = 'attachment' 
and folder_id = '$parent_folder' 
and pm.meta_key = '_wp_attached_file'	
order by post_title";
    
    $attachments = $wpdb->get_results($sql);
		
    $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta
where post_id = $parent_folder    
and meta_key = '_wp_attached_file'";	

    $current_row = $wpdb->get_row($sql);
		
    //$image_location = $this->upload_dir['baseurl'] . '/' . $current_row->attached_file;
		$baseurl = $this->upload_dir['baseurl'];
		$baseurl = rtrim($baseurl, '/') . '/';
		$image_location = $baseurl . ltrim($current_row->attached_file, '/');
		
    $folder_path = $this->get_absolute_path($image_location);

    $folder_contents = array_diff(scandir($folder_path), array('..', '.'));
		
    foreach ($folder_contents as $file_path) {
			
			if($file_path !== '.DS_Store') {
				$new_attachment = $folder_path . DIRECTORY_SEPARATOR . $file_path;
				//error_log("new_attachment $new_attachment");
				if(!is_dir($new_attachment)) {
					if($this->is_base_file($file_path, $folder_contents)) {				
						if(!$this->search_folder_attachments($file_path, $attachments)) {

							if($image_seo === 'on') {

								$folder_name = $this->get_folder_name($parent_folder);

								$file_name = basename( $new_attachment );

								$new_file_title = $default_title;

								$new_file_title = str_replace('%foldername', $folder_name, $new_file_title );			

								$new_file_title = str_replace('%filename', $file_name, $new_file_title );			

								$default_alt = $alt_text;

								$default_alt = str_replace('%foldername', $folder_name, $default_alt );			

								$default_alt = str_replace('%filename', $file_name, $default_alt );			
								
							} else {
								$new_file_title = preg_replace( '/\.[^.]+$/', '', basename( $new_attachment ) );								
							}	
														
							$old_attachment_name = $new_attachment;
							$new_attachment = pathinfo($new_attachment, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($new_attachment, PATHINFO_FILENAME) . "." . strtolower(pathinfo($new_attachment, PATHINFO_EXTENSION));
							
							if(rename($old_attachment_name, $new_attachment)) {														
								if($this->add_new_attachment($new_attachment, $parent_folder, $new_file_title, $default_alt)) 
									$files_added++;
							} else {
								if($this->add_new_attachment($old_attachment_name, $parent_folder, $new_file_title, $default_alt)) 
									$files_added++;								
							}
						}	
					}
			  } else {
					// folder found	
					//find the uploads folder
					$uploads_path = wp_upload_dir();

					if(!$uploads_path['error']) {

						$uploads_folder = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, "uploads");      
						$uploads_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
						$uploads_length = strlen($uploads_folder);

						$uploads_url = $uploads_path['baseurl'];

						$upload_pos = strpos($new_attachment, $uploads_folder);
						$url = $uploads_url . substr($new_attachment, ($upload_pos+$uploads_length));

						// fix slashes if running windows
						if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
							$url = str_replace('\\', '/', $url);      
						}

						if($this->folder_exist($url) === false) {							
					    $abs_folder_path = $this->get_absolute_path($url);
		          if(!file_exists($abs_folder_path . DIRECTORY_SEPARATOR . 'mlpp-hidden' )){
								if($this->add_media_folder($file_path, $parent_folder, $url))
									$files_added++;
							}
						}
					}
				}
			}		
		}
		echo $files_added;
		
    die();		
	}
			
	private function is_base_file($file_path, $file_array) {
		
		$dash_position = strrpos($file_path, '-' );
		$x_position = strrpos($file_path, 'x', $dash_position);
		$dot_position = strrpos($file_path, '.' );
		
		if(($dash_position) && ($x_position)) {
			$base_file = substr($file_path, 0, $dash_position) . substr($file_path, $dot_position );
			if(in_array($base_file, $file_array))
				return false;
			else 
				return true;
		} else 
			return true;
				
	}
	
	private function search_folder_attachments($file_path, $attachments){

		$found = false;
    if($attachments) {
      foreach($attachments as $row) {
        $current_file_path = pathinfo(get_attached_file($row->ID), PATHINFO_BASENAME);				
				if($current_file_path === $file_path) {
					$found = true;
					break;
				}
      }			
    }
		return $found; 
	}
	
	public function write_log ( $log )  {
    if ( true === WP_DEBUG ) {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    }
  }
	
		public function mlp_tb_load_folder2() {
		
    global $wpdb;
		    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(stripslashes(strip_tags($_POST['folder'])));
    else
      $current_folder_id = "";
    
    if(!is_numeric($current_folder_id))
      die();

		$this->display_folder_contents ($current_folder_id, false);

		echo '<div class="clearfix"></div>' . PHP_EOL;
				
	  die();
		
	}

		
	public function mlp_tb_load_folder() {
		
    global $wpdb;
		    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(stripslashes(strip_tags($_POST['folder'])));
    else
      $current_folder_id = "";
    
    if(!is_numeric($current_folder_id))
      die();

		echo '<div id="alwrap">' . PHP_EOL;
		echo '	<div style="display:none" id="ajaxloader"></div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;

		$folder_location = $this->get_folder_path($current_folder_id);

		$folders_path = "";
		$parents = $this->get_parents($current_folder_id);

		$folder_count = count($parents);
		$folder_counter = 0;        
		$current_folder_string = site_url() . "/wp-content";
		foreach( $parents as $key => $obj) { 
			$folder_counter++;
			if($folder_counter === $folder_count)
				$folders_path .= $obj['name'];      
			else
				$folders_path .= '<a folder="' . $obj['id'] . '" class="media-link">' . $obj['name'] . '</a>/';      
			$current_folder_string .= '/' . $obj['name'];
		}

		echo '<div id="mgmlp-tb-container">' . PHP_EOL;
	  echo "  <h3 id='mgmlp-breadcrumbs'>" . __('Location:','maxgalleria-media-library') . " $folders_path</h3>";
		echo '<div id="folder-tree-container"><ul id="folder-tree"></ul></div>' . PHP_EOL;

		echo '  <div class="clearfix"></div>' . PHP_EOL;
			
		echo '  <div id="mgmlp-file-container">' . PHP_EOL;
//		echo '<div id="folder-tree-container"><ul id="folder-tree"></ul></div>' . PHP_EOL;
			$this->display_folder_contents ($current_folder_id, false);
		echo '  </div>' . PHP_EOL;
		echo '</div>' . PHP_EOL;

		echo '<div class="clearfix"></div>' . PHP_EOL;
				
	  die();
		
	}
	
	public function mlp_load_folder() {
		
    global $wpdb;
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['folder'])) && (strlen(trim($_POST['folder'])) > 0))
      $current_folder_id = trim(stripslashes(strip_tags($_POST['folder'])));
    else
      $current_folder_id = "";
    
    if(!is_numeric($current_folder_id))
      die();

		$folder_location = $this->get_folder_path($current_folder_id);

		$folders_path = "";
		$parents = $this->get_parents($current_folder_id);

		$folder_count = count($parents);
		$folder_counter = 0;        
		$current_folder_string = site_url() . "/wp-content";
		foreach( $parents as $key => $obj) { 
			$folder_counter++;
			if($folder_counter === $folder_count)
				$folders_path .= $obj['name'];      
			else
				$folders_path .= '<a folder="' . $obj['id'] . '" class="media-link">' . $obj['name'] . '</a>/';      
			$current_folder_string .= '/' . $obj['name'];
		}
		
		$this->display_folder_contents ($current_folder_id, false, $folders_path);
		

//		echo '<div id="mgmlp-tb-container">' . PHP_EOL;
//		echo "  <h3 id='mgmlp-breadcrumbs'>" . __('Location:','maxgalleria-media-library') . " $folders_path</h3>";
//
//		echo '  <div class="clearfix"></div>' . PHP_EOL;
//			
//		echo '  <div id="mgmlp-file-container">' . PHP_EOL;
//		echo '<div id="folder-tree-container"><ul id="folder-tree"></ul></div>' . PHP_EOL;
//		$this->display_folder_contents ($current_folder_id, false);
//		echo '  </div>' . PHP_EOL;
//		echo '</div>' . PHP_EOL;
//		echo '</div>' . PHP_EOL;
//
//		echo '<div class="clearfix"></div>' . PHP_EOL;
//		echo '<script>' . PHP_EOL;
//		echo '	jQuery(document).ready(function(){' . PHP_EOL;
//		echo '		jQuery("input.mgmlp-media").change(function(){' . PHP_EOL;
//	  echo '	    jQuery("input.mgmlp-media").not(this).prop("checked", false);' . PHP_EOL;
//		echo '	    var image_id = jQuery(this).attr("id");' . PHP_EOL;
//	 	echo '		  alert(image_id);' . PHP_EOL;
//	 	echo '		});' . PHP_EOL;
//		echo '	});' . PHP_EOL;  
//		echo '</script>' . PHP_EOL; 
				
	  die();
		
	}
	
//	public function mlp_get_image_html() {
//		
//    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
//      exit(__('missing nonce!','maxgalleria-media-library'));
//    } 
//    
//    if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
//      $image_id = trim(stripslashes(strip_tags($_POST['image_id'])));
//    else
//      $image_id = "";
//		
//    if ((isset($_POST['title'])) && (strlen(trim($_POST['title'])) > 0))
//      $title = trim(stripslashes(strip_tags($_POST['title'])));
//    else
//      $title = "";
//		
//    if ((isset($_POST['caption'])) && (strlen(trim($_POST['caption'])) > 0))
//      $caption = trim(stripslashes(strip_tags($_POST['caption'])));
//    else
//      $caption = "";
//		
//    if ((isset($_POST['alt'])) && (strlen(trim($_POST['alt'])) > 0))
//      $alt = trim(stripslashes(strip_tags($_POST['alt'])));
//    else
//      $alt = "";
//		
//    if ((isset($_POST['desc'])) && (strlen(trim($_POST['desc'])) > 0))
//      $desc = trim(stripslashes(strip_tags($_POST['desc'])));
//    else
//      $desc = "";
//		
//    if ((isset($_POST['alignment'])) && (strlen(trim($_POST['alignment'])) > 0))
//      $alignment = trim(stripslashes(strip_tags($_POST['alignment'])));
//    else
//      $alignment = "";
//		
//    if ((isset($_POST['size'])) && (strlen(trim($_POST['size'])) > 0))
//      $size = trim(stripslashes(strip_tags($_POST['size'])));
//    else
//      $size = "";
//		
//    if ((isset($_POST['link'])) && (strlen(trim($_POST['link'])) > 0))
//      $link = trim(stripslashes(strip_tags($_POST['link'])));
//    else
//      $link = "";
//		
//    if ((isset($_POST['custom_link'])) && (strlen(trim($_POST['custom_link'])) > 0))
//      $custom_link = trim(stripslashes(strip_tags($_POST['custom_link'])));
//    else
//      $custom_link = "";
//		
//    if(!is_numeric($image_id))
//      die();
//		
//		$image_info = $this->wp_get_attachment($image_id);
//		
//		if($alt === '')
//			$alt = $title; 
//		
//		$image_html = "<img alt='{$alt}' src= '{$image_info['src']}' title='{$title}' />";
//
//		echo $image_html;
//				
//		die();
//	}
	
	public function wp_get_attachment( $attachment_id ) {

		$attachment = get_post( $attachment_id );

		$base_url = $this->upload_dir['baseurl'];
    $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
		$base_url = rtrim($base_url, '/') . '/';
		$image_location = $base_url . ltrim($attached_file, '/');
		
		$available_sizes = array();
		
		if (wp_attachment_is_image($attachment_id)) {
			foreach ( $this->image_sizes as $size ) {
				$image = wp_get_attachment_image_src( $attachment_id, $size );
								
				if(!empty( $image ) && ( true == $image[3] || 'full' == $size )) {
					$available_sizes[$size] = $image[1] . " x " . $image[2];
				}	
			}
		} else {
			$available_sizes["full"] = "full";
		}
	
		
		$image_data = array(
				'id' => $attachment_id,
				'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'href' => get_permalink( $attachment->ID ),
				'src' => $image_location,
				'title' => $attachment->post_title,
				'available_sizes'	=> $available_sizes
		);
		
		return $image_data;
	}
	
	public function mlp_get_image_info() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
      $image_id = trim(stripslashes(strip_tags($_POST['image_id'])));
    else
      $image_id = "";
    
    if(!is_numeric($image_id))
      die();
		
		$image_info = $this->wp_get_attachment($image_id);
				
		echo json_encode($image_info);
						
		die();
		
	}
	
	public function mlp_image_add_caption() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['html'])) && (strlen(trim($_POST['html'])) > 0))
      $html = stripslashes($_POST['html']);
    else
      $html = "";
		
    if ((isset($_POST['id'])) && (strlen(trim($_POST['id'])) > 0))
      $id = intval(trim(stripslashes(strip_tags($_POST['id']))));
    else
      $id = 0;
		
    if ((isset($_POST['caption'])) && (strlen(trim($_POST['caption'])) > 0))
      $caption = trim(stripslashes(strip_tags($_POST['caption'])));
    else
      $caption = "";
		
    if ((isset($_POST['title'])) && (strlen(trim($_POST['title'])) > 0))
      $title = trim(stripslashes(strip_tags($_POST['title'])));
    else
      $title = "";
		
    if ((isset($_POST['align'])) && (strlen(trim($_POST['align'])) > 0))
      $align = trim(stripslashes(strip_tags($_POST['align'])));
    else
      $align = "";
		
    if ((isset($_POST['url'])) && (strlen(trim($_POST['url'])) > 0))
      $url = trim(stripslashes(strip_tags($_POST['url'])));
    else
      $url = "";
				
    if ((isset($_POST['size'])) && (strlen(trim($_POST['size'])) > 0))
      $size = trim(stripslashes(strip_tags($_POST['size'])));
    else
      $size = "";
		
    if ((isset($_POST['alt'])) && (strlen(trim($_POST['alt'])) > 0))
      $alt = trim(stripslashes(strip_tags($_POST['alt'])));
    else
      $alt = "";	
				   
    $caption_html = image_add_caption( $html, $id, $caption, $title, $align, $url, $size, $alt );		
		
		echo json_encode($caption_html);
						
		die();
		
	}
	
	function mlp_update_description () {
		
		global $wpdb;
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['id'])) && (strlen(trim($_POST['id'])) > 0))
      $id = intval(trim(stripslashes(strip_tags($_POST['id']))));
    else
      $id = 0;
		
    if ((isset($_POST['desc'])) && (strlen(trim($_POST['desc'])) > 0))
      $desc = trim(stripslashes(strip_tags($_POST['desc'])));
    else
      $desc = "";
		
		if($id !== 0) {
			$table = $wpdb->prefix . "posts";
			$data = array('post_content' => $desc );
			$where = array('ID' => $id);
			$wpdb->update( $table, $data, $where);
		}
	
		die();
	}
	
	function mlp_admin_post_thumbnail( $content, $post_id = null )  {
		
    if ($post_id == null) {
			global $post;

		  if ( !is_object($post) )
		    return $content;
       
      $post_id = $post->ID;
    }
		
		$thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
		
		global $wp_version;

		if (version_compare($wp_version, '3.5', '>=') && $thumbnail_id <= 0) {

			$set_thumbnail_link = '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set MLP featured image', 'maxgalleria-media-library' ) . '" href="#TB_inline?height=450&amp;width=753&amp;inlineId=select-mlp-container" id="set-mlp-post-thumbnail" class="thickbox">%s</a></p>';
			$content .= sprintf($set_thumbnail_link, esc_html__( 'Set MLP featured image', 'maxgalleria-media-library' )) . PHP_EOL;
			$content .= '<script>' . PHP_EOL;
			$content .= '	jQuery(document).ready(function(){' . PHP_EOL;
			$content .= '	  jQuery("#set-mlp-post-thumbnail").click(function(){' . PHP_EOL;      
			$content .= '	    jQuery("#mlp_featured").val("featured");' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_custom_link_label").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_custom_link").hide();' . PHP_EOL;			
			$content .= '	    jQuery("#mlp_tb_align_label").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_alignment").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_size_label").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_size").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_link_to_label").hide();' . PHP_EOL;
			$content .= '	    jQuery("#mlp_tb_link_select").hide();' . PHP_EOL;
			$content .= '	    jQuery("#insert_mlp_media").val("Set Featured Image");' . PHP_EOL;
			
		  $content .= '	  });' . PHP_EOL;
			$content .= '	});' . PHP_EOL;
			$content .= '</script>' . PHP_EOL;
		}
			
		return $content;
						
	}
	
	public function mlp_add_featured_image () {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
				
		if ((isset($_POST['id'])) && (strlen(trim($_POST['id'])) > 0))
			$post_ID = intval(trim(stripslashes(strip_tags($_POST['id']))));
		else
			$post_ID = 0;

		if ((isset($_POST['image_id'])) && (strlen(trim($_POST['image_id'])) > 0))
			$thumbnail_id = intval(trim(stripslashes(strip_tags($_POST['image_id']))));
		else
			$thumbnail_id = 0;
		
		if($post_ID !== 0 ) {
			if ( set_post_thumbnail( $post_ID, $thumbnail_id ) ) {
				$return = _wp_post_thumbnail_html( $thumbnail_id, $post_ID );
				echo $return;
			}
		}
		else {
			$return = _wp_post_thumbnail_html( $thumbnail_id, 0 );
			echo $return;			
		}
		
		die();
		
	}
	
	public function mlp_get_attachment_image_src () {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
								
    if ((isset($_POST['serial_image_ids'])) && (strlen(trim($_POST['serial_image_ids'])) > 0))
      $image_ids = trim(stripslashes(strip_tags($_POST['serial_image_ids'])));
    else
      $image_ids = "";
		
						        
    $image_ids = str_replace('"', '', $image_ids);    
    
    $image_ids = explode(',', $image_ids);
						
//		if ((isset($_POST['size'])) && (strlen(trim($_POST['size'])) > 0))
//			$size = intval(trim(stripslashes(strip_tags($_POST['size']))));
//		else
//			$size = "";
		
		if ((isset($_POST['id'])) && (strlen(trim($_POST['id'])) > 0))
			$post_id = intval(trim(stripslashes(strip_tags($_POST['id']))));
		else
			$post_id = 0;		
		
		$image_array = array();
		
		foreach( $image_ids as $image_id) {

			$image = array();
			$image['image_id'] = $image_id;
			//$image_path_sizes = wp_get_attachment_image_src( $image_id, $size );
			$image_path_sizes = wp_get_attachment_image_src($image_id, array('150','150'));
			$image_srcset = wp_get_attachment_image_srcset($image_id);
			if(isset($image_path_sizes[0])) { 
				$image['src'] = '<img class="attachment-thumbnail size-thumbnail" src="' . $image_path_sizes[0] . '" alt="" srcset="' . $image_srcset . '" width="150" height="150" >';
			}	else 
				$image['src'] = "";
			
			$image_array[] = $image; 
		}
		//$output = print_r($image_array, true);
		//error_log($output);
			
		echo json_encode($image_array);
		die();
	}				

	public function mlp_save_featured_image_id( $post_ID ) {
		
		if( !current_user_can( 'edit_pages' ) ) return;

		if( isset( $_REQUEST['mlp_featured_image'] )) {		
			$thumbnail_id = $_REQUEST['mlp_featured_image'];
			set_post_thumbnail( $post_ID, $thumbnail_id );
		}		

	}
	
	public function view_nextgen (){ 

	  global $wpdb;
		$folders_found = false;
		$images_found = false;
		
    ?>      
<!--      <div id="fb-root"></div>
      <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=636262096435499";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));</script>-->
    <?php		
		
	  $site_url = site_url();
		$ngg_options =	get_option('ngg_options');
		$ng_folder = $site_url . '/' . $ngg_options['gallerypath'];
		$folders_path = "NextGen Galleries"
						
			?>
      <div id="wp-media-grid" class="wrap">                
        <!--empty h2 for where WP notices will appear--> 
				<h1></h1>
        <div class="media-plus-toolbar"><div class="media-toolbar-secondary">  
            
        <div id='mgmlp-title-area'>
          <h2 class='mgmlp-title'><?php _e('Media Library Plus Pro - NextGen Gallery Viewer', 'maxgalleria-media-library' ); ?> </h2>  
          <div class="mgmlp-title" id='mg-prono-top'>
            <div><?php _e('Brought to you by', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxfoundry.com"> <img alt="Max Foundry" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/max-foundry-new.png"></a> <?php _e('makers of', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxbuttons.com/?ref=mbpro">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></div>
            <!--<div><?php _e('Brought to you by', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxfoundry.com"> <img alt="Max Foundry" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/max-foundry-new.png"></a> <?php _e('makers of', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxbuttons.com/?ref=mbpro">MaxButtons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxinbound.com/?ref=mbpro">MaxInbound</a></div>-->
            <div class="fb-like" data-href="https://www.facebook.com/maxfoundry" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>        </div>      
        </div>    
        <div class="clearfix"></div>  
        <!--<p id='mlp-more-info'><a href='http://maxgalleria.com/media-library-plus/' target="_blank"><?php _e('Click here to learn about the Media Library Plus Pro', 'maxgalleria-media-library' ); ?></a></p>-->
                                      
        <div class="clearfix"></div>
				
          <!--<a id="mgmlp-scan_folders">Scan Folders</a>-->  
          
          <div id="mgmlp-library-container">
            <div id="alwrap">
              <div style="display:none" id="ajaxloader"></div>
            </div>
            <?php 
						
							if ((isset($_GET['media-folder'])) && (strlen(trim($_GET['media-folder'])) > 0)) {
								$current_folder_id = trim(stripslashes(strip_tags($_GET['media-folder'])));
								if(is_numeric($current_folder_id)) {
									$gallery_name = $this->get_gallery_name($current_folder_id);
									$folders_path .= "/" . $gallery_name;
									echo "media folder: $current_folder_id, folders_path: $folders_path<br>";
								}
							} else {
								$current_folder_id = 0;
							}
						
	            echo "<h3 id='mgmlp-breadcrumbs'>" . __('Location:','maxgalleria-media-library') . " $folders_path</h3>";
														
							echo '<div id="mgmlp-toolbar">' . PHP_EOL;
							echo '</div><!-- mgmlp-toolbar -->' . PHP_EOL;
							echo '<div class="clearfix"></div>' . PHP_EOL;


							if($current_folder_id === 0)
							  $sql = "SELECT gid, name FROM {$wpdb->prefix}ngg_gallery ORDER BY name";
							else	
							  $sql = "SELECT pid, filename FROM {$wpdb->prefix}ngg_pictures WHERE galleryid = $current_folder_id ORDER BY filename";
							//echo $sql;
							$folder_list = "";
							$rows = $wpdb->get_results($sql);

              echo '<div id="mgmlp-file-container">' . PHP_EOL;
						  //echo '<div id="folder-tree-container"><ul id="folder-tree" class="ztree"></ul></div>' . PHP_EOL;
							

							echo '<ul class="mg-media-list">' . PHP_EOL;              
							if($rows) {
								$folders_found = true;
								foreach($rows as $row) {

									if($current_folder_id === 0) {
										//$checkbox = sprintf("<input type='checkbox' class='mgmlp-folder' id='%s' value='%s' />", $row->gid, $row->gid );
										
										echo "<li>" . PHP_EOL;
										echo "  <a id='$row->ID' class='media-folder media-link' folder='$row->gid'></a>" . PHP_EOL;
										echo "  <div class='attachment-name'><span class='image_select'></span><a href='$gallery_link' class='media-link' folder='$row->gid'>$row->name</a></div>" . PHP_EOL;
										//echo "  <div class='attachment-name'><span class='image_select'>$checkbox</span><a href='$gallery_link' class='media-link' folder='$row->gid'>$row->name</a></div>" . PHP_EOL;
										echo "</li>" . PHP_EOL;       
									} else {
										$images_found = true;
										//$checkbox = sprintf("<input type='checkbox' class='mgmlp-media' id='%s' value='%s' />", $row->pid, $row->pid );
										if($image_link)
											$class = "media-attachment"; 
										else
											$class = "tb-media-attachment"; 

										$media_edit_link = "";

										$filename = $ng_folder . "/" . $gallery_name . "/" . $row->filename;
										
										$thumbnail = $ng_folder . "/" . $gallery_name . "/thumbs/thumbs_" . $row->filename;

										echo "<li>" . PHP_EOL;
										if($image_link)
											echo "   <a class='$class' href='$filename'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
										else
											echo "   <a id='$row->pid' class='$class'><img alt='' src='$thumbnail' /></a>" . PHP_EOL;
										echo "   <div class='attachment-name'><span class='image_select'></span>$row->filename</div>" . PHP_EOL;
										//echo "   <div class='attachment-name'><span class='image_select'>$checkbox</span>$row->filename</div>" . PHP_EOL;
										echo "</li>" . PHP_EOL;              
									
									}
								}
							}

							echo '</ul>' . PHP_EOL;
							echo '</div><!-- mgmlp-file-container -->' . PHP_EOL;

							if(!$images_found && !$folders_found)
								echo "<p style='text-align:center'>" . __('No files were found.','maxgalleria-media-library')  . "</p>";
																
						?>
                        
          </div> <!-- mgmlp-library-container -->
				</div> <!-- media-toolbar-secondary -->
				</div> <!-- media-plus-toolbar -->
			</div> <!-- wp-media-grid -->	
		<script>

	jQuery(document).on("click", ".media-link", function () {

		var folder = jQuery(this).attr('folder');

		var home_url = "<?php echo site_url(); ?>"; 

		window.location.href = home_url + '/wp-admin/admin.php?page=view-nextgen&' + 'media-folder=' + folder;

	});

	</script>  

			<?php
		
	}
	
	private function get_gallery_name($current_folder_id) {
    global $wpdb;    
    
	  $sql = "select name from {$wpdb->prefix}ngg_gallery where gid = $current_folder_id";
    
    $row = $wpdb->get_row($sql);
    if($row === null)
      return false;
    else
      return $row->name;
		
	}
	
	public function mlpp_hide_template_ad() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    update_option('mlpp_show_template_ad', "off");
		
		die();
	}
	
	public function mlpp_create_new_ng_gallery() {
		
    global $wpdb;
		$retval = false;
		
		$ngg_options =	get_option('ngg_options');
		$ng_folder_path = get_home_path() . DIRECTORY_SEPARATOR . $ngg_options['gallerypath'];
		    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		
    if ((isset($_POST['parent_folder'])) && (strlen(trim($_POST['parent_folder'])) > 0))
      $parent_folder_id = trim(stripslashes(strip_tags($_POST['parent_folder'])));
		else
			$parent_folder_id = 0;
    
    if ((isset($_POST['new_gallery_name'])) && (strlen(trim($_POST['new_gallery_name'])) > 0)) {
      $new_gallery_name = trim(stripslashes(strip_tags($_POST['new_gallery_name'])));
			
			$gallery_slug =  strtolower(str_replace(' ', '-', $new_gallery_name));
			
			if(class_exists('C_Gallery_Mapper')) {
				$mapper = C_Gallery_Mapper::get_instance();
				if (($gallery = $mapper->create(array('title'	=>	$new_gallery_name))) && $gallery->save()) {
					$retval = $gallery->id();
				}
			}
			
			if($retval !== false) {
			
				$new_gallery_path = $ng_folder_path . DIRECTORY_SEPARATOR . $gallery_slug;
				$thumbs_path = $ng_folder_path . DIRECTORY_SEPARATOR . $gallery_slug . DIRECTORY_SEPARATOR .  "thumbs";
				if(!file_exists($new_gallery_path)) {
					$retval = mkdir($new_gallery_path, 0755);
					mkdir($thumbs_path, 0755);
				} else {
	        echo __('The gallery already exists.','maxgalleria-media-library');
		      die();
				}
			}
						
			if($retval !== false) {
	      echo __('The gallery was created.','maxgalleria-media-library');				
        $location = 'window.location.href = "' . site_url() . '/wp-admin/admin.php?page=media-library&media-folder=' . $parent_folder_id .'";';
        echo "<script>$location</script>";				
			} else
	      echo __('The gallery could not be created.','maxgalleria-media-library');		
						
		}
		
		die();
		
	}
	
	public function mg_add_to_ng_gallery() {
	
    global $wpdb;
		$image_count = 0;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_gallery_image_ids'])) && (strlen(trim($_POST['serial_gallery_image_ids'])) > 0))
      $serial_gallery_image_ids = trim(stripslashes(strip_tags($_POST['serial_gallery_image_ids'])));
    else
      $serial_gallery_image_ids = "";
    
    $serial_gallery_image_ids = str_replace('"', '', $serial_gallery_image_ids);    
    
    $serial_gallery_image_ids = explode(',', $serial_gallery_image_ids);
        
    if ((isset($_POST['gallery_id'])) && (strlen(trim($_POST['gallery_id'])) > 0))
      $gallery_id = trim(stripslashes(strip_tags($_POST['gallery_id'])));
    else
      $gallery_id = 0;

		if(class_exists('nggdb')) {
			
      require_once ABSPATH . '/wp-content/plugins/nextgen-gallery/products/photocrati_nextgen/modules/ngglegacy/admin/functions.php';	

			$home_path = get_home_path();
			if(substr($home_path, -1) == '/') 
				$home_path = substr($home_path, 0, -1);
			$gallery_location = $home_path . $this->get_ng_gallery_folder($gallery_id);

			foreach( $serial_gallery_image_ids as $attachment_id) {

				//$sql = "select guid, post_excerpt from {$wpdb->prefix}posts where post_type = 'attachment' and ID = $attachment_id";
				$sql = "select pm.meta_value as attached_file, post_excerpt 
from {$wpdb->prefix}posts 
LEFT JOIN wp_postmeta AS pm ON (pm.post_id = wp_posts.ID) 
where post_type = 'attachment' and ID = $attachment_id";
				

				$row = $wpdb->get_row($sql);
				if($row) {
					//$image_location = $this->check_for_attachment_id($row->guid, $attachment_id);
          //$image_location = $this->upload_dir['baseurl'] . '/' . $row->attached_file;
					$baseurl = $this->upload_dir['baseurl'];
					$baseurl = rtrim($baseurl, '/') . '/';
					$image_location = $baseurl . ltrim($row->attached_file, '/');
					
					$image_path = $this->get_absolute_path($image_location);
					
					$alttext = get_post_meta ( $attachment_id, '_wp_attachment_image_alt', true );
					
					$filename = pathinfo($image_location, PATHINFO_BASENAME);					
					
					if($alttext === "")
						$alttext = $filename; 

					$date = date('Y-m-d H:i:s');
					
					$new_nggdb = new nggdb();
					
					$retval = $new_nggdb->add_image( $gallery_id, $filename, $row->post_excerpt, $alttext, false, 0, $date );
					$image_id = $wpdb->insert_id;
					
					if($retval) {
						$destination_name = $gallery_location . DIRECTORY_SEPARATOR . $filename;
						copy($image_path, $destination_name );
						copy($image_path, $destination_name . '_backup' );
            nggAdmin::create_thumbnail($image_id);
	          nggAdmin::import_MetaData($image_id);
					$image_count++;
					} 
				}
			}
		}
	  echo  $image_count . __(' image(s) were added.','maxgalleria-media-library');		
		die();
	}
		
	private function get_ng_gallery_folder($gallery_id) {
		
		global $wpdb;
		
		$sql = "select path from {$wpdb->prefix}ngg_gallery where gid = $gallery_id";
		
		$row = $wpdb->get_row($sql);
		if($row) {
			return $row->path;
		}	
		else
			return "";
	}
	
	public function mgmlp_add_to_gallery() {
		
		global $wpdb;
		    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
    if ((isset($_POST['serial_add_ids'])) && (strlen(trim($_POST['serial_add_ids'])) > 0)) {
      $add_ids = trim(stripslashes(strip_tags($_POST['serial_add_ids'])));
      $add_ids = str_replace('"', '', $add_ids);
      $add_ids = explode(",",$add_ids);
      //$output = print_r($delete_ids, true);
    }  
    else
      $add_ids = '';
		
		$output = "";
            
//    foreach( $add_ids as $add_id) {
//		
//      $sql = "select guid, post_title from {$wpdb->prefix}posts where ID = $add_id";    
//      
//      $row = $wpdb->get_row($sql); 
//			
//			if($row) {
//			
//		    $image_location = $this->check_for_attachment_id($row->guid, $add_id);
//				$output .= '<li id="' . $add_id . '">' . PHP_EOL;				
//        $output .= '  <img src="" alt="">' . PHP_EOL;
//        $output .= '  <div class="attachment-name"><span class="image_select"><input type="checkbox" value="206" id="206" class="mgmlp-media"></span>black-business-woman3.jpg</div>' . PHP_EOL;
//				$output .= '</li>' . PHP_EOL;
//				
//			}
//	  }
		
		echo $output;
		
		die();
	}
	
	public function mlpp_settings() {
		
		$license 	= get_option( 'mg_edd_mlpp_license_key' );
		$status 	= get_option( 'mg_edd_mlpp_license_status' );
		$response = get_option( 'mg_edd_mlpp_license_response' );				
		error_log("Last Response $response");
		?>	
		
		<div styel="clear:both"></div>
		<h4><?php _e('Plugin License Options', 'maxgalleria-media-library'); ?></h4>
		<form method="post" action="options.php">

			<?php settings_fields('edd_mlpp_license'); ?>

			<table>
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key', 'maxgalleria-media-library'); ?>
						</th>
						<td>
							<input id="edd_mlpp_license_key" name="mg_edd_mlpp_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="mg_edd_mlpp_license_key"><?php _e('Enter your license key', 'maxgalleria-media-library'); ?></label>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php // _e('Activate License', 'maxgalleria-media-library'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active'); ?></span>
									<?php wp_nonce_field( 'edd_mlpp_nonce', 'edd_mlpp_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_mlpp_license_deactivate" value="<?php _e('Deactivate License', 'maxgalleria-media-library'); ?>"/>
								<?php } else {
									wp_nonce_field( 'edd_mlpp_nonce', 'edd_mlpp_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_mlpp_license_activate" value="<?php _e('Activate License', 'maxgalleria-media-library'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
		
		<?php 
	}
	
	public function regen_mlp_thumbnails() {
		
    global $wpdb;
        
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		    
    if ((isset($_POST['serial_image_ids'])) && (strlen(trim($_POST['serial_image_ids'])) > 0))
      $image_ids = trim(stripslashes(strip_tags($_POST['serial_image_ids'])));
    else
      $image_ids = "";
				        
    $image_ids = str_replace('"', '', $image_ids);    
    
    $image_ids = explode(',', $image_ids);
		
		$counter = 0;
		
		foreach( $image_ids as $image_id) {
			
			// check if the file is an image
			if(wp_attachment_is_image($image_id)) {
			
				// get the image path
				$image_path = get_attached_file( $image_id );

				// get the name of the file
				$base_name = wp_basename( $image_path );

				// set the time limit o five minutes
				@set_time_limit( 300 ); 

				// regenerate the thumbnails
				$metadata = wp_generate_attachment_metadata( $image_id, $image_path );

				// check for errors
				if (is_wp_error($metadata)) {
					echo "Error: $base_name, " . $metadata->get_error_message();
					continue;
				}	
				if(empty($metadata)) {
					echo "Unknown error with $base_name";
					continue;
				}	

				// update the meta data
				wp_update_attachment_metadata( $image_id, $metadata );
				$counter++;

			}		
		}
				
		echo "Thumbnails have been regenerated for $counter image(s)";
		
		die();
	}
		
		public function regenerate_interface() {
		global $wpdb;

		?>

      <div id="message" class="updated fade" style="display:none"></div>

      <div id="wp-media-grid" class="wrap">                
        <!--empty h2 for where WP notices will appear--> 
				<h1></h1>
        <div class="media-plus-toolbar"><div class="media-toolbar-secondary">  
            
				<div id="mgmlp-header">		
					<div id='mgmlp-title-area'>
						<h2 class='mgmlp-title'><?php _e('Media Library Plus Pro - Regenerate Thumbnails', 'maxgalleria-media-library' ); ?></h2>  

					</div> <!-- mgmlp-title-area -->
					<div id="new-top-promo">
						<a id="mf-top-logo" target="_blank" href="http://maxfoundry.com"><img alt="maxfoundry logo" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/mf-logo.png" width="140" height="25" ></a>
						<p class="center-text"><?php _e('Makers of', 'maxgalleria-media-library' ); ?> <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></p>
						<p class="center-text-no-ital">Need help? Click here for <a href="https://maxgalleria.com/forums/forum/media-library-plus-pro/" target="_blank">Awesome Support!</a></p>
						<p class="center-text-no-ital">Or Email Us at <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></p>
					</div>
					
				</div><!--mgmlp-header-->
        <div class="clearfix"></div>  


<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! current_user_can( $this->capability ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer(MAXGALLERIA_MEDIA_LIBRARY_NONCE);

			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( $_REQUEST['ids'], ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
					echo '	<p>' . sprintf( __( "Unable to find any images. Are you sure <a href='%s'>some exist</a>?", 'maxgalleria-media-library' ), admin_url( 'upload.php?post_mime_type=image' ) ) . "</p></div>";
					return;
				}

				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}

			echo '	<p>' . __( "Please wait while the thumbnails are regenerated. This may take a while.", 'maxgalleria-media-library' ) . '</p>';

			$count = count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', 'maxgalleria-media-library' ), 'javascript:history.go(-1)' ) : '';
			$text_failures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s', 'maxgalleria-media-library' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=mlp-regenerate-thumbnails&goback=1' ), 'mlp-regenerate-thumbnails' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s', 'maxgalleria-media-library' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>


	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'maxgalleria-media-library' ) ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?php _e( 'Abort Resizing Images', 'maxgalleria-media-library' ) ?>" /></p>

	<h3 class="title"><?php _e( 'Debugging Information', 'maxgalleria-media-library' ) ?></h3>

	<p>
		<?php printf( __( 'Total Images: %s', 'maxgalleria-media-library' ), $count ); ?><br />
		<?php printf( __( 'Images Resized: %s', 'maxgalleria-media-library' ), '<span id="regenthumbs-debug-successcount">0</span>' ); ?><br />
		<?php printf( __( 'Resize Failures: %s', 'maxgalleria-media-library' ), '<span id="regenthumbs-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			$("#regenthumbs-stop").click(function() {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?php echo $this->esc_quotes( __( 'Stopping...', 'maxgalleria-media-library' ) ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo $text_failures; ?>';
				} else {
					rt_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
							response = new Object;
							response.success = false;
							response.error = "<?php printf( esc_js( __( 'The resize request was abnormally terminated (ID %s). This is likely due to the image exceeding available memory or some other type of fatal error.', 'maxgalleria-media-library' ) ), '" + id + "' ); ?>";
						}

						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
<?php
		}

		// No button click? Display the form.
		else {
?>
	<form method="post" action="">
<?php wp_nonce_field(MAXGALLERIA_MEDIA_LIBRARY_NONCE) ?>

	<p><?php printf( __( "Click the button below to regenerate thumbnails for all images in the Media Library. This is helpful if you have added new thumbnail sizes to your site. Existing thumbnails will not be removed to prevent breaking any links.", 'maxgalleria-media-library' ), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php printf( __( "You can regenerate thumbnails for individual images from the Media Library Plus page by checking the box below one or more images and clicking the Regenerate Thumbnails button. The regenerate operation is not reversible but you can always generate the sizes you need by adding additional thumbnail sizes to your theme.", 'regenerate-thumbnails '), admin_url( 'upload.php' ) ); ?></p>


	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php _e( 'Regenerate All Thumbnails', 'maxgalleria-media-library' ) ?>" /></p>

	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'maxgalleria-media-library' ) ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
			</div>
		</div>
	</div>

<?php
	}


	// Process a single image ID (this is an AJAX handler)
	public function ajax_process_image() {
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( __( 'Failed resize: %s is an invalid image ID.', 'maxgalleria-media-library' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! current_user_can( $this->capability ) )
			$this->die_json_error_msg( $image->ID, __( "Your user account doesn't have permission to resize images", 'maxgalleria-media-library' ) );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( __( 'The originally uploaded image file cannot be found at %s', 'maxgalleria-media-library' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, __( 'Unknown failure reason.', 'maxgalleria-media-library' ) );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'maxgalleria-media-library' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}


	// Helper to make a JSON error message
	public function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'maxgalleria-media-library' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}


	// Helper function to escape quotes in strings for use in Javascript
	public function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
	
	public function image_seo() {
		
		?>

					<div id="wp-media-grid" class="wrap">                
						<!--empty h2 for where WP notices will appear--> 
						<h1></h1>
						<div class="media-plus-toolbar"><div class="media-toolbar-secondary">  

						<div id="mgmlp-header">		
							<div id='mgmlp-title-area'>
								<h2 class='mgmlp-title'><?php _e('Media Library Plus Pro - Image SEO', 'maxgalleria-media-library' ); ?></h2>  

							</div> <!-- mgmlp-title-area -->
							<div id="new-top-promo">
								<a id="mf-top-logo" target="_blank" href="http://maxfoundry.com"><img alt="maxfoundry logo" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/mf-logo.png" width="140" height="25" ></a>
								<p class="center-text"><?php _e('Makers of', 'maxgalleria-media-library' ); ?> <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></p>
								<p class="center-text-no-ital">Need help? Click here for <a href="https://maxgalleria.com/forums/forum/media-library-plus-pro/" target="_blank">Awesome Support!</a></p>
								<p class="center-text-no-ital">Or Email Us at <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></p>
							</div>

						</div><!--mgmlp-header-->
						<div class="clearfix"></div>  
	
						<div id="mlp-left-column">
							<p><?php _e('When Image SEO is enabled Media Library Plus automatically adds  ALT and Title attributes with the default settings defined below to all your images as they are uploaded.','maxgalleria-media-library'); ?></p>
							<p><?php _e('You can easily override the Image SEO default settings when you  are uploading new images. When Image SEO is enabled you will see two fields  under the Upload Box when you add a file - Image Title Text and Image ALT Text.  Whatever you type into these fields overrides the default settings for the  current upload or sync operations.','maxgalleria-media-library'); ?></p>
							<p><?php _e('To change the settings on an individual image simply click on  the image and change the settings on the far right.  Save and then back click to return to Media  Library Plus or MLPP.','maxgalleria-media-library'); ?><br>
							<p><?php _e('Image SEO supports two special tags:','maxgalleria-media-library'); ?><br>
							<?php _e('%filename - replaces image file name ( without extension )','maxgalleria-media-library'); ?><br>
							<?php _e('%foldername - replaces image folder name','maxgalleria-media-library'); ?></p>
						
							<?php 
							$defatul_alt = '';
							$default_title = '';
							if($defatul_alt === '')
								$defatul_alt = '%foldername - %filename';
							if($default_title === '')
								$default_title = '%foldername photo';

							$checked = get_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, 'off');						

							?>
							<table id="mlp-image-seo">
								<thead>
									<tr>
										<td colspan="3"><?php _e('Settings','maxgalleria-media-library'); ?></td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php _e('Turn on Image SEO:','maxgalleria-media-library'); ?></td>
										<td><input name="seo-images" id="seo-images" type="checkbox" <?php checked($checked, 'on', true ); ?> </td>
										<td></td>
									</tr>
									<tr>
										<td><?php _e('Image ALT attribute:','maxgalleria-media-library'); ?></td>
										<td><input type="text" value="<?php echo $defatul_alt; ?>" name="default-alt" id="default-alt"></td>
										<td><em><?php _e('example','maxgalleria-media-library'); ?> %foldername - %filename</em></td>									
									</tr>
									<tr>
										<td><?php _e('Image Title attribute:','maxgalleria-media-library'); ?></td>
										<td><input type="text" value="<?php echo $default_title; ?>" name="default-title" id="default-title"></td>
										<td><em><?php _e('example','maxgalleria-media-library'); ?> %filename photo</em></td>									
									</tr>								
									<tr>
										<td colspan="3"><a class="button" id="mlp-update-seo-settings"><?php _e('Update Settings','maxgalleria-media-library'); ?></a></td>									
									</tr>
								</tbody>							
							</table>
							<div id="folder-message"></div>
						</div>    
												
					</div>    
				</div>    
			</div>    


		<?php
		
	}
	
	public function mlp_image_seo_change() {
		
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
		    
    if ((isset($_POST['checked'])) && (strlen(trim($_POST['checked'])) > 0))
      $checked = trim(stripslashes(strip_tags($_POST['checked'])));
    else
      $checked = "off";
		
    if ((isset($_POST['default_alt'])) && (strlen(trim($_POST['default_alt'])) > 0))
      $default_alt = trim(stripslashes(strip_tags($_POST['default_alt'])));
    else
      $default_alt = "";
		
    if ((isset($_POST['default_title'])) && (strlen(trim($_POST['default_title'])) > 0))
      $default_title = trim(stripslashes(strip_tags($_POST['default_title'])));
    else
      $default_title = "";
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_IMAGE_SEO, $checked );		
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_ATL_DEFAULT, $default_alt );		
		
    update_option(MAXGALLERIA_MEDIA_LIBRARY_TITLE_DEFAULT, $default_title );		
		
		echo __('The Image SEO setting have been updated ','maxgalleria-media-library');
				
		die();
		
		
	}
		
	public function get_browser() {
		// http://www.php.net/manual/en/function.get-browser.php#101125.
		// Cleaned up a bit, but overall it's the same.

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browser_name = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		// First get the platform
		if (preg_match('/linux/i', $user_agent)) {
			$platform = 'Linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
			$platform = 'Mac';
		}
		elseif (preg_match('/windows|win32/i', $user_agent)) {
			$platform = 'Windows';
		}
		
		// Next get the name of the user agent yes seperately and for good reason
		if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
			$browser_name = 'Internet Explorer';
			$browser_name_short = "MSIE";
		}
		elseif (preg_match('/Firefox/i', $user_agent)) {
			$browser_name = 'Mozilla Firefox';
			$browser_name_short = "Firefox";
		}
		elseif (preg_match('/Chrome/i', $user_agent)) {
			$browser_name = 'Google Chrome';
			$browser_name_short = "Chrome";
		}
		elseif (preg_match('/Safari/i', $user_agent)) {
			$browser_name = 'Apple Safari';
			$browser_name_short = "Safari";
		}
		elseif (preg_match('/Opera/i', $user_agent)) {
			$browser_name = 'Opera';
			$browser_name_short = "Opera";
		}
		elseif (preg_match('/Netscape/i', $user_agent)) {
			$browser_name = 'Netscape';
			$browser_name_short = "Netscape";
		}
		
		// Finally get the correct version number
		$known = array('Version', $browser_name_short, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $user_agent, $matches)) {
			// We have no matching number just continue
		}
		
		// See how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			// We will have two since we are not using 'other' argument yet
			// See if version is before or after the name
			if (strripos($user_agent, "Version") < strripos($user_agent, $browser_name_short)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		// Check if we have a number
		if ($version == null || $version == "") { $version = "?"; }
		
		return array(
			'user_agent' => $user_agent,
			'name' => $browser_name,
			'version' => $version,
			'platform' => $platform,
			'pattern' => $pattern
		);
	}
	
	public function mlp_support() {

		$theme = wp_get_theme();
    $browser = $this->get_browser();
		
		?>

					<div id="wp-media-grid" class="wrap">                
						<!--empty h2 for where WP notices will appear--> 
						<h1></h1>
						<div class="media-plus-toolbar"><div class="media-toolbar-secondary">  

						<div id="mgmlp-header">		
							<div id='mgmlp-title-area'>
							  <h2 class='mgmlp-title'><?php _e('Media Library Plus - Support - System Information', 'maxgalleria-media-library' ); ?> </h2>    

							</div> <!-- mgmlp-title-area -->
							<div id="new-top-promo">
								<a id="mf-top-logo" target="_blank" href="http://maxfoundry.com"><img alt="maxfoundry logo" src="<?php echo MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL ?>/images/mf-logo.png" width="140" height="25" ></a>
								<p class="center-text"><?php _e('Makers of', 'maxgalleria-media-library' ); ?> <a target="_blank"  href="http://maxbuttons.com/">MaxButtons</a>, <a target="_blank" href="http://maxbuttons.com/product-category/button-packs/">WordPress Buttons</a> <?php _e('and', 'maxgalleria-media-library' ); ?> <a target="_blank" href="http://maxgalleria.com/">MaxGalleria</a></p>
								<p class="center-text-no-ital">Need help? Click here for <a href="https://maxgalleria.com/forums/forum/media-library-plus-pro/" target="_blank">Awesome Support!</a></p>
								<p class="center-text-no-ital">Or Email Us at <a href="mailto:support@maxfoundry.com">support@maxfoundry.com</a></p>
							</div>

						</div><!--mgmlp-header-->
						<div class="clearfix"></div>  
						<div id="support-info">
							<h4><?php _e('You may be asked to provide the information below to help troubleshoot your issue.', 'maxgalleria-media-library') ?></h4>
							<textarea class="system-info" readonly="readonly" wrap="off">
----- Begin System Info -----

WordPress Version:      <?php echo get_bloginfo('version') . "\n"; ?>
PHP Version:            <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:          <?php 
														global $wpdb;
														$mysql_version = $wpdb->db_version();

														echo $mysql_version . "\n"; 
?>
Web Server:             <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress URL:          <?php echo get_bloginfo('wpurl') . "\n"; ?>
Home URL:               <?php echo get_bloginfo('url') . "\n"; ?>

PHP cURL Support:       <?php echo (function_exists('curl_init')) ? 'Yes' . "\n" : 'No' . "\n"; ?>
PHP GD Support:         <?php echo (function_exists('gd_info')) ? 'Yes' . "\n" : 'No' . "\n"; ?>
PHP Memory Limit:       <?php echo ini_get('memory_limit') . "\n"; ?>
PHP Post Max Size:      <?php echo ini_get('post_max_size') . "\n"; ?>
PHP Upload Max Size:    <?php echo ini_get('upload_max_filesize') . "\n"; ?>

WP_DEBUG:               <?php echo defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
Multi-Site Active:      <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

Operating System:       <?php echo $browser['platform'] . "\n"; ?>
Browser:                <?php echo $browser['name'] . ' ' . $browser['version'] . "\n"; ?>
User Agent:             <?php echo $browser['user_agent'] . "\n"; ?>

Active Theme:
- <?php echo $theme->get('Name') ?> <?php echo $theme->get('Version') . "\n"; ?>
  <?php echo $theme->get('ThemeURI') . "\n"; ?>

Active Plugins:
<?php
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

foreach ($plugins as $plugin_path => $plugin) {
	
	// Only show active plugins
	if (in_array($plugin_path, $active_plugins)) {
		echo '- ' . $plugin['Name'] . ' ' . $plugin['Version'] . "\n";
	
		if (isset($plugin['PluginURI'])) {
			echo '  ' . $plugin['PluginURI'] . "\n";
		}
		
		echo "\n";
	}
}
?>
----- End System Info -----
						</textarea>

							
						</div>												
					</div>    
				</div>    
			</div>    

		<?php

	}
	
	public  function mlp_remove_slashes() {

		global $wpdb;
			
    $sql = "select ID, pm.meta_value, pm.meta_id
from {$wpdb->prefix}posts 
LEFT JOIN {$wpdb->prefix}postmeta AS pm ON pm.post_id = {$wpdb->prefix}posts.ID
where post_type = 'attachment' 
or post_type = '" . MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE . "'
and pm.meta_key = '_wp_attached_file'
group by ID
order by meta_id";


		//echo $sql;

		$rows = $wpdb->get_results($sql);

		if($rows) {
			foreach($rows as $row) {
				if($row->meta_value !== '') {
					if( $row->meta_value[0] == "/") {
						$new_meta = $row->meta_value;
						$new_meta = ltrim($new_meta, '/');
						update_post_meta($row->ID, '_wp_attached_file', $new_meta);							
					}	
				}
			}
		}	
	}
	
	public function hide_maxgalleria_media() {
		
    global $wpdb;
    
    if ( !wp_verify_nonce( $_POST['nonce'], MAXGALLERIA_MEDIA_LIBRARY_NONCE)) {
      exit(__('missing nonce!','maxgalleria-media-library'));
    } 
    
		if ((isset($_POST['folder_id'])) && (strlen(trim($_POST['folder_id'])) > 0))
      $folder_id = trim(stripslashes(strip_tags($_POST['folder_id'])));
    else
      $folder_id = "";
			
		if($folder_id !== '') {
			
			$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;			
			$parent_folder =  $this->get_parent($folder_id);
			
		  $sql = "select meta_value as attached_file
from {$wpdb->prefix}postmeta
where post_id = $folder_id
and meta_key = '_wp_attached_file';";
	
			$row = $wpdb->get_row($sql);
			if($row) {
				
				$basedir = $this->upload_dir['basedir'];
				$basedir = rtrim($basedir, '/') . '/';
				$skip_folder_file = $basedir . ltrim($row->attached_file, '/') . DIRECTORY_SEPARATOR . "mlpp-hidden";
				file_put_contents($skip_folder_file, '');
				
				$this->remove_children($folder_id);
				$del_post = array('post_id' => $folder_id);                        
				wp_delete_post($folder_id, true); //delete the post record
				$wpdb->delete( $folder_table, $del_post ); // delete the folder table record
								
			}
			
			$location = 'window.location.href = "' . home_url() . '/wp-admin/admin.php?page=media-library&media-folder=' . $parent_folder .'";';
			echo __('The selected folder, subfolders and thier files have been hidden.','maxgalleria-media-library');
			echo "<script>$location</script>";
					
		}	
		
		die();
	}
		
	private function remove_children($folder_id) {
		
    global $wpdb;
		
		if($folder_id !== 0) {
			
			$folder_table = $wpdb->prefix . MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE;
							
		  $sql = "select post_id
from $folder_table 
where folder_id = $folder_id";
		
			$rows = $wpdb->get_results($sql);
			if($rows) {
				foreach($rows as $row) {

					$this->remove_children($row->post_id);
				  $del_post = array('post_id' => $row->post_id);                        
					wp_delete_post($row->post_id, true); //delete the post record
					$wpdb->delete( $folder_table, $del_post ); // delete the folder table record
								
				}
			}	
		}	
	}
	
	public function get_file_thumbnail($ext) {
		switch ($ext) {

			case 'psd':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/psd.png";
				break;			
			
			// spread sheet
			case 'xlsx':
			case 'xlsm':
			case 'xlsb':
			case 'xltx':
			case 'xltm':
			case 'xlam':
			case 'ods':
			case 'numbers':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/xls.png";
				break;
			
			// video formats
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'wmx':
			case 'wm':
			case 'avi':
			case 'divx':
			case 'flv':
			case 'mov':
			case 'qt':
			case 'mpeg':
			case 'mpg':
			case 'mpe':
			case 'mp4':
			case 'm4v':
			case 'ogv':
			case 'webm':
			case 'mkv':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/video.png";
				break;
			
			// text formats
			case 'txt':
			case 'asc':
			case 'c':
			case 'cc':
			case 'h':
			case 'js':
			case 'cpp':
			case 'csv':
			case 'tsv':
			case 'ics':
			case 'rtx':
			case 'css':
			case 'htm':
			case 'html':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/txt.png";
				break;

			case 'mp3':
			case 'm4a':
			case 'm4b':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'ogg':
			case 'oga':
			case 'mid':
			case 'midi':
			case 'wma':
			case 'wax':
			case 'mka':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/audio.png";
				break;
			
			// archive formats
			case '7z':
			case 'rar':
			case 'gz':
			case 'gzip':
			case 'zip':
			case 'tar':
			case 'swf':
			case 'class':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/arch.png";
				break;

			// doc files
			case 'doc':
			case 'odt':
			case 'rtf':
			case 'wri':
			case 'mdb':
			case 'mpp':
			case 'docx':
			case 'docm':
			case 'dotx':
			case 'dotm':
			case 'wp':
			case 'wpd':
			case 'pages':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/doc.png";
				break;
			
			case 'pdf':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/pdf.png";
				break;
						
			// power point
			case 'pptx':
			case 'pptm':
			case 'ppsx':
			case 'ppsm':
			case 'potx':
			case 'potm':
			case 'ppam':
			case 'sldx':
			case 'sldm':
			case 'odp':
			case 'key':
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/ppt.png";
				break;
						
			default:
				$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file-types/default.png";
				break;
				
		}
		return $thumbnail;
	}
							  
}

$maxgalleria_media_library_pro = new MaxGalleriaMediaLibPro();

?>