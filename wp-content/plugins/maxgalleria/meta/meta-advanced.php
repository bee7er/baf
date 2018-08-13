<?php
global $post;
$maxgallery_options = new MaxGalleryOptions($post->ID);
?>

<div class="meta-options">
	<table>
		<tr>
			<td colspan="2" class="mxg-option-title">
				<strong><?php _e('Custom Styles', 'maxgalleria') ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="mxg-instructions">
				<div><?php _e('Add custom styles by using another stylesheet. This stylesheet will be loaded after all other gallery styles.', 'maxgalleria') ?></div>
			</td>
		</tr>
		<tr>
			<td width="60">
				<label for="<?php echo $maxgallery_options->custom_styles_enabled_key ?>"><?php _e('Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $maxgallery_options->custom_styles_enabled_key ?>" name="<?php echo $maxgallery_options->custom_styles_enabled_key ?>" <?php echo (($maxgallery_options->get_custom_styles_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td width="60">
				<?php _e('URL:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="wide" id="<?php echo $maxgallery_options->custom_styles_url_key ?>" name="<?php echo $maxgallery_options->custom_styles_url_key ?>" value="<?php echo $maxgallery_options->get_custom_styles_url() ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br /><!-- Spacer -->
			</td>
		</tr>
		<tr>
			<td colspan="2" class="mxg-option-title">
				<strong><?php _e('Custom Scripts', 'maxgalleria') ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="mxg-instructions">
				<div><?php _e('Add custom scripts by using another JavaScript file. This file will be loaded after all other gallery scripts.', 'maxgalleria') ?></div>
			</td>
		</tr>
		<tr>
			<td width="60">
				<label for="<?php echo $maxgallery_options->custom_scripts_enabled_key ?>"><?php _e('Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $maxgallery_options->custom_scripts_enabled_key ?>" name="<?php echo $maxgallery_options->custom_scripts_enabled_key ?>" <?php echo (($maxgallery_options->get_custom_scripts_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td width="60">
				<?php _e('URL:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="wide" id="<?php echo $maxgallery_options->custom_scripts_url_key ?>" name="<?php echo $maxgallery_options->custom_scripts_url_key ?>" value="<?php echo $maxgallery_options->get_custom_scripts_url() ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br /><!-- Spacer -->
			</td>
		</tr>
		<tr>
			<td colspan="2" class="mxg-option-title">
				<strong><?php _e('Reset', 'maxgalleria') ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="mxg-instructions">
				<div><?php _e('Reset all gallery options to their default values. This action cannot be undone.', 'maxgalleria') ?></div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="checkbox" id="<?php echo $maxgallery_options->reset_options_key ?>" name="<?php echo $maxgallery_options->reset_options_key ?>" />
				<label for="<?php echo $maxgallery_options->reset_options_key ?>"><?php _e('Yes, I understand', 'maxgalleria') ?></label>
			</td>
		</tr>
	</table>
</div>
