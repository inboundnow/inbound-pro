/** 
 *  Record Impressions For Each Variation in CTA Object
 *
 * @param JSON ctas : a json string of {'cta':'vid'}
 */
function wp_cta_record_impressions(ctas) {

	/* Add Impressions to loaded varations*/
	jQuery.ajax({
		type: 'POST',
		url: cta_variation.admin_url,
		data: {
			action: 'wp_cta_record_impressions',
			ctas: ctas
		},
		success: function(user_id){
				_inbound.deBugger( 'cta', 'CTA Impressions Recorded');
		},
		error: function(MLHttpRequest, textStatus, errorThrown){

		}

	});

}

/**
 *   Adds Tracking Classes to Links and Forms to CTAs
 * @param OBJECT ctas : object containing {'cta','vid'}
 */
function wp_cta_add_tracking_classes(ctas) {
	jQuery.each( ctas,  function(cta_id,vid) {
		var vid = ctas[cta_id];

		//console.log('CTA '+cta_id+' loads variation:' + vid);
		jQuery('.wp_cta_'+cta_id+'_variation_'+vid).show();

		/* add tracking classes to links and forms */
		var wp_cta_id = '<input type="hidden" name="wp_cta_id" value="' + cta_id + '">';
		var wp_cta_vid = '<input type="hidden" name="wp_cta_vid" value="'+ vid +'">';
		jQuery('#wp_cta_'+cta_id+'_variation_'+vid+' form').each(function(){
			jQuery(this).addClass('wpl-track-me');
			jQuery(this).append(wp_cta_id);
			jQuery(this).append(wp_cta_vid);
		});


		/* add click tracking - get lead cookies */
		var lead_cpt_id = _inbound.Utils.readCookie("wp_lead_id");
		var lead_email = _inbound.Utils.readCookie("wp_lead_email");
		var lead_unique_key = _inbound.Utils.readCookie("wp_lead_uid");


		/* add click tracking  - setup lead data for click event tracking */
		if (typeof (lead_cpt_id) != "undefined" && lead_cpt_id !== null) {
			string = "&wpl_id=" + lead_cpt_id + "&l_type=wplid";
		} else if (typeof (lead_email) != "undefined" && lead_email !== null && lead_email !== "") {
			string = "&wpl_id=" + lead_email + "&l_type=wplemail";;
		} else if (typeof (lead_unique_key) != "undefined" && lead_unique_key !== null && lead_unique_key !== "") {
			string = "&wpl_id=" + lead_unique_key + "&l_type=wpluid";
		} else {
			string = "";
		}

	});
}

function wp_cta_load_variation( cta_id, vid, disable_ajax ) {

	/* Preload wp_cta_loaded storage object into variable */
	var loaded_ctas = _inbound.totalStorage('wp_cta_loaded');
	if (loaded_ctas === null) {
		var loaded_ctas = {};
	}

	/* if variation is pre-defined then immediately load variation*/
	if ( typeof vid != 'undefined' && vid != null && vid != '' ) {
		/* reveal variation */
		_inbound.debug('CTA '+cta_id+' loads variation:' + vid);
		jQuery('.wp_cta_'+cta_id+'_variation_'+vid).show();

		/* record impression  */
		loaded_ctas[cta_id] = vid;
		_inbound.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data

		/* add tracking classes */
		wp_cta_add_tracking_classes( loaded_ctas );

	}
	/* if split testing is disabled then update wp_cta_loaded storage object with variation 0 */
	else if ( parseInt(disable_ajax) == 1 ) {
		/* update local storage variable */
		loaded_ctas[cta_id] = 0;

		/* update local storage object */
		_inbound.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data
		_inbound.deBugger('WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas));

	}
	/* Poll the ajax server for the correct variation to display */
	else {

		jQuery.ajax({
			 type: "GET",
			 url: cta_variation.ajax_url,
			 dataType: "script",
			 async: false,
			 data : {
				'action' : 'cta_get_variation',
				'cta_id' : cta_id
			 },
			 success: function(vid) {
				/* update local storage variable */
				loaded_ctas[cta_id] = vid.trim();

				/* update local storage object */
				_inbound.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data

				_inbound.deBugger( 'cta', 'WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas) );
			}
		});
	}
}

/* reset local storage variable every page load */
_inbound.totalStorage.deleteItem('wp_cta_loaded');

jQuery(document).ready(function($) {

	setTimeout( function() {

		if (cta_variation.cta_id) {
			wp_cta_load_variation( cta_variation.cta_id , null , cta_variation.disable_ajax );
		}

		var ctas = _inbound.totalStorage('wp_cta_loaded');

		if(ctas === null || ctas === "undefined") { return false; }

		/* Add Tracking Classes & Reveal CTAs */
		wp_cta_add_tracking_classes(ctas);

		/* Record Impressions */
		wp_cta_record_impressions(ctas);

	} , 1 );

});
