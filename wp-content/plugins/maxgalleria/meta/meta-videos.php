<?php
global $post;
global $maxgalleria;

$video_gallery = $maxgalleria->video_gallery;

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
		// To add the video count in the meta box title
		jQuery("#meta-video-gallery h3.hndle span").html("<?php _e('Gallery', 'maxgalleria') ?> (<?php echo count($attachments) ?> <?php _e('videos', 'maxgalleria') ?>)");
		
		// Video moving and re-ordering
		jQuery("#gallery-media").dataTable({ bPaginate: false }).rowReordering({
			fnAlert: function(message) {
				alert(message);
			},
			fnSuccess: function() {
				reorderVideos();
			}
		});
		
		// Need the menu order table cell, but don't need to show it
		jQuery("th.order").css("display", "none");
		jQuery("td.order").css("display", "none");
		
		jQuery("#gallery_media_select_button").click(function() {				
			post_id = <?php echo $post->ID ?>;
			tb_show("<?php _e('Add Videos', 'maxgalleria') ?>", "<?php echo MAXGALLERIA_PLUGIN_URL ?>/meta/video-add.php?post_id=" + post_id + "&TB_iframe=true");
			return false;
		});
		
		jQuery(window).bind("tb_unload", function() {
			reloadPage();
		});
		
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
			 jQuery("input[type='checkbox']").attr("checked", jQuery("#select-all").is(":checked")); 
		});
		
		jQuery("#bulk-action-apply").click(function() {
			var bulk_action = jQuery("#bulk-action-select").val();
			
			if (bulk_action == "edit") {
				var form_data_array = jQuery("#post").serializeArray();

				var video_ids = "";
				for (i = 0; i < form_data_array.length; i++) {
					if (form_data_array[i].name == "media-id[]") {
						video_ids += form_data_array[i].value + ",";
					}
				}

				if (video_ids != "") {
					tb_show("<?php _e('Edit Bulk Videos', 'maxgalleria') ?>", "<?php echo MAXGALLERIA_PLUGIN_URL ?>/meta/video-edit-bulk.php?video_ids=" + video_ids + "&TB_iframe=true");
				}
			}
			else {
				var form_data = jQuery("#post").serialize();
				var data_action = "";
				
				if (bulk_action == "exclude") { data_action = form_data + "&action=exclude_bulk_videos_from_gallery"; }
				if (bulk_action == "include") { data_action = form_data + "&action=include_bulk_videos_in_gallery"; }
				if (bulk_action == "remove") { data_action = form_data + "&action=remove_bulk_videos_from_gallery"; }
				
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
			jQuery("#gallery-media_wrapper").removeClass("rows videos");
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
			jQuery("#gallery-media_wrapper").addClass("rows videos");
			jQuery(".maxgalleria-meta .bulk-actions").show();
		});

		jQuery("#grid-view").on("click", function(e) {
			e.preventDefault(); 
			jQuery("#list-view").removeClass("active");
			jQuery("#rows-view").removeClass("active");
			jQuery(this).addClass("active");
			jQuery("#gallery-media_wrapper").removeClass("list");
			jQuery("#gallery-media_wrapper").removeClass("rows videos");
			jQuery("#gallery-media_wrapper").addClass("grid");
			jQuery(".maxgalleria-meta .bulk-actions").hide();
		});
         
    jQuery("td.thumb.video a").magnificPopup({
        type: 'iframe',
        removalDelay: 160,
        fixedContentPos: false
    });                 
    
	});
	
	function viewVideo(video_id) {
    
    
    
    
//		var video = jQuery("#video-" + video_id);
//		var iframe_width = parseInt(jQuery(video).find("iframe").attr("width"));
//		var iframe_height = parseInt(jQuery(video).find("iframe").attr("height"));
//		
//		jQuery(video).modal({
//			minWidth: iframe_width + 20,
//			minHeight: iframe_height + 20,
//			overlayClose: true,
//			autoResize: true
//		});
	}
	
	function editVideo(video_id) {
		tb_show("<?php _e('Edit Video', 'maxgalleria') ?>", "<?php echo MAXGALLERIA_PLUGIN_URL ?>/meta/video-edit.php?video_id=" + video_id + "&TB_iframe=true");
		return false;
	}
	
	function excludeVideo(video_id) {
		var result = confirm("<?php _e('Are you sure you want to exclude this video from the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $video_gallery->nonce_video_exclude_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'exclude_single_video_from_gallery',
					id: video_id,
					<?php echo $video_gallery->nonce_video_exclude_single['name'] ?>: nonce_value
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
	
	function includeVideo(video_id) {
		var result = confirm("<?php _e('Are you sure you want to include this video in the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $video_gallery->nonce_video_include_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'include_single_video_in_gallery',
					id: video_id,
					<?php echo $video_gallery->nonce_video_include_single['name'] ?>: nonce_value
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
	
	function removeVideo(video_id) {
		var result = confirm("<?php _e('Are you sure you want to remove this video from the gallery?', 'maxgalleria') ?>");
		if (result == true) {
			var nonce_value = jQuery("#<?php echo $video_gallery->nonce_video_remove_single['name'] ?>").val();
			
			jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: 'remove_single_video_from_gallery',
					id: video_id,
					<?php echo $video_gallery->nonce_video_remove_single['name'] ?>: nonce_value
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
	
	function reorderVideos() {
		jQuery(".maxgalleria-meta .media table td.order").each(function() {
			jQuery(this).siblings().find(".media-order-input").val(jQuery(this).html());
		});
		
		var form_data = jQuery("#post").serialize();
		
		jQuery.ajax({
			type: "POST",
			url: "<?php echo admin_url('admin-ajax.php') ?>",
			data: form_data + "&action=reorder_videos"
		});
		
		return false;
	}
	
	function showAddingVideosNote() {
		jQuery(".maxgalleria-meta .adding-videos-note").show();
	}
	
	function reloadPage() {
		tb_remove();
		window.location = "<?php echo admin_url() ?>post.php?post=<?php echo $post->ID ?>&action=edit";
	}
</script>
<?php 
require_once MAXGALLERIA_PLUGIN_DIR . '/addons/media-sources/youtube/youtube-options.php';

$options = new MaxGalleriaYoutubeOptions();
if($options->get_developer_api_key_default() === '') {
  $diabled = "disabled";  
  echo '<div class="maxgalleria-meta label-warning center-text">' . __('In order to access Youtube videos you are now required to obtain a Google Developer API Key. ', 'maxgalleria') . 
       '<a href="http://maxgalleria.com/youtube-api-key/" target="_blank">' . __('Click here for more details', 'maxgalleria') . '.</a> ' .          
        __('If you have Google Developer API Key ', 'maxgalleria') .
       '<a href="' . home_url() .'/wp-admin/edit.php?post_type=maxgallery&page=maxgalleria-settings&addon=maxgalleria-youtube">' . __('click here', 'maxgalleria') . '</a>' .
         __(' to enter it into Youtube Settings.', 'maxgalleria') .
          '</div><br>';  
}
else
  $diabled = "";

?>
<div class="add-media">
  <!--<input type="button" class="btn btn-primary" id="gallery_media_select_button" name="gallery_media_select_button" value="<?php _e('Add Videos', 'maxgalleria') ?>" <?php echo $diabled; ?> />-->
  <a class="mxg-btn maxgalleria-open-media" id="gallery_media_select_button" name="gallery_media_select_button" href="#" <?php echo $diabled; ?> ><i class="fa fa-plus-circle"></i> <?php _e('Add Videos', 'maxgalleria') ?></a>	
</div>
<?php if (count($attachments) > 0) { ?>
	<div class="bulk-actions">
		<select name="bulk-action-select" id="bulk-action-select">
			<option value=""><?php _e('Bulk Actions', 'maxgalleria') ?></option>
			<option value="edit"><?php _e('Edit', 'maxgalleria') ?></option>
			<option value="exclude"><?php _e('Exclude', 'maxgalleria') ?></option>
			<option value="include"><?php _e('Include', 'maxgalleria') ?></option>
			<option value="remove"><?php _e('Remove', 'maxgalleria') ?></option>
		</select>
		<input type="button" id="bulk-action-apply" class="button" value="<?php _e('Apply', 'maxgalleria') ?>" />
	</div>
	<ul class="views">
		<li><a id="list-view" class="active" title="<?php _e('List', 'maxgalleria') ?>"><i class="fa fa-th-list"></i></a></li>
		<li><a id="rows-view" title="<?php _e('Rows', 'maxgalleria') ?>"><i class="fa fa-bars"></i></a></li>
		<li><a id="grid-view" title="<?php _e('Grid', 'maxgalleria') ?>"><i class="fa fa-th"></i></a></li>
	</ul>
<?php } ?>
<div class="clear"></div>

<div class="media">
	<div class="adding-videos-note alert alert-info">
		<div class="gif">
			<img src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/loading-small.gif" width="16" height="16" alt="" />
		</div>
		<div class="text">
			<h4><?php _e('Adding videos, one moment...', 'maxgalleria') ?></h4>
		</div>
		<div class="clear"></div>
	</div>
	
	<?php if (count($attachments) < 1) { ?>
		<h4><?php _e('No videos have been added to this gallery.', 'maxgalleria') ?></h4>
	<?php } else { ?>
		<table id="gallery-media" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th class="order">&nbsp;</th>
					<th class="checkbox"><input type="checkbox" name="select-all" id="select-all" /></th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th class="reorder"><?php _e('Reorder', 'maxgalleria') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($attachments as $attachment) { ?>
				<?php $is_excluded = get_post_meta($attachment->ID, 'maxgallery_attachment_video_exclude', true) ?>
				
				<tr id="<?php echo $attachment->ID ?>">
					<td class="order"><?php echo $attachment->menu_order ?></td>
					<td class="checkbox">
						<input type="checkbox" name="media-id[]" id="media-id-<?php echo $attachment->ID ?>" value="<?php echo $attachment->ID ?>" />
						<input type="hidden" name="media-order[]" id="media-order-<?php echo $attachment->ID ?>" value="<?php echo $attachment->menu_order ?>" class="media-order-input" />
						<input type="hidden" name="media-order-id[]" id="media-order-id-<?php echo $attachment->ID ?>" value="<?php echo $attachment->ID ?>" />
					</td>
					<td class="thumb video">            
            <a href="<?php echo get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true); ?>">
						<!--<a href="#" onclick="javascript:viewVideo(<?php echo $attachment->ID ?>); return false;">-->
							<?php if ($is_excluded == true) { ?>
								<div class="exclude">
									<?php echo wp_get_attachment_image($attachment->ID, MAXGALLERIA_META_VIDEO_THUMB_SMALL) ?>
								</div>
							<?php } else { ?>							
								<?php echo wp_get_attachment_image($attachment->ID, MAXGALLERIA_META_VIDEO_THUMB_SMALL) ?>
							<?php } ?>
						</a>
						
						<?php
							//$video_url = str_replace('https://', 'http://', get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true));
              $video_url = get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true);
							$enable_related_videos = get_post_meta($attachment->ID, 'maxgallery_attachment_video_enable_related_videos', true);
							$enable_hd_playback = get_post_meta($attachment->ID, 'maxgallery_attachment_video_enable_hd_playback', true);
							
							// Initialize the embed code and then pass it to the filter to be populated
							$embed_code = '';
							$embed_code = apply_filters(MAXGALLERIA_FILTER_VIDEO_EMBED_CODE, $embed_code, $video_url, $enable_related_videos, $enable_hd_playback);
							
							echo '<div id="video-' . $attachment->ID . '" style="display: none;">';
							echo '	<div align="center">';
							echo $embed_code;
							echo '	</div>';
							echo '</div>';
						?>
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
								<?php echo $maxgalleria->common->format_seconds_to_time(get_post_meta($attachment->ID, 'maxgallery_attachment_video_seconds', true)) ?> |
								<?php echo date(get_option('date_format'), strtotime($attachment->post_date)) ?>
							</div>
							<div class="clear"></div>
							
							<div class="detail-label"><?php _e('URL', 'maxgalleria') ?>:</div>
							<div class="detail-value">
								<a href="<?php echo get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true) ?>" target="_blank">
									<?php echo get_post_meta($attachment->ID, 'maxgallery_attachment_video_url', true) ?>
								</a>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<td class="reorder">
<!--						<div class="reorder-media">
						</div>-->
						<div class="mxg-actions">
              <a title="<?php _e('Edit', 'maxgalleria') ?>" onclick="javascript:editVideo(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-pencil"></i></a>
              <a title="<?php _e('Remove', 'maxgalleria') ?>" onclick="javascript:removeVideo(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-close"></i></a>
							<?php if ($is_excluded) { ?>
              <a title="<?php _e('Include', 'maxgalleria') ?>" onclick="javascript:includeVideo(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-eye"></i></a>
							<?php } else { ?>
              <a title="<?php _e('Exclude', 'maxgalleria') ?>" onclick="javascript:excludeVideo(<?php echo $attachment->ID ?>); return false;"><i class="fa fa-fw fa-eye-slash"></i></a>
							<?php } ?>
              <a title="<?php _e('Reorder', 'maxgalleria') ?>" class="mxg-reorder"><i class="fa fa-fw fa-sort"></i></a>
            </div>

					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		
		<?php wp_nonce_field($video_gallery->nonce_video_exclude_single['action'], $video_gallery->nonce_video_exclude_single['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_exclude_bulk['action'], $video_gallery->nonce_video_exclude_bulk['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_include_single['action'], $video_gallery->nonce_video_include_single['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_include_bulk['action'], $video_gallery->nonce_video_include_bulk['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_remove_single['action'], $video_gallery->nonce_video_remove_single['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_remove_bulk['action'], $video_gallery->nonce_video_remove_bulk['name']) ?>
		<?php wp_nonce_field($video_gallery->nonce_video_reorder['action'], $video_gallery->nonce_video_reorder['name']) ?>
	<?php } ?>
</div>