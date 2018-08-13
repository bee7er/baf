<?php
class MaxGalleriaVideoShortcode {
	public function __construct() {
		add_shortcode('maxgallery-video', array($this, 'maxgallery_video_shortcode'));
	  add_shortcode('mlpp-display-files', array($this, 'mlpp_display_files'));
	}
	
	public function enqueue_styles($skin) {
      
		$lightbox_stylesheet = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/magnific-popup.css');
		wp_enqueue_style('maxgalleria-magnific', $lightbox_stylesheet);

		// The main styles for this template
		$main_stylesheet = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_MAIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/video-tiles.css');
		wp_enqueue_style('maxgalleria-video-tiles', $main_stylesheet);
		
		// Load skin style
		if($skin) {
			$skin_stylesheet = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_SKIN_STYLESHEET, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/skins/' . $skin . '.css', $skin);
			wp_enqueue_style('maxgalleria-video-tiles-skin-' . $skin, $skin_stylesheet);
		}
	}
	
	public function enqueue_scripts() {
		wp_enqueue_script('jquery');
		      
		$lightbox_script = apply_filters(MAXGALLERIA_FILTER_IMAGE_TILES_LIGHTBOX_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/libs/magnific/jquery.magnific-popup.js');
		wp_enqueue_script('maxgalleria-magnific', $lightbox_script, array('jquery'));

		//$main_script = apply_filters(MAXGALLERIA_FILTER_VIDEO_TILES_MAIN_SCRIPT, MAXGALLERIA_PLUGIN_URL . '/addons/templates/video-tiles/video-tiles.js');
		//wp_enqueue_script('maxgalleria-video-tiles', $main_script, array('jquery'));

	}
	
	public function maxgallery_video_shortcode($atts) {	
		
		extract(shortcode_atts(array(
			'url' => '',
			'height' => '200',
			'width' => '200',
			'title' => '',
			'caption' => '',
			'skin' => 'standard',
			'cover' => ''
		), $atts));
		
		$this->enqueue_styles($skin);
		$this->enqueue_scripts();
				
		$output = '<div class="mg-video-tiles '. $skin .'" id="maxgallery-video-single">' . PHP_EOL
						. '  <div class="mg-videos">' . PHP_EOL
						. '    <div class="mg-thumbs mg-onecol">'
						. '      <ul>' . PHP_EOL
						. '        <li>'  . PHP_EOL
						. '          <a rel="mg-rel-video-thumbs" target="" href="'. $url . '" class="video">' . PHP_EOL
						. '            <div class="mg-video-button">'  . PHP_EOL
						. '              <img width="' . $width . '" height="' . $height . '" title="' . $title . '" alt="' . $title . '" src="' . $cover . '" class="">' . PHP_EOL
						. '            </div>' . PHP_EOL;
		if($caption !== '')
		  $output .= '            <p class="caption below">' . $caption . '</p>' . PHP_EOL;
		
		$output .= '          </a>'
						. '        </li>'  . PHP_EOL
						. '      </ul>' . PHP_EOL
						. '    </div>' . PHP_EOL
						. '  </div>' . PHP_EOL
						. '</div>' . PHP_EOL
						. '<script>' . PHP_EOL
						. 'jQuery(document).ready(function() {' . PHP_EOL
						. '  jQuery("#maxgallery-video-single.mg-video-tiles .mg-thumbs a.video").magnificPopup({' . PHP_EOL
						.	'  type: "iframe",' . PHP_EOL
						. '  verticalFit: true,' . PHP_EOL
						. '  showCloseBtn: false,' . PHP_EOL
						. '  enableEscapeKey: true,' . PHP_EOL
						. '  alignTop: false,' . PHP_EOL
						. '  closeOnBgClick: true,' . PHP_EOL
					  . '  fixedContentPos: "auto",' . PHP_EOL
						. '  overflowY: "auto"' . PHP_EOL
					 . '  });'  . PHP_EOL
					 . '});' . PHP_EOL
					 . '</script>' . PHP_EOL;
		
		return $output;
	}
	
	public function mlpp_display_files( $atts ) {

		global $wpdb, $maxgalleria_media_library_pro;

		extract(shortcode_atts(array(
			'folder_id' => '',
		), $atts));

		$upload_dir = wp_upload_dir(); 
		$output = "";
		$folder_table = $wpdb->prefix . "mgmlp_folders";

							$sql = "select ID, post_title, $folder_table.folder_id, pm.meta_value as attached_file 
	from {$wpdb->prefix}posts 
	LEFT JOIN $folder_table ON({$wpdb->prefix}posts.ID = $folder_table.post_id)
	LEFT JOIN {$wpdb->prefix}postmeta AS pm ON (pm.post_id = {$wpdb->prefix}posts.ID) 
	where post_type = 'attachment' 
	and folder_id = '$folder_id'
	AND pm.meta_key = '_wp_attached_file' 
	order by post_title";

		$output .= "<style>" . PHP_EOL;
		$output .= "  ul.mlpp-file-list li {" . PHP_EOL;
		$output .= "    display: inline-block;" . PHP_EOL;
		$output .= "    float: left;" . PHP_EOL;
		$output .= "    list-style: outside none none;" . PHP_EOL;
		$output .= "    height: 222px;" . PHP_EOL;
		$output .= "  }" . PHP_EOL;	
		$output .= "  ul.mlpp-file-list li img {" . PHP_EOL;
		$output .= "		height: 135px;" . PHP_EOL;
		$output .= "		width: 135px;" . PHP_EOL;
		$output .= "		margin: 10px;" . PHP_EOL;	
		$output .= "	}" . PHP_EOL;
		$output .= "	ul.mlpp-file-list li p.title {" . PHP_EOL;
		$output .= "		text-align: center;" . PHP_EOL;
		$output .= "		word-wrap: break-word;" . PHP_EOL;
		$output .= "		width: 154px;" . PHP_EOL;
		$output .= "	}" . PHP_EOL;
		$output .= "</style>" . PHP_EOL;

		$output .= '<ul class="mlpp-file-list">' . PHP_EOL;
		$rows = $wpdb->get_results($sql);            
		if($rows) {
			foreach($rows as $row) {
				$thumbnail = wp_get_attachment_thumb_url($row->ID);                
				if($thumbnail === false) {
					//$thumbnail = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . "/images/file.jpg";
					$ext = pathinfo($row->attached_file, PATHINFO_EXTENSION);										
					$thumbnail = $maxgalleria_media_library_pro->get_file_thumbnail($ext);
				}  

				$file_ulr = $upload_dir['baseurl'] . "/" . $row->attached_file;

				$output .=  "<li><a href='$file_ulr' target='_blank'><img alt='$row->post_title' src='$thumbnail' /><p class='title'>$row->post_title</p></a></li>" . PHP_EOL;
			}      
		}
		$output .= '</ul>' . PHP_EOL;

		return $output;

	}

}
