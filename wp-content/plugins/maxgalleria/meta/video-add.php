<?php
require('../../../../wp-load.php');

global $wpdb;
global $maxgalleria;
$video_gallery = $maxgalleria->video_gallery;

// Get all video media source addons
$video_addons = array('' => '');
$media_source_addons = $maxgalleria->get_media_source_addons();

foreach ($media_source_addons as $addon) {
	if ($addon['subtype'] == 'video') {
		$video_addons = array_merge($video_addons, array($addon['name'] => $addon['key']));
	}
}

// Determine count of video addons
$video_addons_count = 0;
foreach ($video_addons as $name => $key) {
	if ($name != '') {
		$video_addons_count++;
	}
}

$gallery = get_post($_GET['post_id']);
$updated = false;

if ($_POST && check_admin_referer($video_gallery->nonce_video_add['action'], $video_gallery->nonce_video_add['name'])) {
	if (isset($gallery)) {
		if (isset($_POST['video-urls']) && $_POST['video-urls'] != '') {
			$video_urls = explode("\n", strip_tags($_POST['video-urls']));

			do_action(MAXGALLERIA_ACTION_BEFORE_ADD_VIDEOS_TO_GALLERY, $video_urls);
			
			if(class_exists('MaxGalleriaVideo'))
				global $maxgalleria_video;
			
			$site_url = site_url();
			$uploads_dir = wp_upload_dir();
			
			foreach ($video_urls as $video_url) {
				// Use rtrim to remove the \n on the end of the strings
				$video_url = rtrim($video_url);
				
				if ($video_url != '') {
					if(strpos($video_url, $site_url) !== false) {
						if(class_exists('MaxGalleriaVideo')) {

							$comma_position = strpos($video_url, ',');
							if($comma_position !== false) {
								$comma_position2 = strrpos($video_url, ',');								
								
								$video = substr($video_url, 0, $comma_position );
								$thumb_url = substr($video_url, $comma_position+1, $comma_position2 - ($comma_position+1) );
								$thumb_id = substr($video_url, $comma_position2+1);

								$video_path = str_replace( $uploads_dir['baseurl'], $uploads_dir['basedir'], $video ); 
								$thumbnail_path = str_replace( $uploads_dir['baseurl'], $uploads_dir['basedir'], $thumb_url ); 

								require_once(ABSPATH . 'wp-admin/includes/media.php');

								$filetype = wp_check_filetype(basename($thumbnail_path), null);
								$data = wp_read_video_metadata($video_path);

								// Include image.php so we can call wp_generate_attachment_metadata()
								require_once(ABSPATH . 'wp-admin/includes/image.php');

								// Insert the attachment
								$attachment_id = $thumb_id;
								
								// update the partent of the post thumbnail record
								$menu_order = $maxgalleria->common->get_next_menu_order($gallery->ID);
																
								$post_table = $wpdb->prefix . "posts";
								$record = array('post_parent' => $gallery->ID, 'menu_order' => $menu_order );
								$where = array('ID' => $thumb_id);
								$wpdb->update( $post_table, $record, $where);
																								
								$data['id'] = $attachment_id;								
								if(!isset($data['duration']))
									$data['duration'] = $data['length'];
								if(!isset($data['title']))
									$data['title'] = preg_replace( '/\.[^.]+$/', '', basename($video_path));
								
								// Save some of the data in the post meta of the attachment
								do_action(MAXGALLERIA_ACTION_VIDEO_ATTACHMENT_POST_META, $attachment_id, $video, $thumb_url, $data);
								$updated = true;

							}	
						}						
					} else {
						// is this a vimeo url?
						if( strpos($video_url, "vimeo.com") === false) {
							$page_link = strpos($video_url, "youtube.com/watch?v=");

							// if an yourtube embedded url convert it to a page link
							if($page_link === false) {
								$video_pos = strrpos($video_url, '/');
								$video_url = "https://www.youtube.com/watch?v=" . substr($video_url, $video_pos+1);
							}
						}

						// Get the data for the video; first initialize the API URL
						// and then pass it to the filter so it can get populated
						$api_url = '';
						$api_url = apply_filters(MAXGALLERIA_FILTER_VIDEO_API_URL, $api_url, $video_url);

						// Continue only if we have an API URL to call
						if ($api_url != '') {
							// Perform a remote GET to get the body of data from
							// the API URL and then decode it into JSON bits
							$response = wp_remote_get($api_url);
							$contents = wp_remote_retrieve_body($response);
							$data = json_decode($contents, true);
							
							// Get the URL of the video thumbnail; first initialize the thumb
							// URL and then pass it to the filter so it can get populated
							$thumb_url = '';
							$thumb_url = apply_filters(MAXGALLERIA_FILTER_VIDEO_THUMB_URL, $thumb_url, $video_url, $data);

							// Now that we have the thumb URL, get its remote contents
							// so we can store it as an attachment for the gallery
							$response = wp_remote_get($thumb_url);
							$contents = wp_remote_retrieve_body($response);

							// Upload and get file data
							$upload = wp_upload_bits(basename($thumb_url), null, $contents);
							$guid = $upload['url'];
							$file = $upload['file'];
							$file_type = wp_check_filetype(basename($file), null);

							// Set up the video thumb as an attachment; first initialize the
							// attachment and then pass it to the filter so it can get populated
							$attachment = array();
							$attachment = apply_filters(MAXGALLERIA_FILTER_VIDEO_ATTACHMENT, $attachment, $video_url, $gallery->ID, $guid, $file_type['type'], $data);

							// Include image.php so we can call wp_generate_attachment_metadata()
							require_once(ABSPATH . 'wp-admin/includes/image.php');

							// Insert the attachment
							$attachment_id = wp_insert_attachment($attachment, $file, $gallery->ID);
							$attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
							wp_update_attachment_metadata($attachment_id, $attachment_data);

							// Save some of the data in the post meta of the attachment
							do_action(MAXGALLERIA_ACTION_VIDEO_ATTACHMENT_POST_META, $attachment_id, $video_url, $thumb_url, $data);
							$updated = true;
						}
					}
				}
			}
			
			do_action(MAXGALLERIA_ACTION_AFTER_ADD_VIDEOS_TO_GALLERY, $video_urls);
		}
	}
}
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php _e('Add Videos', 'maxgalleria') ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo MAXGALLERIA_PLUGIN_URL ?>/maxgalleria.css" />
	<?php $maxgalleria->thickbox_l10n_fix() ?>
	<script type="text/javascript" src="<?php echo admin_url() ?>load-scripts.php?load=jquery-core,thickbox,wp-ajax-response"></script>
	<script type="text/javascript">
		<?php if ($updated) { ?>
			parent.eval("showAddingVideosNote()");
			parent.eval("reloadPage()");
		<?php } ?>
		
		jQuery(document).ready(function() {
			jQuery(".maxgalleria-meta.video-add .actions .loading").css("display", "none");
			
			jQuery("#save-button").click(function () {
				jQuery(".maxgalleria-meta.video-add .actions .loading").css("display", "inline-block");
				jQuery("#video-add-form").submit();
				return false;
			});
			
			jQuery("#cancel-button").click(function () {
				parent.eval("reloadPage()");
			});
		});
	</script>
</head>

<body>

<div class="maxgalleria-meta video-add">
	<?php if ($video_addons_count < 1) { ?>
		<p><?php _e('You do not have any video addons installed.', 'maxgalleria') ?></p>
		<p><?php printf(__('You can get video addons from the %sMaxGalleria website%s.', 'maxgalleria'), '<a href="http://maxgalleria.com/shop/category/addons/" target="_blank">', '</a>') ?></p>
		<div class="actions">
			<div class="cancel">
				<input type="button" class="btn" id="cancel-button" value="<?php _e('Close', 'maxgalleria') ?>" />
			</div>
		</div>
	<?php } else { ?>
		<form id="video-add-form" method="post">
			<p><?php _e('You can add as many videos to this gallery as you like. Simply enter the page URL of each video (not the embedded URL) in the box, and data about each video will be retrieved automatically.', 'maxgalleria') ?></p>
			<p><?php _e('Video URLs from the following sites are currently supported:', 'maxgalleria') ?></p>
			
			<ul class="addons">
				<?php foreach ($video_addons as $name => $key) { ?>
					<?php if ($name != '') { ?>
						<li><?php echo $name ?></li>
					<?php } ?>
				<?php } ?>
			</ul>
			
			<div class="fields">
				<div class="field">
					<div class="field-label">
						<?php _e('Video URLs', 'maxgalleria') ?> <span><?php _e('Multiple URLs accepted, one per line', 'maxgalleria') ?></span>
					</div>
					<div class="field-value">
						<textarea name="video-urls"></textarea>
					</div>
				</div>
			</div>
			
			<div class="actions">
				<div class="save">
					<input type="button" class="btn btn-primary" id="save-button" value="<?php _e('Add to Gallery', 'maxgalleria') ?>" />
				</div>
				<div class="cancel">
					<input type="button" class="btn" id="cancel-button" value="<?php _e('Cancel', 'maxgalleria') ?>" />
				</div>
				<div class="loading">
					<div class="gif">
						<img src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/loading-small.gif" width="16" height="16" alt="" />
					</div>
					<div class="text">
						<?php _e('Adding media to gallery...', 'maxgalleria') ?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			
			<?php wp_nonce_field($video_gallery->nonce_video_add['action'], $video_gallery->nonce_video_add['name']) ?>
		</form>
	<?php } ?>
</div>

</body>

</html>
