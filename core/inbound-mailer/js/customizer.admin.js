jQuery(document).ready(function($) {

	jQuery('.variation-group a').each(function(){
		var permalink = jQuery(this).attr('data-permalink');

		jQuery(this).attr('href', permalink + "&frontend=true&email-customizer=on");

	});	

	/* remove addressing */
	jQuery('.mail-headers-container').remove();
	
	/* remove send settings */	
	jQuery('.mail-send-settings-container').remove();
	
	/* remove ability to add new tab */
	jQuery('#tabs-add-variation').remove();
	
	/* move save button down to bottom */
	jQuery('#publish').appendTo('#postbox-container-2');
	
	/* remove unused send buttons */
	jQuery('#postbox-container-1').remove();
	
	jQuery('.wrap h2:first').hide();
	jQuery('#inbound-mailer-change-template-button').hide();
	
	jQuery('body').on( 'submit' , 'form' , function() {
		setTimeout( function() {
			parent.location.reload();

		}, 1000 );
		
	});
	/**
	
	*/

});