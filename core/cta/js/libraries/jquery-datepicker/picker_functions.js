jQuery(document).ready(function ($) {

	jQuery('.time-picker').timepicker({ 'timeFormat': 'H:i' });
	
	if ($('.current_lander .new-date').length) { // implies *not* zero
		var current_val = jQuery(".current_lander .new-date").val();
  	} else {
		var current_val = jQuery(".new-date").val();
  	}
  	// if no timepicker in options fix it
  	if (typeof (current_val) == "undefined" || current_val === null || current_val == "") {
  		var current_val = '';
  	}

	var ret = current_val.split(" ");
	var current_date = ret[0];
	var current_time = ret[1];
	jQuery(".date").val(current_date);
	jQuery(".time").val(current_time);

	jQuery('.wp-cta_select_template').live('click', function() {
		var template = jQuery(this).attr('id');
		jQuery("#date-picker-"+template).val(current_date).addClass("live_date");
		jQuery("#time-picker-"+template).val(current_time).addClass("live_time");
	});

	jQuery('.current_lander .date, .current_lander .time').live('change', function () {
		var date_chosen = jQuery(".current_lander .date").val();
		var time_chosen = jQuery(".current_lander .time").val();
		var total_time = date_chosen + " " + time_chosen;
		jQuery(".new-date").val(total_time);

	});

});