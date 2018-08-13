<?php
class MaxGalleriaGalleryThumbWidget extends WP_Widget {
	public function __construct() {
		$widget_id = 'maxgalleria-gallery-thumb-widget';
		$widget_name = __('MaxGalleria Gallery Thumbnail', 'maxgalleria');
		$widget_args = array('description' => __('Show thumbnail for a MaxGalleria gallery.', 'maxgalleria'));
		
		parent::__construct($widget_id, $widget_name, $widget_args);
	}
	
	// Outputs the contents of the widget
	public function widget($args, $instance) {
		$output = '';
		
		$title = apply_filters('widget_title', $instance['title']);
		$gallery_id = (isset($instance['gallery_id'])) ? $instance['gallery_id'] : '';
		$thumb_width = (isset($instance['thumb_width'])) ? $instance['thumb_width'] : '';
		$thumb_height = (isset($instance['thumb_height'])) ? $instance['thumb_height'] : '';
		$url = (isset($instance['url'])) ? $instance['url'] : '';
		
		$output .= $args['before_widget'];
		
		if (isset($title) && $title != '') {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		
		if (isset($gallery_id) && $gallery_id != '') {
			$output .= apply_filters(MAXGALLERIA_FILTER_GALLERY_WIDGET_BEFORE_THUMB_OUTPUT, '', $gallery_id);
			$output .= do_shortcode('[maxgallery_thumb id="' . $gallery_id . '" width="' . $thumb_width . '" height="' . $thumb_height . '" url="' . $url . '"]');
			$output .= apply_filters(MAXGALLERIA_FILTER_GALLERY_WIDGET_AFTER_THUMB_OUTPUT, '', $gallery_id);
		}

		$output .= $args['after_widget'];
		
		echo $output;
	}
	
	// Outputs the options in the admin
	public function form($instance) {
		$output = '';
		$galleries = get_posts(array('post_type' => MAXGALLERIA_POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1));
		
		// Form values
		$title = (isset($instance['title'])) ? $instance['title'] : __('Gallery Thumb Widget', 'maxgalleria');
		$gallery_id = (isset($instance['gallery_id'])) ? $instance['gallery_id'] : '';
		$thumb_width = (isset($instance['thumb_width'])) ? $instance['thumb_width'] : '';
		$thumb_height = (isset($instance['thumb_height'])) ? $instance['thumb_height'] : '';
		$url = (isset($instance['url'])) ? $instance['url'] : '';
		
		// Form layout
		$output .= '<table cellpadding="5" style="width: 100%; padding-top: 10px; padding-bottom: 10px;">';
		$output .= '	<tr>';
		$output .= '		<td width="100">' . __('Title: ', 'maxgalleria') . '</td>';
		$output .= '		<td>';
		$output .= '			<input class="widefat" type="text" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" value="' . esc_attr($title) . '" />';
		$output .= '		</td>';
		$output .= '	</tr>';
		$output .= '	<tr>';
		$output .= '		<td>' . __('Gallery: ', 'maxgalleria') . '</td>';
		$output .= '		<td>';
		$output .= '			<select class="widefat" id="' . $this->get_field_id('gallery_id') . '" name="' . $this->get_field_name('gallery_id') . '">';
		$output .= '				<option value="">-- ' . __('Select Gallery', 'maxgalleria') . ' --</option>';
									foreach ($galleries as $g) {
										$selected = ($gallery_id == $g->ID) ? 'selected="selected"' : '';
										$output .= '<option value="' . $g->ID . '" ' . $selected . '>' . $g->post_title . '</option>';
									}
		$output .= '			</select>';
		$output .= '		</td>';
		$output .= '	</tr>';
		$output .= '	<tr>';
		$output .= '		<td>' . __('Thumb Width: ', 'maxgalleria') . '</td>';
		$output .= '		<td>';
		$output .= '			<input style="width: 50px;" type="text" id="' . $this->get_field_id('thumb_width') . '" name="' . $this->get_field_name('thumb_width') . '" value="' . esc_attr($thumb_width) . '" /> px';
		$output .= '		</td>';
		$output .= '	</tr>';
		$output .= '	<tr>';
		$output .= '		<td>' . __('Thumb Height: ', 'maxgalleria') . '</td>';
		$output .= '		<td>';
		$output .= '			<input style="width: 50px;" type="text" id="' . $this->get_field_id('thumb_height') . '" name="' . $this->get_field_name('thumb_height') . '" value="' . esc_attr($thumb_height) . '" /> px';
		$output .= '		</td>';
		$output .= '	</tr>';
		$output .= '	<tr>';
		$output .= '		<td>' . __('URL: ', 'maxgalleria') . '</td>';
		$output .= '		<td>';
		$output .= '			<input class="widefat" type="text" id="' . $this->get_field_id('url') . '" name="' . $this->get_field_name('url') . '" value="' . esc_attr($url) . '" />';
		$output .= '		</td>';
		$output .= '	</tr>';
		$output .= '</table>';
		
		echo $output;
	}
	
	// Saves the widget options
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (isset($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['gallery_id'] = (isset($new_instance['gallery_id'])) ? strip_tags($new_instance['gallery_id']) : '';
		$instance['thumb_width'] = (isset($new_instance['thumb_width'])) ? strip_tags($new_instance['thumb_width']) : '';
		$instance['thumb_height'] = (isset($new_instance['thumb_height'])) ? strip_tags($new_instance['thumb_height']) : '';
		$instance['url'] = (isset($new_instance['url'])) ? strip_tags($new_instance['url']) : '';
		
		return $instance;
	}
}
?>