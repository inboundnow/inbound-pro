jQuery(document).ready(function($) {
	
	jQuery( "#ltpc-twitter-account-wrap" ).accordion({
      collapsible: true
    });
	
	var counter = parseInt(jQuery('#twitter-ac-count').val());
	
	jQuery('#ltpc-add-twitter-account').click(function() {
		
		counter += 1;
		var twitter_account_html = '<h3>Twitter Account ' + counter + '</h3>'
		+
		'<div class="row-fluid">' 
		+
		'<div class="span12"><div class="span4"><label>Account Nick Name</label></div> <div class="span8"><span><input type="text" name="ltpc-extra-lead-data-nick-name[]" id="ltpc-extra-lead-data-nick-name-'+ counter +'" value="" class="required" /></span></div></div>'	
		+
		'<div class="span12"><div class="span4"><label>API key</label></div> <div class="span8"><span><input type="text" name="ltpc-extra-lead-data-api-key[]" id="ltpc-extra-lead-data-api-key-'+ counter +'" value="" class="required" /></span></div></div>'	
		+
		'<div class="span12"><div class="span4"><label>API secret</label></div> <div class="span8"><span><input type="text" name="ltpc-extra-lead-data-api-secret[]" id="ltpc-extra-lead-data-api-secret-'+ counter +'" value="" class="required" /></span></div></div>'
		+
		'<div class="span12"><div class="span4"><label>Access token</label></div> <div class="span8"><span><input type="text" name="ltpc-extra-lead-data-access-token[]" id="ltpc-extra-lead-data-access-token-'+ counter +'" value="" class="required" /></span></div></div>'
		+
		'<div class="span12"><div class="span4"><label>Access token secret</label></div> <div class="span8"><span><input type="text" name="ltpc-extra-lead-data-access-token-secret[]" id="ltpc-extra-lead-data-access-token-secret-'+ counter +'" value="" class="required" /></span></div></div>'
		+
		'<div class="span12"><div class="span4"><label>Enable Service</label> </div><div class="span8"><span><input type="checkbox" name="ltpc-extra-lead-data-enable-service-'+ counter +'" id="ltpc-extra-lead-data-enable-service-'+ counter +'" value="1" checked="checked" />Check or uncheck to enable/ disabling the service.</span></div></div>'
		+
		'</div>'
		;
		jQuery('#ltpc-twitter-account-wrap').append(twitter_account_html);
		jQuery('#ltpc-twitter-account-wrap').accordion('refresh');
		return false;
		
	});
	
	jQuery("#ltpc-settings-form").validate({
		invalidHandler: function(event, validator) {
			if (validator.numberOfInvalids() > 0) {
				validator.showErrors();
				var index = jQuery(".has-error")
					.closest(".ui-accordion-content")
					.index(".ui-accordion-content");
				jQuery("#ltpc-twitter-account-wrap").accordion("option", "active", index);
			}
		},
		ignore: [],
	});
	
	/* enable ladda button */
	//var ladda = Ladda.create(document.querySelector( '#ltpc-test-auto-follow' ));
	
	/* Ajax for twitter autofollow connection */
	jQuery('#ltpc-test-auto-follow').click( function () {
		//ladda.start();
		jQuery('#ltpc-test-auto-follow').text('sending test data...');
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			async: true,
			data: {
				'action' : 'inbound_test_auto_follow'
			},
			success: function(data) {
				if (data) {
					alert(data);
				} else {
					alert('No errors detected! Check to see if your account has followed @atwellpub.');
				}
				
				jQuery('#ltpc-test-auto-follow').text('Test Auto Follow');
			}
		});
	});
	
	
});












