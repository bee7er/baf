<?php
global $wpdb;
global $pagenow;
global $maxgalleria_media_library_pro;
global $post;
$ajax_nonce = wp_create_nonce( "media-send-to-editor" );				

if(isset($_GET['post'])) {
  $post_id = $_GET['post'];
} else {
	if(isset($post->ID))
	  $post_id = $post->ID;
	else
		$post_id = '0';
}	
//	$post_id = '0';
//if($post_id === null)
//	$post_id = '0';
//
//if($post_id !== '0')
//	$featured_image_id = get_post_thumbnail_id();

  
?>

<?php // Only run in post/page creation and edit screens ?>
<?php if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) { ?>

	<div id="select-mlp-container" style="display: none;">
		<div class="wrap">
						
			<?php 
				$current_folder_id = get_option(MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID );
			?>
			
			<div id="mgmlp-library-container">
				
			</div><!-- mgmlp-library-container -->
			
			<script>
				<?php if(class_exists('WooCommerce'))	{ ?>
				var hide_checkboxes = false;
				<?php } else { ?>
				var hide_checkboxes = true;
				<?php } ?>
				jQuery(document).ready(function(){
					
					var folder = <?php echo $current_folder_id; ?>;
										
					jQuery("#ajaxloader").show();

					jQuery.ajax({
						type: "POST",
						async: true,
						data: { action: "mlp_tb_load_folder", folder: folder, nonce: mgmlp_ajax.nonce },
						url : mgmlp_ajax.ajaxurl,
						dataType: "html",
						success: function (data) {
							jQuery("#ajaxloader").hide();          
							jQuery("#mgmlp-library-container").html(data);
				    //jQuery("#mgmlp-tb-container").html(data);						
							
							//console.log(window.hide_checkboxes);
							if(window.hide_checkboxes) {
								jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
		            jQuery("a.tb-media-attachment").css("cursor", "pointer");
							} else {
								jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
		            jQuery("a.tb-media-attachment").css("cursor", "default");
							}	
					},
						error: function (err)
							{ alert(err.responseText);}
					});

					jQuery(document).on("click", ".media-link", function () {						

						var folder = jQuery(this).attr('folder');

						jQuery("#ajaxloader").show();

						jQuery.ajax({
							type: "POST",
							async: true,
							data: { action: "mlp_load_folder", folder: folder, nonce: mgmlp_ajax.nonce },
							url : mgmlp_ajax.ajaxurl,
							dataType: "html",
							success: function (data) {
								jQuery("#ajaxloader").hide();          
								//jQuery("#mgmlp-tb-container").html(data);
							jQuery("#mgmlp-file-container").html(data);						
//							  console.log(window.hide_checkboxes);
								if(window.hide_checkboxes) {
									jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
		              jQuery("a.tb-media-attachment").css("cursor", "pointer");
								} else {
									jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
		              jQuery("a.tb-media-attachment").css("cursor", "default");
								}	
							},
							error: function (err)
								{ alert(err.responseText);}
						});

					});					
										
          jQuery("#insert_mlp_media").click(function(){
						var mlp_image_id = jQuery("#mlp_image_id").val();
						var mlp_title = jQuery("#mlp_tb_title").val();
						var mlp_caption = jQuery("#mlp_tb_caption").val();
						var mlp_alt = jQuery("#mlp_tb_alt").val();
						var mlp_desc = jQuery("#mlp_tb_desc").val();
						var mlp_alignment = jQuery("#mlp_tb_alignment").val();
						var mlp_size = jQuery("#mlp_tb_size").val();
						var mlp_link = jQuery("#mlp_tb_link_select").val();
						var mlp_custom_link = jQuery("#mlp_tb_custom_link").val();
						var mlp_image_src = jQuery("#mlp_image_src").val();
						
						var mlp_featured = jQuery("#mlp_featured").val();
						
						if(mlp_featured === 'featured') {
							
							// put the image id into the hidden field
							//jQuery("#mlp_featured_image").val(mlp_image_id);
																					
							jQuery.ajax({
								type: "POST",
								async: true, 
								data: { action: "mlp_add_featured_image", 
												id: <?php echo $post_id; ?>,
												//id: <?php //echo $post_id = $_GET['post']; ?>,
												image_id: mlp_image_id,
												nonce: mgmlp_ajax.nonce
								},
								url : mgmlp_ajax.ajaxurl,
                dataType: "html",
								success: function (data) {									
                  jQuery("#postimagediv.postbox div.inside").html(data);									
									tb_remove();
								},
								error: function (err)
									{ alert(err.responseText);}
							});
							
							return false;
		        }	
						
						mlp_caption = mlp_caption.trim();
																		
						if(mlp_image_id !== 0) {
							
							//update description
							jQuery.ajax({
								type: "POST",
								async: true, 
								data: { action: "mlp_update_description", 
												id: mlp_image_id,
												desc: mlp_desc,
												nonce: mgmlp_ajax.nonce
								},
								url : mgmlp_ajax.ajaxurl
							});
														
							jQuery("#ajaxloader").show();
														
							//console.log('size:' + mlp_size);

 							jQuery.ajax({
								type: "POST",
								async: true, 
								data: { action: "send-attachment-to-editor", 
							          attachment: { "align" : mlp_alignment,
													            "id" : mlp_image_id,
																			"image-size" : mlp_size,
																			"image_alt" : mlp_alt,
																			"post_content" : "",
																			"post_excerpt" : ""
																		},
												html: '<img src width="undefined" height="undefined" alt="" class="wp-image-165 alignnone size-full"  />',
												nonce: '<?php echo $ajax_nonce; ?>',
												post_id: <?php echo $post_id; ?>
												
							  },
								url : mgmlp_ajax.ajaxurl,
								dataType: "json",
								success: function (json) {
									
									var insert_html = json.data;
									if(mlp_link !== 'none')
										insert_html = mlp_format_link(insert_html, mlp_image_id, mlp_link, mlp_image_src, mlp_custom_link, mlp_title );
									
									if(mlp_caption !== '') {
										//console.log('processing caption');
										jQuery.ajax({
											type: "POST",
											async: true, 
											data: { action: "mlp_image_add_caption", 
															html: insert_html,
															id: mlp_image_id,
															caption: mlp_caption,
															title: mlp_title,
															align: mlp_alignment,
															url: mlp_image_src,
															size: mlp_size,
															alt: mlp_alt,
															nonce: mgmlp_ajax.nonce
											},
											url : mgmlp_ajax.ajaxurl,
											dataType: "json",
											success: function (json) {
												jQuery("#ajaxloader").hide();
												//console.log(json);
												window.send_to_editor(json);
											},
											error: function (err) { 
												alert(err.responseText);
												jQuery("#ajaxloader").hide();          
											}
										});
										
									} else {
										jQuery("#ajaxloader").hide();
										window.send_to_editor(insert_html);
									}	
									tb_remove();
									jQuery("#insert_mlp_media").prop("disabled", true);
								},
								error: function (err) { 
									alert(err.responseText);
									jQuery("#ajaxloader").hide();          
								}
							});
																																											
						}	
											
					});
										
          jQuery("#mlpp_insert_woo_product_image").click(function(){
						var mlp_image_id = jQuery("#mlp_image_id").val();
						var mlp_title = jQuery("#mlp_tb_title").val();
						var mlp_caption = jQuery("#mlp_tb_caption").val();
						var mlp_alt = jQuery("#mlp_tb_alt").val();
						var mlp_desc = jQuery("#mlp_tb_desc").val();
						var mlp_alignment = jQuery("#mlp_tb_alignment").val();
						var mlp_size = jQuery("#mlp_tb_size").val();
						var mlp_link = jQuery("#mlp_tb_link_select").val();
						var mlp_custom_link = jQuery("#mlp_tb_custom_link").val();
						var mlp_image_src = jQuery("#mlp_image_src").val();
													
						// put the image id into the hidden field
						//jQuery("#mlp_featured_image").val(mlp_image_id);

						jQuery.ajax({
							type: "POST",
							async: true, 
							data: { action: "mlp_add_featured_image", 
											id: '<?php echo $post_id; ?>',
											image_id: mlp_image_id,
											nonce: mgmlp_ajax.nonce
							},
							url : mgmlp_ajax.ajaxurl,
							dataType: "html",
							success: function (data) {									
								jQuery("#postimagediv.postbox div.inside").html(data);									
								tb_remove();
							},
							error: function (err)
								{ alert(err.responseText);}
						});

						return false;																	
					});
					
          jQuery("#mlpp_insert_into_woo_product_gallery").click(function(){
						//var mlp_image_id = jQuery("#mlp_image_id").val();
						var mlp_size = jQuery("#mlp_tb_size").val();
						//var mlp_image_src = jQuery("#mlp_image_src").val();
						
						var image_ids = new Array();
						jQuery('input[type=checkbox].mgmlp-media:checked').each(function() {  
							image_ids[image_ids.length] = jQuery(this).attr("id");
						});
												
						var serial_image_ids = JSON.stringify(image_ids.join());
						console.log(serial_image_ids);

		        var $el = jQuery( this );
	          var $product_images = jQuery( '#product_images_container' ).find( 'ul.product_images' );						
						jQuery.ajax({
							type: "POST",
							async: true,
							data: { action: "mlp_get_attachment_image_src", serial_image_ids: serial_image_ids, id: '<?php echo $post_id; ?>', nonce: mgmlp_ajax.nonce },
							//data: { action: "mlp_get_attachment_image_src", image_ids: serial_image_ids, size: mlp_size, id: '<?php echo $post_id; ?>', nonce: mgmlp_ajax.nonce },
							url: mgmlp_ajax.ajaxurl,
							dataType: "html",
							success: function (data) 
								{ 
									var mlp_image_src_array = JSON.parse(data);
									
									var product_image_ids = jQuery('#product_image_gallery').val();
									
									// insert the image into the metabox
										
									var length = mlp_image_src_array.length;   
									for (var i = 0; i < length; i++) {										
						        $product_images.append( '<li class="image" data-attachment_id="' + mlp_image_src_array[i]['image_id'] + '">' + mlp_image_src_array[i]['src'] + '<ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li></ul></li>' );
									
									// add the image ID to the list of gallery images
									if(product_image_ids === '')
										product_image_ids = mlp_image_src_array[i]['image_id'];
									else
										product_image_ids = product_image_ids + ',' + mlp_image_src_array[i]['image_id'];
									}										
									jQuery('#product_image_gallery').val(product_image_ids);
									tb_remove();
								},
									error: function (err)
								{ alert(err.responseText)}
						});
					});
										
					jQuery("#mlp_tb_link_select").change(function () {
							var choice = this.value;
							if(choice === 'custom') {
								jQuery("#mlp_tb_custom_link").prop('disabled', false);
						  } else {
								jQuery("#mlp_tb_custom_link").prop('disabled', true);
							}	
					});					
										
					jQuery(document).on('click','.tb-media-attachment',function(){
						//if(window.hide_checkboxes) { don't check since adding support for Woo
						  var image_id = jQuery(this).attr("id");
						
							jQuery("#ajaxloader").show();

							jQuery.ajax({
								type: "POST",
								async: true,
  				      data: { action: "mlp_get_image_info", image_id: image_id, nonce: mgmlp_ajax.nonce },
								url : mgmlp_ajax.ajaxurl,
								dataType: "json",
								success: function (data) {									
									jQuery("#insert_mlp_media").prop("disabled", false);
									jQuery("#ajaxloader").hide(); 
									jQuery("#mlp_tb_title").val(data.title);
									jQuery("#mlp_tb_caption").val(data.caption);
									jQuery("#mlp_tb_alt").val(data.alt);
									jQuery("#mlp_tb_desc").val(data.description);
									jQuery("#mlp_image_id").val(data.id);
									jQuery("#mlp_image_src").val(data.src);									
										
									jQuery('#mlp_tb_size').empty();
									var option = '';
									jQuery.each(data.available_sizes, function(key, value) {
										option += '<option value="'+ key + '">' + capitalize(key) + " &ndash; "  + value + '</option>';
									});									
									jQuery('#mlp_tb_size').append(option);									
									
								},
								error: function (err) { 
									alert(err.responseText);
									jQuery("#ajaxloader").hide();          
								}
							});
						//}
					});
					
					jQuery(document).on('click','#display_mlpp_images',function(){
						var folder_id = jQuery(this).attr('folder_id');
						var image_link = '0';

						jQuery.ajax({
							type: "POST",
							async: true,
							data: { action: "mlp_display_folder_contents_ajax", current_folder_id: folder_id, image_link: image_link, display_type: 1, nonce: mgmlp_ajax.nonce },
							url: mgmlp_ajax.ajaxurl,
							dataType: "html",
							success: function (data) 
								{ 
									//console.log(window.hide_checkboxes);
									if(window.hide_checkboxes) {
										jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
		                jQuery("a.tb-media-attachment").css("cursor", "pointer");
									} else {
										jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
		                jQuery("a.tb-media-attachment").css("cursor", "default");
									}	
									jQuery("#mgmlp-file-container").html(data); 
								},
									error: function (err)
								{ alert(err.responseText)}
								});
					});

					jQuery(document).on('click','#display_mlpp_titles',function(){
						//console.log('display_mlpp_titles');
						var folder_id = jQuery(this).attr('folder_id');
						var image_link = '0';

						jQuery.ajax({
							type: "POST",
							async: true,
							data: { action: "mlp_display_folder_contents_ajax", current_folder_id: folder_id, image_link: image_link, display_type: 2, nonce: mgmlp_ajax.nonce },
							url: mgmlp_ajax.ajaxurl,
							dataType: "html",
							success: function (data) 
								{ 
									//console.log(window.hide_checkboxes);
									if(window.hide_checkboxes) {
										jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
		                jQuery("a.tb-media-attachment").css("cursor", "pointer");
									} else {
										jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
		                jQuery("a.tb-media-attachment").css("cursor", "default");
									}										
									jQuery("#mgmlp-file-container").html(data); 
								},
									error: function (err)
								{ alert(err.responseText)}
								});
					});
					
					jQuery(document).on('click','#insert_mlp_pe_images',function(){
						var output = "";
						
						if (jQuery("input.mgmlp-media:checkbox:checked").length == 0) {
							alert("Nothing was selected. Check the images you want to include in a gallery and then click the Add button.");
						}	
						
						jQuery('input.mgmlp-media[type=checkbox]:checked').each(function() {  
							var attachment_id = jQuery(this).attr("id");
							var image_html = jQuery(this).closest('div.attachment-name').parent().html();
							if(image_html !== undefined) {
								output += '<li id="' + attachment_id +'">';
								output += image_html;
								output += '</li>';
						  }
														
						});

						jQuery("#mgmlp-gallery-list").append(output);
						jQuery(".mgmlp-media").removeAttr('checked');  

					});
					
					jQuery(document).on('click','#remove_mlp_pe_images',function(){						
						jQuery('ul#mgmlp-gallery-list li div.attachment-name span.image_select input[type=checkbox].mgmlp-media:checked').each(function() {  
							jQuery(this).parents("li:first").remove();
						});						
					});

					jQuery(document).on('click','#insert_mlp_wp_gallery',function(){
						//var id_list = new Array();
						var id_string = "";
						var first = true;
						var shortcode = "";
            jQuery('ul#mgmlp-gallery-list li div.attachment-name span.image_select input.mgmlp-media').each(function() {  
							var nextid = jQuery(this).attr("id");
							//id_list[id_list.length] = nextid;
							if(!first) {
							  id_string += "," + nextid;
							} else {
							  id_string += nextid;
							}	
							first = false;
						});
						var gallery_type = jQuery('#mgmlp-gal-type').val();
						var gallery_columns = jQuery('#mgmlp-gal-columns').val();
						var image_order = jQuery('#mgmlp-gal-order').val();
						var order_type = jQuery('#mgmlp-gal-order-type').val();
						var image_size = jQuery('#mgmlp-gal-size').val();
						var gallery_post_id = jQuery('#gallery_post_id').val();
						var slides_autl_start = jQuery('#slides-autl-start').val();
					
						shortcode = '[gallery ';
						
						if(gallery_type !== 'none')
						  shortcode += 'type="' + gallery_type + '" ';

						if(gallery_columns !== 'none')
							shortcode += 'columns="' + gallery_columns +'" ';
						
						if(order_type !== 'none')
						  shortcode += 'orderby="' + order_type + ' ' + image_order + '" ';
		
						if(image_size !== 'none')
		          shortcode += 'size="' + image_size + '" ';
						
						if(gallery_post_id.length > 0)
							shortcode += 'id="'+ gallery_post_id +'" ';
						
						if(gallery_type === 'slideshow') {
							if(slides_autl_start !== 'none') {
							  shortcode += 'autostart="' + slides_autl_start + '" ';
							}
					  }
						
						if(id_string.length > 0)
						shortcode += 'ids="' + id_string + '" ';
												
						shortcode += ']';
						
						window.send_to_editor(shortcode);
						tb_remove();
												
					});
					
					jQuery(document).on('click','#select_all_mlp_wp_gallery',function(){
						jQuery("ul.mg-media-list li div.attachment-name span.image_select input.mgmlp-media").prop("checked", !jQuery("ul.mg-media-list li div.attachment-name span.image_select input.mgmlp-media").prop("checked"));
					});

					jQuery(document).on('click','#clear_mlp_pe_images',function(){
						jQuery("ul#mgmlp-gallery-list").empty();
					});

		      jQuery("#mlp-show-library").click(function(){
						jQuery("div#mgmlp-tb-container input.mgmlp-media").hide();
		        jQuery("a.tb-media-attachment").css("cursor", "pointer");
						window.hide_checkboxes = false;
					});
					
		      jQuery("#mlp-gallery-insert").click(function(){
						jQuery("div#mgmlp-tb-container input.mgmlp-media").show();
		        jQuery("a.tb-media-attachment").css("cursor", "default");
						window.hide_checkboxes = false;
					});
					
															
				}); 
												
				function capitalize(str) {
						strVal = '';
						str = str.split(' ');
						for (var chr = 0; chr < str.length; chr++) {
								strVal += str[chr].substring(0, 1).toUpperCase() + str[chr].substring(1, str[chr].length) + ' '
						}
						return strVal
				}	
				function mlp_format_link(insert_html, mlp_image_id, mlp_link, mlp_image_src, mlp_custom_link, mlp_title ) {
					
					var html = '';
					switch(mlp_link) {
 						case 'file':
					    html = '<a href="' + mlp_image_src + '" title="'+mlp_title+'">'+mlp_title+'</a>';
							break;
						case 'post':
					    html = '<a href="<?php echo home_url(); ?>/?attachment_id='+ mlp_image_id +'" rel="attachment wp-att-'+ mlp_image_id + '" title="'+mlp_title+'">' + insert_html + '</a>';							
							break;
						case 'custom':
					    html = '<a href="' + mlp_custom_link + '" title="'+mlp_title+'">'+insert_html+'</a>';
							break;										
				  }	
					return html;
				}	
			</script>  
			

		  <div class="clearfix"></div>
			<div id="mlp_insert_row">
				<div class="mlp_insert_info_row">
					<label id="mlp_tb_title_label"><?php _e('Title', 'maxgalleria-media-library'); ?> </label><input type="text" id="mlp_tb_title">
					<label id="mlp_tb_caption_label"><?php _e('Caption', 'maxgalleria-media-library'); ?> </label><input type="text" id="mlp_tb_caption">
					<label id="mlp_tb_alt_label"><?php _e('Alt', 'maxgalleria-media-library'); ?> </label><input type="text" id="mlp_tb_alt">
				</div>
				<div class="mlp_insert_info_row">
					<label id="mlp_tb_desc_label"><?php _e('Description', 'maxgalleria-media-library'); ?> </label><input type="text" id="mlp_tb_desc">
					<label id="mlp_tb_align_label"><?php _e('Alignment', 'maxgalleria-media-library'); ?> </label>
					<select id="mlp_tb_alignment">
						<option value="left"><?php _e('Left', 'maxgalleria-media-library'); ?></option>
						<option value="center"><?php _e('Center', 'maxgalleria-media-library'); ?></option>
						<option value="right"><?php _e('Right', 'maxgalleria-media-library'); ?></option>
						<option selected="" value="none"><?php _e('None', 'maxgalleria-media-library'); ?></option>
					</select>
					<label id="mlp_tb_size_label">Size </label>
					<select id="mlp_tb_size">
					</select>					
				</div>
				<div class="mlp_insert_info_row">
					<label id="mlp_tb_link_to_label"><?php _e('Link To', 'maxgalleria-media-library') ?> </label>
					<select id="mlp_tb_link_select">
						<option selected="" value="none"><?php _e('None', 'maxgalleria-media-library') ?></option>
						<option value="file"><?php _e('Media File', 'maxgalleria-media-library') ?></option>
						<option value="post"><?php _e('Attachment Page', 'maxgalleria-media-library') ?></option>				
						<option value="custom"><?php _e('Custom URL', 'maxgalleria-media-library') ?></option>				
					</select>
					<label id="mlp_tb_custom_link_label"><?php _e('Custom Link', 'maxgalleria-media-library') ?> </label>
          <input type="url" id="mlp_tb_custom_link" disabled>
					<input type="hidden" id="mlp_image_id" value="">
					<input type="hidden" id="mlp_image_src" value="">
					<input type="hidden" id="mlp_featured" value="">
					<input id="insert_mlp_media" type="button" class="button-primary" value="<?php _e('Insert', 'maxgalleria-media-library') ?>" disabled="" />
					<a class="button-secondary" style="margin-left: 10px;" onclick="tb_remove();"><?php _e('Cancel', 'maxgalleria-media-library') ?></a>
				</div>
					<?php $screen = get_current_screen(); ?>
				<?php if(class_exists('WooCommerce'))	{ ?>
						<?php if($screen->post_type === 'product') { ?>
					    <input id="mlpp_insert_woo_product_image" type="button" class="button-primary" value="<?php _e('Select as Product Imge', 'maxgalleria-media-library') ?>" />
					    <input id="mlpp_insert_into_woo_product_gallery" type="button" class="button-primary" value="<?php _e('Insert into Product Gallery', 'maxgalleria-media-library') ?>" />
				  <?php } ?>
				<?php } ?>
			</div>
			<div id="insert_wp_gallery">
				<div class="wpg_options">
					<div class="mlp_go_row_long">Gallery Options</div>
					<div class="mlp_go_row">
						
						<div class="mlp_go_label">Type</div>
						<div class="mlp_go_selection">
							<select id="mgmlp-gal-type" class="mlp_gallery_options_sel">
								<option value="none" selected>No Selection</option>		
								<option value="thumbnail">Thumbnail</option>		
								<option value="slideshow">Slideshow</option>		
						 <?php if(class_exists('Jetpack_Gallery_Settings')) { ?>
								<option value="rectangular">Rectangular</option>		
								<option value="square">Square</option>		
								<option value="circle">Circle</option>		
						 <?php } ?>		
							</select>		
						</div>							
												
						<div class="mlp_go_label">Columns</div>
						<div class="mlp_go_selection">
							<select id="mgmlp-gal-columns" class="mlp_gallery_options_sel">
								<option value="none" selected>No Selection</option>		
								<option value="1">1</option>		
								<option value="2">2</option>		
								<option value="3">3</option>		
								<option value="4">4</option>		
								<option value="5">5</option>		
								<option value="6">6</option>		
								<option value="7">7</option>		
								<option value="8">8</option>		
								<option value="9">9</option>		
								<option value="10">10</option>		
							</select>		
						</div>							
												
						<div class="mlp_go_label">Order By</div>
						<div class="mlp_go_selection">
							<select id="mgmlp-gal-order-type" class="mlp_gallery_options_sel">
								<option value="none" selected>No Selection</option>		
								<option value="ID">ID</option>		
								<option value="menu_order">Menu Order</option>		
								<option value="rand">Random</option>		
								<option value="title">Title</option>		
							</select>		
						</div>							
						
						<div class="mlp_go_label">Order</div>
						<div class="mlp_go_selection">
							<select id="mgmlp-gal-order" class="mlp_gallery_options_sel">
								<option value="ASC">Ascending</option>		
								<option value="DESC">Descending</option>		
							</select>		
						</div>							
						
						<div class="mlp_go_label">Size</div>
						<div class="mlp_go_selection">
							<select id="mgmlp-gal-size" class="mlp_gallery_options_sel">
								<option value="none" selected>No Selection</option>		
								<option value="thumbnail">Thumbnail</option>		
								<option value="medium">Medium</option>		
								<option value="large">Large</option>		
								<option value="full">Full</option>		
							</select>		
						</div>													
												
					</div>
				</div>
				
				<div class="wpg_options">
					<div class="mlp_go_row_long">&nbsp;</div>
					
						<div class="mlp_go_label">Post ID</div>
						<input class="mlp_gallery_options_input" type="text" value="" name="gallery_post_id" id="gallery_post_id">
						
						<div id="mlp-slider-section">
							<div class="mlp_go_label">Auto Start Slides</div>
							<div class="mlp_go_selection">
								<select id="slides-autl-start" class="mlp_gallery_options_sel">
								  <option value="none" selected>No Selection</option>		
									<option value="true">Yes</option>		
									<option value="false">No</option>		
								</select>		
							</div>
						</div>						
						
					  <input id="insert_mlp_pe_images" type="button" class="button-primary" title="Add select images to the gallery image box." value="<?php _e('Add', 'maxgalleria-media-library') ?>" disabled="" />
					  <input id="remove_mlp_pe_images" type="button" class="button-primary" title="Remove selected images from the gallery image box." value="<?php _e('Remove', 'maxgalleria-media-library') ?>" disabled="" />
					  <input id="clear_mlp_pe_images" type="button" class="button-primary" title="Remove selected images from the gallery image box." value="<?php _e('Clear', 'maxgalleria-media-library') ?>" disabled="" />
					  <input id="select_all_mlp_wp_gallery" type="button" class="button-primary" title="Select all images in the current folder" value="<?php _e('Select All', 'maxgalleria-media-library') ?>" disabled="" />
					  <input id="insert_mlp_wp_gallery" type="button" class="button-primary" title="Insert the gallery into the current post or page." value="<?php _e('Insert', 'maxgalleria-media-library') ?>" disabled="" />
											
				</div>
				
				<div id="wpg_selections">
					<p>&nbsp;</p>					
					<ul id="mgmlp-gallery-list">
							
					</ul>
				</div>
				
			</div>
		</div>
	</div>
<?php } ?>