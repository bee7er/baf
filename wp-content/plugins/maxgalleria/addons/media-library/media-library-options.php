<?php
require_once WP_PLUGIN_DIR . '/maxgalleria/maxgallery-options.php';

class MaxGalleriaMediaOptions extends MaxGalleryOptions  {

	public $nonce_save_media_library_settings = array(
		'action' => 'save_media_library_settings',
		'name' => 'maxgalleria_save_media_library_settings'
	);
  
  public function __construct($post_id = 0) {
		parent::__construct($post_id);

  }
    
	public $media_library_enabled_default = '';
	public $media_library_enabled_default_key = 'maxgallery_media_library_default';
	public $media_library_enabled_key = 'maxgallery_media_librarys_enabled';
  
	public $media_library_clear_default = '';
	public $media_library_clear_default_key = 'maxgallery_media_library_clear_default';
	public $media_library_clear_key = 'maxgallery_media_librarys_clear';
  
  
  public function get_media_library_default() {
		return get_option($this->media_library_enabled_default_key, $this->media_library_enabled_default);
	}
  
   public function get_media_library_clear_default() {
		return get_option($this->media_library_clear_default_key, $this->media_library_clear_default);
	} 

  public function save_options($options = null) {
		//if ($this->get_template() == MAXGALLERIA_MASONRY_KEY) {
			$options = $this->get_options();
			parent::save_options($options);
		//}
	}
  
  	public function get_options() {
		return array(
      $this->media_library_enabled_key,
      $this->media_library_clear_key
  	);
	}

}  
?>
