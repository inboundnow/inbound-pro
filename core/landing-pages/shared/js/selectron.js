/**
 * Selectron.js - v 1.0
 * Copyright 2005, 2013 Inbound Now
 * Builds jQuery and CSS Selectors for with mouse clicks
 */
// Selector function used for field mapping, customizer, etc.
function eliminateDuplicates(arr) {
		        var i,
		            len = arr.length,
		            out = [],
		            obj = {};

		        for (i = 0; i < len; i++) {
		            obj[arr[i]] = 0;
		        }
		        for (i in obj) {
		            out.push(i);
		        }
		        return out;
}

function clean_element_classes(element) {
	var element = element.replace("lp-item-clicked", "");
	var element = element.replace("outline-element", "");
	return element;
}

// Make JQuery and CSS selectors
function process_element(object) {
				// remove vars and separate by commas
				var js_array = [],
					css_array = [],
					finalclass = '';
				var hasclass = jQuery(object).attr('class');
				var hasid = jQuery(object).attr('id');
				var elementType = jQuery(object).prop('localName');
				jQuery("[data-selector-on=true]").removeAttr('data-selector-on');
				jQuery(object).attr('data-selector-on', 'true');

				// Assign Class Name
				if (typeof (hasclass) != "undefined" && hasclass !== null && hasclass !== "") {
				var hasclass = clean_element_classes(hasclass);
				var cleanerclass = hasclass.replace(/^\s+|\s+$/g,''); // remove trailing whitespace


		        var clean_spaces = cleanerclass.replace(/\s{2,}/g, ' '); // remove more than one space
		        var cleanerstillclass = clean_spaces.split(' ').join('.'); // split at space and join
		        var finalclass = cleanerstillclass.replace(" ", ".");
		      	}

		        // Get ID
		        if (typeof (hasid) != "undefined" && hasid !== null) {
		            selector = "#" + hasid;
		        // use class if element no ID
		        } else if (typeof (finalclass) != "undefined" && finalclass !== null && finalclass !== "") {
		            selector = "." + finalclass;
		        // use element type if no ID/Class
		        } else {
		            selector = elementType;
		        }
		        // console.log(selector);

		        var parent_class = jQuery(object).parent().attr("class"); // First level parent
		        var parent_class_two = jQuery(object).parent().parent().attr("class"); // Second level parent
		        var parent_class_four = jQuery(object).parent().parent().parent().parent().attr("class"); // Second level parent
		        var parent_id = jQuery(object).parent().attr("id"); // First level id
		        var parent_id_two = jQuery(object).parent().parent().attr("id"); // Second level id
		        var parent_id_three = jQuery(object).parent().parent().parent().attr("id"); // Third level id
		        var parent_id_four = jQuery(object).parent().parent().parent().parent().attr("id"); // fourth level id
		        var parent_id_five = jQuery(object).parent().parent().parent().parent().parent().attr("id"); // fifth level id

		        function santize_class(el){
		        		var this_class = el.replace(/^\s+|\s+$/g,'');
		        	 	var this_class = "." + this_class;
		        	 	var this_class = this_class.replace(/[.\s]+$/g, ""); // remove trailing whitespace
		        		var this_class = this_class.replace(/\s{2,}/g, ' '); // remove more than one space
		        	 	var this_class = this_class.split(' ').join('.'); // split at space and join
		        	 	var this_class = this_class.replace(" ", ".");
		        	 	return this_class;
		        }
		        function santize_id(el){
		        	 	var this_id = el.replace(/^\s+|\s+$/g,''); // remove trailing/leading whitespace
		        	 	var this_id = "#" + this_id;
		        	 	return this_id;
		        }
		        var insert_parent_one = "";
		        var insert_parent_two = "";
		        var insert_parent_one_clean = ""
		        // Parent Class
		        if (typeof (parent_class) != "undefined" && parent_class !== null && parent_class !== "") {
		        	var has_parent = santize_class(parent_class);
		        	var insert_parent_one = " " + has_parent;
		        	var insert_parent_one_clean = has_parent;
		        }
		        // Second Level Parent Class
		        if (typeof (parent_class_two) != "undefined" && parent_class_two !== null && parent_class_two !== "") {
		        	var has_parent_two = santize_class(parent_class_two);
		        	var insert_parent_two = has_parent_two;
		        }
		        // fourth Level Parent Class
		        if (typeof (parent_class_four) != "undefined" && parent_class_four !== null && parent_class_four !== "") {
		        	var parent_class_four = santize_class(parent_class_four);

		        }

				// Use Parent Element in selector
				if (typeof (hasid) != "undefined" && hasid !== null) {
				    selector = santize_id(hasid);
				    parent_selector = "";
				} else if (typeof (parent_id) != "undefined" && parent_id !== null) {
		            var parent_selector = santize_id(parent_id) + insert_parent_one_clean;
				} else if (typeof (parent_id_three) != "undefined" && parent_id_three !== null && parent_id_two !== "") {
			    	var parent_selector = "#" + parent_id_three + insert_parent_one;
				} else if (typeof (parent_id_four) != "undefined" && parent_id_four !== null && parent_id_four !== "") {
			    	var parent_selector = "#" + parent_id_four + insert_parent_one;
				} else if (typeof (parent_id_five) != "undefined" && parent_id_five !== null && parent_id_five !== "") {
			    	var parent_selector = "#" + parent_id_five + insert_parent_one;
				} else if (typeof (has_parent) != "undefined" && has_parent !== null && has_parent !== "") {
		        	var parent_selector = has_parent;
		   		} else if (typeof (has_parent_two) != "undefined" && has_parent_two !== null && has_parent_two !== "") {
		   			var parent_selector = has_parent_two;
		        } else {
		            var parent_selector = "body";
		        }

		        var element_selector = parent_selector + " " + selector; // the clicked element without eq number
		        var element_selector = element_selector.replace(/^\s+|\s+$/g,''); // trim leading/trailing spaces
		        var element_length = jQuery(element_selector).length;
		        //console.log(element_selector);
		       	grab_eq_number(element_selector); // Process element_selector for uniques

		        // Grab jQuery :eq number for exact element match
		        function grab_eq_number(elm) {

		            jQuery(elm).each(function (index, value, count_of_elements) {
		            	var count_of_elements = jQuery(element_selector).size(); // how many of the same elements exist
		         		eqselector = parent_selector + " " + selector + ":eq(" + index + ")";
		         		eqselector = eqselector.replace(/^\s+|\s+$/g,''); // trim leading/trailing spaces
		         		jQuery(this).attr('data-eq-selector', eqselector); // add html5 data attr for js selector

		         		jQuery(this).attr('data-count-size', count_of_elements);
		            	// If only one element_selector exists, write css & js
		            	if (count_of_elements === 1) {
		            		// console.log(count_of_elements + " count of " + element_selector);
		            		jQuery(this).attr('data-css-selector', element_selector);
		            		jQuery(this).attr('data-js-selector', element_selector);
		            		css_array.push(element_selector); // push single elements to css array
		            		js_array.push(element_selector); // push single element to js array
		            		return false;
		            	// Add EQ number for non unique js selector
		            	} else {
		            		// console.log(count_of_elements + " count of " + element_selector);

		            		jQuery(this).attr('data-css-selector', element_selector); // add html5 data css attr
		            		css_array.push(element_selector); // push single elements to css array
		            		final_js_selector = parent_selector + " " + selector + ":eq(" + index + ")";
		            		final_js_selector = final_js_selector.replace(/^\s+|\s+$/g,''); // trim spaces
		            		jQuery(this).attr('data-js-selector', final_js_selector);

		            		// Push correct eq number through
		            		if (jQuery(this).attr('data-selector-on')) {
		            		     js_array.push(final_js_selector);
		            		}

		            	}

		            });

		        }


		    dedupe_js_array = eliminateDuplicates(js_array);
		    dedupe_css_array = eliminateDuplicates(css_array);
		    //console.log(dedupe_js_array); // Log of all selected elements in array
		    //console.log(dedupe_css_array); // Log of all selected elements that are single in array
		   	var css_single_selectors = dedupe_css_array.join(", "); // Join Array items with commas
		    var eq_js_selectors = dedupe_js_array.join(", "); // Join Array items with commas

		   return [eq_js_selectors, css_single_selectors];

}
// Run on page
jQuery(document).ready(function($) {
   	jQuery('html').on('click', 'body', function (event) {
   		event.preventDefault();
   	    var $tgt = jQuery(event.target);
   	    $tgt.toggleClass(event.type == 'click' ? 'lp-item-clicked' : '');
   	    process_element($tgt);
   	});
 });