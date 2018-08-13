<?php
class MaxGalleriaImageTiles {
	public $addon_key;
	public $addon_name;
	public $addon_type;
	public $addon_subtype;
	public $addon_settings;
	public $addon_image;
	public $addon_output;

	public function __construct() {
		$this->addon_key = 'image-tiles';
		$this->addon_name = __('Image Tiles', 'maxgalleria');
		$this->addon_type = 'template';
		$this->addon_subtype = 'image';
		$this->addon_settings = MAXGALLERIA_PLUGIN_DIR . '/addons/templates/image-tiles/image-tiles-settings.php';
		$this->addon_image = MAXGALLERIA_PLUGIN_URL . '/addons/templates/image-tiles/images/image-tiles.png';
		$this->addon_output = array($this, 'get_output');
		
		require_once 'image-tiles-options.php';
		
		add_action('save_post', array($this, 'save_gallery_options'));
		add_action('maxgalleria_template_options', array($this, 'show_template_options'));
		add_action('wp_ajax_save_image_tiles_defaults', array($this, 'save_image_tiles_defaults'));
		add_action('wp_ajax_nopriv_save_image_tiles_defaults', array($this, 'save_image_tiles_defaults'));
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
			
			$options = new MaxGalleriaImageTilesOptions($post->ID);
			$options->save_options();
		}
	}
	
	public function show_template_options() {
		global $post;
		$options = new MaxGalleriaImageTilesOptions($post->ID);
		
		if ($options->get_template() == 'image-tiles') {
			require_once 'image-tiles-meta.php';
		}
	}
	
	public function save_image_tiles_defaults() {
		$options = new MaxGalleriaImageTilesOptions();
		
		if (isset($_POST) && check_admin_referer($options->nonce_save_image_tiles_defaults['action'], $options->nonce_save_image_tiles_defaults['name'])) {
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
		global $post;
		
		// Check to add lightbox styles
		if ($options->get_thumb_click() == 'lightbox' || $options->get_thumb_click() == 'attachment_image_link_lightbox' ) {
			//$lightbox_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/libs/fancybox/jquery.fancybox-1.3.4.css');
			//wp_enqueue_style('maxgalleria-fancybox', $lightbox_stylesheet);
      
			$lightbox_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/magnific-popup.css');
			wp_enqueue_style('maxgalleria-magnific', $lightbox_stylesheet);
		}
		
		// The main styles for this template
		$main_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_MAIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/image-tiles/image-tiles.css');
		wp_enqueue_style('maxgalleria-image-tiles', $main_stylesheet);
		
		// Load skin style
		$skin = $options->get_skin();
		$skin_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_SKIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/image-tiles/skins/' . $skin . '.css', $skin);
		wp_enqueue_style('maxgalleria-image-tiles-skin-' . $skin, $skin_stylesheet);
		
		$mg_style_file = get_post_meta($options->gallery_id, "mg-css-file", true );
		if($mg_style_file !== '') {
		  wp_enqueue_style('maxgalleria-image-tiles-style-' . $options->gallery_id, $mg_style_file);			
		}
				
		// Check to load custom styles
		if ($options->get_custom_styles_enabled() == 'on' && $options->get_custom_styles_url() != '') {
			wp_enqueue_style('maxgalleria-image-tiles-custom', $options->get_custom_styles_url());
		}
						
	}

	public function enqueue_scripts($options) {
		wp_enqueue_script('jquery');
    
		if ($options->get_thumb_click() == 'lightbox' || $options->get_thumb_click() == 'attachment_image_link_lightbox' ) {
      //$lightbox_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/jquery.magnific-popup.min.js');
      $lightbox_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/jquery.magnific-popup.js');
      wp_enqueue_script('maxgalleria-magnific', $lightbox_script, array('jquery'));
            						
			$easing_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_EASING_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/fancybox/jquery.easing-1.3.pack.js');
			wp_enqueue_script('maxgalleria-easing', $easing_script, array('jquery'));
			
			$main_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_MAIN_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/addons/templates/image-tiles/image-tiles.js');
			wp_enqueue_script('maxgalleria-image-tiles', $main_script, array('jquery'));
		}
    
		if ($options->get_lazy_load_enabled() == 'on') {
			$lazyload_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LAZY_LOAD_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/lazyload/jquery.lazyload.min.js');
			wp_enqueue_script('maxgalleria-unveil', $lazyload_script, array('jquery'));
    }    
		
		// Check to load custom scripts
		if ($options->get_custom_scripts_enabled() == 'on' && $options->get_custom_scripts_url() != '') {
			wp_enqueue_script('maxgalleria-image-tiles-custom', $options->get_custom_scripts_url(), array('jquery'));
		}
				
	}

	public function get_lightbox_image($options, $attachment) {
		global $maxgalleria;
		
		$lightbox_image = null;
		
		if ($options->get_lightbox_image_size() == 'custom') {
			$custom_width = $options->get_lightbox_image_size_custom_width();
			$custom_height = $options->get_lightbox_image_size_custom_height();
			$lightbox_image = $maxgalleria->image_gallery->resize_image($attachment, $custom_width, $custom_height, true);
		}
		else {
			$meta = wp_get_attachment_metadata($attachment->ID);
			$url = wp_get_attachment_url($attachment->ID); // replaces $attachment->guid
			if (is_array($meta) && array_key_exists('width', $meta) && array_key_exists('height', $meta)) {
				$lightbox_image = array('url' => $url, 'width' => $meta['width'], 'height' => $meta['height']);
			}
			else {
				$lightbox_image = array('url' => $url, 'width' => '', 'height' => '');
			}
		}
		
		return $lightbox_image;
	}
	
	public function get_thumb_image($options, $attachment) {
		global $maxgalleria;

		$thumb_shape = $options->get_thumb_shape();
		$thumb_columns = $options->get_thumb_columns();
		
		return $maxgalleria->image_gallery->get_thumb_image($attachment, $thumb_shape, $thumb_columns);
	}
	
	public function get_output($gallery, $attachments) {
		global $maxgalleria;
				
		$options = new MaxGalleriaImageTilesOptions($gallery->ID);

		do_action(MAXGALLERIA_ACTION_IMAGE_TILES_BEFORE_ENQUEUE_STYLES, $options);
		$this->enqueue_styles($options);
		do_action(MAXGALLERIA_ACTION_IMAGE_TILES_AFTER_ENQUEUE_STYLES, $options);
		
		do_action(MAXGALLERIA_ACTION_IMAGE_TILES_BEFORE_ENQUEUE_SCRIPTS, $options);
		$this->enqueue_scripts($options);
		do_action(MAXGALLERIA_ACTION_IMAGE_TILES_AFTER_ENQUEUE_SCRIPTS, $options);
		
		$output = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_BEFORE_GALLERY_OUTPUT, '', $gallery, $attachments, $options);
		//$output .= '<div id="maxgallery-' . $gallery->ID . '" class="mg-image-tiles ' . $options->get_style() . '">';
		$output .= '<div id="maxgallery-' . $gallery->ID . '" class="mg-image-tiles ' . $options->get_skin() . '">';		
		
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
   		
		$output .= '	<div class="mg-thumbs ' . $options->get_thumb_columns_class() . '">';
		$output .= '		<ul>';
		
		$uploads = wp_upload_dir();
		
		foreach ($attachments as $attachment) {
			$excluded = get_post_meta($attachment->ID, 'maxgallery_attachment_image_exclude', true);
			if (!$excluded) {
				$title = $attachment->post_title;
				$caption = $attachment->post_excerpt; // Used for the thumb and lightbox captions, if enabled
				$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
				$link = get_post_meta($attachment->ID, 'maxgallery_attachment_image_link', true);
				$image_class = $options->get_thumb_image_class();
				$image_container_class = $options->get_thumb_image_container_class();
				$image_rel = $options->get_thumb_image_rel_attribute();
								
				//$href = $attachment->guid;
        
//        $attach_id_found = strpos($href, 'attachment_id=');
//        if($attach_id_found !== false) {
//          $href = wp_get_attachment_url($attachment->ID);
//        }   
                 
				$target = '';
				
//				if ($options->get_thumb_click() == 'lightbox') {
//					if ($options->get_lightbox_image_size() == 'custom') {
//						$lightbox_image = $this->get_lightbox_image($options, $attachment);
//						$href = $lightbox_image['url'];
//					}
//				}
				
				$href = '';
				if ($options->get_thumb_click() == 'attachment_image_page') {
					$href = get_attachment_link($attachment->ID);
				} else if ($options->get_thumb_click() == 'attachment_image_link_lightbox') {
					$href = get_post_meta($attachment->ID, 'maxgallery_attachment_image_link', true);	
					// if no ling, get the original image
					if($href === '')
					  $href = $maxgalleria->mg_get_attachment_url($attachment, $uploads);
				} else if ($options->get_thumb_click() == 'attachment_image_link') {
					if ($link != '') {
						$href = $link;
					}
				} else {
				  // Default to original, full size image
					$href = $maxgalleria->mg_get_attachment_url($attachment, $uploads);
				}					
				
				if ($options->get_thumb_click_new_window() == 'on') {
					$target = '_blank';
				}
                        
        if($options->get_lightbox_caption_enabled() == 'on')
          $image_caption = $caption;
        else
          $image_caption = "";
				
				$thumb_image = $this->get_thumb_image($options, $attachment);
        //$image_container_class = 'mg-magnific';

		    if ($options->get_lazy_load_enabled() == 'on') { //data-unveil="true" style="opacity: 1;" data-src <noscript>&lt;img src="' . $thumb_image['url'] . '" /&gt;</noscript>
          if(strlen(trim($image_class))>0)
            $image_class .= ' mg_lazy';
          else        
            $image_class = 'mg_lazy';
                  
				  $thumb_image_element = '<img class="lazy ' . $image_class . '" data-original="' . $thumb_image['url'] . '" width="' . $thumb_image['width'] . '" height="' . $thumb_image['height'] . '" alt="' . esc_attr($alt) . '" title="' . esc_attr($title) . '" /><noscript><img src="' . $thumb_image['url'] . '" width="' . $thumb_image['width'] . '" height="' . $thumb_image['height'] . '" alt="' . esc_attr($alt) . '" /></noscript>';
        }  
        else  
				  $thumb_image_element = '<img class="' . $image_class . '" src="' . $thumb_image['url'] . '" width="' . $thumb_image['width'] . '" height="' . $thumb_image['height'] . '" alt="' . esc_attr($alt) . '" title="' . esc_attr($title) . '" />';
				
//				$output .= "<p>$title</p>";
//        
				$output .= '<li>';
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'above') {
					$output .= '	<p class="caption above">' . $caption . '</p>';
				}
																												
        if($options->get_thumb_click() !== 'no_link') {
		      if ($options->get_thumb_click() === 'lightbox' || $options->get_thumb_click() === 'attachment_image_link_lightbox' )
            $output .= "	<a class='mg-magnific' href='" . $href . "' target='" . $target . "' rel='$image_rel' title='" . esc_attr($image_caption) . "'>";
          else {
            if($options->get_dfactory_lightbox() == 'on') 
              $output .= "	<a  data-rel='" . $reponsive_lb_settings ."-0' href='" . $href . "' target='" . $target . "' rel='$image_rel' title='" . esc_attr($image_caption) . "'>";
            else
              $output .= "	<a  href='" . $href . "' target='" . $target . "' rel='$image_rel' title='" . esc_attr($image_caption) . "'>";
          }  
        }
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'center') {
					$output .= '	<p class="caption middle">' . $caption . '</p>';
				}
								
				$output .= '		<div class="' . $image_container_class . '">';
				$output .= 				apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_BEFORE_THUMB, '', $options);
				$output .=				apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_THUMB, $thumb_image_element, $thumb_image, $image_class, $alt, $title);
				$output .= 				apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_AFTER_THUMB, '', $options);
        
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'bottom') {
					$output .= '		<div class="caption-bottom-container">';
					$output .= '			<p class="caption bottom">' . $caption . '</p>';
					$output .= '		</div>';
				}
				
				$output .= '		</div>';
				$output .= '	</a>';
				
				if ($options->get_thumb_caption_enabled() == 'on' && $options->get_thumb_caption_position() == 'below') {
				  $output .= 		apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_BEFORE_CAPTION, '', $title);
					$output .= '	<p class="caption below">' . $caption . '</p>';
				}
				
				$output .= '</li>';
			}
		}

		$output .= '		</ul>';
		$output .= '		<div class="clear"></div>';
		
		if ($options->get_description_enabled() == 'on' && $options->get_description_position() == 'below') {
			if ($options->get_description_text() != '') {
				$output .= '<p class="mg-description">' . $options->get_description_text() . '</p>';
			}
		}

		$output .= '	</div>';
		
		// Hidden elements used by image-tiles.js
		$output .= '	<span style="display: none;" class="hidden-image-tiles-gallery-id">' . $gallery->ID . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-vertical-fit-enabled">' . (($options->get_vertical_fit_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-content-click-close-enabled">' . (($options->get_content_click_close_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-bg-click-close-enabled">' . (($options->get_bg_click_close_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-close-btn-inside-enabled">' . (($options->get_close_btn_inside_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-hide-close-btn-enabled">' . (($options->get_hide_close_btn_enabled()== 'on') ? 'false' : 'true' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-escape-key-enabled">' . (($options->get_escape_key_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-align-top-enabled">' . (($options->get_align_top_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-fixed-content-position">' . $options->get_fixed_content_position() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-zoom-enabled">' . (($options->get_zoom_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-popup-main-class">' . $options->get_main_class()  . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-easing-type">' . $options->get_easing_type() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-zoom-duration">' . $options->get_zoom_duration() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-overflow-y">' . $options->get_overflow_y() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-retina-enabled">' . (($options->get_retina_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-removal-delay">' . $options->get_removal_delay() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-gallery-enabled">' . (($options->get_gallery_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-navigate-by-img-click-enabled">' . (($options->get_navigate_by_img_click_enabled()== 'on') ? 'true' : 'false' ) . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-arrow-markup">' . $options->get_arrow_markup() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-prev-button-title">' . $options->get_prev_button_title() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-next-button-title">' . $options->get_next_button_title() . '</span>';
		$output .= '	<span style="display: none;" class="hidden-image-tiles-counter-markup">' . $options->get_counter_markup() . '</span>';

		$output .= '</div>';
		$output .= apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_AFTER_GALLERY_OUTPUT, '', $gallery, $attachments, $options);
        
		if ($options->get_lazy_load_enabled() == 'on') {
      $output .= '<script>';
      $output .= 'document.addEventListener("DOMContentLoaded", function(event) { ';
      $lazy_load_threshold = $options->get_lazy_load_threshold();
      if($lazy_load_threshold != '' && $lazy_load_threshold != 0)
        $output .=  'jQuery("img.mg_lazy").lazyload({threshold : ' . $lazy_load_threshold . '});';
      else
        $output .=  'jQuery("img.mg_lazy").lazyload();';
      $output .= '});';
      $output .= '</script>';						
    }
    		
    
		return apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_GALLERY_OUTPUT, $output, $gallery, $attachments, $options);
	}
}
?>