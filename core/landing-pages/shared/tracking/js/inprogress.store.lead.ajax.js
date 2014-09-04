/* function add_inbound_form_class(el, value) {
  var value = value.replace(" ", "_");
  var value = value.replace("-", "_");
  el.addClass('inbound_map_value');
  el.attr('data-inbound-form-map', 'inbound_map_' + value);
}

// Build Form Object
function inbound_map_fields(el, value, Obj) {
  var formObj = [];
  var $this = el;
  var clean_output = value;
  var label = $this.closest('label').text();
  var exclude = ['credit-card']; // exlcude values from formObj
  var inarray = jQuery.inArray(clean_output, exclude);
  if(inarray == 0){
  	return null;
  }
  // Add items to formObj
  formObj.push({
  				field_label: label,
                field_name: $this.attr("name"),
                field_value: $this.attr("value"),
                field_id: $this.attr("id"),
                field_class: $this.attr("class"),
                field_type: $this.attr("type"),
                match: clean_output,
                js_selector: $this.attr("data-js-selector")
              });
  return formObj;
}

// Trim Whitespace
function trim(s) {
    s = s.replace(/(^\s*)|(\s*$)/gi,"");
    s = s.replace(/[ ]{2,}/gi," ");
    s = s.replace(/\n /,"\n"); return s;
}

// Run Form Mapper
function run_field_map_function(el, form, lookingfor) {
  var return_form;
  var formObj = new Array();
  var $this = el;

      var array = lookingfor.split(",");
      var array_length = array.length - 1;
      //var test = process_element($this);
      //console.log(test);
      // Main Loop
      for (var i = 0; i < array.length; i++) {
          var clean_output = trim(array[i]);
          //console.log(clean_output);

          // Look for attr name match
          if ($this.attr("name").toLowerCase().indexOf(clean_output)>-1) {
            var the_map = inbound_map_fields($this, clean_output, formObj);
            add_inbound_form_class($this, clean_output);
            console.log('match name: ' + clean_output);
          }
          // look for id match
          else if ($this.attr("id").toLowerCase().indexOf(clean_output)>-1) {
            var the_map = inbound_map_fields($this, clean_output, formObj);
            add_inbound_form_class($this, clean_output);
            console.log('match id: ' + clean_output);
          }
          // Look for label name match
          else if ($this.closest('li').children('label').length>0)
          {
            if ($this.closest('li').children('label').html().toLowerCase().indexOf(clean_output)>-1)
            {
              var the_map = inbound_map_fields($this, clean_output, formObj);
              add_inbound_form_class($this, clean_output);
              console.log('match li: ' + clean_output);
            }
          }
          // Look for closest div label name match
          else if ($this.closest('div').children('label').length>0)
          {
            if ($this.closest('div').children('label').html().toLowerCase().indexOf(clean_output)>-1)
            {
              var the_map = inbound_map_fields($this, clean_output, formObj);
              add_inbound_form_class($this, clean_output);
              console.log('match div: ' + clean_output);
            }
          } else {
          	return false;
          }
      }
      return_form = the_map;

  return return_form;
}
*/
jQuery(document).ready(function($) {

/* Mapping IN PROGRESS
var matched_form_items = [];
	jQuery('form.wpl-track-me').find('input[type=text],input[type=email]').each(function() {
	    var $this = jQuery(this);
	    var the_name = $this.attr('name');
	    var look_for = run_field_map_function( $this, 'form.wpl-track-me', "job title, first name, last name, email, e-mail, company, phone, tele");

	    if (typeof look_for !== "undefined") {
	    	matched_form_items.push(look_for);
	    } else {
	    	console.log('NO Match on ' + the_name);
	    }

	    //console.log(test);
	});
console.log(matched_form_items);
// Testing
//var test = run_field_map_function('form.wpl-track-me', "job title, fiz name, last name, email, e-mail, company, phonez, tele");

//console.log(test);
 /* Mapping IN PROGRESS */

/* Core Inbound Form Tracking Script */

	jQuery("body").on('submit', '.wpl-track-me', function (e) {

		this_form = jQuery(this);

		element_type = 'FORM';

		// process form only once
		processed = this_form.hasClass('lead_processed');
		if (processed === true) {
			return;
		}

		form_id = jQuery(this).attr('id');
		form_class = jQuery(this).attr('class');

		jQuery('button, input[type="button"]').css('cursor', 'wait');
		jQuery('input').css('cursor', 'wait');
		jQuery('body').css('cursor', 'wait');


		e.preventDefault(); // halt normal form

		var email = "";
		var firstname = "";
		var lastname = "";
		var phone = "";
		var company = "";
		var address = "";

		var tracking_obj = JSON.stringify(trackObj);
		var page_view_count = countProperties(pageviewObj);
		//console.log("view count" + page_view_count);

		if (!email)
		{

			 jQuery(this_form).find('input[type=text],input[type=email]').each(function() {
				if (this.value)
				{
					if (jQuery(this).attr("name").toLowerCase().indexOf('email')>-1&&!email) {
						email = this.value;

					}
					else if(jQuery(this).attr("name").toLowerCase().indexOf('e-mail')>-1&&!email) {
						 email = this.value;
					}
					else if(jQuery(this).attr("name").toLowerCase().indexOf('name')>-1&&!firstname) {
						 firstname = this.value;
					}
					else if (jQuery(this).attr("name").toLowerCase().indexOf('last')>-1) {
						 lastname = this.value;
					}
					else if (jQuery(this).attr("name").toLowerCase().indexOf('phone')>-1) {
						 phone = this.value;
					}
					else if (jQuery(this).attr("name").toLowerCase().indexOf('company')>-1) {
						 company = this.value;
					}
					else if (jQuery(this).attr("name").toLowerCase().indexOf('address')>-1) {
						 address = this.value;
					}
				}
			});
		}

		if (!email)
		{
			jQuery(this_form).find('input[type=text],input[type=email]').each(function() {
				if (jQuery(this).closest('li').children('label').length>0)
				{
					if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('email')>-1&&!email)
					{
						email = this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('e-mail')>-1&&!email) {
						email =  this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('name')>-1&&!firstname) {
						firstname = this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('last')>-1) {
						lastname = this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('phone')>-1) {
						 phone = this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('company')>-1) {
						 company = this.value;
					}
					else if (jQuery(this).closest('li').children('label').html().toLowerCase().indexOf('address')>-1) {
						 address = this.value;
					}
				}

			});
		}

		if (!email)
		{
			jQuery(this_form).find('input[type=text],input[type=email]').each(function() {
				if (jQuery(this).closest('div').children('label').length>0)
				{
					if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('email')>-1&&!email)
					{
						email = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('e-mail')>-1&&!email) {
						email = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('name')>-1&&!firstname) {
						firstname = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('last')>-1) {
						lastname = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('phone')>-1) {
						 phone = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('company')>-1) {
						 company = this.value;
					}
					else if (jQuery(this).closest('div').children('label').html().toLowerCase().indexOf('address')>-1) {
						 address = this.value;
					}
				}
			});
		}


		if (!lastname&&firstname)
		{
			var parts = firstname.split(" ");
			firstname = parts[0];
			lastname = parts[1];
		}

		var form_inputs = jQuery('.wpl-track-me').find('input[type=text],textarea,select');
		var post_values = {};
		// unset values with exclude array
		form_inputs.each(function() {

			post_values[this.name] = jQuery(this).val();
		});
		var post_values_json = JSON.stringify(post_values);

		var wp_lead_uid = jQuery.cookie("wp_lead_uid");
		var page_views = JSON.stringify(pageviewObj);
		var page_id = inbound_ajax.post_id;
		if (typeof (landing_path_info) != "undefined" && landing_path_info != null && landing_path_info != "") {
			var lp_variation = landing_path_info.variation;
		} else if (typeof (cta_path_info) != "undefined" && cta_path_info != null && cta_path_info != "") {
			var lp_variation = cta_path_info.variation;
		} else {
			var lp_variation = null;
		}

		jQuery.cookie("wp_lead_email", email, { path: '/', expires: 365 });



		/* Timeout Fallback
		setTimeout(function() {
			console.log('more than 10 seconds has passed. Release form')
			release_form_sub();
		}, 10000);
		*/
		jQuery.ajax({
			type: 'POST',
			url: inbound_ajax.admin_url,
			timeout: 10000,
			data: {
				element_type : element_type,
				action: 'inbound_store_lead',
				emailTo: email,
				first_name: firstname,
				last_name: lastname,
				phone: phone,
				address: address,
				company_name: company,
				wp_lead_uid: wp_lead_uid,
				page_view_count: page_view_count,
				page_views: page_views,
				post_type: inbound_ajax.post_type,
				lp_variation: lp_variation,
				json: tracking_obj, // replace with page_view_obj
				raw_post_values_json : post_values_json,
				lp_id: page_id
				/* Replace with jquery hook
					do_action('wpl-lead-collection-add-ajax-data');
				*/
			},
			success: function(user_id){
					jQuery(this_form).trigger("inbound_form_complete"); // Trigger custom hook
					jQuery.cookie("wp_lead_id", user_id, { path: '/', expires: 365 });
					jQuery.totalStorage('wp_lead_id', user_id);
					this_form.addClass('lead_processed');


					// Unbind form
					this_form.unbind('click');
					this_form.submit();

					jQuery('button, input[type="button"]').css('cursor', 'default');
					jQuery('input').css('cursor', 'default');
					jQuery('body').css('cursor', 'default');


					jQuery.totalStorage.deleteItem('page_views'); // remove pageviews
					jQuery.totalStorage.deleteItem('tracking_events'); // remove events
					//jQuery.totalStorage.deleteItem('cta_clicks'); // remove cta
				   },
			error: function(MLHttpRequest, textStatus, errorThrown){
					//alert(MLHttpRequest+' '+errorThrown+' '+textStatus); // debug

					// Create fallback localstorage object
					var conversionObj = new Array();
					conversionObj.push({
										action: 'inbound_store_lead',
										emailTo: email,
										first_name: firstname,
										last_name: lastname,
										wp_lead_uid: wp_lead_uid,
										page_view_count: page_view_count,
										page_views: page_views,
										post_type: inbound_ajax.post_type,
										lp_variation: lp_variation,
										json: tracking_obj,
										// type: 'form-completion',
										raw_post_values_json : post_values_json,
										lp_id: page_id
										});

					jQuery.totalStorage('failed_conversion', conversionObj); // store failed data
					jQuery.cookie("failed_conversion", true, { path: '/', expires: 365 });

					// If fail, cookie form data and ajax submit on next page load
					console.log('ajax fail');
					release_form_sub( this_form , element_type );

				}
		});

	});

	jQuery("body").on('click', '.wpl-track-me-link', function (e) {

		this_form = jQuery(this);

		var element_type='A';
		var a_href = jQuery(this).attr("href");

		// process form only once
		processed = this_form.hasClass('lead_processed');
		if (processed === true) {
			return;
		}

		form_id = jQuery(this).attr('id');
		form_class = jQuery(this).attr('class');

		jQuery(this).css('cursor', 'wait');
		jQuery('body').css('cursor', 'wait');


		e.preventDefault(); // halt normal form

		var tracking_obj = JSON.stringify(trackObj);
		var page_view_count = countProperties(pageviewObj);
		//console.log("view count" + page_view_count);

		var wp_lead_uid = jQuery.cookie("wp_lead_uid");
		var page_views = JSON.stringify(pageviewObj);

		var page_id = inbound_ajax.post_id;
		if (typeof (landing_path_info) != "undefined" && landing_path_info != null && landing_path_info != "") {
			var lp_variation = landing_path_info.variation;
		} else if (typeof (cta_path_info) != "undefined" && cta_path_info != null && cta_path_info != "") {
			var lp_variation = cta_path_info.variation;
		} else {
			var lp_variation = null;
		}

		/* Timeout Fallback
		setTimeout(function() {
			console.log('more than 10 seconds has passed. Release form')
			release_form_sub();
		}, 10000);
		*/
		jQuery.ajax({
			type: 'POST',
			url: inbound_ajax.admin_url,
			timeout: 10000,
			data: {
				action: 'inbound_store_lead',
				element_type : 'A',
				wp_lead_uid: wp_lead_uid,
				page_view_count: page_view_count,
				page_views: page_views,
				post_type: inbound_ajax.post_type,
				lp_variation: lp_variation,
				json: tracking_obj, // replace with page_view_obj
				lp_id: page_id
				/* Replace with jquery hook
					do_action('wpl-lead-collection-add-ajax-data');
				*/
			},
			success: function(user_id){
					jQuery(this_form).trigger("inbound_form_complete"); // Trigger custom hook
					jQuery.cookie("wp_lead_id", user_id, { path: '/', expires: 365 });
					jQuery.totalStorage('wp_lead_id', user_id);
					this_form.addClass('lead_processed');

					this_form.unbind('click');

					if (a_href)
					{
						window.location = a_href;
					}
					else
					{
						location.reload();
					}


					jQuery.totalStorage.deleteItem('page_views'); // remove pageviews
					jQuery.totalStorage.deleteItem('tracking_events'); // remove events
					//jQuery.totalStorage.deleteItem('cta_clicks'); // remove cta
				   },
			error: function(MLHttpRequest, textStatus, errorThrown){
					//alert(MLHttpRequest+' '+errorThrown+' '+textStatus); // debug

					// Create fallback localstorage object
					var conversionObj = new Array();
					conversionObj.push({
										action: 'inbound_store_lead',
										emailTo: email,
										first_name: firstname,
										last_name: lastname,
										wp_lead_uid: wp_lead_uid,
										page_view_count: page_view_count,
										page_views: page_views,
										post_type: inbound_ajax.post_type,
										lp_variation: lp_variation,
										json: tracking_obj,
										// type: 'form-completion',
										raw_post_values_json : post_values_json,
										lp_id: page_id
										});

					jQuery.totalStorage('failed_conversion', conversionObj); // store failed data
					jQuery.cookie("failed_conversion", true, { path: '/', expires: 365 });

					// If fail, cookie form data and ajax submit on next page load
					console.log('ajax fail');
					release_form_sub( this_form , element_type );

				}
		});



	});


	// Fallback for form ajax fails
	var failed_conversion = jQuery.cookie("failed_conversion");
	var fallback_obj = jQuery.totalStorage('failed_conversion');

	if (typeof (failed_conversion) != "undefined" && failed_conversion == 'true' ) {
		if (typeof fallback_obj =='object' && fallback_obj)
		{
			//console.log('fallback ran');
				jQuery.ajax({
					type: 'POST',
					url: inbound_ajax.admin_url,
					data: {
							action: fallback_obj[0].action,
							emailTo: fallback_obj[0].emailTo,
							first_name: fallback_obj[0].first_name,
							last_name: fallback_obj[0].last_name,
							wp_lead_uid: fallback_obj[0].wp_lead_uid,
							page_view_count: fallback_obj[0].page_view_count,
							page_views: fallback_obj[0].page_views,
							post_type: fallback_obj[0].post_type,
							lp_variation: fallback_obj[0].lp_variation,
							json: fallback_obj[0].json, // replace with page_view_obj
							// type: 'form-completion',
							raw_post_values_json : fallback_obj[0].raw_post_values_json,
							lp_id: fallback_obj[0].lp_id
							/* Replace with jquery hook
								do_action('wpl-lead-collection-add-ajax-data');
							*/
						},
					success: function(user_id){
						//console.log('Fallback fired');
						jQuery.removeCookie("failed_conversion"); // remove failed cookie
						jQuery.totalStorage.deleteItem('failed_conversion'); // remove failed data
						   },
					error: function(MLHttpRequest, textStatus, errorThrown){
							//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
							//die();
						}

				});
		}
	}

 });

function release_form_sub(this_form , element_type){
	jQuery('button, input[type="button"]').css('cursor', 'default');
	jQuery('input').css('cursor', 'default');
	jQuery('body').css('cursor', 'default');

	if (element_type=='FORM')
	{
		this_form.unbind('submit');
		this_form.submit();
	}

	if (element_type=='A')
	{
		this_form.unbind('wpl-track-me');

		if (a_href)
		{
			window.location = a_href;
		}
		else
		{
			location.reload();
		}
	}
}

 function inbound_find_form_fields(element, field_name, regex) {
	//console.log(element);
	//console.log(field_name);
	var return_val = "";
	var name = element.attr("name");
	var id = element.attr("id");
	var form_value = element.val();
	var nearest_li = element.closest('li').children('label');
	var nearest_div = element.closest('div').children('label');
	var newregex = new RegExp(regex, 'gi');
	//console.log(newregex);

	// Check name attributes for common names
	if (typeof (name) != "undefined" && name != null && name != "" && return_val === "") {

		var match = newregex.test(name); // regex to find matching name
		//console.log(match + name);
		if (match == true) {
			return form_value + " Regex 'name' Match: " + name;
			var return_val = form_value;
		}
		if (name.toLowerCase().indexOf(field_name)>-1) {
			return form_value + " indexof Match: " + name;
			var return_val = form_value;
		}

	}

	// Check nearest li element for common names
	if (typeof (nearest_li) != "undefined" && nearest_li != null && nearest_li != "" && return_val === "") {
		var the_label_text = nearest_li.html();
		var match = newregex.test(the_label_text); // regex to find matching label

		if (match == true){
			return the_label_text + " Regex Label Match" + name;
			var return_val = form_value;
		}

	}

	// Check nearest div element for common names
	if (typeof (nearest_div) != "undefined" && nearest_div != null && nearest_div != "" && return_val === "") {
		var the_div_text = nearest_div.html();
		var match = newregex.test(the_div_text); // regex to find matching label

		if (match == true){
			return the_div_text + " Regex Div Match" + name;
			var return_val = form_value;
		}

	}

	if(return_val === "") {
		//return "Not Found:" + name + "Looking for:" + field_name;
		return;
	}

}

// Regex to match form field values
/* Runs the above function and grabs form values
setTimeout(function() {
jQuery(".wpl-track-me").find('input[type=text],input[type=email]').each(function() {
			var this_input = jQuery(this);

			var email_field = inbound_find_form_fields(this_input, 'email', 'email|e-mail');
			var first_name_field = inbound_find_form_fields(this_input, 'first', 'first name|first-name|first_name');
			var last_name_field =  inbound_find_form_fields(this_input, 'last', 'Last name|last-name|last_name');

			console.log(email_field);
			console.log(first_name_field);
			console.log(last_name_field);
});
}, 400);
*/
	// end function to parse form fields
