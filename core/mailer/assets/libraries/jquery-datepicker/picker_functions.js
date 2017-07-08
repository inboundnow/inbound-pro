/**
*  These javascript functions help determine the datatime already populated by the email emailer
*/

jQuery(document).ready(function ($) {

	jQuery('.time-picker').timepicker({ 'timeFormat': 'H:i' });
	
	/* Get the current set date time */
	var current_val = jQuery(".new-date").val();
  	
  	// if no timepicker in options fix it
  	if (typeof (current_val) == "undefined" || current_val === null || current_val == "") {
  		var current_val = '';
  	}

	var ret = current_val.split(" ");
	var current_date = ret[0];
	var current_time = ret[1];
	
	jQuery(".date").val(current_date);
	jQuery(".time").val(current_time);

	/* Set the hidden field */
	jQuery('.date, .time').on( 'change' , function() {
		var new_datetime = jQuery(".date").val() + ' ' + jQuery(".time").val();
		jQuery('#inbound_send_datetime').val( new_datetime );
	});

});