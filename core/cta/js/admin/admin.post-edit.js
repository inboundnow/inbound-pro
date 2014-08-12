jQuery(document).ready(function($) {

	
	/* Check to See if cookies are on */
	var cookies = (typeof (jQuery.cookie) != "undefined" ? true : false); // Check for JQuery Cookie
	function cookie_notice() {
		alert('Oh no! jQuery Cookie not loaded. Your Server Might be Blocking this. Some functionality may be impaired');
	}

	jQuery("body").on('click', '#content-tmce, .wp-switch-editor.switch-tmce', function () {
		if(cookies) {
			$.cookie("cta-edit-view-choice", "editor", { path: '/', expires: 7 });
		} else {
			cookie_notice();
		}
	});

	jQuery("body").on('click', '#content-html, .wp-switch-editor.switch-html', function () {
		if(cookies) {
		$.cookie("cta-edit-view-choice", "html", { path: '/', expires: 7 });
		} else {
		cookie_notice();
		}
	});

	if(cookies) {
		var which_editor = $.cookie("cta-edit-view-choice");
	} else {
		var which_editor = 'editor';
		cookie_notice();
	}
	
	if(which_editor === null){
		setTimeout(function() {
		//jQuery("#content-tmce").click();
		//jQuery(".wp-switch-editor.switch-tmce").click();
		}, 1000);

	}
		
	if(which_editor === 'editor'){
		setTimeout(function() {
		//jQuery("#content-tmce").click();
		//jQuery(".wp-switch-editor.switch-tmce").click();
		jQuery('.inbound-wysiwyg-option textarea').each(function(){
			var chtml= "#" + jQuery(this).attr('id') + '-html';
			var ctmce= "#" + jQuery(this).attr('id') + '-tmce';
			var html_box = jQuery(chtml);
			var tinymce_box = jQuery(ctmce);
			switchEditors.switchto(tinymce_box[0]); // switch to tinymce
		});
		}, 1000);
	}


	jQuery('#template-filter a').click(function(){
		var selector = jQuery(this).attr('data-filter');
		$(".template-item-boxes").fadeOut(500);
			setTimeout(function() {
			$(selector).fadeIn(500);
			}, 500);
		return false;
	});

	jQuery(".inbound-multi-select").select2({
		placeholder: "Select one or more calls to action to rotate through",
		allowClear: true,
	});


	var shortcode_copy = jQuery("#cta_shortcode_form");
	jQuery(".add-new-h2").after(shortcode_copy);
	shortcode_copy.show();

	var current_a_tab = jQuery("#tabs-0").hasClass('nav-tab-special-active');
	if (current_a_tab === true){
		var url_norm = jQuery("#view-post-btn a").attr('href');
		var new_url = url_norm + "?wp-cta-variation-id=0";
		jQuery("#view-post-btn a").attr('href', new_url);
	}

	// Fix inactivate theme display
	jQuery("#template-box a").live('click', function () {
		setTimeout(function() {
			jQuery('#TB_window iframe').contents().find("#customize-controls").hide();
			jQuery('#TB_window iframe').contents().find(".wp-full-overlay.expanded").css("margin-left", "0px");
		}, 600);
	});

	var calc = jQuery(".calc.button-secondary");

	jQuery("input.cta-width").parent().find(".wp_cta_tooltip").after(calc);
	jQuery(".calc.button-secondary").css('display', 'inline-block');

	// Fix Split testing iframe size
	jQuery("#wp-cta-metabox-splittesting a.thickbox, #leads-table-container-inside .column-details a").live('click', function () {
		jQuery('#TB_iframeContent, #TB_window').hide();
		setTimeout(function() {

		jQuery('#TB_iframeContent, #TB_window').width( 640 ).height( 800 ).css("margin-left", "0px").css("left", "35%");
		jQuery('#TB_iframeContent, #TB_window').show();
		}, 600);
	});

	// Load meta box in correct position on page load
	var current_template = jQuery("input#wp_cta_select_template ").val();
	var current_template_meta = "#wp_cta_" + current_template + "_custom_meta_box";
	jQuery(current_template_meta).removeClass("postbox").appendTo("#template-display-options").addClass("Old-Template");
	var current_template_h3 = "#wp_cta_" + current_template + "_custom_meta_box h3";

	jQuery(current_template_meta +' .handlediv').hide();
	jQuery(current_template_meta +' .hndle').css('cursor','default');


	// filter Styling
	jQuery('#template-filter a').first().addClass('button-primary');
	jQuery('#template-filter a').click(function(){
		jQuery("#template-filter a.button-primary").removeClass("button-primary");
		jQuery(this).addClass('button-primary');
	});

	jQuery('.wp_cta_select_template').click(function(){

		var template = jQuery(this).attr('id');
		var label = jQuery(this).attr('label');
		var selected_template_id = "#" + template;
		var currentlabel = jQuery(".currently_selected").show();
		var current_template = jQuery("input#wp_cta_select_template ").val();
		var current_template_meta = "#wp_cta_" + current_template + "_custom_meta_box";
		var current_template_h3 = "#wp_cta_" + current_template + "_custom_meta_box h3";
		var current_template_div = "#wp_cta_" + current_template + "_custom_meta_box .handlediv";
		var open_variation = jQuery("#open_variation").val();

		if (open_variation>0) {
			var variation_tag = "-"+open_variation;
		} else {
			var variation_tag = "";
		}
		jQuery("#template-box.default_template_highlight").removeClass("default_template_highlight");

		jQuery(selected_template_id).parent().addClass("default_template_highlight").prepend(currentlabel);
		jQuery(".wp-cta-template-selector-container").fadeOut(500,function(){

			jQuery('#template-display-options').fadeOut(500, function(){
			});

			var ajax_data = {
				action: 'wp_cta_get_template_meta',
				selected_template: template,
				post_id: wp_cta_post_edit_ui.post_id,
			};

			jQuery.ajax({
					type: "POST",
					url: wp_cta_post_edit_ui.ajaxurl,
					data: ajax_data,
					dataType: 'html',
					timeout: 7000,
					success: function (response) {

						jQuery('#wp_cta_template_select_meta_box .input').remove();
						jQuery('#wp_cta_template_select_meta_box .inside').remove();
						
						jQuery('#wp_cta_template_select_meta_box h3').remove();
						
						var html = '<div class="inside">'
								+ '<input id="wp_cta_select_template" type="hidden" value="'+template+'" name="wp-cta-selected-template'+variation_tag+'">'
								+ '<input type="hidden" value="'+wp_cta_post_edit_ui.wp_call_to_action_template_nonce+'" name="wp_cta_wp-cta_custom_fields_nonce">'
								+ '<h3 class="hndle" style="cursor: default;">'
								+ '<span>'
								+ '<small>'+ template +' Options:</small>'
								+	'</span>'
								+	'</h3>'
								
								+ response
								+ '</div>';
								
						jQuery('#wp_cta_template_select_meta_box').append(html);

					},
					error: function(request, status, err) {
						alert(status);
					}
				});
				jQuery(".wrap").fadeIn(500, function(){
			});
		});

		jQuery(current_template_meta).appendTo("#template-display-options");
		jQuery('#wp_cta_metabox_select_template h3').first().html('Current Active Template: '+label);
		jQuery('#wp_cta_select_template').val(template);
		jQuery(".Old-Template").hide();

		jQuery(current_template_div).css("display","none");
		jQuery(current_template_h3).css("background","#f8f8f8");
		jQuery(current_template_meta).show().appendTo("#template-display-options").removeClass("postbox").addClass("Old-Template");
		//alert(template);
		//alert(label);
	});


	jQuery('#wp-cta-cancel-selection').click(function(){
		jQuery(".wp-cta-template-selector-container").fadeOut(500,function(){
			jQuery(".wrap").fadeIn(500, function(){
			});
		});

	});

	$("#blag").select2();

	jQuery("body").on('click', '.calc', function () {
		image_exists = jQuery("#content_ifr").contents().find('img').length;
		if (image_exists > 0){
		width = jQuery("#content_ifr").contents().find('img').width() + 15;
		height = jQuery("#content_ifr").contents().find('img').height() + 15;
		round_height = Math.ceil(height);
		round_width = Math.ceil(width);
		jQuery(".cta-width").val(round_width);
		jQuery(".cta-height").val(round_height);
		} else {
			alert('No image found. For more complex templates you need to enter height and width manually. You can use free browser plugins like "measureit" to measure screen pixels');
		}
	});

	// the_content default overwrite
	jQuery('#overwrite-content').click(function(){
		if (confirm('Are you sure you want to overwrite what is currently in the main edit box above?')) {
			var default_content = jQuery(".default-content").text();
			jQuery("#content_ifr").contents().find("body").html(default_content);
		} else {
	// Do nothing!
	}
	});

	// Colorpicker fix
	jQuery('.jpicker').one('mouseenter', function () {
		jQuery(this).jPicker({
			window: // used to define the position of the popup window only useful in binded mode
			{
				title: null, // any title for the jPicker window itself - displays "Drag Markers To Pick A Color" if left null
				position: {
					x: 'screenCenter', // acceptable values "left", "center", "right", "screenCenter", or relative px value
					y: 'center', // acceptable values "top", "bottom", "center", or relative px value
				},
				expandable: false, // default to large static picker - set to true to make an expandable picker (small icon with popup) - set
				// automatically when binded to input element
				liveUpdate: true, // set false if you want the user to click "OK" before the binded input box updates values (always "true"
				// for expandable picker)
				alphaSupport: false, // set to true to enable alpha picking
				alphaPrecision: 0, // set decimal precision for alpha percentage display - hex codes do not map directly to percentage
				// integers - range 0-2
				updateInputColor: true // set to false to prevent binded input colors from changing
			}
		},
		function(color, context)
		{
			var all = color.val('all');
		// alert('Color chosen - hex: ' + (all && '#' + all.hex || 'none') + ' - alpha: ' + (all && all.a + '%' || 'none'));
			//jQuery(this).attr('rel', all.hex);
			jQuery(this).parent().find(".wp-cta-success-message").remove();
			jQuery(this).parent().find(".new-save-wp-cta").show();
			jQuery(this).parent().find(".new-save-wp-cta-frontend").show();

			//jQuery(this).attr('value', all.hex);
		});
	});

	if (jQuery(".wp-cta-template-selector-container").css("display") == "none"){
		jQuery(".currently_selected").hide(); }
	else {
		jQuery(".currently_selected").show();
	}

	// Add current title of template to selector
	var selected_template = jQuery('#wp_cta_select_template').val();
	//alert(selected_template);
	var selected_template_id = "#" + selected_template;
	var currentlabel = jQuery(".currently_selected");



	jQuery('#wp-cta-change-template-button').live('click', function () {
		jQuery(".wrap").fadeOut(500,function(){

			jQuery(".wp-cta-template-selector-container").fadeIn(500, function(){
				jQuery(".currently_selected").show();
				jQuery('#wp-cta-cancel-selection').show();
			});
			jQuery("#template-filter li a").first().click();
		});
	});

	/* Move Slug Box
	var slugs = jQuery("#edit-slug-box");
	jQuery('#main-title-area').after(slugs.show());
	*/
	// Background Options
	jQuery('.current_lander .background-style').live('change', function () {
		var input = jQuery(".current_lander .background-style option:selected").val();
		if (input == 'color') {
			jQuery('.current_lander tr.background-color').show();
			jQuery('.current_lander tr.background-image').hide();
			jQuery('.background_tip').hide();
		}
		else if (input == 'default') {
			jQuery('.current_lander tr.background-color').hide();
			jQuery('.current_lander tr.background-image').hide();
			jQuery('.background_tip').hide();
		}
		else if (input == 'custom') {
			var obj = jQuery(".current_lander tr.background-style td .wp_cta_tooltip");
			obj.removeClass("wp_cta_tooltip").addClass("background_tip").html("Use the custom css block at the bottom of this page to set up custom CSS rules");
			jQuery('.background_tip').show();
		}
		else {
			jQuery('.current_lander tr.background-color').hide();
			jQuery('.current_lander tr.background-image').show();
			jQuery('.background_tip').hide();
		}

	});

	// Check BG options on page load
	jQuery(document).ready(function () {
		var input2 = jQuery(".current_lander .background-style option:selected").val();
		if (input2 == 'color') {
			jQuery('.current_lander tr.background-color').show();
			jQuery('.current_lander tr.background-image').hide();
		} else if (input2 == 'custom') {
			var obj = jQuery(".current_lander tr.background-style td .wp_cta_tooltip");
			obj.removeClass("wp_cta_tooltip").addClass("background_tip").html("Use the custom css block at the bottom of this page to set up custom CSS rules");
			jQuery('.background_tip').show();
		} else if (input2 == 'default') {
			jQuery('.current_lander tr.background-color').hide();
			jQuery('.current_lander tr.background-image').hide();
		} else {
			jQuery('.current_lander tr.background-color').hide();
			jQuery('.current_lander tr.background-image').show();
		}
	});

	//Stylize lead's wp-list-table
	var cnt = $("#leads-table-container").contents();

	//remove inputs from wp-list-table
	jQuery('#leads-table-container-inside input').each(function(){
		jQuery(this).remove();
	});

	var post_status = jQuery("#original_post_status").val();

	if (post_status === "draft") {
		// jQuery( ".nav-tab-wrapper.a_b_tabs .wp-cta-ab-tab, #tabs-add-variation").hide();
		jQuery(".new-save-wp-cta-frontend").on("click", function(event) {
			event.preventDefault();
			alert("Must publish this page before you can use the visual editor!");
		});
		var subbox = jQuery("#submitdiv");
		jQuery("#wp_cta_ab_display_stats_metabox").before(subbox);
		jQuery("body").on('click', '#content-html', function () {
			// alert('Ut oh! Hit publish to use text editor OR refresh the page.');
		});
	} else {
		jQuery("#publish").val("Update All");

	}

function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
}




	jQuery('#main-title-area input').on("change keyup", function (e) {
		// iframe content change needs its own change function $("#iFrame").contents().find("#someDiv")
		// media uploader needs its own change function
		var this_id = jQuery(this).attr("id");
		var current_view = jQuery("#wp-cta-current-view").text();
		if (current_view !== "0") {
			this_id = this_id + '-' + current_view;
		}
		var parent_el = jQuery(this).parent();
		jQuery(parent_el).find(".wp-cta-success-message").remove();
		jQuery(parent_el).find(".new-save-wp-cta").remove();
		var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta" id="' + this_id + '" style="margin-left:10px">Update</span>');
		//console.log(parent_el);
		jQuery(ajax_save_button).appendTo(parent_el);
	});




	var nonce_val = wp_cta_post_edit_ui.wp_call_to_action_meta_nonce; // NEED CORRECT NONCE
	jQuery("body").on('click', '.new-save-wp-cta', function () {

		jQuery('body').css('cursor', 'wait');
		jQuery(this).parent().find(".wp-cta-success-message").hide();
		var input_type = jQuery(this).attr('data-field-type');

		console.log(input_type);
		var this_meta_id = jQuery(this).attr("id");
		console.log(this_meta_id);
		// prep data
		if (input_type == "text" || input_type == "number" ||	input_type == "colorpicker") {
			var meta_to_save = jQuery(this).parent().find("input").val();
		} else if (input_type == "textarea") {
			var meta_to_save = jQuery(this).parent().find("textarea").val();
		} else if (input_type == "select") {
			var meta_to_save = jQuery(this).parent().find("select").val();
		} else if (input_type == "dropdown") {
			var meta_to_save = jQuery(this).parent().find("select").val();
		} else if (input_type == "radio") {
			var meta_to_save = jQuery(this).parent().find("input:checked").val();
		} else if (input_type == "checkbox") {
			var meta_to_save = jQuery(this).parent().find('input[type="checkbox"]:checked').val();
		} else if (input_type == "editor") {
			var meta_to_save = jQuery(this).parent().find('textarea').val();
		} else if (input_type == "iframe") {
			var meta_to_save = jQuery(".iframe-options-"+this_meta_id+" iframe").contents().find('body').html();
		} else {
			var meta_to_save = "";
		}
		console.log(meta_to_save);
		// if data exists save it

		var post_id = jQuery("#post_ID").val();

		function do_reload_preview() {
		var cache_bust =	generate_random_cache_bust(35);
		var reload_url = parent.window.location.href;
		reload_url = reload_url.replace('cta-template-customize=on','');
		//alert(reload_url);
		var current_variation_id = jQuery("#wp-cta-current-view").text();

		// var reload = jQuery(parent.document).find("#lp-live-preview").attr("src");
		var new_reload = reload_url + "&live-preview-area=" + cache_bust + "&wp-cta-variation-id=" + current_variation_id;
		//alert(new_reload);
		jQuery(parent.document).find("#wp-cta-live-preview").attr("src", new_reload);

		var iframe_w = jQuery('.cta-width').val();
		var iframe_h = jQuery('.cta-height').val();
		if (typeof (iframe_w) != "undefined" && iframe_w != null && iframe_w != "") {
		var iframe_h = jQuery('.cta-height').val() || "100%";
		jQuery(parent.document).find("#wp-cta-live-preview").css('width', iframe_w).css('height', iframe_h);
		}


		// console.log(new_reload);
	}
		var frontend_status = jQuery("#frontend-on").val();
		setTimeout(function() {
		do_reload_preview();
				}, 1000);

		jQuery.ajax({
			type: 'POST',
			url: wp_cta_post_edit_ui.ajaxurl,
			context: this,
			data: {
				action: 'wp_wp_call_to_action_meta_save',
				meta_id: this_meta_id,
				new_meta_val: meta_to_save,
				page_id: post_id,
				nonce: nonce_val
			},

			success: function (data) {
				var self = this;
				jQuery('body').css('cursor', 'default');
				//alert(data);
				// jQuery('.wp-cta-form').unbind('submit').submit();
				//var worked = '<span class="success-message-map">Success! ' + this_meta_id + ' set to ' + meta_to_save + '</span>';
				var worked = '<span class="wp-cta-success-message">Updated!</span>';
				var s_message = jQuery(self).parent();
				jQuery(worked).appendTo(s_message);
				jQuery(self).hide();
				jQuery("#switch-wp-cta").text("0");
				// RUN RELOAD
				if (typeof (frontend_status) != "undefined" && frontend_status !== null) {
				console.log('reload frame');
				do_reload_preview();
				} else {
				console.log('No reload frame');
				}
				//alert("Changes Saved!");
			},

			error: function (MLHttpRequest, textStatus, errorThrown) {
				alert("Ajax not enabled");
			}
		});

		return false;

	});
});
