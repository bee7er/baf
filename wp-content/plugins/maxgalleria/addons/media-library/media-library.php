<?php
/*
Add On: Media Library Plus
Description: Gives you the ability to adds folders and move files in the WordPress Media Library.
Author: Max Foundry
Author URI: http://maxfoundry.com

Copyright 2015 Max Foundry, LLC (http://maxfoundry.com)
*/

class MaxGalleriaMedia {

  public $upload_dir;
  public $wp_version;
  //public $customdir;
	public $addon_key;
	public $addon_name;
	public $addon_type;
	public $addon_subtype;
	public $addon_settings;
	public $addon_image;
	public $addon_output;
  public $theme_mods;
  public $uploads_folder_name;
	public $uploads_folder_name_length;
	public $uploads_folder_ID;
  

  public function __construct() {
    
		$this->addon_key = 'media-library';
		$this->addon_name = __('Media Library', 'maxgalleria');
		$this->addon_type = 'library';
		$this->addon_subtype = 'image';
		$this->addon_settings = MAXGALLERIA_PLUGIN_DIR . '/addons/media-library/media-library-settings.php';
		//$this->addon_image = MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/images/video-tiles.png';
		$this->addon_image = '';
		$this->addon_output = array($this, 'get_output');
    
    
		$this->set_global_constants();
		//$this->check_for_update();
		//$this->set_activation_hooks();
		$this->initialize_properties();
		$this->setup_hooks();       
		//$this->upload_dir = wp_upload_dir();  
    //$this->wp_version = get_bloginfo('version'); 
    
    //convert theme mods into an array
    //$theme_mods = get_theme_mods();
    //$this->theme_mods = json_decode(json_encode($theme_mods), true);    
        
    //add_option( MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER, '0' );    
	}

	public function set_global_constants() {	
		//define('MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE', 'maxgalleria_media_library_ignore_notice');
		//define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR', MAXGALLERIA_PLUGIN_DIR . '/addons/media-library' );
		//define('MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL', MAXGALLERIA_PLUGIN_URL . '/addons/media-library');
    //define("MAXGALLERIA_MEDIA_LIBRARY_NONCE", "mgmlp_nonce");
    //define("MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE", "mgmlp_media_folder");
    //define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME", "mgmlp_upload_folder_name");
    //define("MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID", "mgmlp_upload_folder_id");
    //define("MAXGALLERIA_MEDIA_LIBRARY_FOLDER_TABLE", "mgmlp_folders");
    //define("MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER", "mgmlp_sort_order");
		//if(!defined('NEW_MEDIA_LIBRARY_VERSION'))
    //  define("NEW_MEDIA_LIBRARY_VERSION", "4.0.0");    
		
		// Bring in all the actions and filters
		//require_once MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_DIR . '/media-library-hooks.php';
	}
  
 	public function set_activation_hooks() {
    //register_activation_hook(__FILE__, array($this,'add_folder_table'));    
		//register_activation_hook(__FILE__, array($this, 'do_activation'));
		//register_deactivation_hook(__FILE__, array($this, 'do_deactivation'));
	}
  
//  public function do_activation($network_wide) {
//		if ($network_wide) {
//			$this->call_function_for_each_site(array($this, 'activate'));
//		}
//		else {
//			$this->activate();
//		}
//	}
	
//	public function do_deactivation($network_wide) {	
//		if ($network_wide) {
//			$this->call_function_for_each_site(array($this, 'deactivate'));
//		}
//		else {
//			$this->deactivate();
//		}
//	}
  
	public function activate($clear_status) {
		//update_option(MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY, MAXGALLERIA_MEDIA_LIBRARY_VERSION_NUM);
    //update_option('uploads_use_yearmonth_folders', 1);    
    $this->add_folder_table();
    if ( 'impossible_default_value_1234' === get_option( MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME, 'impossible_default_value_1234' ) ) {
      $this->scan_attachments(false);
      $this->admin_check_for_new_folders(true);
      update_option(MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX, true);
    } else {
      if($clear_status) {
        $this->scan_attachments($clear_status);
        $this->admin_check_for_new_folders(true);
        update_option(MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX, true);
      }  
    }
				        
    if ( ! wp_next_scheduled( 'new_folder_check' ) )
      wp_schedule_event( time(), 'daily', 'new_folder_check' );
    
	}
	
  public function deactivate() {
    wp_clear_scheduled_hook('new_folder_check');
	}
    
  public function enqueue_admin_print_styles() {		
//    if(isset($_REQUEST['page'])) {
//        if($_REQUEST['page'] === 'media-library' || $_REQUEST['page'] === 'search-library') {
//          wp_enqueue_style('thickbox');
//          wp_enqueue_style('maxgalleria-media-library', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/media-library.css');
//          //wp_enqueue_style('foundation', MAXGALLERIA_PLUGIN_URL . '/libs/foundation/foundation.min.css');
//      }
//    }
	}
  
  public function enqueue_admin_print_scripts() {
    if(isset($_REQUEST['page'])) {
      if($_REQUEST['page'] === 'media-library') {
        wp_register_script( 'loader-folders', MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/mgmlp-loader.js', array( 'jquery' ), '', true );

        wp_localize_script( 'loader-folders', 'mgmlp_ajax', 
              array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
                     'nonce'=> wp_create_nonce(MAXGALLERIA_MEDIA_LIBRARY_NONCE))
                   ); 

        wp_enqueue_script('loader-folders');
      }
    }
  }
 
	public function initialize_properties() {
		require_once 'media-library-options.php';
	}
  
  public function setup_hooks() {
		//add_action('init', array($this, 'load_textdomain'));
	  //add_action('init', array($this, 'register_mgmlp_post_type'));
	  //add_action('admin_init', array($this, 'ignore_notice'));
		//add_action('admin_notices', array($this, 'show_admin_notice'));
		//add_action('admin_print_styles', array($this, 'enqueue_admin_print_styles'));
		//add_action('admin_print_scripts', array($this, 'enqueue_admin_print_scripts'));
    //add_action('admin_menu', array($this, 'setup_mg_media_plus'));
    
//		add_action('wp_ajax_save_media_library_settings', array($this, 'save_media_library_settings'));
//		add_action('wp_ajax_nopriv_save_media_library_settings', array($this, 'save_media_library_settings'));
//            
//    add_action('wp_ajax_nopriv_create_new_folder', array($this, 'create_new_folder'));
//    add_action('wp_ajax_create_new_folder', array($this, 'create_new_folder'));
//    
//    add_action('wp_ajax_nopriv_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
//    add_action('wp_ajax_delete_maxgalleria_media', array($this, 'delete_maxgalleria_media'));
//    
//    add_action('wp_ajax_nopriv_upload_attachment', array($this, 'upload_attachment'));
//    add_action('wp_ajax_upload_attachment', array($this, 'upload_attachment'));
//    
//    add_action('wp_ajax_nopriv_copy_media', array($this, 'copy_media'));
//    add_action('wp_ajax_copy_media', array($this, 'copy_media'));
//        
//    add_action('wp_ajax_nopriv_move_media', array($this, 'move_media'));
//    add_action('wp_ajax_move_media', array($this, 'move_media'));
//    
//    add_action('wp_ajax_nopriv_add_to_max_gallery', array($this, 'add_to_max_gallery'));
//    add_action('wp_ajax_add_to_max_gallery', array($this, 'add_to_max_gallery'));
//    
//    add_action('wp_ajax_nopriv_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
//    add_action('wp_ajax_maxgalleria_rename_image', array($this, 'maxgalleria_rename_image'));
//        
//    add_action('wp_ajax_nopriv_sort_contents', array($this, 'sort_contents'));
//    add_action('wp_ajax_sort_contents', array($this, 'sort_contents'));
//        
//    add_action( 'new_folder_check', array($this,'admin_check_for_new_folders'));
//    
//    add_action( 'add_attachment', array($this,'add_attachment_to_folder'));
//    
//    add_action( 'delete_attachment', array($this,'delete_folder_attachment'));
//		
//    add_action('wp_ajax_nopriv_max_sync_contents', array($this, 'max_sync_contents'));
//    add_action('wp_ajax_max_sync_contents', array($this, 'max_sync_contents'));		
		    
    //register_deactivation_hook( __FILE__, array($this, 'run_on_deactivate') );
        
    //register_activation_hook(__FILE__, array($this,'add_folder_table'));    
                                  
  }
  
  public function save_media_library_settings() {
		$options = new MaxGalleriaMediaOptions();
		
		if (isset($_POST) && check_admin_referer($options->nonce_save_media_library_settings['action'], $options->nonce_save_media_library_settings['name'])) {
			global $maxgalleria;
			$message = '';
      $activate_status = false;
      $clear_status = false;
            
			foreach ($_POST as $key => $value) {
				if ($maxgalleria->common->string_starts_with($key, 'maxgallery_')) {
					update_option($key, $value);
          if($key === 'maxgallery_media_library_default') {
            if($value === 'on')
              $activate_status = true;
          }          
          if($key === 'maxgallery_media_librarys_clear') {
            if($value === 'on')
              $clear_status = true;
          }          
				}
			}
      if($activate_status === true) 
        $this->activate($clear_status);
      else  
        $this->deactivate();            
			
			$message = 'success';
			
			echo $message;
			die();
		}
    
  }
	    
}

?>