<?php
class MaxGalleriaNewGallery {
	public function __construct() {
		add_action('wp_ajax_save_new_gallery_type', array($this, 'save_new_gallery_type'));
		add_action('wp_ajax_nopriv_save_new_gallery_type', array($this, 'save_new_gallery_type'));
	}

	public function save_new_gallery_type() {	
		$options = new MaxGalleryOptions($_POST['post_ID']);
		$options->save_post_meta($options->type_key);
		$options->save_post_meta($options->template_key);

		echo $_POST['post_ID'];
		die();
	}
	
	public function show_meta_box_new($post) {
		require_once 'meta/meta-new.php';
	}
}
?>