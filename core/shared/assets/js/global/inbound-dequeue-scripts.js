jQuery(document).ready(function($) {
   jQuery("body").on('click', 'label', function () {

   	var status = jQuery(this).attr('class');
   	var status = status.replace('turn-', "");
   	if (status === 'off') {
   		jQuery(this).parent().find('.switch-button').addClass('status-off');
   	} else {
   		jQuery(this).parent().find('.switch-button').removeClass('status-off');
   	}
   	var the_script = jQuery(this).parent().attr('id');
   	var post_id = $('#inbound-dequeue-id').text();
   	var admin_screen = $('#inbound-fix-page').attr('data-admin-screen');
   	if (typeof (admin_screen) != "undefined" && admin_screen != null && admin_screen != "") {
   		var admin_screen = admin_screen;
   		var action = 'inbound_dequeue_admin_js'
   	} else {
   		var admin_screen = "";
   		var action = 'inbound_dequeue_js'
   	}
   	console.log(the_script);
   	console.log(status);

 	jQuery.ajax({
	   	    type: 'POST',
	   	    url: inbound_debug.admin_url,
	   	    context: this,
	   	    data: {
	   	        action: action,
	   	        post_id: post_id,
	   	        status: status,
	   	        the_script: the_script,
	   	        admin_screen: admin_screen
	   	    },

	   	    success: function (data) {
	   	       console.log("The script " + the_script + " has been turned " + status);
	   	       var self = this;
	   	       var str = data;
	   	       var obj = JSON.parse(str);
	   	      console.log(obj);
	   	    },

	   	    error: function (MLHttpRequest, textStatus, errorThrown) {
	   	        alert("Ajax not enabled");
	   	    }
	   	});
     });
 });
