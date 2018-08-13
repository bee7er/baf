<?php
require('../../../../wp-load.php');

global $maxgalleria;
$video_gallery = $maxgalleria->video_gallery;

$video_ids = stripslashes(strip_tags($_GET['video_ids']));
$updated = false;

if ($_POST && check_admin_referer($video_gallery->nonce_video_edit_bulk['action'], $video_gallery->nonce_video_edit_bulk['name'])) {
	$i = 0;
	foreach ($_POST['video-edit-id'] as $video_edit_id) {
		// First update the post itself
		$temp = array();
		$temp['ID'] = $video_edit_id;
		$temp['post_title'] = stripslashes(strip_tags($_POST['video-edit-title'][$i]));
		$temp['post_excerpt'] = stripslashes(strip_tags($_POST['video-edit-caption'][$i]));
		wp_update_post($temp);
		
		// Now update the image alt in the meta
		update_post_meta($video_edit_id, '_wp_attachment_image_alt', stripslashes($_POST['video-edit-alt'][$i]));
		
		// Then the related videos flag in the meta
		if (isset($_POST['video-edit-related-videos-' . $video_edit_id])) {
			update_post_meta($video_edit_id, 'maxgallery_attachment_video_enable_related_videos', 1);
		}
		else {
			update_post_meta($video_edit_id, 'maxgallery_attachment_video_enable_related_videos', 0);
		}
		
		// And finally the HD playback flag in the meta
		if (isset($_POST['video-edit-hd-playback-' . $video_edit_id])) {
			update_post_meta($video_edit_id, 'maxgallery_attachment_video_enable_hd_playback', 1);
		}
		else {
			update_post_meta($video_edit_id, 'maxgallery_attachment_video_enable_hd_playback', 0);
		}
		
		// Increment the counter
		$i++;
	}
	
	$updated = true;
}
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php _e('Edit Bulk Videos', 'maxgalleria') ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo MAXGALLERIA_PLUGIN_URL ?>/maxgalleria.css" />
	<?php $maxgalleria->thickbox_l10n_fix() ?>
	<script type="text/javascript" src="<?php echo admin_url() ?>load-scripts.php?load=jquery-core,thickbox,wp-ajax-response"></script>
	<script type="text/javascript">
		<?php if ($updated) { ?>
			parent.eval("reloadPage()");
		<?php } ?>
		
		jQuery(document).ready(function() {
			jQuery("#save-button").click(function () {
				jQuery("#video-bulk-edit-form").submit();
				return false;
			});
			
			jQuery("#cancel-button").click(function () {
				parent.eval("reloadPage()");
			});
		});
	</script>
</head>

<body>

<div class="maxgalleria-meta video-edit-bulk">	
	<form id="video-bulk-edit-form" method="post">
		<?php if (isset($video_ids)) { ?>
			<?php $video_ids_array = explode(',', $video_ids) ?>
			
			<table cellpadding="0" cellspacing="0">
				<?php foreach ($video_ids_array as $video_id) { ?>
					<?php if (isset($video_id)) { ?>
						<?php $video = get_post($video_id) ?>
							<?php if (isset($video)) { ?>
								<tr>
									<td class="thumb">
										<?php echo wp_get_attachment_image($video->ID, MAXGALLERIA_META_VIDEO_THUMB_SMALL) ?>
										<input type="hidden" name="video-edit-id[]" value="<?php echo $video->ID ?>" />
									</td>
									<td>
										<div class="fields">
											<div class="field">
												<div class="field-label">
													<?php _e('Title', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="video-edit-title[]" value="<?php echo $video->post_title ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-label">
													<?php _e('Alt Text', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="video-edit-alt[]" value="<?php echo get_post_meta($video->ID, '_wp_attachment_image_alt', true) ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-label">
													<?php _e('Caption', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="video-edit-caption[]" value="<?php echo $video->post_excerpt ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-value">
													<?php
													$enable_related_videos = get_post_meta($video->ID, 'maxgallery_attachment_video_enable_related_videos', true);
													if (!isset($enable_related_videos) || $enable_related_videos == '') {
														// Default to true for backwards compatibility
														$enable_related_videos = 1;
													}
													?>
<!--													<input type="checkbox" name="video-edit-related-videos-<?php echo $video->ID ?>" id="video-edit-related-videos-<?php echo $video->ID ?>" <?php echo ($enable_related_videos == 1) ? 'checked="checked"' : '' ?>>
													<label for="video-edit-related-videos-<?php echo $video->ID ?>"><strong><?php _e('Enable Related Videos', 'maxgalleria') ?></strong></label>-->
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-value last">
													<?php
													$enable_hd_playback = get_post_meta($video->ID, 'maxgallery_attachment_video_enable_hd_playback', true);
													if (!isset($enable_hd_playback) || $enable_hd_playback == '') {
														// Default to false for backwards compatibility
														$enable_hd_playback = 0;
													}
													?>
<!--													<input type="checkbox" name="video-edit-hd-playback-<?php echo $video->ID ?>" id="video-edit-hd-playback-<?php echo $video->ID ?>" <?php echo ($enable_hd_playback == 1) ? 'checked="checked"' : '' ?>>
													<label for="video-edit-hd-playback-<?php echo $video->ID ?>"><strong><?php _e('Enable HD Playback', 'maxgalleria') ?></strong></label>-->
												</div>
											</div>
											<div class="clear"></div>
										</div>
									</td>
								</tr>
							<?php } ?>
					<?php } ?>
				<?php } ?>
			</table>
		<?php } ?>
		
		<div class="actions">
			<div class="save">
				<input type="button" class="btn btn-primary" id="save-button" value="<?php _e('Save Changes', 'maxgalleria') ?>" />
			</div>
			<div class="cancel">
				<input type="button" class="btn" id="cancel-button" value="<?php _e('Cancel', 'maxgalleria') ?>" />
			</div>
		</div>
		
		<?php wp_nonce_field($video_gallery->nonce_video_edit_bulk['action'], $video_gallery->nonce_video_edit_bulk['name']) ?>
	</form>
</div>

</body>

</html>
