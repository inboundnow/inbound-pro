function generate_random_cache_bust(length) {
			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}

			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}
			return str;
	}

jQuery(document).ready(function($) {
	jQuery('.nav-tab-wrapper.a_b_tabs a').each(function(){
	var this_link = jQuery(this).attr('href');
	jQuery(this).attr('href', this_link + "&frontend=true");

	});

jQuery(".nav-tab-wrapper.a_b_tabs a").on('click', function (event) {
	
	jQuery(parent.document).find(".lp-load-overlay").fadeIn('slow');

});	


var open_variation = jQuery("#open_variation").val();
var link_variation = jQuery("#view-post-btn a").attr('href');

var preview_window = jQuery(parent.document).find("#lp-live-preview").attr('src');
console.log(preview_window);

console.log(link_variation);

	// reload the iframe preview page (for option toggles)
	//jQuery('.reload').on('click', function (event) {
		reload_preview(); // need to trigger reload from parent frame
	//});

	//alert(jQuery("#current_variation_id").text());
	function reload_preview() {    
		var cache_bust =  generate_random_cache_bust(35);
		var reload_url = parent.window.location.href;
		reload_url = reload_url.replace('template-customize=on','');
		//alert(reload_url);
		var current_variation_id = jQuery("#open_variation").val();
	
		// var reload = jQuery(parent.document).find("#lp-live-preview").attr("src"); 
		var new_reload = reload_url + "&live-preview-area=" + cache_bust + "&lp-variation-id=" + current_variation_id;
		jQuery(parent.document).find("#lp-live-preview").attr("src", new_reload);
		// console.log(new_reload);
		jQuery(parent.document).find(".lp-load-overlay").fadeOut('slow');
	}

});
