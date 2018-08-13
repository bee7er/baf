<?php
global $maxgalleria;
global $post;
$options = new MaxGalleryOptions($post->ID);
?>

<script type="text/javascript">			
	jQuery(document).ready(function() {
		// Hides the meta box
		jQuery("#new-gallery").css("display", "none");
		
		// Creates the modal and sets its properties
		jQuery(".maxgalleria-meta").modal();
		jQuery("#simplemodal-container").css("background-color", "#ffffff");
		jQuery("#simplemodal-container").css("border-width", "2px");
		jQuery("#simplemodal-container").css("color", "#222222");
		jQuery("#simplemodal-container").css("width", "auto");
		jQuery("#simplemodal-container").css("height", "200px");
		
		jQuery("#simplemodal-container a.simplemodal-close").click(function() {
			jQuery.modal.close();
			window.location = "<?php echo admin_url() ?>edit.php?post_type=<?php echo MAXGALLERIA_POST_TYPE ?>";
		});
		
		jQuery("#<?php echo $options->type_key ?>_image_icon").click(function() {
			jQuery("#<?php echo $options->type_key ?>_image_icon").addClass("selected");
			jQuery("#<?php echo $options->type_key ?>_video_icon").removeClass("selected");
			jQuery("#<?php echo $options->type_key ?>").val("image");
			jQuery("#<?php echo $options->template_key ?>").val("<?php echo $maxgalleria->settings->get_default_image_gallery_template() ?>");
			submitForm();
		});
		
		jQuery("#<?php echo $options->type_key ?>_video_icon").click(function() {
			jQuery("#<?php echo $options->type_key ?>_video_icon").addClass("selected");
			jQuery("#<?php echo $options->type_key ?>_image_icon").removeClass("selected");
			jQuery("#<?php echo $options->type_key ?>").val("video");
			jQuery("#<?php echo $options->template_key ?>").val("<?php echo $maxgalleria->settings->get_default_video_gallery_template() ?>");
			submitForm();
		});
	});
	
	function submitForm() {
		var form_data = jQuery("#post").serialize();
		form_data += "&<?php echo $options->type_key ?>=" + jQuery("#<?php echo $options->type_key ?>").val();
		form_data += "&<?php echo $options->template_key ?>=" + jQuery("#<?php echo $options->template_key ?>").val();
		form_data += "&action=save_new_gallery_type";
		
		jQuery.modal.close();
		
		jQuery.ajax({
			type: "POST",
			url: "<?php echo admin_url('admin-ajax.php') ?>",
			data: form_data,
			success: function(post_id) {
				window.location = "<?php echo admin_url() ?>post.php?post=" + post_id + "&action=edit";
			}
		});
	}
</script>

<div class="maxgalleria-meta" style="display: none;">
	<div class="gallery-type">
		<div align="center">
			<p><?php _e('What type of gallery do you want to create?', 'maxgalleria') ?></p>
			
			<table>
				<tr>
					<td>
						<img id="<?php echo $options->type_key ?>_image_icon" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/image-80.png" alt="<?php _e('Image', 'maxgalleria') ?>" title="<?php _e('Image', 'maxgalleria') ?>" />
						<br />
						<label><?php _e('Image', 'maxgalleria') ?></label>
					</td>
					<td>
						<img id="<?php echo $options->type_key ?>_video_icon" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/video-80.png" alt="<?php _e('Video', 'maxgalleria') ?>" title="<?php _e('Video', 'maxgalleria') ?>" />
						<br />
						<label><?php _e('Video', 'maxgalleria') ?></label>
					</td>
				</tr>
			</table>
			
			<!-- Default to an image gallery with the Image Tiles template -->
			<input type="hidden" id="<?php echo $options->type_key ?>" name="<?php echo $options->type_key ?>" value="image" />
			<input type="hidden" id="<?php echo $options->template_key ?>" name="<?php echo $options->template_key ?>" value="image-tiles" />
		</div>
	</div>
</div>
