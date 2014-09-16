 jQuery(document).ready(function($) {

    jQuery('form').each(function(){
    	jQuery(this).addClass('wpl-track-me');
	});

 	// Saves variation page views

 	// Save page view count
	jQuery.ajax({
		type: 'POST',
		url: landing_path_info.admin_url,
		data: {
			action: 'lp_record_impression',
			current_url: window.location.href,
			post_id: landing_path_info.post_id,
			variation_id: landing_path_info.variation,
			post_type: landing_path_info.post_type
			// add date?
		},
		success: function(user_id){
			//console.log('LP Page View Fired');
			   },
		error: function(MLHttpRequest, textStatus, errorThrown){
				//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
				//die();
			}

	});

 });