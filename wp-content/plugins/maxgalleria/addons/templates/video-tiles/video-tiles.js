jQuery(document).ready(function() {
	jQuery("span.hidden-video-tiles-gallery-id").each(function() {
		var gallery_id = jQuery(this).html();
		var thumb_click = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-thumb-click").html();
		var vertical_fit_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-vertical-fit-enabled").html();
		var escape_key_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-escape-key-enabled").html();
		var align_top_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-align-top-enabled").html();
		var hide_close_btn_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-hide-close-btn-enabled").html();
		var bg_click_close_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-bg-click-close-enabled").html();
		var fixed_content_position = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-fixed-content-position").html();
		var overflowY = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-overflow-y").html();
		var removal_delay = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-removal-delay").html();
		var popup_main_class = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-popup-main-class").html();
		var gallery_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-gallery-enabled").html();
		var arrow_markup = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-arrow-markup").html();
		var prev_button_title = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-prev-button-title").html();
		var next_button_title = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-next-button-title").html();
		var counter_markup = jQuery("#maxgallery-" + gallery_id + " span.hidden-video-tiles-counter-markup").html();
        
    vertical_fit_enabled = (vertical_fit_enabled === 'true') ? true : false;
    escape_key_enabled = (escape_key_enabled === 'true') ? true : false;
    align_top_enabled = (align_top_enabled === 'true') ? true : false;
    hide_close_btn_enabled = (hide_close_btn_enabled === 'true') ? true : false;
    bg_click_close_enabled = (bg_click_close_enabled === 'true') ? true : false;
    gallery_enabled = (gallery_enabled === 'true') ? true : false;
    
    switch(fixed_content_position) {
      case 'auto':
        break;
      case 'true':
        fixed_content_position = true;
        break;
      case 'false':
        fixed_content_position = false;
        break;
    }
    		
        
		if (thumb_click === "lightbox") {
			jQuery("#maxgallery-" + gallery_id + ".mg-video-tiles .mg-thumbs a.video").magnificPopup({
        type: 'iframe',
        removalDelay: removal_delay,
        verticalFit: vertical_fit_enabled,
			  titleSrc: 'title',				
        showCloseBtn: hide_close_btn_enabled,
        enableEscapeKey: escape_key_enabled,
        alignTop: align_top_enabled,
        closeOnBgClick: bg_click_close_enabled,
        fixedContentPos: fixed_content_position,
        overflowY: overflowY,
        mainClass: popup_main_class,
        gallery: {
          enabled: gallery_enabled,
          navigateByImgClick: false,
          arrowMarkup: arrow_markup,
          tPrev: prev_button_title,
          tNext: next_button_title,
          tCounter: counter_markup
        }
   });                 
		}
	});
   
});