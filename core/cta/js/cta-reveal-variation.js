
/* Record Impressions For Each Variation in CTA Object
* @param JSON ctas : a json string of {'cta':'vid'}
*/
function wp_cta_record_impressions(ctas) {

	/* Add Impressions to loaded varations*/
	jQuery.ajax({
		type: 'POST',
		url: cta_reveal.admin_url,
		data: {
			action: 'wp_cta_record_impressions',
			ctas: ctas
		},
		success: function(user_id){
				//console.log('CTA Impressions Recorded');
			   },
		error: function(MLHttpRequest, textStatus, errorThrown){

			}

	});

}

/* Adds Tracking Classes to Links and Forms to CTAs
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
		var lead_cpt_id = jQuery.cookie("wp_lead_id");
		var lead_email = jQuery.cookie("wp_lead_email");
		var lead_unique_key = jQuery.cookie("wp_lead_uid");


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

		var external = RegExp('^((f|ht)tps?:)?//(?!' + location.host + ')');
		jQuery('#wp_cta_'+cta_id+'_variation_'+vid+' a').each(function(){

			jQuery(this).attr("data-event-id",  cta_id ).attr("data-cta-variation", vid );
			var originalurl = jQuery(this).attr("href");

			if (originalurl  && originalurl.substr(0,1)!='#') {

				if ( jQuery(this).hasClass('do-not-track') ) {
					return;
				}

				var cta_variation_string = "&wp-cta-v=" + vid;
				var newurl =  cta_reveal.home_url + "?wp_cta_redirect_" + cta_id + "=" + originalurl + cta_variation_string + string;
				jQuery(this).attr("href", newurl);
			}
		});

	});
}


jQuery(document).ready(function($) {

	var ctas = localStorage.getItem('wp_cta_loaded');
	if(ctas){
		var loaded_ctas = JSON.parse(localStorage.getItem('wp_cta_loaded'));
	} else {
	  return false;
	}

	/* Record Impressions */
	wp_cta_record_impressions(ctas);

	/* Add Tracking Classes */
	wp_cta_add_tracking_classes(loaded_ctas);

});