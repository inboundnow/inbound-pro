function inbound_form_classes(forms_array, functionName, classes) {
	jQuery.each(forms_array, function(index, id) {
		var selector = jQuery.trim(id);
		for (var this_class in classes) {
			if (selector.indexOf('#')>-1) {
				jQuery(selector)[functionName](classes[this_class]);
				//console.log(selector);
			} else if (selector.indexOf('.')>-1) {
				jQuery(selector)[functionName](classes[this_class]);
			} else {
				jQuery("#" + selector)[functionName](classes[this_class]);
			}
		}

	});
}

function inbound_process_all_forms(){

	jQuery('form').each(function() {
	var match = 'comment', attributes = {}, form = jQuery(this), form_id = form.attr('id'), form_class = form.attr('class'), form_name = form.attr('name'), form_action = form.attr('action'), form_target = form.attr('target');

		if ( form.is( ".wpcf7-form" ) ) {
			var is_cf7_ajax = form.find('.ajax-loader');
			if (is_cf7_ajax){
				form.addClass('wpl-ajax-fallback');
			}
		}
		// map attrs
		attributes = {
			"form_id": form_id,
			"form_class": form_class,
			"form_name": form_name,
			"form_action": form_action,
			"form_target": form_target
		};
		// loop through attrs for match
		for (var atr in attributes) {
			/*var halt = false;
			clean_atr = atr.replace('form_', "");
			var class_match = jQuery.inArray('.' + attributes[atr], exclude_forms);
			var id_match = jQuery.inArray('#' + attributes[atr], exclude_forms);
			if (class_match > -1){
				var halt = true;
			}
			if (id_match > -1){
				var halt = true;
			} */
		   // console.log(atr + ": " + attributes[atr]);
		   if (typeof (attributes[atr]) != "undefined" && attributes[atr] != null && attributes[atr] != "") {
				if (attributes[atr].toLowerCase().indexOf(match)>-1 && inbound_ajax.comment_tracking === 'on') {
					form.addClass('wpl-track-me').addClass('wpl-comment-form');
				}
				// add fallback to ajaxed forms
				if (attributes[atr].toLowerCase().indexOf('ajax')>-1) {
					form.addClass('wpl-ajax-fallback');
				}

				if (attributes[atr].toLowerCase().indexOf('search')>-1 && inbound_ajax.search_tracking === 'on') {
					form.addClass('wpl-search-box');
				}
			}
		}
	});
}

jQuery(document).ready(function($) {

	var form_ids = wpleads.form_ids;
	var forms = form_ids.split(',');
	var form_exclude_ids = wpleads.form_exclude_ids;
	var exclude_forms = form_exclude_ids.split(',');
	var classes = ['wpl-track-me', 'wpl-search-box', 'wpl-ajax-fallback', 'wpl-comment-form'];

	// Process all forms on page
	inbound_process_all_forms();

	// Include Specific Forms from Settings
	if (typeof (form_ids) != "undefined" && form_ids != null && form_ids != "") {
		inbound_form_classes(forms, 'addClass', classes);
	}
	// Exclude Specific Forms from Settings
	if (typeof (form_exclude_ids) != "undefined" && form_exclude_ids != null && form_exclude_ids != "") {
		inbound_form_classes(exclude_forms, 'removeClass', classes);
	}


});