<?php
class MaxGalleriaVideoTiles {
	public $addon_key;
	public $addon_name;
	public $addon_type;
	public $addon_subtype;
	public $addon_settings;
	public $addon_image;
	public $addon_output;

	public function __construct() {
		$this->addon_key = 'video-tiles';
		$this->addon_name = __('Video Tiles', 'maxgalleria');
		$this->addon_type = 'template';
		$this->addon_subtype = 'video';
		$this->addon_settings = MAXGALLERIA_PLUGIN_DIR . '/addons/templates/video-tiles/video-tiles-settings.php';
		$this->addon_image = MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/images/video-tiles.png';
		$this->addon_output = array($this, 'get_output');
		
		require_once 'video-tiles-options.php';
		
		add_action('save_post', array($this, 'save_gallery_options'));
		add_action('maxgalleria_template_options', array($this, 'show_template_options'));
		add_action('wp_ajax_save_video_tiles_defaults', array($this, 'save_video_tiles_defaults'));
		add_action('wp_ajax_nopriv_save_video_tiles_defaults', array($this, 'save_video_tiles_defaults'));
	}
	
	public function save_gallery_options() {
		global $post;

		if (isset($post)) {
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return $post->ID;
			}

			if (!current_user_can('edit_post', $post->ID)) {
				return $post->ID;
			}
			
			$options = new MaxGalleriaVideoTilesOptions($post->ID);
			$options->save_options();
		}
	}
	
	public function show_template_options() {
		global $post;
		$options = new MaxGalleriaVideoTilesOptions($post->ID);
		
		if ($options->get_template() == 'video-tiles') {
			require_once 'video-tiles-meta.php';
		}
	}
	
	public function save_video_tiles_defaults() {
		$options = new MaxGalleriaVideoTilesOptions();
		
		if (isset($_POST) && check_admin_referer($options->nonce_save_video_tiles_defaults['action'], $options->nonce_save_video_tiles_defaults['name'])) {
			global $maxgalleria;
			$message = '';

			foreach ($_POST as $key => $value) {
				if ($maxgalleria->common->string_starts_with($key, 'maxgallery_')) {
          if($key === $options->arrow_markup_default_key)
            $value = stripslashes($value);
          if($key === $options->counter_markup_default_key)
            $value = stripslashes($value);
					update_option($key, $value);
				}
			}
			
			$message = 'success';
			
			echo $message;
			die();
		}
	}
	
	public function enqueue_styles($options) {
		// Check to add lightbox styles
		if ($options->get_thumb_click() == 'lightbox') {
			//$lightbox_stylesheet = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_LIGHTBOX_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/libs/simplemodal/simplemodal.css');
			//wp_enqueue_style('maxgalleria-simplemodal', $lightbox_stylesheet);
      
			$lightbox_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/magnific-popup.css');
			wp_enqueue_style('maxgalleria-magnific', $lightbox_stylesheet);
      
		}
		
		// The main styles for this template
		$main_stylesheet = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_MAIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/video-tiles.css');
		wp_enqueue_style('maxgalleria-video-tiles', $main_stylesheet);
		
		// Load skin style
		$skin = $options->get_skin();
		$skin_stylesheet = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_SKIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/skins/' . $skin . '.css', $skin);
		wp_enqueue_style('maxgalleria-video-tiles-skin-' . $skin, $skin_stylesheet);
		
		$mg_style_file = get_post_meta($options->gallery_id, "mg-css-file", true );
		if($mg_style_file !== '') {
		  wp_enqueue_style('maxgalleria-video-tiles-style-' . $options->gallery_id, $mg_style_file);			
		}
		
		// Check to load custom styles
		if ($options->get_custom_styles_enabled() == 'on' && $options->get_custom_styles_url() != '') {
			wp_enqueue_style('maxgalleria-video-tiles-custom', $options->get_custom_styles_url());
		}
	}

	public function enqueue_scripts($options) {
		wp_enqueue_script('jquery');
		
		if ($options->get_thumb_click() == 'lightbox') {
			//$lightbox_script = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_LIGHTBOX_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/simplemodal/jquery.simplemodal-1.4.3.min.js');
			//wp_enqueue_script('maxgalleria-simplemodal', $lightbox_script, array('jquery'));
      
      $lightbox_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/jquery.magnific-popup.js');
      wp_enqueue_script('maxgalleria-magnific', $lightbox_script, array('jquery'));
      			
			$main_script = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_MAIN_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/video-tiles.js');
			wp_enqueue_script('maxgalleria-video-tiles', $main_script, array('jquery'));
		}
		
		// Check to load custom scripts
		if ($options->get_custom_scripts_enabled() == 'on' && $options->get_custom_scripts_url() != '') {
			wp_enqueue_script('maxgalleria-video-tiles-custom', $options->get_custom_scripts_url(), array('jquery'));
		}
	}
	
	public function get_thumb_image($options, $attachment) {
		global $maxgalleria;

		$thumb_shape = $options->get_thumb_shape();
		$thumb_columns = $options->get_thumb_columns();
		
		return $maxgalleria->image_gallery->get_thumb_image($attachment, $thumb_shape, $thumb_columns);
	}
	
	public function get_output($gallery, $attachments) {
		$options = new MaxGalleriaVideoTilesOptions($gallery->ID);

		do_action(MAXGALLERIA_ACTION_VIDEO_TILES_BEFORE_ENQUEUE_STYLES, $options);
		$this->enqueue_styles($options);
		do_action(MAXGALLERIA_ACTION_VIDEO_TILES_AFTER_ENQUEUE_STYLES, $options);
		
		do_action(MAXGALLERIA_ACTION_VIDEO_TILES_BEFORE_ENQUEUE_SCRIPTS, $options);
		$this->enqueue_scripts($options);
		do_action(MAXGALLERIA_ACTION_VIDEO_TILES_AFTER_ENQUEUE_SCRIPTS, $options);
		
		$output = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_BEFORE_GALLERY_OUTPUT, '', $gallery, $attachments, $options);
		$output .= '<div id="maxgallery-' . $gallery->ID . '" class="mg-video-tiles ' . $options->get_skin() . '">';
		
		if ($options->get_description_enabled() == 'on' && $options->get_description_position() == 'above') {
			if ($options->get_description_text() != '') {
				$output .= '<p class="mg-description">' . $options->get_description_text() . '</p>';
			}
		}
    
    // check for dfactory selector
    if($options->get_dfactory_lightbox() == 'on') {
      $dfactory_settings = get_option('responsive_lightbox_settings');
      if(!empty($dfactory_settings)) 
        $reponsive_lb_settings = $dfactory_settings['selector'];        
      else
        $reponsive_lb_settings = "";
    }
    		
		$output .= '	<div class="mg-videos">';

		foreach ($attachments as $attachment) {
			$excluded = get_post_meta($attachment->ID, 'maxgallery_attachment_video_exclude', true);
		
			if (!$excluded) {
				//$video_url = str_replace('https://', 'http://', get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true));
				$video_url = get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true);
				$enable_related_videos = get_post_meta($attachment->ID, 'maxgallery_attachment_video_enable_related_videos', true);
				$enable_hd_playback = get_post_meta($attachment->ID, 'maxgallery_attachment_video_enable_hd_playback', true);
				
				// Initialize the embed code so it can be passed to the filter below to get populated
				$embed_code = '';
				
				$output .= '<div data-video-id="' . $attachment->ID . '" style="display: none;">';
				$output .= '	<div align="center">';
				
        $embed_code = apply_filters(MAXGALLERIA_FILTER_VIDEO_EMBED_CODE, $embed_code, $video_url, $enable_related_videos, $enable_hd_playback);
        $output .= $embed_code;
        				
				$output .= '	</div>';
				$output .= '</div>';
			}
		}

		$output .= '	</div>';
		$output .= '	<div class="mg-thumbs ' . $options->get_thumb_columns_class() . '">';
		$output .= '		<ul>';

		foreach ($attachments as $attachment) {
			$excluded = get_post_meta($attachment->ID, 'maxgallery_attachment_video_exclude', true);
			if (!$excluded) {
				$title = $attachment->post_title;
				$caption = $attachment->post_excerpt; // Used for the thumb caption, if enabled
				$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
				$href = get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true);
				$image_class = $options->get_thumb_image_class();
				$image_container_class = $options->get_thumb_image_container_class();
				$image_rel = $options->get_thumb_image_rel_attribute();
				$target = '';
				
//				if ($options->get_thumb_click() == 'lightbox') {
//					$href = '#';
//				}
				
				if ($options->get_thumb_click_new_window() == 'on') {
					$target = '_blank';
				}
				
				$thumb_image = $this->get_thumb_image($options, $attachment);
				$thumb_image_element = '<img class="' . $image_class . '" src="' . $thumb_image['url'] . '" width="' . $thumb_image['width'] . '" height="' . $thumb_image['height'] . '" alt="' . esc_attr($alt) . '" title="' . esc_attr($title) . '" />';
				        
				$output .= '<li>';
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'above') {
					$output .= '	<p class="caption above">' . $caption . '</p>';
				}
				        
        if($options->get_dfactory_lightbox() == 'on') 
				  $output .= '	<a data-rel="' . $reponsive_lb_settings . '-0" class="video" data-video-thumb-id="' . $attachment->ID . '" href="' . $href . '" target="' . $target . '" rel="' . $image_rel . '">';
        else
				  $output .= '	<a class="video" data-video-thumb-id="' . $attachment->ID . '" href="' . $href . '" target="' . $target . '" rel="' . $image_rel . '">';
          
				$output .= '		<div class="' . $image_container_class . '">';
				$output .= 				apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_BEFORE_THUMB, '', $options);
				$output .=				apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_THUMB, $thumb_image_element, $thumb_image, $image_class, $alt, $title);
				$output .= 				apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_AFTER_THUMB, '', $options);
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'bottom') {
					$output .= '		<div class="caption-bottom-container">';
					$output .= '			<p class="caption bottom">' . $caption . '</p>';
					$output .= '		</div>';
				}
				
				$output .= '		</div>';
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'below') {
					$output .= '<p class="caption below">' . $caption . '</p>';
				}
				
				$output .= '	</a>';
				$output .= '</li>';
			}
		}

		$output .= '		</ul>';
		$output .= '		<div class="clear"></div>';
		$output .= '	</div>';
		
		if ($options->get_description_enabled() == 'on' && $options->get_description_position() == 'below') {
			if ($options->get_description_text() != '') {
				$output .= '<p class="mg-description">' . $options->get_description_text() . '</p>';
			}
		}
        
		// Hidden elements used by video-tiles.js
		$output .= '	<span style="display: none;" class="hidden-video-tiles-gallery-id">' . $gallery->ID . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-thumb-click">' . $options->get_thumb_click() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-vertical-fit-enabled">' . (($options->get_vertical_fit_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-escape-key-enabled">' . (($options->get_escape_key_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-align-top-enabled">' . (($options->get_align_top_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-hide-close-btn-enabled">' . (($options->get_hide_close_btn_enabled()== 'on') ? 'false' : 'true' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-bg-click-close-enabled">' . (($options->get_bg_click_close_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-fixed-content-position">' . $options->get_fixed_content_position() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-overflow-y">' . $options->get_overflow_y() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-removal-delay">' . $options->get_removal_delay() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-popup-main-class">' . $options->get_main_class()  . '</span>';
  	$output .= '	<span style="display: none;" class="hidden-video-tiles-gallery-enabled">' . (($options->get_gallery_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-arrow-markup">' . $options->get_arrow_markup() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-prev-button-title">' . $options->get_prev_button_title() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-next-button-title">' . $options->get_next_button_title() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-video-tiles-counter-markup">' . $options->get_counter_markup() . '</span>';

		$output .= '</div>';
		$output .= apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_AFTER_GALLERY_OUTPUT, '', $gallery, $attachments, $options);
		
		return apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_GALLERY_OUTPUT, $output, $gallery, $attachments, $options);
	}
}
?>