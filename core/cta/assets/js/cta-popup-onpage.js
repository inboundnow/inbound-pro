/* Popup CTA Script */
jQuery(document).ready(function($) {
	var the_pop_id = "wp_cta_" + jQuery("#cta-popup-id").text();
	var global_cookie = _inbound.Utils.readCookie( 'wp_cta_global' );
	var local_cookie = _inbound.Utils.readCookie( the_pop_id );
	var c_length = parseInt(wp_cta_popup.c_length);
	var g_length = parseInt(wp_cta_popup.global_c_length);
	var page_view_theshold = parseInt(wp_cta_popup.page_views);
	var page_view_count = 99;
	var show_me = true;
	if (wp_cta_popup.c_status === 'yes' && local_cookie === 'true') {
		console.log('Popup halted by local cookie');
		var show_me = false;
		return false;
	}
	if (page_view_theshold > page_view_count) {
		console.log('Popup halted by not enough page views');
		var show_me = false;
		return false;
	}
	// global settings show only once on and cookie exists turn off
	if (wp_cta_popup.global_c_status == 1 && global_cookie === 'true') {
		console.log('Popup halted by global settings show only once on');
		var show_me = false;
		return false;
	}

	// Popup rendering
	if (show_me === true){
		jQuery('.popup-modal').magnificPopup({
		  type: 'inline',
		  preloader: false
		  // modal: true // disables close
		});

		setTimeout(function() {
			var parent = jQuery('#wp-cta-popup').parent().width();
			jQuery('.wp_cta_popup').attr('data-parent', parent);
			jQuery(".white-popup-block").addClass("cta_wait_hide");
			jQuery("#wp-cta-popup").show();
			jQuery('.popup-modal').magnificPopup('open');
			_inbound.Utils.createCookie( the_pop_id, true, c_length );				
			_inbound.Utils.createCookie( 'wp_cta_global', true, g_length );

		}, wp_cta_popup.timeout);
    }
	
	jQuery(document).on('click', '.popup-modal-dismiss', function (e) {
	  e.preventDefault();
	  $.magnificPopup.close();
	});

 });