function wp_cta_load_variation( cta_id, vid, disable_ajax ) {
	/* Preload wp_cta_loaded storage object into variable */
	var loaded_ctas = {};
	var loaded_local_cta = jQuery.totalStorage('wp_cta_loaded');
	if (loaded_local_cta != null) {
		var loaded_ctas = JSON.parse(localStorage.getItem('wp_cta_loaded'));
	}

	/* if variation is pre-defined then immediately load variation*/
	if ( typeof vid != 'undefined' && vid != null && vid != '' ) {
		/* reveal variation */
		InboundAnalytics.debug('CTA '+cta_id+' loads variation:' + vid);
		jQuery('.wp_cta_'+cta_id+'_variation_'+vid).show();

		/* record impression  */
		loaded_ctas[cta_id] = vid;
		wp_cta_record_impressions( JSON.stringify(loaded_ctas) );

		/* add tracking classes */
		wp_cta_add_tracking_classes( loaded_ctas );

	}
	/* if split testing is disabled then update wp_cta_loaded storage object with variation 0 */
	else if ( parseInt(disable_ajax) == 1 ) {
		/* update local storage variable */
		loaded_ctas[cta_id] = 0;

		/* update local storage object */
		jQuery.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data
		InboundAnalytics.debug('WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas));

	}
	/* Poll the ajax server for the correct variation to display */
	else {
		jQuery.ajax({
			 type: "GET",
			 url: cta_variation.ajax_url,
			 dataType: "script",
			 async:false,
			 data : {
				'action' : 'cta_get_variation',
				'cta_id' : cta_id
			 },
			 success: function(vid) {
				/* update local storage variable */
				loaded_ctas[cta_id] = vid;

				/* update local storage object */
				jQuery.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data
				InboundAnalytics.debug('WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas));
			}
		});
	}
}

jQuery(document).ready(function($) {
	/* reset local storage variable every page load */
	jQuery.totalStorage.deleteItem('wp_cta_loaded'); // remove pageviews

	if (cta_variation.cta_id) {
		wp_cta_load_variation( cta_variation.cta_id , null , cta_variation.disable_ajax );
	}
});
