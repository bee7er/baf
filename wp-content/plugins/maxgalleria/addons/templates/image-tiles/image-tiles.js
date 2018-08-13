jQuery(document).ready(function() {
	jQuery("span.hidden-image-tiles-gallery-id").each(function() {
		var gallery_id = jQuery(this).html();
		var vertical_fit_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-vertical-fit-enabled").html();
		var content_click_close_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-content-click-close-enabled").html();
		var bg_click_close_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-bg-click-close-enabled").html();
		var close_btn_inside_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-close-btn-inside-enabled").html();
		var hide_close_btn_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-hide-close-btn-enabled").html();
		var escape_key_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-escape-key-enabled").html();
		var align_top_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-align-top-enabled").html();
		var fixed_content_position = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-fixed-content-position").html();
		var zoom_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-zoom-enabled").html();
		var popup_main_class = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-popup-main-class").html();
		var easing_type = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-easing-type").html();
		var zoom_duration = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-zoom-duration").html();
		var overflowY = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-overflow-y").html();
		var retina_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-retina-enabled").html();
		var removal_delay = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-removal-delay").html();
		var gallery_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-gallery-enabled").html();
		var navigate_by_img_click_enabled = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-navigate-by-img-click-enabled").html();
		var arrow_markup = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-arrow-markup").html();
		var prev_button_title = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-prev-button-title").html();
		var next_button_title = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-next-button-title").html();
		var counter_markup = jQuery("#maxgallery-" + gallery_id + " span.hidden-image-tiles-counter-markup").html();
    
    vertical_fit_enabled = (vertical_fit_enabled === 'true') ? true : false;
    content_click_close_enabled = (content_click_close_enabled === 'true') ? true : false;
    bg_click_close_enabled = (bg_click_close_enabled === 'true') ? true : false;
    close_btn_inside_enabled = (close_btn_inside_enabled === 'true') ? true : false;
    hide_close_btn_enabled = (hide_close_btn_enabled === 'true') ? true : false;
    escape_key_enabled = (escape_key_enabled === 'true') ? true : false;
    align_top_enabled = (align_top_enabled === 'true') ? true : false;
    zoom_enabled = (zoom_enabled === 'true') ? true : false;
    retina_enabled = (retina_enabled === 'true') ? 2 : 1;
    gallery_enabled = (gallery_enabled === 'true') ? true : false;
    navigate_by_img_click_enabled = (navigate_by_img_click_enabled === 'true') ? true : false;
        
    switch(fixed_content_position) {
      case 'auto':
        fixed_content_position = "auto";
        break;
      case 'true':
        fixed_content_position = true;
        break;
      case 'false':
        fixed_content_position = false;
        break;
    }
                 
    jQuery('#maxgallery-' + gallery_id + ' .mg-thumbs .mg-magnific').magnificPopup({
      type:'image',
      verticalFit: vertical_fit_enabled,
			titleSrc: 'title',
      closeOnContentClick: content_click_close_enabled,
      closeOnBgClick: bg_click_close_enabled,
      //closeBtnInside: close_btn_inside_enabled,
      showCloseBtn: hide_close_btn_enabled,
      enableEscapeKey: escape_key_enabled,
      alignTop: align_top_enabled,
      fixedContentPos: fixed_content_position,
      overflowY: overflowY,
      removalDelay: removal_delay,
      mainClass: popup_main_class,
      zoom: {
        enabled: zoom_enabled,
        duration: zoom_duration,
        easing: easing_type          
      },
      gallery: {
        enabled: gallery_enabled,
        navigateByImgClick: navigate_by_img_click_enabled,
        arrowMarkup: arrow_markup,        
        tPrev: prev_button_title,
        tNext: next_button_title,
        tCounter: counter_markup
      },
      retina: {
        ratio: retina_enabled
      }       
    });  
    
	});
});