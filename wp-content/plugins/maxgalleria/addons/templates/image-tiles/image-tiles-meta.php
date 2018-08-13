<?php
global $post;
$options = new MaxGalleriaImageTilesOptions($post->ID);
?>

<script type="text/javascript">		
	jQuery(document).ready(function() {
		enableDisableThumbClickNewWindow();
    enableDisableOverflowY();
    enableGallery();
		
		jQuery("#<?php echo $options->thumb_click_key ?>").change(function() {
			enableDisableThumbClickNewWindow();
		});
    
		jQuery("#<?php echo $options->fixed_content_position_key ?>").change(function() {
			enableDisableOverflowY();
		});
    
		jQuery("#<?php echo $options->gallery_enabled_key ?>").change(function() {
			enableGallery();
		});
		
		jQuery('#<?php echo $options->ns_border_color_key ?>').colpick({
				layout:'hex',
				submit:0,
				colorScheme:'dark',
				onChange:function(hsb,hex,rgb,el,bySetColor) {
					jQuery(el).css('border-color','#'+hex);
					// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
					if(!bySetColor) jQuery(el).val('#'+hex);
		    }
		}).keyup(function(){
				jQuery(this).colpickSetColor(this.value);
		});		
		        
		jQuery('#<?php echo $options->ns_shadow_color_key ?>').colpick({
				layout:'hex',
				submit:0,
				colorScheme:'dark',
				onChange:function(hsb,hex,rgb,el,bySetColor) {
					jQuery(el).css('border-color','#'+hex);
					// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
					if(!bySetColor) jQuery(el).val('#'+hex);
		    }
		}).keyup(function(){
				jQuery(this).colpickSetColor(this.value);
		});		
				        
	});
	
	
	function enableDisableThumbClickNewWindow() {
		var thumb_click = jQuery("#<?php echo $options->thumb_click_key ?>").val();
		
		if (thumb_click === "lightbox") {
			jQuery("#<?php echo $options->thumb_click_new_window_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->thumb_click_new_window_key ?>").removeAttr("checked");
		}
		else {
			jQuery("#<?php echo $options->thumb_click_new_window_key ?>").removeAttr("disabled");
		}
    
		if (thumb_click !== "lightbox") {
			jQuery(".mag-popup-settings").attr("disabled", "disabled");
      enableGallery();
    } else {
			jQuery(".mag-popup-settings").removeAttr("disabled");
      enableGallery();
    }  
	}
  
	function enableDisableOverflowY() {
		var fcp_click = jQuery("#<?php echo $options->fixed_content_position_key ?>").val();
        
		if (fcp_click !== 'true') {
			jQuery("#<?php echo $options->overflow_y_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->overflow_y_key ?>").removeAttr("checked");
		}
		else {
			jQuery("#<?php echo $options->overflow_y_key ?>").removeAttr("disabled");
		}
	}
  
	function enableGallery() {
		if (jQuery("#<?php echo $options->gallery_enabled_key ?>").attr("checked") == "checked") {      
			jQuery("#<?php echo $options->navigate_by_img_click_enabled_key ?>").removeAttr("disabled");
			jQuery("#<?php echo $options->arrow_markup_key ?>").removeAttr("disabled");
			jQuery("#<?php echo $options->prev_button_title_key ?>").removeAttr("disabled");
			jQuery("#<?php echo $options->next_button_title_key ?>").removeAttr("disabled");
			jQuery("#<?php echo $options->counter_markup_key ?>").removeAttr("disabled");
		}
		else {
			jQuery("#<?php echo $options->navigate_by_img_click_enabled_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->arrow_markup_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->prev_button_title_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->next_button_title_key ?>").attr("disabled", "disabled");
			jQuery("#<?php echo $options->counter_markup_key ?>").attr("disabled", "disabled");
		}
    
  }  
  
</script>

<div class="meta-options">
	<table>
		<tr>
			<td colspan="2" class="options-heading"><span class="mg-heading"><?php _e('GALLERY STYLES', 'maxgalleria') ?></span></td><td></td>
		</tr>
		<tr>
			<td class="padding-top">
				<?php _e('Preset Layouts:', 'maxgalleria') ?>
			</td>
			<td class="padding-top">
		    <?php if($options->hide_presets === 'off') 
				  $skins = array_merge($options->new_skins, $options->skins );
					  else
					$skins = $options->new_skins;
		     ?>
				<select id="<?php echo $options->skin_key ?>" name="<?php echo $options->skin_key ?>">
				<?php foreach ($skins as $key => $name) { ?>
					<?php $selected = ($options->get_skin() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->ns_show_border_key ?>"><?php _e('Display Border:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $options->ns_show_border_key ?>" name="<?php echo $options->ns_show_border_key ?>" <?php echo (($options->get_show_border() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Border Thickness:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input id="border-thickness-default" type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="1" class="border-thickness" <?php echo ($options->get_border_thickness() === '1') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="3" class="border-thickness" <?php echo ($options->get_border_thickness() === '3') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="5" class="border-thickness" <?php echo ($options->get_border_thickness() === '5') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="7" class="border-thickness" <?php echo ($options->get_border_thickness() === '7') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="9" class="border-thickness" <?php echo ($options->get_border_thickness() === '9') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_thickness_key ?>" value="15" class="border-thickness" <?php echo ($options->get_border_thickness() === '15') ? 'checked' : ''; ?>>
						</td>
					</tr>	
					<tr>
						<td>
							<img title="<?php _e('1 pixel', 'maxgalleria') ?>" alt="<?php _e('border thickness 1 pixel', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-01.png" >
						</td>
						<td>
							<img title="<?php _e('3 pixels', 'maxgalleria') ?>" alt="<?php _e('border thickness 3 pixels', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-03.png" >
						</td>
						<td>
							<img title="<?php _e('5 pixels', 'maxgalleria') ?>" alt="<?php _e('border thickness 5 pixels', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-05.png" >
						</td>
						<td>
							<img title="<?php _e('7 pixels', 'maxgalleria') ?>" alt="<?php _e('border thickness 7 pixels', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-07.png" >
						</td>
						<td>
							<img title="<?php _e('9 pixels', 'maxgalleria') ?>" alt="<?php _e('border thickness 9 pixels', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-09.png" >
						</td>
						<td>
							<img title="<?php _e('15 pixel', 'maxgalleria') ?>" alt="<?php _e('border thickness 15 pixels', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-15.png" >
						</td>
					</tr>
				</table>
			</td>								
		</tr>		
		<tr>
			<td>
				<?php _e('Border Color:', 'maxgalleria') ?>
			</td>
			<td>
				<?php 				
				$border_color = get_post_meta($post->ID, $options->ns_border_color_key, true);				
				if($border_color === '')
          $border_color = get_option($options->ns_border_color_default_key, $options->ns_border_color_default );	
				?>
				<img id="<?php echo $options->ns_border_color_key . '2' ?>" class="left" alt="border color button" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/color.png">
				<input class="color-input" type="text" id="<?php echo $options->ns_border_color_key ?>" name="<?php echo $options->ns_border_color_key ?>" value="<?php echo $border_color; ?>" />
			</td>
		</tr>
		
		<tr>
			<td>
				<?php _e('Border Radius:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" id="border-radius-default" name="<?php echo $options->ns_border_radius_key ?>" value="0" class="border-radius" <?php echo ($options->get_border_radius() === '0') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="10" class="border-radius" <?php echo ($options->get_border_radius() === '10') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="20" class="border-radius" <?php echo ($options->get_border_radius() === '20') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="30" class="border-radius" <?php echo ($options->get_border_radius() === '30') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="40" class="border-radius" <?php echo ($options->get_border_radius() === '40') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="50" class="border-radius" <?php echo ($options->get_border_radius() === '50') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="60" class="border-radius" <?php echo ($options->get_border_radius() === '60') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="70" class="border-radius" <?php echo ($options->get_border_radius() === '70') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="80" class="border-radius" <?php echo ($options->get_border_radius() === '80') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_border_radius_key ?>" value="90" class="border-radius" <?php echo ($options->get_border_radius() === '90') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('No border radius', 'maxgalleria') ?>" alt="<?php _e('No border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-0.png" >
						</td>
						<td>
							<img title="<?php _e('10 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('10 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-10.png" >
						</td>
						<td>
							<img title="<?php _e('20 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('20 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-20.png" >
						</td>
						<td>
							<img title="<?php _e('30 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('30 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-30.png" >
						</td>
						<td>
							<img title="<?php _e('40 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('40 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-40.png" >
						</td>
						<td>
							<img title="<?php _e('50 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('50 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-50.png" >
						</td>
						<td>
							<img title="<?php _e('60 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('60 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-60.png" >
						</td>
						<td>
							<img title="<?php _e('70 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('70 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-70.png" >
						</td>
						<td>
							<img title="<?php _e('80 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('80 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-80.png" >
						</td>
						<td>
							<img title="<?php _e('90 pixel border radius', 'maxgalleria') ?>" alt="<?php _e('90 pixel border radius', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/border-radius-90.png" >
						</td>
					</tr>
				</table>
			</td>
		</tr>		
		<tr>
			<td>
				<?php _e('Shadow Type:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_key ?>" value="none" class="ns-shadow-type" <?php echo ($options->get_shadow() === 'none') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_key ?>" value="inside" class="ns-shadow-type" <?php echo ($options->get_shadow() === 'inside') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_key ?>" value="behind" class="ns-shadow-type" <?php echo ($options->get_shadow() === 'behind') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" id="shadow-color-option" name="<?php echo $options->ns_shadow_key ?>" value="color" class="ns-shadow-type" <?php echo ($options->get_shadow() === 'color') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('No shadow', 'maxgalleria') ?>" alt="<?php _e('no shadow style', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-none.png">
						</td>
						<td>
							<img title="<?php _e('Inside shadow', 'maxgalleria') ?>" alt="<?php _e('inside shadow style', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-inside.png" >
						</td>
						<td>
							<img title="<?php _e('Behind shadow', 'maxgalleria') ?>" alt="<?php _e('behind shadow style', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-behind.png" >
						</td>
						<td>
							<img title="<?php _e('Color shadow', 'maxgalleria') ?>" alt="<?php _e('color shadow style', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-color.png" >
						</td>
					</tr>
				</table>
			</td>								
		</tr>		
		<tr>
			<td>
				<?php _e('Shadow Color:', 'maxgalleria') ?>
			</td>
			<td>
				<?php 				
				$shadow_color = get_post_meta($post->ID, $options->ns_shadow_color_key, true);				
				if($shadow_color === '')
          $shadow_color = get_option($options->ns_shadow_color_default_key, $options->ns_shadow_color_default );	
				?>
				<img id="<?php echo $options->ns_shadow_color_key . '2' ?>" class="left" alt="border color button" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/color.png">
				<input class="color-input" type="text" id="<?php echo $options->ns_shadow_color_key ?>" name="<?php echo $options->ns_shadow_color_key ?>" value="<?php echo $shadow_color; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Shadow Blur:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_blur_key ?>" value="5" class="ns-blur-type" <?php echo ($options->get_shadow_blur() === '5') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_blur_key ?>" value="10" class="ns-blur-type" <?php echo ($options->get_shadow_blur() === '10') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_blur_key ?>" value="15" class="ns-blur-type" <?php echo ($options->get_shadow_blur() === '15') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_blur_key ?>" value="20" class="ns-blur-type" <?php echo ($options->get_shadow_blur() === '20') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('5 pixels', 'maxgalleria') ?>" alt="<?php _e('5 pixel blur', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-blur-5.png">
						</td>
						<td>
							<img title="<?php _e('10 pixels', 'maxgalleria') ?>" alt="<?php _e('10 pixel blur', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-blur-10.png">
						</td>
						<td>
							<img title="<?php _e('15 pixels', 'maxgalleria') ?>" alt="<?php _e('15 pixel blur', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-blur-15.png">
						</td>
						<td>
							<img title="<?php _e('20 pixels', 'maxgalleria') ?>" alt="<?php _e('20 pixel blur', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-blur-20.png">
						</td>
					</tr>
				</table>
			</td>															
		</tr>				
		<tr>
			<td>
				<?php _e('Shadow Spread:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_spread_key ?>" value="0" class="ns-spread-type" <?php echo ($options->get_shadow_spread() === '0') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_spread_key ?>" value="1" class="ns-spread-type" <?php echo ($options->get_shadow_spread() === '1') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_spread_key ?>" value="2" class="ns-spread-type" <?php echo ($options->get_shadow_spread() === '2') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_shadow_spread_key ?>" value="3" class="ns-spread-type" <?php echo ($options->get_shadow_spread() === '3') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('0 pixel spread', 'maxgalleria') ?>" alt="<?php _e('0 pixel spread', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-spread-0.png">
						</td>
						<td>
							<img title="<?php _e('1 pixel spread', 'maxgalleria') ?>" alt="<?php _e('1 pixel spread', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-spread-1.png">
						</td>
						<td>
							<img title="<?php _e('2 pixels spread', 'maxgalleria') ?>" alt="<?php _e('2 pixels spread', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-spread-2.png">
						</td>
						<td>
							<img title="<?php _e('3 pixels spread', 'maxgalleria') ?>" alt="<?php _e('3 pixels spread', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/shadow-spread-3.png">
						</td>
					</tr>
				</table>
			</td>															
		</tr>		
    <tr><td colspan="2" class="options-heading"><span class="mg-heading">THUMBNAIL OPTIONS</span></td></tr>		
		<tr>
			<td class="padding-top">
				<?php _e('Thumbnail Columns:', 'maxgalleria') ?>
			</td>
			<td class="padding-top">
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="1" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '1') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="2" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '2') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="3" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '3') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="4" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '4') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="5" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '5') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="6" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '6') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="7" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '7') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="8" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '8') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio"> 
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="9" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '9') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio"> 
							<input type="radio" name="<?php echo $options->thumb_columns_key ?>" value="10" class="thumbnail-column-type" <?php echo ($options->get_thumb_columns() === '10') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('1 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('1 column thumnbnail', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-01.png">
						</td>
						<td>
							<img title="<?php _e('2 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('2 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-02.png">
						</td>
						<td>
							<img title="<?php _e('3 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('3 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-03.png">
						</td>
						<td>
							<img title="<?php _e('4 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('4 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-04.png">
						</td>
						<td>
							<img title="<?php _e('5 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('5 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-05.png">
						</td>
						<td>
							<img title="<?php _e('6 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('6 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-06.png">
						</td>
						<td>
							<img title="<?php _e('7 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('7 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-07.png">
						</td>
						<td>
							<img title="<?php _e('8 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('8 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-08.png">
						</td>
						<td>
							<img title="<?php _e('9 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('9 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-09.png">
						</td>
						<td>
							<img title="<?php _e('10 column thumnbnails', 'maxgalleria') ?>" alt="<?php _e('10 column thumnbnails', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-columns-10.png">
						</td>
					</tr>
				</table>
			</td>																						
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Shape:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_shape_key ?>" value="landscape" class="thumbnail-shape-type" <?php echo ($options->get_thumb_shape() === 'landscape') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_shape_key ?>" value="portrait" class="thumbnail-shape-type" <?php echo ($options->get_thumb_shape() === 'portrait') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_shape_key ?>" value="square" class="thumbnail-shape-type" <?php echo ($options->get_thumb_shape() === 'square') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('Landscape thumnbnail shape', 'maxgalleria') ?>" alt="<?php _e('landscape thumnbnail shape', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-shape-landscape.png">
						</td>
						<td>
							<img title="<?php _e('Portrait thumnbnail shape', 'maxgalleria') ?>" alt="<?php _e('portrait thumnbnail shape', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-shape-portrait.png">
						</td>
						<td>
							<img title="<?php _e('Square thumnbnail shape', 'maxgalleria') ?>" alt="<?php _e('square thumnbnail shape', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-shape-square.png">
						</td>
					</tr>
				</table>
			</td>																
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->thumb_caption_enabled_key ?>"><?php _e('Thumbnail Captions Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $options->thumb_caption_enabled_key ?>" name="<?php echo $options->thumb_caption_enabled_key ?>" <?php echo (($options->get_thumb_caption_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Captions Position:', 'maxgalleria') ?>
			</td>
			<td>
				<table class="mg-settings">
					<tr>
						<td class="mg-radio">
							<input id="default-caption-position" type="radio" name="<?php echo $options->thumb_caption_position_key ?>" value="below" class="caption-position-type" <?php echo ($options->get_thumb_caption_position() === 'below') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_caption_position_key ?>" value="bottom" class="caption-position-type" <?php echo ($options->get_thumb_caption_position() === 'bottom') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_caption_position_key ?>" value="above" class="caption-position-type" <?php echo ($options->get_thumb_caption_position() === 'above') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->thumb_caption_position_key ?>" value="center" class="caption-position-type" <?php echo ($options->get_thumb_caption_position() === 'center') ? 'checked' : ''; ?>>
						</td>
					</tr>
					<tr>
						<td>
							<img title="<?php _e('Below Image caption', 'maxgalleria') ?>" alt="<?php _e('below image caption', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-captions-below.png">
						</td>
						<td>
							<img title="<?php _e('Bottom of Image caption', 'maxgalleria') ?>" alt="<?php _e('bottom of image caption', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-captions-bottom.png">
						</td>
						<td>
							<img title="<?php _e('Above Image caption', 'maxgalleria') ?>" alt="<?php _e('above image caption', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-captions-above.png">
						</td>
						<td>
							<img title="<?php _e('Center of Image caption', 'maxgalleria') ?>" alt="<?php _e('center of image caption', 'maxgalleria') ?>" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/options-icons/thumbnail-captions-center.png">
						</td>
					</tr>
				</table>
			</td>																
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Click Opens:', 'maxgalleria') ?>
			</td>
			<td>
				<select id="<?php echo $options->thumb_click_key ?>" name="<?php echo $options->thumb_click_key ?>">
				<?php foreach ($options->thumb_clicks as $key => $name) { ?>
					<?php $selected = ($options->get_thumb_click() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->thumb_click_new_window_key ?>"><?php _e('Thumbnail Click New Window:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $options->thumb_click_new_window_key ?>" name="<?php echo $options->thumb_click_new_window_key ?>" <?php echo (($options->get_thumb_click_new_window() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Custom Image Class:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" id="<?php echo $options->thumb_image_class_key ?>" name="<?php echo $options->thumb_image_class_key ?>" value="<?php echo $options->get_thumb_image_class() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Custom Image Container Class:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" id="<?php echo $options->thumb_image_container_class_key ?>" name="<?php echo $options->thumb_image_container_class_key ?>" value="<?php echo $options->get_thumb_image_container_class() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Thumbnail Custom Rel Attribute:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" id="<?php echo $options->thumb_image_rel_attribute_key ?>" name="<?php echo $options->thumb_image_rel_attribute_key ?>" value="<?php echo $options->get_thumb_image_rel_attribute() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Images Per Page:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="small" id="<?php echo $options->images_per_page_key ?>" name="<?php echo $options->images_per_page_key ?>" value="<?php echo $options->get_images_per_page() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Image display order:', 'maxgalleria') ?>
			</td>
			<td>
				<select id="<?php echo $options->sort_order_key ?>" name="<?php echo $options->sort_order_key ?>">
				<?php foreach ($options->sort_orders as $key => $name) { ?>
					<?php $selected = ($options->get_sort_order() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
    <tr><td colspan="2" class="options-heading"><span class="mg-heading">LIGHTBOX SETTINGS</span></td></tr>
        
		<tr>
			<td class="padding-top">
				<label for="<?php echo $options->bg_click_close_enabled_key ?>"><?php _e('Close on Background Click Enabled:', 'maxgalleria') ?></label>
			</td>
			<td class="padding-top">
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->bg_click_close_enabled_key ?>" name="<?php echo $options->bg_click_close_enabled_key ?>" <?php echo (($options->get_bg_click_close_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
    <!--this options does not seem to work-->
<!--		<tr>
			<td>
				<label for="<?php echo $options->close_btn_inside_enabled_key ?>"><?php _e('Close Button Inside Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->close_btn_inside_enabled_key ?>" name="<?php echo $options->close_btn_inside_enabled_key ?>" <?php echo (($options->get_close_btn_inside_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>-->
		<tr>
			<td>
				<label for="<?php echo $options->escape_key_enabled_key ?>"><?php _e('Close with Escape Key Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->escape_key_enabled_key ?>" name="<?php echo $options->escape_key_enabled_key ?>" <?php echo (($options->get_escape_key_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		
		<tr>
			<td>
				<?php _e('Lightbox Close Icon:', 'maxgalleria') ?>
				<?php 
				  $lightbox_close = $options->get_lightbox_close(); 
					if($lightbox_close == false)
						$lightbox_close = '0';
				?>
			</td>
			<td>
					<table id="close-table">
						<tr>
							<td class="mg-radio">
								<input type="radio" name="<?php echo $options->ns_lightbox_close_key ?>" value="0" <?php echo ($lightbox_close === '0') ? 'checked' : ''; ?>>
							</td>
							<td class="mg-radio">
								<input type="radio" name="<?php echo $options->ns_lightbox_close_key ?>" value="1" <?php echo ($lightbox_close === '1') ? 'checked' : ''; ?>>
							</td>
							<td class="mg-radio">
								<input type="radio" name="<?php echo $options->ns_lightbox_close_key ?>" value="2" <?php echo ($lightbox_close === '2') ? 'checked' : ''; ?>>
							</td>
							<td class="mg-radio">
								<input type="radio" name="<?php echo $options->ns_lightbox_close_key ?>" value="3" <?php echo ($lightbox_close === '3') ? 'checked' : ''; ?>>
							</td>
							<td class="mg-radio">
								<input type="radio" name="<?php echo $options->ns_lightbox_close_key ?>" value="4" <?php echo ($lightbox_close === '4') ? 'checked' : ''; ?>>
							</td>
						</tr>	
						<tr style="background-color:#3C3C3C">
							<td>
								<img alt="close style 0" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/close-style-0-wt.png" >
							</td>
							<td>
								<img alt="close style 1" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/close-style-1-wt.png" >
							</td>
							<td>
								<img alt="close style 2" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/close-style-2-wt.png" >
							</td>
							<td>
								<img alt="close style 3" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/close-style-3-wt.png" >
							</td>
							<td>
								<img alt="close style 4" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/close-style-4-wt.png" >
							</td>
						</tr>
					</table>
				
			</td>
		</tr>
		<tr>
			<td colspan = "2">&nbsp;</td>
		</tr>
		<tr>
			<td class="mg-align-top"><?php _e('Lightbox Arrows:', 'maxgalleria') . "value: " . $options->get_lightbox_arrow_default(); ?></td>
			<td>
				<table id="arrow-table">
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="0" <?php echo ($options->get_lightbox_arrow() === '0') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="1" <?php echo ($options->get_lightbox_arrow() === '1') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="2" <?php echo ($options->get_lightbox_arrow() === '2') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="3" <?php echo ($options->get_lightbox_arrow() === '3') ? 'checked' : ''; ?>>
						</td>
					</tr>	
					<tr style="background-color:#3C3C3C">
						<td>
							<img class="mg-float" alt="arrow style 0" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-0l-wt.png" >
							<img class="mg-float" alt="arrow style 0" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-0r-wt.png" >
						</td>
						<td>
							<img class="mg-float" alt="arrow style 1" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-1l-wt.png" >
							<img class="mg-float" alt="arrow style 1" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-1r-wt.png" >
						</td>
						<td>
							<img class="mg-float" alt="arrow style 2" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-2l-wt.png" >
							<img class="mg-float" alt="arrow style 2" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-2r-wt.png" >
						</td>
						<td>
							<img class="mg-float" alt="arrow style 3" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-3l-wt.png" >
							<img class="mg-float" alt="arrow style 3" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-3r-wt.png" >
						</td>
					</tr>
					
					<tr>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="4" <?php echo ($options->get_lightbox_arrow() === '4') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="5" <?php echo ($options->get_lightbox_arrow() === '5') ? 'checked' : ''; ?>>
						</td>
						<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="6" <?php echo ($options->get_lightbox_arrow() === '6') ? 'checked' : ''; ?>>
						</td>
							<td class="mg-radio">
							<input type="radio" name="<?php echo $options->ns_lightbox_arrow_key ?>" value="7" <?php echo ($options->get_lightbox_arrow() === '7') ? 'checked' : ''; ?>>
						</td>
					</tr>	
					<tr style="background-color:#3C3C3C">
						<td>
							<img class="mg-float" alt="arrow style 4" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-4l-wt.png" >
							<img class="mg-float" alt="arrow style 4" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-4r-wt.png" >
						</td>
						<td>
							<img class="mg-float" alt="arrow style 5" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-5l-wt.png" >
							<img class="mg-float" alt="arrow style 5" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-5r-wt.png" >
						</td>
						<td>
							<img class="mg-float" alt="arrow style 6" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-6l-wt.png" >
							<img class="mg-float" alt="arrow style 6" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-6r-wt.png" >
						</td>
							<td>
							<img class="mg-float" alt="arrow style 7" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-7l-wt.png" >
							<img class="mg-float" alt="arrow style 7" src="<?php echo MAXGALLERIA_PLUGIN_URL ?>/images/icons/arrow-style-7r-wt.png" >
						</td>
					</tr>
					
				</table>
			</td>
		</tr>			
		
    <?php if(class_exists('Responsive_Lightbox')) { ?>    
      <tr>
        <td>
          <label for="<?php echo $options->dfactory_lightbox_key ?>"><?php _e('Use dFactory Responsive Lightbox:', 'maxgalleria') ?></label>
        </td>
        <td>
          <input type="checkbox" id="<?php echo $options->dfactory_lightbox_key ?>" name="<?php echo $options->dfactory_lightbox_key ?>" <?php echo (($options->get_dfactory_lightbox() == 'on') ? 'checked' : '') ?> />
        </td>
      </tr>
			<tr>
	      <td class="mg-italic" colspan = "2"><?php _e('Set "Thumbnail Click Opens" to "Original Image" or "Image Link" when using this option.</span>', 'maxgalleria') ?></td>
	    </tr>	
    <?php } ?>
		
		<tr>
			<td>
				<label for="<?php echo $options->hide_close_btn_enabled_key ?>"><?php _e('Hide Close Button:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->hide_close_btn_enabled_key ?>" name="<?php echo $options->hide_close_btn_enabled_key ?>" <?php echo (($options->get_hide_close_btn_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->lightbox_caption_enabled_key ?>"><?php _e('Lightbox Captions Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->lightbox_caption_enabled_key ?>" name="<?php echo $options->lightbox_caption_enabled_key ?>" <?php echo (($options->get_lightbox_caption_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->retina_enabled_key ?>"><?php _e('Retina Images Enabled:', 'maxgalleria') ?></label>				
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->retina_enabled_key ?>" name="<?php echo $options->retina_enabled_key ?>" <?php echo (($options->get_retina_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td class="mg-italic" colspan = "2"><?php _e("Enabling Retina Images requires two images both with the same path, a normal resolution image and a high-resolution
					 image with a file name ending with '@2x'. Example: image.jpg & image@2x.jpg. Include the normal resolution image in
					 a gallery and load high-resolution images directly to the Wordpress Media Library. When Retina Images are enbled then the popup will display the high-resolution
					 image on high-dpi screens.", "maxgalleria") ?></td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->zoom_enabled_key ?>"><?php _e('Zoom Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->zoom_enabled_key ?>" name="<?php echo $options->zoom_enabled_key ?>" <?php echo (($options->get_zoom_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Zoom Easing:', 'maxgalleria') ?>
			</td>
			<td>
				<select class="mag-popup-settings" id="<?php echo $options->easing_type_key ?>" name="<?php echo $options->easing_type_key ?>">
				<?php foreach ($options->easing_types as $key => $name) { ?>
					<?php $selected = ($options->get_easing_type() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Zoom Duration (in milliseconds):', 'maxgalleria') ?>
			</td>
			<td>
				<select class="mag-popup-settings" id="<?php echo $options->zoom_duration_key ?>" name="<?php echo $options->zoom_duration_key ?>">
				<?php foreach ($options->zoom_durations as $key => $name) { ?>
					<?php $selected = ($options->get_zoom_duration() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
    <tr><td colspan="2" class="options-heading"><span class="mg-heading">GALLERY OPTIONS</span></td></tr>
		<tr>
			<td class="padding-top">
				<label for="<?php echo $options->gallery_enabled_key ?>"><?php _e('Gallery Enabled (Displays previous and next navigation arrows):', 'maxgalleria') ?></label>
			</td>
			<td class="padding-top">
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->gallery_enabled_key ?>" name="<?php echo $options->gallery_enabled_key ?>" <?php echo (($options->get_gallery_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->navigate_by_img_click_enabled_key ?>"><?php _e('Navigate By Image Click Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->navigate_by_img_click_enabled_key ?>" name="<?php echo $options->navigate_by_img_click_enabled_key ?>" <?php echo (($options->get_navigate_by_img_click_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Arrow Button Markup:', 'maxgalleria') ?>
        <br><span class='mg-italic'><?php _e("Please use only single quotes in markup text.", "maxgalleria") ?></span>
			</td>
			<td>
        <?php 
          $button_markup = trim($options->get_arrow_markup()); 
          if ($button_markup === '')
            $button_markup = $options->arrow_markup_default;          
        ?>
				<input type="text" class="wide mag-popup-settings" id="<?php echo $options->arrow_markup_key ?>" name="<?php echo $options->arrow_markup_key ?>" value="<?php echo $button_markup; ?>" />        
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Left Button Title:', 'maxgalleria') ?>
			</td>
			<td>
        <?php 
          $left_title = trim($options->get_prev_button_title()); 
          if ($left_title === '')
            $left_title = $options->prev_button_title_default;
        ?>
				<input type="text" class="medium mag-popup-settings" id="<?php echo $options->prev_button_title_key ?>" name="<?php echo $options->prev_button_title_key ?>" value="<?php echo $left_title; ?>" />
			</td>
		</tr>    
		<tr>
			<td>
				<?php _e('Right Button Title:', 'maxgalleria') ?>
			</td>
			<td>
        <?php 
          $right_title = trim($options->get_next_button_title()); 
          if ($right_title === '')
            $right_title = $options->next_button_title_default;
        ?>
				<input type="text" class="medium mag-popup-settings" id="<?php echo $options->next_button_title_key ?>" name="<?php echo $options->next_button_title_key ?>" value="<?php echo $right_title; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Counter Markup:', 'maxgalleria') ?>
        <br><span class='mg-italic'><?php _e("Please use only single quotes in markup text.", "maxgalleria") ?></span>
			</td>
			<td>
        <?php 
          $counter_markup = trim($options->get_counter_markup()); 
          if ($counter_markup === '')
            $counter_markup = $options->counter_markup_default;
        ?>
				<input type="text" class="wide mag-popup-settings" id="<?php echo $options->counter_markup_key ?>" name="<?php echo $options->counter_markup_key ?>" value="<?php echo $counter_markup; ?>" />
			</td>
		</tr>    
		<tr>
			<td colspan="2" class="options-heading"><span class="mg-heading"><?php _e('ADVANCED SETTINGS', 'maxgalleria') ?></span></td><td></td>
		</tr>
		<tr><td class="padding-top"><span class="mg-bold"><?php _e('Thumbnail Options', 'maxgalleria') ?></span></td></tr>
		<tr>
			<td>
				<label for="<?php echo $options->lazy_load_enabled_key ?>"><?php _e('Lazy Load Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" id="<?php echo $options->lazy_load_enabled_key ?>" name="<?php echo $options->lazy_load_enabled_key ?>" <?php echo (($options->get_lazy_load_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
		<tr>
			<td class="mg-italic" colspan = "2"><?php _e('Lazy Loading allows for faster page loading times and is enabled by default for a better user experience. But you can turn it off if individual images in your gallery are not loading fast enough.', 'maxgalleria') ?></td>			
		</tr>
		<tr>
			<td>
				<?php _e('Lazy Load Threshold (Pixels):', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="small" id="<?php echo $options->lazy_load_threshold_key ?>" name="<?php echo $options->lazy_load_threshold_key ?>" value="<?php echo $options->get_lazy_load_threshold() ?>" />
			</td>
		</tr>
		<tr>
			<td class="mg-italic" colspan = "2"><?php _e('Lazy Load Threshold is the number of pixels above an image before it starts loading as the user scrolls down your page.  We set the default to 50 pixels.  If you find you want your images to start loading sooner increase the number of pixels for the threshold.', 'maxgalleria') ?></td>			
		</tr>
		<tr><td><span class="mg-bold"><?php _e('Lightbox Options', 'maxgalleria') ?></span></td></tr>
		<tr>
			<td>
				<label for="<?php echo $options->align_top_enabled_key ?>"><?php _e('Align Top Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->align_top_enabled_key ?>" name="<?php echo $options->align_top_enabled_key ?>" <?php echo (($options->get_align_top_enabled() == 'on') ? 'checked' : '') ?> />
				<br><span class='mg-italic'><?php _e('If set to true popup is aligned to top instead of to center.', 'maxgalleria') ?></span>
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Fixed Content Position:', 'maxgalleria') ?>
			</td>
			<td>
				<select class="mag-popup-settings" id="<?php echo $options->fixed_content_position_key ?>" name="<?php echo $options->fixed_content_position_key ?>">
				<?php foreach ($options->content_positions as $key => $name) { ?>
					<?php $selected = ($options->get_fixed_content_position() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>    
		<tr>      
			<td width="300">
				<?php _e('Overflow Y (Displays a vertical scroll bar on the page when fixed content position is "on" or "auto"):', 'maxgalleria') ?>
			</td>
			<td>
				<select class="mag-popup-settings" id="<?php echo $options->overflow_y_key ?>" name="<?php echo $options->overflow_y_key ?>">
				<?php foreach ($options->overflow_y_settings as $key => $name) { ?>
					<?php $selected = ($options->get_overflow_y() == $key) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $key ?>" <?php echo $selected ?>><?php echo $name ?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		<tr> 
			<td>
				<?php _e('Popup Custom Class:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="mag-popup-settings" id="<?php echo $options->main_class_key ?>" name="<?php echo $options->main_class_key ?>" value="<?php echo $options->get_main_class() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php _e('Removal Delay:', 'maxgalleria') ?>
			</td>
			<td>
				<input type="text" class="small mag-popup-settings" id="<?php echo $options->removal_delay_key ?>" name="<?php echo $options->removal_delay_key ?>" value="<?php echo $options->get_removal_delay() ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="<?php echo $options->vertical_fit_enabled_key ?>"><?php _e('Vertical Fit Enabled:', 'maxgalleria') ?></label>
			</td>
			<td>
				<input type="checkbox" class="mag-popup-settings" id="<?php echo $options->vertical_fit_enabled_key ?>" name="<?php echo $options->vertical_fit_enabled_key ?>" <?php echo (($options->get_vertical_fit_enabled() == 'on') ? 'checked' : '') ?> />
			</td>
		</tr>
    
	</table>
  
</div>
<script type="text/javascript">		
	jQuery(document).ready(function() {
    
    //replaces double quotes with single quotes so that the markup text
    //does not interfer with the form
    jQuery("#<?php echo $options->arrow_markup_key; ?>").keyup(function() {  
      var a = jQuery(this).val();
      var newTemp = a.replace(/"/g, "'");
      jQuery(this).val(newTemp);
    });        
  
    jQuery("#<?php echo $options->counter_markup_key; ?>").keyup(function() {  
      var a = jQuery(this).val();
      var newTemp = a.replace(/"/g, "'");
      jQuery(this).val(newTemp);
    });        
		
		jQuery('#<?php echo $options->ns_border_color_key ?>').css('border-color','<?php echo $options->get_border_color(); ?>');
		jQuery('#<?php echo $options->ns_shadow_color_key ?>').css('border-color','<?php echo $options->get_shadow_color(); ?>');
		
		jQuery("#<?php echo $options->ns_shadow_key ?>").change(function() {
			var shadow_type = jQuery("#<?php echo $options->ns_shadow_key ?>").val();
			if(shadow_type === 'color') {
				jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', false);
			} else {
				jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', 'disabled');
			}	
    });   
		
		var shadow_type = jQuery("#<?php echo $options->ns_shadow_key ?>").val();
		if(shadow_type === 'color') {
			jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', false);
		} else {
			jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', 'disabled');
		}	
		
		jQuery('#<?php echo $options->ns_show_border_key ?>').click(function(){
			if(this.checked) {
			  jQuery("#<?php echo $options->ns_border_color_key ?>").prop('disabled', false);
			  jQuery(".border-thickness").prop('disabled', false);
			  jQuery(".border-radius").prop('disabled', false);				
				jQuery("#border-thickness-default").prop("checked", true)
				jQuery("#border-radius-default").prop("checked", true)
				
			} else {
			  jQuery("#<?php echo $options->ns_border_color_key ?>").prop('disabled', 'disabled');
			  jQuery(".border-thickness").prop('disabled', 'disabled');
			  jQuery(".border-radius").prop('disabled', 'disabled');
			}	
		});		
		
		if(jQuery('#<?php echo $options->ns_show_border_key ?>').is(':checked')) {
			jQuery("#<?php echo $options->ns_border_color_key ?>").prop('disabled', false);
			jQuery(".border-thickness").prop('disabled', false);
			jQuery(".border-radius").prop('disabled', false);				
		} else {
			jQuery("#<?php echo $options->ns_border_color_key ?>").prop('disabled', 'disabled');
			jQuery(".border-thickness").prop('disabled', 'disabled');
			jQuery(".border-radius").prop('disabled', 'disabled');
			
		}

		jQuery("#<?php echo $options->ns_border_color_key . '2' ?>").click(function() {
			if(!jQuery("#<?php echo $options->ns_border_color_key ?>").prop('disabled')) {				
		    jQuery("#<?php echo $options->ns_border_color_key ?>").click();
			}	
	  });  

		jQuery(".ns-shadow-type").change(function() {
			var shadow_type = this.value
			if(shadow_type === 'color') {
				jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', false);
			jQuery("#<?php echo $options->ns_shadow_color_key . '2' ?>").prop('disabled', false);
			} else {
				jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', 'disabled');
			jQuery("#<?php echo $options->ns_shadow_color_key . '2' ?>").prop('disabled', 'disabled');
			}	
			if(shadow_type === 'none') {
				jQuery(".ns-blur-type").prop('disabled', 'disabled');
				jQuery(".ns-spread-type").prop('disabled', 'disabled');
				jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled', 'disabled');
			  jQuery("#<?php echo $options->ns_shadow_color_key . '2' ?>").prop('disabled', 'disabled');
			} else {
				jQuery(".ns-blur-type").prop('disabled', false);
				jQuery(".ns-spread-type").prop('disabled', false);
			}				
			
    });   
		
		jQuery("#<?php echo $options->ns_shadow_color_key . '2' ?>").click(function() {
			if(!jQuery("#<?php echo $options->ns_shadow_color_key ?>").prop('disabled')) {				
		    jQuery("#<?php echo $options->ns_shadow_color_key ?>").click();
		  }
	  });  
		
		jQuery('#<?php echo $options->thumb_caption_enabled_key ?>').click(function(){
			if(this.checked) {
			  jQuery(".caption-position-type").prop('disabled', false);				
			  jQuery("input#default-caption-position").prop("checked", true);
			} else {
			  jQuery(".caption-position-type").prop('disabled', 'disabled');				
			}	
		});		
				
		if(jQuery('#<?php echo $options->thumb_caption_enabled_key ?>').is(':checked')) {
			jQuery(".caption-position-type").prop('disabled', false);
		} else {
			jQuery(".caption-position-type").prop('disabled', 'disabled');
		}			
		
	});
</script>
