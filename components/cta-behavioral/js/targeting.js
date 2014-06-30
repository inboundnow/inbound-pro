function behavioral_load_variation(cta_id)
{
	jQuery.ajax({
		 type: "GET",
		 url: wp_cta_bt.ajax_url,
		 dataType: "script",
		 async:false,
		 data : {
		  'cta_id' : cta_id
		 },
		 success: function(vid) {
			console.log('Behvioral: Variation Loaded: CTA ID:' + cta_id +' Variation ID:'+ vid);
			
			
			jQuery('.cta_behavioral').each(function(){
				if ( !jQuery(this).hasClass('cta_behavioral_' + cta_id + '_' +vid ) )
				{
					jQuery(this).remove();
				}
			});
			
			jQuery('.wp_cta_'+cta_id+'_variation_'+vid+':first').show();
			
			/* once variation loaded fire impression counter */
			jQuery.ajax({
				type: 'POST',
				url: wp_cta_bt.admin_url,
				data: {
					action: 'wp_cta_record_impression',
					cta_id: cta_id,
					variation_id: vid	
				},
				success: function(user_id){
						console.log('Behavioral: CTA Page View Fired');	
					   },
				error: function(MLHttpRequest, textStatus, errorThrown){

					}

			});
			
			/* add tracking classes to links and forms */
			var wp_cta_id = '<input type="hidden" name="wp_cta_id" value="'+cta_id+'">';
			var wp_cta_vid = '<input type="hidden" name="wp_cta_vid" value="'+ vid +'">';
			jQuery('#wp_cta_'+cta_id+'_container form').each(function(){				
				jQuery(this).addClass('wpl-track-me');
				jQuery(this).append(wp_cta_id);
				jQuery(this).append(wp_cta_vid);
			})
			
			/* add click tracking to cta & lead */
			var lead_cpt_id = jQuery.cookie("wp_lead_id");
			var lead_email = jQuery.cookie("wp_lead_email");
			var lead_unique_key = jQuery.cookie("wp_lead_uid");

			// turn off link rewrites for custom ajax triggers
			if (typeof (wp_cta_settings) != "undefined" && wp_cta_settings !== null) {
				//return false;
			}
			
			/* setup lead data for click event tracking */
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
			jQuery('#wp_cta_'+cta_id+'_container a').each(function () 
			{
				jQuery(this).attr("data-event-id",  cta_variation.cta_id ).attr("data-cta-variation", vid );
				
				var orignalurl = jQuery(this).attr("href");
				if (originalurl.substr(0,1)!='#')
				{
				
					var cta_variation_string = "&wp-cta-v=" + vid;

					var newurl =  cta_variation.home_url + "?wp_cta_redirect_" + cta_variation.cta_id + "=" + orignalurl + cta_variation_string + string;
					jQuery(this).attr("href", newurl);
				}
			});
		}
	});
}
jQuery(document).ready(function($) {
   // put all your jQuery goodness in here.
	var stop = 'off';
	
	var lead_lists = jQuery.cookie("wp_lead_list");
	if (lead_lists) {
		var lead_lists = JSON.parse(lead_lists);
	}
    

    if (typeof (lead_lists) != "undefined" && lead_lists != null && lead_lists != "") 
	{		
		console.log('Behavior Targeting Enabled.');

		var list_array = lead_lists.ids; // the lists the lead belongs to
		
		console.log('Behavioral: Visitor belongs to these list ids:' + lead_lists.ids);
		
		jQuery('.wp_cta_container .wp_cta_variation').each(function()
		{	
			if (!jQuery(this).hasClass('is_behavioral')){
				return;
			}
			
			var cta_id = jQuery(this).attr('cta_id');
			var vid = jQuery(this).attr('vid');
			var behavioral = jQuery(this).attr('behavioral');
			if (typeof behavioral == 'undefined') {
				return true;
			}
			
			var behavioral_array = behavioral.split(',');
			
			behavioral_array.forEach(function(list_id){ 				
				var list_id = parseInt(list_id);
				var in_array = list_array.indexOf(list_id);
			
				if (in_array > -1) 
				{
					console.log("Behavioral: It's a match val: customer belongs to list id " + list_id );
					
					/* replace local storage object with new updated data */
					var loaded_ctas = JSON.parse(localStorage.getItem('wp_cta_loaded'));
			
					if (!loaded_ctas)
					{
						var loaded_ctas = {};
					}
					
					loaded_ctas[cta_id] = vid;
					localStorage.setItem('wp_cta_loaded', JSON.stringify(loaded_ctas));
					console.log('WP CTA Load Object Updated:' + JSON.stringify(loaded_ctas));
				}
			});
		});		
    }

 });
