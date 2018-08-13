/*
 * Photo Box
 * Author: http://photoboxone.com/
 */
 
jQuery(document).ready(function($){
	var tgm_media_frame_img,
		clicked_on_imgbtn = false,
		i = 0;
	
    $(document.body).on('click.tgmOpenMediaManager', '#offline_box_upload_background_button', function(e){
        e.preventDefault();
		
        if ( tgm_media_frame_img ) {
            tgm_media_frame_img.open();
            return;
        }

        tgm_media_frame_img = wp.media.frames.tgm_media_frame = wp.media({
            className: 'media-frame tgm-media-frame',
            frame: 'select',
            multiple: false,
            title: 'Upload Image',
            library: {
                type: 'image'
            },
            button: {
                text: 'Use this image'
            }
        });

        tgm_media_frame_img.on('select', function(){
            var media_attachment = tgm_media_frame_img.state().get('selection').first().toJSON(),
				url = media_attachment.url;
			//console.log(media_attachment);
			
			if( typeof media_attachment.sizes != 'undefined' )
				url = media_attachment.sizes.thumbnail.url;
			
            jQuery('#offline_box_display_background_id').val(media_attachment.id);
			jQuery('#offline_box_display_background_thumb').html('<img src="'+url+'" height=150 alt="' + media_attachment.title + '"/>');
        });
		
        tgm_media_frame_img.open();
		
    });
	/* set click for remove_image_button */
	$('#offline_box_remove_background_button').click(function(e) {
		e.preventDefault();
		$('#offline_box_display_background_id').attr('value','');
		$('#offline_box_display_background_thumb').html('');
	});
	
	$('#offline_box_upload_background_button').click(function() {
		formfield = jQuery('#offline_box_display_background_id').attr('name');
		clicked_on_imgbtn = true;
		// if tb_show is function
		//if( typeof tb_show == 'function' )
		tb_show('Add Image', 'media-upload.php?type=image&amp;TB_iframe=true');
		
		return false;
	});
	
	$('.mybutton').click(function(){
		i++;
		
		console.log(i);
	});
	
});