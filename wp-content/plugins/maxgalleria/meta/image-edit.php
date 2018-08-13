<?php
require('../../../../wp-load.php');

global $maxgalleria;
$common = $maxgalleria->common;
$image_gallery = $maxgalleria->image_gallery;

$gallery_id = $_GET['gallery_id'];
$template = get_post_meta( $gallery_id, 'maxgallery_template', true );
$image = get_post($_GET['image_id']);
$updated = false;

if ($_POST && check_admin_referer($image_gallery->nonce_image_edit['action'], $image_gallery->nonce_image_edit['name'])) {
	if (isset($image)) {
		// First update the post itself
		$temp = array();
		$temp['ID'] = $image->ID;
		$temp['post_title'] = stripslashes($_POST['image-edit-title']);
		$temp['post_excerpt'] = stripslashes($_POST['image-edit-caption']);
		wp_update_post($temp);
		
		// Determine if we need to prepend http:// to the link
		$link = $_POST['image-edit-link'];
		if ($link != '' && !$common->string_starts_with($link, 'http://') && !$common->string_starts_with($link, 'https://') ) {
			$link = 'http://' . $link;
		}
		
		// Now update the meta
		update_post_meta($image->ID, '_wp_attachment_image_alt', stripslashes($_POST['image-edit-alt']));
		update_post_meta($image->ID, 'maxgallery_attachment_image_link', stripslashes($link));
		
		if($template === 'material-design') {
		  $action_link_text = $_POST['image-edit-action-text'];		
		  update_post_meta($image->ID, 'maxgallery_attachment_action_link_text', stripslashes($action_link_text));
		}
		
		$updated = true;
	}
}
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php _e('Edit Image', 'maxgalleria') ?></title>
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
				jQuery("#image-edit-form").submit();
				return false;
			});
			
			jQuery("#cancel-button").click(function () {
				parent.eval("reloadPage()");
			});
		});
	</script>
</head>

<body>

<div class="maxgalleria-meta image-edit">	
	<form id="image-edit-form" method="post">
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<div class="fields">
						<div class="field">
							<div class="field-label">
								<?php _e('Title', 'maxgalleria') ?>
							</div>
							<div class="field-value">
								<input type="text" name="image-edit-title" value="<?php echo $image->post_title ?>" />
							</div>
						</div>
						
						<div class="field">
							<div class="field-label">
								<?php _e('Alternate Text', 'maxgalleria') ?>
							</div>
							<div class="field-value">
								<input type="text" name="image-edit-alt" value="<?php echo get_post_meta($image->ID, '_wp_attachment_image_alt', true) ?>" />
							</div>
						</div>
						
						<div class="field">
							<div class="field-label">
								<?php _e('Caption', 'maxgalleria') ?>
							</div>
							<div class="field-value">
								<textarea name="image-edit-caption"><?php echo $image->post_excerpt ?></textarea>
							</div>
						</div>

						<?php if($template === 'material-design') {	?>
						
						<div class="field">
							<div class="field-label">
								<?php _e('Action Link Text', 'maxgalleria') ?>
							</div>
							<div class="field-value">
								<input type="text" id="image-edit-action-text" name="image-edit-action-text" value="<?php echo get_post_meta($image->ID, 'maxgallery_attachment_action_link_text', true) ?>" />
							</div>
						</div>						
						
						<?php }	?>
						
						<div class="field">
							<div class="field-label">
								<?php _e('Link', 'maxgalleria') ?>
							</div>
							<div class="field-value">
								<input type="text" name="image-edit-link" value="<?php echo get_post_meta($image->ID, 'maxgallery_attachment_image_link', true) ?>" />
							</div>
						</div>
					</div>
				</td>
				<td>
					<div class="thumb">
						<?php echo wp_get_attachment_image($image->ID, MAXGALLERIA_META_IMAGE_THUMB_LARGE) ?>
					</div>
				</td>
			</tr>
		</table>
		
		<div class="actions">
			<div class="save">
				<input type="button" class="btn btn-primary" id="save-button" value="<?php _e('Save Changes', 'maxgalleria') ?>" />
			</div>
			<div class="cancel">
				<input type="button" class="btn" id="cancel-button" value="<?php _e('Cancel', 'maxgalleria') ?>" />
			</div>
		</div>
		
		<?php wp_nonce_field($image_gallery->nonce_image_edit['action'], $image_gallery->nonce_image_edit['name']) ?>
	</form>
</div>
<script>
	jQuery(document).ready(function(){
	
	});  
</script>  
	

</body>

</html>
