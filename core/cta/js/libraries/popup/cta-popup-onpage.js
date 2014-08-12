/* Popup CTA Script */
jQuery(document).ready(function($) {
	var the_pop_id = "wp_cta_" + jQuery("#cta-popup-id").text();
	var global_cookie = jQuery.cookie("wp_cta_global");
	var local_cookie = jQuery.cookie(the_pop_id);
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
	        $('.popup-modal').magnificPopup({
	          type: 'inline',
	          preloader: false
	          // modal: true // disables close
	        });

	       	setTimeout(function() {
	       			var parent = $('#wp-cta-popup').parent().width();
	       			$('.wp_cta_popup').attr('data-parent', parent);
	       			$(".white-popup-block").addClass("cta_wait_hide");
	                $("#wp-cta-popup").show();
	                $('.popup-modal').magnificPopup('open');
	                jQuery.cookie(the_pop_id, true, { path: '/', expires: c_length });
	                jQuery.cookie("wp_cta_global", true, { path: '/', expires: g_length });

	        }, wp_cta_popup.timeout);
       }
        $(document).on('click', '.popup-modal-dismiss', function (e) {
          e.preventDefault();
          $.magnificPopup.close();
        });

 });