jQuery(document).ready(function($) {

	jQuery('.nav-tab-wrapper.a_b_tabs a').each(function(){
		var permalink = jQuery(this).attr('data-permalink');

		jQuery(this).attr('href', permalink + "&frontend=true&cta-template-customize=on");

	});	
	
	jQuery('#tabs-add-variation').remove();
	jQuery('#wp-cta-notes-area').hide();
	jQuery('.wrap h2:first').hide();
	jQuery('#wp-cta-change-template-button').hide();
	
	jQuery('body').on( 'submit' , 'form' , function() {
		setTimeout( function() {
			parent.location.reload();

		}, 1000 );
		
	});
	/**
	
	*/

});