/* Was non-conversion-tracking.js */
jQuery(document).ready(function($) {
	//alert(wplnct.admin_url);

	//record non conversion status
	var wp_lead_uid = jQuery.cookie("wp_lead_uid");
	var wp_lead_id = jQuery.cookie("wp_lead_id");
	//var data_block = jQuery.parseJSON(trackObj);
	var json = 0;
	var page_id = wplnct.final_page_id;
	//console.log(page_id);

// Page view trigging moved to /shared/tracking/page-tracking.js

// Check for Lead lists
var expired = jQuery.cookie("lead_session_list_check"); // check for session
if (expired != "true") {
	//var data_to_lookup = global-localized-vars;
	if (typeof (wp_lead_id) != "undefined" && wp_lead_id != null && wp_lead_id != "") {
		jQuery.ajax({
					type: 'POST',
					url: wplnct.admin_url,
					data: {
						action: 'wpl_check_lists',
						wp_lead_id: wp_lead_id,

					},
					success: function(user_id){
							jQuery.cookie("lead_session_list_check", true, { path: '/', expires: 1 });
							console.log("Lists checked");
						   },
					error: function(MLHttpRequest, textStatus, errorThrown){

						}

				});
		}
	}
/* end list check */

/* Set Expiration Date of Session Logging */
var e_date = new Date(); // Current date/time
var e_minutes = 30; // 30 minute timeout to reset sessions
e_date.setTime(e_date.getTime() + (e_minutes * 60 * 1000)); // Calc 30 minutes from now
jQuery.cookie("lead_session_expire", false, {expires: e_date, path: '/' }); // Set cookie on page loads
var expire_time = jQuery.cookie("lead_session_expire"); //
//console.log(expire_time);
});