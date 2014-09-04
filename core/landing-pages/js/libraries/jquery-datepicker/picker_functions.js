jQuery(document).ready(function ($) {

/* Populates timepicker values */

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

  	jQuery('.new-date').each(function(){
  		var the_val = $(this).val();
  		if (typeof (the_val) == "undefined" || the_val === null || the_val == "") {
  			var the_val = '';
  		}
  		var ret = the_val.split(" ");
  		var current_date = ret[0];
  		var current_time = ret[1];
  		jQuery(this).parent().parent().find(".date.start").val(current_date);
  		jQuery(this).parent().parent().find(".time-picker").val(current_time);
  	});

	jQuery("body").on('change', '.jquery-date-picker .date.start', function () {
		var date_chosen = jQuery(this).val();
		var time_chosen = jQuery(this).parent().parent().find(".jquery-date-picker .time-picker").val();
		var total_time = date_chosen + " " + time_chosen;
		jQuery(this).parent().parent().find(".new-date").val(total_time);

	});

	jQuery("body").on('change', '.jquery-date-picker .time-picker', function () {
		var date_chosen = jQuery(this).parent().parent().find(".jquery-date-picker .date.start").val();
		var time_chosen = jQuery(this).val();
		if (typeof (time_chosen) === "undefined" && time_chosen == null && time_chosen === "") {
		var time_chosen = "00:00";
		}
		var total_time = date_chosen + " " + time_chosen;
		jQuery(this).parent().find(".new-date").val(total_time);

	});

});