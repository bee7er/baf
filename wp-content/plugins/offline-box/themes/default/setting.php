<?php
extract(shortcode_atts(array(
	'background_id'	=> 0,
	'title'	=> 'Coming soon - Sitename',
	'description'	=> 'Tagline or description',
), (array)get_option('offline_box_display')));

?>
<h4>Default Theme Setting</h4>
<h4>Background</h4>
<p id="offline_box_display_background_thumb"><?php echo ($background_id>0?wp_get_attachment_image($background_id,'thumbnail','',array('height' => 150) ):'');?></p>
<p>
	<input value="<?php echo $background_id;?>" type="hidden" name="offline_box_display[background_id]" id="offline_box_display_background_id" />
	<button id="offline_box_upload_background_button">Choose Image</button>
	<button id="offline_box_remove_background_button">Remove Image</button>
</p>
<h4>Title</h4>
<p><input value="<?php echo $title;?>" name="offline_box_display[title]" style="width: 80%; max-width: 90%; " /></p>
<h4>Description</h4>
<p><textarea name="offline_box_display[description]" style="width: 80%; max-width: 90%; height: 70px;"><?php echo $description;?></textarea></p>