<?php
require('../../../../wp-load.php');

global $maxgalleria;
$common = $maxgalleria->common;
$image_gallery = $maxgalleria->image_gallery;

$image_ids = stripslashes(strip_tags($_GET['image_ids']));
$updated = false;

if ($_POST && check_admin_referer($image_gallery->nonce_image_edit_bulk['action'], $image_gallery->nonce_image_edit_bulk['name'])) {
	$i = 0;
	foreach ($_POST['image-edit-id'] as $image_edit_id) {
		// First update the post itself
		$temp = array();
		$temp['ID'] = $image_edit_id;
		$temp['post_title'] = stripslashes(strip_tags($_POST['image-edit-title'][$i]));
		$temp['post_excerpt'] = stripslashes(strip_tags($_POST['image-edit-caption'][$i]));
		wp_update_post($temp);
		
		// Determine if we need to prepend http:// to the link
		$link = $_POST['image-edit-link'][$i];
		if (isset($link) && $link != '') {
			if (!$common->string_starts_with($link, 'http://') && !$common->string_starts_with($link, 'https://')) {
				$link = 'http://' . $link;
			}
		}
		
		// Now update the meta
		update_post_meta($image_edit_id, '_wp_attachment_image_alt', stripslashes($_POST['image-edit-alt'][$i]));
		update_post_meta($image_edit_id, 'maxgallery_attachment_image_link', stripslashes($link));
		
		// Increment the counter
		$i++;
	}
	
	$updated = true;
}
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php _e('Edit Bulk Images', 'maxgalleria') ?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo MAXGALLERIA_PLUGIN_URL ?>/maxgalleria.css" />
	<?php $maxgalleria->thickbox_l10n_fix() ?>
	<script type="text/javascript" src="<?php echo admin_url() ?>load-scripts.php?load=jquery-core,thickbox,wp-ajax-response,imgareaselect,image-edit"></script>
	<script type="text/javascript">
		<?php if ($updated) { ?>
			parent.eval("reloadPage()");
		<?php } ?>
		
		jQuery(document).ready(function() {
			jQuery("#save-button").click(function () {
				jQuery("#image-bulk-edit-form").submit();
				return false;
			});
			
			jQuery("#cancel-button").click(function () {
				parent.eval("reloadPage()");
			});
		});
	</script>
</head>

<body>

<div class="maxgalleria-meta image-edit-bulk">	
	<form id="image-bulk-edit-form" method="post">
		<?php if (isset($image_ids)) { ?>
			<?php $image_ids_array = explode(',', $image_ids) ?>
			
			<table cellpadding="0" cellspacing="0">
				<?php foreach ($image_ids_array as $image_id) { ?>
					<?php if (isset($image_id)) { ?>
						<?php $image = get_post($image_id) ?>
							<?php if (isset($image)) { ?>
								<tr>
									<td class="thumb">
										<?php echo wp_get_attachment_image($image->ID, MAXGALLERIA_META_IMAGE_THUMB_SMALL) ?>
										<input type="hidden" name="image-edit-id[]" value="<?php echo $image->ID ?>" />
									</td>
									<td>
										<div class="fields">
											<div class="field">
												<div class="field-label">
													<?php _e('Title', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="image-edit-title[]" value="<?php echo $image->post_title ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-label">
													<?php _e('Alt Text', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="image-edit-alt[]" value="<?php echo get_post_meta($image->ID, '_wp_attachment_image_alt', true) ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-label">
													<?php _e('Caption', 'maxgalleria') ?>
												</div>
												<div class="field-value">
													<input type="text" name="image-edit-caption[]" value="<?php echo $image->post_excerpt ?>" />
												</div>
											</div>
											<div class="clear"></div>
											
											<div class="field">
												<div class="field-label">
													<?php _e('Link', 'maxgalleria') ?>
												</div>
												<div class="field-value last">
													<input type="text" name="image-edit-link[]" value="<?php echo get_post_meta($image->ID, 'maxgallery_attachment_image_link', true) ?>" />
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
		
		<?php wp_nonce_field($image_gallery->nonce_image_edit_bulk['action'], $image_gallery->nonce_image_edit_bulk['name']) ?>
	</form>
</div>

</body>

</html>
