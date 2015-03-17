/* Record Impressions For Each Variation in CTA Object
* @param JSON ctas : a json string of {'cta':'vid'}
*/
function wp_cta_record_impressions(ctas) {

	/* Add Impressions to loaded varations*/
	InboundQuery.ajax({
		type: 'POST',
		url: cta_variation.admin_url,
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
	InboundQuery.each( ctas,  function(cta_id,vid) {
		var vid = ctas[cta_id];

		//console.log('CTA '+cta_id+' loads variation:' + vid);
		InboundQuery('.wp_cta_'+cta_id+'_variation_'+vid).show();

		/* add tracking classes to links and forms */
		var wp_cta_id = '<input type="hidden" name="wp_cta_id" value="' + cta_id + '">';
		var wp_cta_vid = '<input type="hidden" name="wp_cta_vid" value="'+ vid +'">';
		InboundQuery('#wp_cta_'+cta_id+'_variation_'+vid+' form').each(function(){
			InboundQuery(this).addClass('wpl-track-me');
			InboundQuery(this).append(wp_cta_id);
			InboundQuery(this).append(wp_cta_vid);
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

		var external = RegExp('^((f|ht)tps?:)?//(?!' + location.host + ')');
		InboundQuery('#wp_cta_'+cta_id+'_variation_'+vid+' a').each(function(){

			InboundQuery(this).attr("data-event-id",  cta_id ).attr("data-cta-variation", vid );
			var originalurl = InboundQuery(this).attr("href");

			if (originalurl  && originalurl.substr(0,1)!='#') {

				if ( InboundQuery(this).hasClass('do-not-track') ) {
					return;
				}

				var cta_variation_string = "&wp-cta-v=" + vid;
				var newurl =  cta_variation.home_url + "?wp_cta_redirect_" +cta_id + "=" +  encodeURIComponent(originalurl) + cta_variation_string + string;
				InboundQuery(this).attr("href", newurl);
			}
		});

	});
}

function wp_cta_load_variation( cta_id, vid, disable_ajax ) {
	/* Preload wp_cta_loaded storage object into variable */
	var loaded_ctas = {};
	var loaded_local_cta = _inbound.totalStorage('wp_cta_loaded');
	if (loaded_local_cta != null) {
		var loaded_ctas = JSON.parse(localStorage.getItem('wp_cta_loaded'));
	}

	/* if variation is pre-defined then immediately load variation*/
	if ( typeof vid != 'undefined' && vid != null && vid != '' ) {
		/* reveal variation */
		_inbound.debug('CTA '+cta_id+' loads variation:' + vid);
		InboundQuery('.wp_cta_'+cta_id+'_variation_'+vid).show();

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
		_inbound.totalStorage('wp_cta_loaded', loaded_ctas); // store cta data
		_inbound.deBugger('WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas));

	}
	/* Poll the ajax server for the correct variation to display */
	else {
		
		InboundQuery.ajax({
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

InboundQuery(document).ready(function($) {
	
	if (cta_variation.cta_id) {
		wp_cta_load_variation( cta_variation.cta_id , null , cta_variation.disable_ajax );
	}

	var ctas = localStorage.getItem('wp_cta_loaded');

	if(ctas){
		var loaded_ctas = JSON.parse(localStorage.getItem('wp_cta_loaded'));
	} else {
	  return false;
	}

	/* Add Tracking Classes & Reveal CTAs */
	wp_cta_add_tracking_classes(loaded_ctas);

	/* Record Impressions */
	wp_cta_record_impressions(ctas);
	
});
