<?php
global $post;
global $maxgalleria;

$image_gallery = $maxgalleria->image_gallery;

if (class_exists('MaxGalleriaWatermark')) {
  global $maxgalleria_watermark;
}

$gallery_id = $_GET['post'];
$template = get_post_meta( $gallery_id, 'maxgallery_template', true );

$uploads_dir = wp_upload_dir();
$base_uploads_url = $uploads_dir['baseurl'];

$args = array(
	'post_type' => 'attachment',
	'numberposts' => -1, // All of them
	'post_parent' => $post->ID,
	'orderby' => 'menu_order',
	'order' => 'ASC'
);

$attachments = get_posts($args);
?>

<script type="text/javascript">		
	jQuery(document).ready(function() {
		// To add the image count in the meta box title
		jQuery("#meta-image-gallery h3.hndle span").html("<?php _e('Gallery', 'maxgalleria') ?> (<?php echo count($attachments) ?> <?php _e('images', 'maxgalleria') ?>)");
		jQuery("#simplemodal-container img#image-to-crop").css({"height": "518px", "width":"auto" });
		
		
		// Image moving and re-ordering
		jQuery("#gallery-media").dataTable({ bPaginate: false }).rowReordering({
			fnAlert: function(message) {
				alert(message);
			},
			fnSuccess: function() {
				reorderImages();
			}
		});

		// Lightbox
    jQuery('a.lightbox').magnificPopup({type:'image'});  

		
		// Need the menu order table cell, but don't need to show it
		jQuery("th.order").css("display", "none");
		jQuery("td.order").css("display", "none");
		
		jQuery(".maxgalleria-meta .media table td").hover(
			function() {
				jQuery(this).find(".actions").css("visibility", "visible");
				jQuery(this).siblings().find(".actions").css("visibility", "visible");
			},
			function() {
				jQuery(this).find(".actions").css("visibility", "hidden");
				jQuery(this).siblings().find(".actions").css("visibility", "hidden");
			}
		);
		
		jQuery("#select-all").click(function() {
			 jQuery(".checkbox input[type='checkbox']").attr("checked", jQuery("#select-all").is(":checked")); 
		});
		
		jQuery("#bulk-action-apply").click(function() {
			console.log('bulk-action-apply');
			var bulk_action = jQuery("#bulk-action-select").val();
			

			if (bulk_action == "watermark") {
				
	    <?php if (class_exists("MaxGalleriaWatermark")) { ?>
						
				var counter = 0;
				var form_data_array = jQuery("#post").serializeArray();
				var image_ids = "";
				for (i = 0; i < form_data_array.length; i++) {
					if (form_data_array[i].name == "media-id[]") {
						if(counter == 0)
						  image_ids += form_data_array[i].value;
						else
							image_ids += "," + form_data_array[i].value;
						counter++;
					}
				}

				if (image_ids != "") {
					
					var wm_nonce_value = jQuery("#<?php echo $maxgalleria_watermark->nonce_watermark_gallery_images['name'] ?>").val();

					jQuery.ajax({
						type: "POST",
						url: ajaxurl,          
						data: {
								action: 'mg_watermark_gallery_images',
								image_ids: image_ids,
								wm_nonce_value: wm_nonce_value
						},
						success: function(result) {
							if (result == "success") {
								reloadPage();
								alert('The images were successful watermarked.');              
							}
							else {
								alert("There was a problem watermarking the images.");
							}
						}                
					});					
				}
				
			<?php }?>

		  } else if (bulk_action == "edit") {
				var form_data_array = jQuery("#post").serializeArray();

				var image_ids = "";
				for (i = 0; i < form_data_array.length; i++) {
					if (form_data_array[i].name == "media-id[]") {
						image_ids += form_data_array[i].value + ",";
					}
				}

				if (image_ids != "") {
					tb_show("<?php _e('Edit Bulk Images', 'maxgalleria') ?>", "<?php echo MAXGALLERIA_PLUGIN_URL ?>/meta/image-edit-bulk.php?image_ids=" + image_ids + "&TB_iframe=true");
				}
			}
			else {
				var form_data = jQuery("#post").serialize();
				var data_action = "";
				
				if (bulk_action == "exclude") { data_action = form_data + "&action=exclude_bulk_images_from_gallery"; }
				if (bulk_action == "include") { data_action = form_data + "&action=include_bulk_images_in_gallery"; }
				if (bulk_action == "remove") { data_action = form_data + "&action=remove_bulk_images_from_gallery"; }
				
				if (data_action != "") {
					jQuery.ajax({
						type: "POST",
						url: "<?php echo admin_url('admin-ajax.php') ?>",
						data: data_action,
						success: function(message) {
							if (message != "") {
								alert(message);
								reloadPage();
							}
						}
					});
				}
			}
			
			return false;
		});
		
		jQuery("#list-view").on("click", function(e) {
			e.preventDefault(); 
			jQuery("#rows-view").removeClass("active");
			jQuery("#grid-view").removeClass("active");
			jQuery(this).addClass("active");
			jQuery("#gallery-media_wrapper").removeClass("rows images");
			jQuery("#gallery-media_wrapper").removeClass("grid");
			jQuery("#gallery-media_wrapper").addClass("list");
			jQuery(".maxgalleria-meta .bulk-actions").show();
		});

		jQuery("#rows-view").on("click", function(e) {
			e.preventDefault(); 
			jQuery("#list-view").removeClass("active");
			jQuery("#grid-view").removeClass("active");
			jQuery(this).addClass("active");
			jQuery("#gallery-media_wrapper").removeClass("list");
			jQuery("#gallery-media_wrapper").removeClass("grid");
			jQuery("#gallery-media_wrapper").addClass("rows images");
			jQuery(".maxgalleria-meta .bulk-actions").show();
		});

		jQuery("#grid-view").on("click", function(e) {
			e.preventDefault(); 
			jQuery("#list-view").removeClass("active");
			jQuery("#rows-view").removeClass("active");
			jQuery(this).addClass("active");
			jQuery("#gallery-media_wrapper").removeClass("list");
			jQuery("#gallery-media_wrapper").removeClass("rows images");
			jQuery("#gallery-media_wrapper").addClass("grid");
			jQuery(".maxgalleria-meta .bulk-actions").hide();
		});
		
	});
	
	function editImage(image_id) {
		tb_show("<?php _e('Edit Image', 'maxgalleria') ?>", "<?php echo MAXGALLERIA_PLUGIN_URL ?>/meta/image-edit.php?image_id=" + image_id + "&gallery_id=" + <?php echo $post->ID ?> + "&TB_iframe=true");
		return false;
	}
	
	function excludeImage(image_id) {
		var result = confirm("<?php _e('Are you sure you want to exclude this image from the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $image_gallery->nonce_image_exclude_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'exclude_single_image_from_gallery',
					id: image_id,
					<?php echo $image_gallery->nonce_image_exclude_single['name'] ?>: nonce_value
				},
				success: function(message) {
					if (message != "") {
						alert(message);
						reloadPage();
					}
				}
			});
			
			return false;
		}
	}
	
	function includeImage(image_id) {
		var result = confirm("<?php _e('Are you sure you want to include this image in the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $image_gallery->nonce_image_include_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'include_single_image_in_gallery',
					id: image_id,
					<?php echo $image_gallery->nonce_image_include_single['name'] ?>: nonce_value
				},
				success: function(message) {
					if (message != "") {
						alert(message);
						reloadPage();
					}
				}
			});
			
			return false;
		}
	}
	
	function removeImage(image_id) {
		var result = confirm("<?php _e('Are you sure you want to remove this image from the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $image_gallery->nonce_image_remove_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'remove_single_image_from_gallery',
					id: image_id,
					<?php echo $image_gallery->nonce_image_remove_single['name'] ?>: nonce_value
				},
				success: function(message) {
					if (message != "") {
						alert(message);
						reloadPage();
					}
				}
			});
			
			return false;
		}
	}
	
	function reorderImages() {
		jQuery(".maxgalleria-meta .media table td.order").each(function() {
			jQuery(this).siblings().find(".media-order-input").val(jQuery(this).html());
		});
		
		var form_data = jQuery("#post").serialize();
		
		jQuery.ajax({
			type: "POST",
			url: "<?php echo admin_url('admin-ajax.php') ?>",
			data: form_data + "&action=reorder_images"
		});
		
		return false;
	}
	
	function reloadPage() {
		tb_remove();
		window.location = "<?php echo admin_url() ?>post.php?post=<?php echo $post->ID ?>&action=edit";
	}	
</script>

<div class="add-media">
  <a class="mxg-btn maxgalleria-open-media" href="#"><i class="fa fa-plus-circle"></i> <?php _e('Add Images', 'maxgalleria') ?></a>		
</div>
<?php if (count($attachments) > 0) { ?>
	<div class="bulk-actions">
		<select name="bulk-action-select" id="bulk-action-select">
			<option value=""><?php _e('Bulk Actions', 'maxgalleria') ?></option>
			<option value="edit"><?php _e('Edit', 'maxgalleria') ?></option>
			<option value="exclude"><?php _e('Exclude', 'maxgalleria') ?></option>
			<option value="include"><?php _e('Include', 'maxgalleria') ?></option>
			<option value="remove"><?php _e('Remove', 'maxgalleria') ?></option>
		<?php if(class_exists('MaxGalleriaWatermark')) { ?>
			<option value="watermark"><?php _e('Watermark', 'maxgalleria') ?></option>
		<?php } ?>	
		</select>
		<input type="button" id="bulk-action-apply" class="button" value="<?php _e('Apply', 'maxgalleria') ?>" />
	</div>
  <?php if (class_exists('MaxGalleriaWatermark')) { ?>
  <div class="add-media">
		<input type="hidden" name="gallery_id" id="gallery_id" value="<?php echo $post->ID ?>" />    
	  <?php wp_nonce_field($maxgalleria_watermark->nonce_watermark_gallery_images['action'], $maxgalleria_watermark->nonce_watermark_gallery_images['name']); ?>        
  </div>  
  <?php } ?>
	<ul class="views">
		<li><a id="list-view" class="active" title="<?php _e('List', 'maxgalleria') ?>"><i class="fa fa-th-list"></i></a></li>
		<li><a id="rows-view" title="<?php _e('Rows', 'maxgalleria') ?>"><i class="fa fa-bars"></i></a></li> 
		<li><a id="grid-view" title="<?php _e('Grid', 'maxgalleria') ?>"><i class="fa fa-th"></i></a></li>
	</ul>
<?php } ?>
<div class="clear"></div>

<div class="media">	
	<?php if($template === 'image-showcase') { ?>
	  <p><?php _e('For the best user experience you will want your images to be the same height. Use the Crop feature under Images to adjust your images as needed.', 'maxgalleria') ?></p>
	<?php } else if($template === 'masonry') { ?>
		<p><?php _e('For the best performance, we recommend uploading images that are the actual size to be displayed rather than full size images. It is also helpful if the images vary in height. Large gallery images are automatically cropped. The Masonry addon automatically determines the best layout for your images based on their size.', 'maxgalleria') ?></p>
	<?php } ?>
	<div class="adding-media-library-images-note alert alert-info">
		<div class="gif">
			<img src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/loading-small.gif" width="16" height="16" alt="" />
		</div>
		<div class="text">
			<h4><?php _e('Adding images from media library, this might take a few moments, please wait...', 'maxgalleria') ?></h4>
		</div>
		<div class="clear"></div>
	</div>
	
	<?php if (count($attachments) < 1) { ?>
		<h4><?php _e('No images have been added to this gallery.', 'maxgalleria') ?></h4>
	<?php } else { ?>
		<table id="gallery-media" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="order">&nbsp;</th>
					<th class="checkbox"><input type="checkbox" name="select-all" id="select-all" /></th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th class="reorder"></th>
				</tr>
			</thead>
			<tbody>
			<?php $uploads = wp_upload_dir(); ?>
			<?php $new_menu_order = 1 ?>	
			<?php foreach ($attachments as $attachment) { ?>
				<?php $is_excluded = get_post_meta($attachment->ID, 'maxgallery_attachment_image_exclude', true) ?>
				
				<tr id="<?php echo $attachment->ID ?>">
					<?php 
					if($attachment->menu_order === -1 || $attachment->menu_order === 0 )						
					  $menu_order = $new_menu_order;
					else
					  $menu_order = $attachment->menu_order;	
					?>
					<td class="order"><?php echo $menu_order ?></td>
					<td class="checkbox">
						<input type="checkbox" name="media-id[]" id="media-id-<?php echo $attachment->ID ?>" value="<?php echo $attachment->ID ?>" />
						<input type="hidden" name="media-order[]" id="media-order-<?php echo $attachment->ID ?>" value="<?php echo $attachment->menu_order ?>" class="media-order-input" />
						<input type="hidden" name="media-order-id[]" id="media-order-id-<?php echo $attachment->ID ?>" value="<?php echo $attachment->ID ?>" />
					</td>
					<td class="thumb image">
						<a href="<?php echo $maxgalleria->mg_get_attachment_url($attachment, $uploads); ?>" class="lightbox" rel="media">
							<?php if ($is_excluded == true) { ?>
								<div class="exclude">
									<?php echo wp_get_attachment_image($attachment->ID, MAXGALLERIA_META_IMAGE_THUMB_SMALL) ?>
								</div>
							<?php } else { ?>
								<?php echo wp_get_attachment_image($attachment->ID, MAXGALLERIA_META_IMAGE_THUMB_SMALL) ?>
							<?php } ?>
						</a>
					</td>
					<td class="text">
						<div class="details">
							<div class="detail-label"><?php _e('Title', 'maxgalleria') ?>:</div>
							<div class="detail-value title-value"><?php echo $attachment->post_title ?></div>
							<div class="clear"></div>
							
							<div class="detail-label"><?php _e('Alt Text', 'maxgalleria') ?>:</div>
							<div class="detail-value"><?php echo get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ?></div>
							<div class="clear"></div>
							
							<div class="detail-label"><?php _e('Caption', 'maxgalleria') ?>:</div>
							<div class="detail-value"><?php echo $attachment->post_excerpt ?></div>
							<div class="clear"></div>
							
							<div class="detail-label"><?php _e('Meta', 'maxgalleria') ?>:</div>
							<div class="detail-value">
								<?php echo $image_gallery->get_image_size_display($attachment) ?> |
								<?php echo $attachment->post_mime_type ?> |
								<?php echo date(get_option('date_format'), strtotime($attachment->post_date)) ?>
							</div>
							<div class="clear"></div>
							
							<div class="detail-label"><?php _e('Link', 'maxgalleria') ?>:</div>
							<div class="detail-value">
								<a href="<?php echo get_post_meta($attachment->ID, 'maxgallery_attachment_image_link', true) ?>" target="_blank">
									<?php echo get_post_meta($attachment->ID, 'maxgallery_attachment_image_link', true) ?>
								</a>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<td class="reorder">
<!--						<div class="reorder-media">
						</div>-->
						<?php $crop_url = site_url() . "/wp-admin/post.php?post={$attachment->ID}&action=edit&image-editor"; ?>	
						<div class="mxg-actions">
              <a title="<?php _e('Edit', 'maxgalleria') ?>" onclick="javascript:editImage(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-pencil"></i></a>
              <a href="<?php echo $crop_url; ?>" target="_blank" title="<?php _e('Crop', 'maxgalleria') ?>" ><i class="fa fa-fw fa-crop"></i></a>
              <a title="<?php _e('Remove', 'maxgalleria') ?>" onclick="javascript:removeImage(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-close"></i></a>
							<?php if ($is_excluded) { ?>
              <a title="<?php _e('Include', 'maxgalleria') ?>" onclick="javascript:includeImage(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-eye"></i></a>
							<?php } else { ?>
              <a title="<?php _e('Exclude', 'maxgalleria') ?>" onclick="javascript:excludeImage(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-eye-slash"></i></a>
							<?php } ?>
              <a title="<?php _e('Reorder', 'maxgalleria') ?>" class="mxg-reorder"><i class="fa fa-fw fa-sort"></i></a>
            </div>

					</td>
				</tr>
				<?php $new_menu_order++; ?>
			<?php } ?>
			</tbody>
		</table>
		
		<?php wp_nonce_field($image_gallery->nonce_image_exclude_single['action'], $image_gallery->nonce_image_exclude_single['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_exclude_bulk['action'], $image_gallery->nonce_image_exclude_bulk['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_include_single['action'], $image_gallery->nonce_image_include_single['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_include_bulk['action'], $image_gallery->nonce_image_include_bulk['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_remove_single['action'], $image_gallery->nonce_image_remove_single['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_remove_bulk['action'], $image_gallery->nonce_image_remove_bulk['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_image_reorder['action'], $image_gallery->nonce_image_reorder['name']) ?>
		<?php wp_nonce_field($image_gallery->nonce_crop_image['action'], $image_gallery->nonce_crop_image['name']) ?>
	<?php } ?>
</div>