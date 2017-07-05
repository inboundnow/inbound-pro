jQuery(document).ready(function($) {

	jQuery('.variation-group a').each(function(){
		var permalink = jQuery(this).attr('data-permalink');

		jQuery(this).attr('href', permalink + "&frontend=true&email-customizer=on");

	});

	/* remove addressing */
	/* jQuery('.mail-headers-container').hide(); */

	/* remove send settings */
	jQuery('.mail-send-settings-container').remove();

	/* remove ability to add new tab */
	jQuery('#tabs-add-variation').remove();

	/* move save button down to bottom */
	jQuery('#publish').remove();

	/* remove unused send buttons */
	jQuery('#postbox-container-1').appendTo('#postbox-container-2');

	/* hide send type*/
	jQuery('#email-send-type').hide();

	/* hide select tempalte */
	jQuery('#email-selected-template').hide();

	/* hide tags */
	jQuery('#tagsdiv-inbound_email_tag').hide();

	jQuery('.wrap h2:first').hide();
	jQuery('#mailer-change-template-button').hide();

	/**

	*/

});