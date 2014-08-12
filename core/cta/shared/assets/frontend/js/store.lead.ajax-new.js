//
//var inbound_data = inbound_data || {};
//// Ensure global _gaq Google Analytics queue has been initialized.
//var _gaq = _gaq || [];
//
//function add_inbound_form_class(el, value) {
//  //var value = value.replace(" ", "_");
//  //var value = value.replace("-", "_");
//  //el.addClass('inbound_map_value');
//  //el.attr('data-inbound-form-map', 'inbound_map_' + value);
//}
//// http://clubmate.fi/setting-and-reading-cookies-with-javascript/
//function createCookie(name, value, days) {
//    var expires;
//    if (days) {
//        var date = new Date();
//        date.setTime(date.getTime()+(days*24*60*60*1000));
//        expires = "; expires="+date.toGMTString();
//    }
//    else {
//        expires = "";
//    }
//    document.cookie = name+"="+value+expires+"; path=/";
//}
//// Read cookie
//function readCookie(name) {
//    var nameEQ = name + "=";
//    var ca = document.cookie.split(';');
//    for(var i=0;i < ca.length;i++) {
//        var c = ca[i];
//        while (c.charAt(0) === ' ') {
//            c = c.substring(1,c.length);
//        }
//        if (c.indexOf(nameEQ) === 0) {
//            return c.substring(nameEQ.length,c.length);
//        }
//    }
//    return null;
//}
//// Erase cookie
//function eraseCookie(name) {
//    createCookie(name,"",-1);
//}
//
///* Count number of session visits */
//function countProperties(obj) {
//    var count = 0;
//    for(var prop in obj) {
//        if(obj.hasOwnProperty(prop))
//            ++count;
//    }
//    return count;
//}
//
//
//function get_inbound_form_value(el) {
//  var value = el.value;
//  return value;
//}
//
//
//// Build Form Object
//function inbound_map_fields(el, value, Obj) {
//  var formObj = [];
//  var $this = el;
//  var clean_output = value;
//  var label = $this.closest('label').text();
//  var exclude = ['credit-card']; // exlcude values from formObj
//  var inarray = jQuery.inArray(clean_output, exclude);
//  if(inarray == 0){
//  	return null;
//  }
//  // Add items to formObj
//  formObj.push({
//  				field_label: label,
//                field_name: $this.attr("name"),
//                field_value: $this.attr("value"),
//                field_id: $this.attr("id"),
//                field_class: $this.attr("class"),
//                field_type: $this.attr("type"),
//                match: clean_output,
//                js_selector: $this.attr("data-js-selector")
//              });
//  return formObj;
//}
//
//// Trim Whitespace
//function trim(s) {
//    s = s.replace(/(^\s*)|(\s*$)/gi,"");
//    s = s.replace(/[ ]{2,}/gi," ");
//    s = s.replace(/\n /,"\n"); return s;
//}
//
//function inbound_ga_log_event(category, action, label) {
//  _gaq.push(['_trackEvent', category, action, label]);
//}
//
//// Run Form Mapper
//// TODO check for already processesed fields via in_object_already
//// check on the dupe value
//function run_field_map_function(el, lookingfor) {
//  var return_form;
//  var formObj = new Array();
//  var $this = el;
//  var body = jQuery("body");
//  var input_id = $this.attr("id") || "NULL";
//  var input_name = $this.attr("name") || "NULL";
//  var this_val = $this.attr("value");
//      var array = lookingfor.split(",");
//      var array_length = array.length - 1;
//
//      // Main Loop
//      for (var i = 0; i < array.length; i++) {
//          var clean_output = trim(array[i]);
//          var nice_name = clean_output.replace(/^\s+|\s+$/g,'');
//          var nice_name = nice_name.replace(" ",'_');
//          var in_object_already = nice_name in inbound_data;
//          //console.log(clean_output);
//
//          // Look for attr name match
//          if (input_name.toLowerCase().indexOf(clean_output)>-1) {
//            var the_map = inbound_map_fields($this, clean_output, formObj);
//            add_inbound_form_class($this, clean_output);
//            console.log('match name: ' + clean_output);
//            console.log(nice_name in inbound_data);
//            if (!in_object_already) {
//            inbound_data[nice_name] = this_val;
//        	}
//          }
//          // look for id match
//          else if (input_id.toLowerCase().indexOf(clean_output)>-1) {
//            var the_map = inbound_map_fields($this, clean_output, formObj);
//            add_inbound_form_class($this, clean_output);
//            console.log('match id: ' + clean_output);
//            if (!in_object_already) {
//            inbound_data[nice_name] = this_val;
//        	}
//          }
//          // Look for label name match
//          else if ($this.closest('li').children('label').length>0){
//          	var closest_label = $this.closest('li').children('label').html() || "NULL";
//            if (closest_label.toLowerCase().indexOf(clean_output)>-1)
//            {
//              var the_map = inbound_map_fields($this, clean_output, formObj);
//              add_inbound_form_class($this, clean_output);
//              console.log($this.context);
//
//              var exists_in_dom = body.find("[data-inbound-form-map='inbound_map_" + nice_name + "']").length;
//              console.log(exists_in_dom);
//              console.log('match li: ' + clean_output);
//              if (!in_object_already) {
//              	inbound_data[nice_name] = this_val;
//              }
//
//            }
//          }
//          // Look for closest div label name match
//          else if ($this.closest('div').children('label').length>0) {
//          	var closest_div = $this.closest('div').children('label').html() || "NULL";
//            if (closest_div.toLowerCase().indexOf(clean_output)>-1)
//            {
//              var the_map = inbound_map_fields($this, clean_output, formObj);
//              add_inbound_form_class($this, clean_output);
//              console.log('match div: ' + clean_output);
//              if (!in_object_already) {
//              inbound_data[nice_name] = this_val;
//          	  }
//            }
//          }
//          // Look for closest p label name match
//          else if ($this.closest('p').children('label').length>0) {
//          	var closest_p = $this.closest('p').children('label').html() || "NULL";
//            if (closest_p.toLowerCase().indexOf(clean_output)>-1)
//            {
//              var the_map = inbound_map_fields($this, clean_output, formObj);
//              add_inbound_form_class($this, clean_output);
//              console.log('match p: ' + clean_output);
//              if (!in_object_already) {
//              inbound_data[nice_name] = this_val;
//          	  }
//            }
//          } else {
//          	console.log('Need additional mapping data');
//          }
//      }
//      return_form = the_map;
//
//  return inbound_data;
//}
//
//function return_mapped_values(this_form) {
//	// Map form fields
//	jQuery(this_form).find('input[type!="hidden"],textarea,select').each(function() {
//		console.log('run');
//		var this_input = jQuery(this);
//		var this_input_val = this_input.val();
//		if (typeof (this_input_val) != "undefined" && this_input_val != null && this_input_val != "") {
		//var inbound_data = run_field_map_function( this_input, "name, first name, last name, email, e-mail, phone, website, job title, company, tele, address, comment");
//		}
//		return inbound_data;
//	});
//	return inbound_data;
//}
//
//function merge_form_options(obj1,obj2){
//    var obj3 = {};
//    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
//    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
//    return obj3;
//}
//
//function release_form_sub(this_form, element_type, form_type){
//	form_type = typeof form_type !== 'undefined' ? form_type : "normal";
//	jQuery('body, button, input, input[type="button"]').css('cursor', 'default');
//	jQuery.totalStorage.deleteItem('inbound_search'); // remove search
//	if (element_type=='FORM') {
//		this_form.unbind('submit');
//		this_form.submit();
//
//		if (form_type === "comment"){
//			console.log("RELEASE ME");
//			setTimeout(function() {
//			  jQuery(".wpl-comment-form").find('[type="submit"]').click();
//			}, 100);
//		}
//	}
//
//	if (element_type=='A') {
//		this_form.unbind('wpl-track-me');
//		var link = this_form.attr('href');
//		if (link) {
//			window.location = link;
//		} else {
//			location.reload();
//		}
//	}
//}
//
//function set_lead_fallback(data){
//	jQuery.totalStorage('failed_conversion', data); // store failed data
//	jQuery.cookie("failed_conversion", true, { path: '/', expires: 365 });
//	console.log('Set backup lead storage');
//}
//
///* Check form type */
//function inbound_form_type(this_form) {
//	var inbound_data = inbound_data || {},
//	form_type = 'normal';
//	if ( this_form.is( ".wpl-comment-form" ) ) {
//		inbound_data['form_type'] = 'comment';
//		form_type = 'comment';
//	} else if ( this_form.is( ".wpl-search-box" ) ) {
//		var is_search = true;
//		form_type = 'search';
//		inbound_data['form_type'] = 'search';
//	} else if ( this_form.is( '.wpl-track-me-link' ) ){
//		var have_email = readCookie('wp_lead_email');
//		console.log(have_email);
//		inbound_data['form_type'] = 'link';
//		form_type = 'search';
//	}
//	return form_type;
//}
//
//function grab_all_form_input_vals(this_form){
//	var post_values = post_values || {},
//	inbound_exclude = inbound_exclude || [],
//	form_inputs = this_form.find('input,textarea,select');
	//inbound_exclude.push('inbound_furl', 'inbound_current_page_url', 'inbound_notify', 'inbound_submitted', 'post_type', 'post_status', 's', 'inbound_form_name', 'inbound_form_id', 'inbound_form_lists');
//	var form_type = inbound_form_type(this_form),
//	inbound_data = inbound_data || {},
//	email = inbound_data['email'] || false;
//
//	form_inputs.each(function() {
//		var $input = jQuery(this),
//		input_type = $input.attr('type'),
//		input_val = $input.val();
//		if (input_type === 'checkbox') {
//			input_checked = $input.attr("checked");
//			console.log(input_val);
//			console.log(input_checked);
//			console.log(post_values[this.name]);
//			if (input_checked === "checked"){
//			if (typeof (post_values[this.name]) != "undefined") {
//				post_values[this.name] = post_values[this.name] + "," + input_val;
//				console.log(post_values[this.name]);
//			} else {
//				post_values[this.name] = input_val;
//			}
//
//			}
//		}
//		if (jQuery.inArray(this.name, inbound_exclude) === -1 && input_type != 'checkbox'){
//		   post_values[this.name] = input_val;
//		}
//		if (this.value.indexOf('@')>-1&&!email){
//			email = input_val;
//			inbound_data['email'] = email;
//		}
//		if (form_type === 'search') {
//			inbound_data['search_keyword'] = input_val.replace('"', "'");
//		}
//	});
//	var all_form_fields = JSON.stringify(post_values);
//	return all_form_fields;
//}
//
//function inbound_form_submit(this_form, e) {
//	/* Define Variables */
//	var inbound_data = inbound_data || {};
//	// Dynamic JS object for passing custom values. This can be hooked into by third parties by using the below syntax.
//	var pageviewObj = jQuery.totalStorage('page_views');
//	inbound_data['page_view_count'] = countProperties(pageviewObj);
//	inbound_data['leads_list'] = jQuery(this_form).find('#inbound_form_lists').val();
//	inbound_data['source'] = jQuery.cookie("wp_lead_referral_site") || "NA";
//	inbound_data['page_id'] = inbound_ajax.post_id;
//	inbound_data['page_views'] = JSON.stringify(pageviewObj);
//
//	// Map form fields
//	var returned_form_data = return_mapped_values(this_form); //console.log(returned_form_data);
//	var inbound_data = merge_form_options(inbound_data,returned_form_data); //console.log(inbound_data);
//
//	// Set variables after mapping
//	inbound_data['email'] = (!inbound_data['email']) ? this_form.find('.inbound-email').val() : inbound_data['email'];
//	inbound_data['form_name'] = this_form.find('.inbound_form_name').val() || "Not Found";
//	inbound_data['form_id'] = this_form.find('.inbound_form_id').val() || "Not Found";
//	inbound_data['first_name'] = (!inbound_data['first_name']) ? inbound_data['name'] : inbound_data['first_name'];
//	inbound_data['last_name'] = inbound_data['last_name'] || '';
//	inbound_data['phone'] = inbound_data['phone'] || '';
//	inbound_data['company'] = inbound_data['company'] || '';
//	inbound_data['address'] = inbound_data['address'] || '';
//
//	// Fallbacks for values
	//inbound_data['name'] = (inbound_data['first_name'] && inbound_data['last_name']) ? inbound_data['first_name'] + " " + inbound_data['last_name'] : inbound_data['name'];
//
//	if (!inbound_data['last_name'] && inbound_data['first_name']) {
//		var parts = inbound_data['first_name'].split(" ");
//		inbound_data['first_name'] = parts[0];
//		inbound_data['last_name'] = parts[1];
//	}
//
//	/* Store form fields & exclude field values */
//	var all_form_fields = grab_all_form_input_vals(this_form);
//	/* end Store form fields & exclude field values */
//
//	if(inbound_data['email']){
//	   createCookie("wp_lead_email", inbound_data['email'], 365); /* set email cookie */
//	}
//
//	//var variation = (typeof (landing_path_info) != "undefined") ? landing_path_info.variation : false;
//
//	if (typeof (landing_path_info) != "undefined") {
//		var variation = landing_path_info.variation;
//	} else if (typeof (cta_path_info) != "undefined") {
//		var variation = cta_path_info.variation;
//	} else {
//		var variation = 0;
//	}
//
//	inbound_data['variation'] = variation;
//	inbound_data['post_type'] = inbound_ajax.post_type;
//	inbound_data['wp_lead_uid'] = jQuery.cookie("wp_lead_uid") || null;
//	inbound_data['ip_address'] = inbound_ajax.ip_address;
//	inbound_data['search_data'] = JSON.stringify(jQuery.totalStorage('inbound_search')) || {};
//
//	var lp_check = (inbound_ajax.post_type === 'landing-page') ? 'Landing Page' : "";
//	var cta_check = (inbound_ajax.post_type === 'wp-call-to-action') ? 'Call to Action' : "";
//	var page_type = (!cta_check && !lp_check) ? inbound_ajax.post_type : lp_check + cta_check;
//
//	// jsonify inbound_data
//	var mapped_form_data = JSON.stringify(inbound_data);
//
//	var data = {};
//	var data = {
//			"action": 'inbound_store_lead',
//			"emailTo": inbound_data['email'],
//			"first_name": inbound_data['first_name'],
//			"last_name": inbound_data['last_name'],
//			"phone": inbound_data['phone'],
//			"address": inbound_data['address'],
//			"company_name": inbound_data['company'],
//			"page_views": inbound_data['page_views'],
//			"form_input_values": all_form_fields,
//			"Mapped_Data": mapped_form_data,
//			"Search_Data": inbound_data['search_data']
//		}
//	return data;
//}
//
//jQuery(document).ready(function($) {
//
//	var cookies = InboundAnalytics.Utils.getAllCookies();
//	var inbound_store = jQuery.totalStorage('inbound_cookies');
		//
//	// loop through cookies and assign to inbound_data object
//	if (typeof inbound_store =='object' && inbound_store) {
//		for(var name in inbound_store) {
//		  if (name.indexOf('utm')>-1) {
//		  inbound_data[name] = cookies[name];
//		  //console.log( name + " : " + cookies[name]  );
//		  }
//		}
//	}
//
//	//console.log(inbound_data);
//	if ( jQuery('.wpl-search-box').length ) {
//	/* Core Inbound Search Tracking Script */
//	jQuery("body").on('submit', '.wpl-search-box', function (e) {
//		var inbound_search_data = jQuery.totalStorage('inbound_search') || {},
//		$this_form = $(this),
//		processed = false;
//		datetime = wplft.track_time;
//		if( $this_form.is(".search-processes") ) {
//			console.log('yep');
//			processed = true;
//
//		}
//		//return false;
//		$('body, button, input[type="button"], input').css('cursor', 'wait');
//		e.preventDefault();
//		var has_email = readCookie('wp_lead_email');
//		var search_count = countProperties(inbound_search_data);
//		form_inputs = $this_form.find('input[type=text],input[type=search]');
//
//		form_inputs.each(function(i) {
//			var value = jQuery(this).val().replace('"', "'");
//			inbound_search_data[search_count + 1] = {"value" : value, "date" : datetime};
//		});
//		jQuery.totalStorage('inbound_search', inbound_search_data); // store search history data
//		console.log(JSON.stringify(inbound_search_data));
//
//		// If no data to id the user exit
//		if (typeof (has_email) != "undefined" && has_email != null && has_email != "" && processed === false) {
//
//			// store search data and release
//			var data = {};
//			var data = {
//					"action": 'inbound_store_lead_search',
//					"search_data": inbound_search_data,
//					"email": has_email,
//					"date": datetime
//				}
//
//			//return false;
//			jQuery.ajax({
//				type: 'POST',
//				url: inbound_ajax.admin_url,
//				timeout: 10000,
//				data: data,
//				dataType: 'html',
//				success: function(user_id){
//						$this_form.trigger("inbound_search_form_complete"); // Trigger custom hook
//						$this_form.addClass('search-processed');
//						$this_form.removeClass('wpl-search-box');
//						// Unbind form
//						//release_form_sub($this_form, 'FORM', inbound_data['form_type']);
//
//						$('body, button, input[type="button"], input').css('cursor', 'default');
//						jQuery.totalStorage.deleteItem('inbound_search'); // remove search
//						console.log("search fired");
//						$this_form.unbind('submit');
//						$this_form.submit();
//					   },
//				error: function(MLHttpRequest, textStatus, errorThrown){
//
//						console.log("failwhale fired");
//						$this_form.unbind('submit');
//						$this_form.submit();
//
//					}
//			});
//		} else {
//			$this_form.unbind('submit');
//			$this_form.submit();
//			// storage local storage search history
//			jQuery.totalStorage('inbound_search', inbound_search_data); // store search history data
//		}
//
//
//	});
//	}
//
//
//	/* Core Inbound Form Tracking Script */
//	if ( jQuery('.wpl-track-me').length ) {
//	jQuery("body").on('submit', '.wpl-track-me', function (e) {
//		var inbound_data = inbound_data || {},
//		this_form = jQuery(this),
//		event_type = e.type,
//		is_search = false,
//		form_type = 'normal';
//
//
//		inbound_data['form_type'] = inbound_form_type(this_form);
//
//		element_type = 'FORM';
//
//		// halt normal form submission
//		$('body, button, input[type="button"], input').css('cursor', 'wait');
//		e.preventDefault();
//
//		// Email Validation Check
//		var inbound_form_exists = $("#inbound-form-wrapper").length;
//		var email_validation = $(".inbound-email.invalid-email").length;
//		if (email_validation > 0 && inbound_form_exists > 0) {
//			jQuery(".inbound-email.invalid-email").focus();
//			alert("Please enter a valid email address");
//			return false;
//		}
//		$(this_form).trigger("inbound_form_custom_data"); // trigger custom hook
//		data = inbound_form_submit(this_form, e); // big function for processing
//
//		ajax_fallback = this_form.is('.wpl-ajax-fallback');
//
//		if (ajax_fallback === true) {
//			console.log('true');
//			this_form.removeClass('wpl-track-me'); // release submit
//			set_lead_fallback(data);
//			console.log('ajax conflict stop process');
//			$('body, button, input[type="button"], input').css('cursor', 'default');
//			var ninja = this_form.is('.ninja-forms-form');
//			var cf7 = this_form.is('.wpcf7-form');
//			if (!ninja && !cf7){
//				release_form_sub( this_form , element_type );
//			}
//			return false;
//		}
//
//		var inbound_debug = this_form.is('.inbound-debug');
//		if (inbound_debug) {
//			//console.log("Inbound Form Data:"); console.log(post_form_data);
//			//console.log("Raw Field Data:"); console.log(all_form_fields);
//			console.log("Ajax Data:"); console.log(data);
//			return false;
//		}
//
//		jQuery.ajax({
//			type: 'POST',
//			url: inbound_ajax.admin_url,
//			timeout: 10000,
//			data: data,
//			success: function(user_id){
//					jQuery(this_form).trigger("inbound_form_complete"); // Trigger custom hook
//					createCookie("wp_lead_id", user_id, 365);
//					jQuery.totalStorage('wp_lead_id', user_id);
//
					//inbound_ga_log_event('Inbound Form Conversions', 'Conversion', "Conversion on '"+ inbound_data['form_name'] + "' form on page '" + document.title + "' on url '" + window.location.href + "'"); // GA push
//					this_form.removeClass('wpl-track-me');
//					// Unbind form
//
//					release_form_sub(this_form, 'FORM', inbound_data['form_type']);
//
//					$('body, button, input[type="button"], input').css('cursor', 'default');
//
//					jQuery.totalStorage.deleteItem('page_views'); // remove pageviews
//					jQuery.totalStorage.deleteItem('tracking_events'); // remove events
//					//jQuery.totalStorage.deleteItem('cta_clicks'); // remove cta
//
//				   },
//			error: function(MLHttpRequest, textStatus, errorThrown){
//					jQuery(this_form).trigger("inbound_form_fail"); // trigger custom hook
//					set_lead_fallback(data); // Create fallback localstorage object
//					console.log('ajax fail'); console.log(MLHttpRequest+' '+errorThrown+' '+textStatus);
//					release_form_sub( this_form , element_type ); // release form
//
//				}
//		});
//
//	});
//	}
//
//	/* Core Inbound Link Tracking */
//	if ( jQuery('.wpl-track-me-link').length ) {
//
//	jQuery("body").on('click', '.wpl-track-me-link', function (e) {
//
//		this_link = jQuery(this);
//
//		var element_type='A';
//		var a_href = jQuery(this).attr("href");
//
//		// process form only once
//		processed = this_link.hasClass('lead_processed');
//		if (processed === true) {
//			return;
//		}
//
//		form_id = jQuery(this).attr('id');
//		form_class = jQuery(this).attr('class');
//
//		jQuery(this).css('cursor', 'wait');
//		jQuery('body').css('cursor', 'wait');
//
//
//		e.preventDefault(); // halt normal form
//
//		var pageviewObj = jQuery.totalStorage('page_views');
//		var page_view_count = countProperties(pageviewObj);
//		//console.log("view count" + page_view_count);
//
//		var wp_lead_uid = jQuery.cookie("wp_lead_uid");
//		var page_views = JSON.stringify(pageviewObj);
//
//		var page_id = inbound_ajax.post_id;
//		if (typeof (landing_path_info) != "undefined" && landing_path_info != null && landing_path_info != "") {
//			var lp_variation = landing_path_info.variation;
//		} else if (typeof (cta_path_info) != "undefined" && cta_path_info != null && cta_path_info != "") {
//			var lp_variation = cta_path_info.variation;
//		} else {
//			var lp_variation = null;
//		}
//
//		jQuery.ajax({
//			type: 'POST',
//			url: inbound_ajax.admin_url,
//			timeout: 10000,
//			data: {
//				action: 'inbound_store_lead',
//				wp_lead_uid: wp_lead_uid,
//				page_views: page_views,
//				post_type: inbound_ajax.post_type,
//				variation: lp_variation,
//				page_id: page_id
//				/* Replace with jquery hook
//					do_action('wpl-lead-collection-add-ajax-data');
//				*/
//			},
//			success: function(data){
//					// Unbind form
//					release_form_sub(this_link, 'A');
//					//this_link.click();
//					jQuery.totalStorage.deleteItem('page_views'); // remove pageviews
//					jQuery.totalStorage.deleteItem('tracking_events'); // remove events
//					//jQuery.totalStorage.deleteItem('cta_clicks'); // remove cta
					//
//					return true;
//				   },
//			error: function(MLHttpRequest, textStatus, errorThrown){
//					console.log(MLHttpRequest+' '+errorThrown+' '+textStatus); // debug
//
//					// Create fallback localstorage object
//					var conversionObj = new Array();
//					conversionObj.push({
//										action: 'inbound_store_lead',
//										emailTo: email,
//										first_name: firstname,
//										last_name: lastname,
//										wp_lead_uid: wp_lead_uid,
//
//										page_views: page_views,
//										post_type: inbound_ajax.post_type,
//										variation: lp_variation,
//										// type: 'form-completion',
//										form_input_values : all_form_fields,
//										page_id: page_id
//										});
//
//					jQuery.totalStorage('failed_conversion', conversionObj); // store failed data
//					jQuery.cookie("failed_conversion", true, { path: '/', expires: 365 });
//
//					// If fail, cookie form data and ajax submit on next page load
//					console.log('ajax fail');
//					release_form_sub( this_link , element_type );
//
//				}
//		});
//
//	});
//	}
//
//	// gform_confirmation_loaded
//	/*  Fallback for lead storage if ajax fails */
//	var failed_conversion = jQuery.cookie("failed_conversion");
//	var fallback_obj = jQuery.totalStorage('failed_conversion');
//
//	if (typeof (failed_conversion) != "undefined" && failed_conversion == 'true' ) {
//		if (typeof fallback_obj == 'object' && fallback_obj) {
//
//				jQuery.ajax({
//					type: 'POST',
//					url: inbound_ajax.admin_url,
//					data: fallback_obj,
//					success: function(user_id){
//						console.log('Fallback fired');
//						jQuery.totalStorage.deleteItem('page_views'); // remove pageviews
//						jQuery.totalStorage.deleteItem('tracking_events'); // remove events
//						jQuery.removeCookie("failed_conversion"); // remove failed cookie
//						jQuery.totalStorage.deleteItem('failed_conversion'); // remove failed data
//					},
//					error: function(MLHttpRequest, textStatus, errorThrown){
//							//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
//							//die();
//					}
//
//				});
//		}
//	}
//
//});