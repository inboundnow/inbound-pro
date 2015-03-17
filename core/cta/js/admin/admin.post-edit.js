InboundQuery(document).ready(function($) {
	
	/* Removes wp_content wysiwyg */
	InboundQuery('#postdivrich').hide();
	
	/* Removes Permalink edit option */
	InboundQuery('#edit-slug-box').hide();
	
	/* Removes handle from templates option box */
	InboundQuery('#postbox-container-2 .handlediv').hide();

	/* Check to See if cookies are on */
	var cookies = (typeof (InboundQuery.cookie) != "undefined" ? true : false); // Check for InboundQuery Cookie
	function cookie_notice() {
		alert('Oh no! InboundQuery Cookie not loaded. Your Server Might be Blocking this. Some functionality may be impaired');
	}

	InboundQuery("body").on('click', '#content-tmce, .wp-switch-editor.switch-tmce', function () {
		if(cookies) {
			InboundQuery.cookie("cta-edit-view-choice", "editor", { path: '/', expires: 7 });
		} else {
			cookie_notice();
		}
	});

	InboundQuery("body").on('click', '#content-html, .wp-switch-editor.switch-html', function () {
		if(cookies) {
		InboundQuery.cookie("cta-edit-view-choice", "html", { path: '/', expires: 7 });
		} else {
		cookie_notice();
		}
	});

	if(cookies) {
		var which_editor = InboundQuery.cookie("cta-edit-view-choice");
	} else {
		var which_editor = 'editor';
		cookie_notice();
	}

	if(which_editor === null){
		setTimeout(function() {
		//InboundQuery("#content-tmce").click();
		//InboundQuery(".wp-switch-editor.switch-tmce").click();
		}, 1000);

	}

	if(which_editor === 'editor'){
		setTimeout(function() {
		//InboundQuery("#content-tmce").click();
		//InboundQuery(".wp-switch-editor.switch-tmce").click();
		InboundQuery('.inbound-wysiwyg-option textarea').each(function(){
			var chtml= "#" + InboundQuery(this).attr('id') + '-html';
			var ctmce= "#" + InboundQuery(this).attr('id') + '-tmce';
			var html_box = InboundQuery(chtml);
			var tinymce_box = InboundQuery(ctmce);
			switchEditors.switchto(tinymce_box[0]); // switch to tinymce
		});
		}, 1000);
	}


	InboundQuery('#template-filter a').click(function(){
		var selector = InboundQuery(this).attr('data-filter');
		$(".template-item-boxes").fadeOut(500);
			setTimeout(function() {
			$(selector).fadeIn(500);
			}, 500);
		return false;
	});

	InboundQuery(".inbound-multi-select").select2({
		placeholder: "Select one or more calls to action to rotate through",
		allowClear: true,
	});


	var shortcode_copy = InboundQuery("#cta_shortcode_form");
	InboundQuery(".add-new-h2").after(shortcode_copy);
	shortcode_copy.show();

	var current_a_tab = InboundQuery("#tabs-0").hasClass('nav-tab-special-active');
	if (current_a_tab === true){
		var url_norm = InboundQuery("#view-post-btn a").attr('href');
		var new_url = url_norm + "?wp-cta-variation-id=0";
		InboundQuery("#view-post-btn a").attr('href', new_url);
	}

	// Fix inactivate theme display
	InboundQuery("#template-box a").live('click', function () {
		setTimeout(function() {
			InboundQuery('#TB_window iframe').contents().find("#customize-controls").hide();
			InboundQuery('#TB_window iframe').contents().find(".wp-full-overlay.expanded").css("margin-left", "0px");
		}, 600);
	});

	var calc = InboundQuery(".calc.button-secondary");

	InboundQuery("input.cta-width").parent().find(".wp_cta_tooltip").after(calc);
	InboundQuery(".calc.button-secondary").css('display', 'inline-block');

	// Fix Split testing iframe size
	InboundQuery("#wp-cta-metabox-splittesting a.thickbox, #leads-table-container-inside .column-details a").live('click', function () {
		InboundQuery('#TB_iframeContent, #TB_window').hide();
		setTimeout(function() {

		InboundQuery('#TB_iframeContent, #TB_window').width( 640 ).height( 800 ).css("margin-left", "0px").css("left", "35%");
		InboundQuery('#TB_iframeContent, #TB_window').show();
		}, 600);
	});

	// Load meta box in correct position on page load
	var current_template = InboundQuery("input#wp_cta_select_template ").val();
	var current_template_meta = "#wp_cta_" + current_template + "_custom_meta_box";
	InboundQuery(current_template_meta).removeClass("postbox").appendTo("#template-display-options").addClass("Old-Template");
	var current_template_h3 = "#wp_cta_" + current_template + "_custom_meta_box h3";

	InboundQuery(current_template_meta +' .handlediv').hide();
	InboundQuery(current_template_meta +' .hndle').css('cursor','default');


	// filter Styling
	InboundQuery('#template-filter a').first().addClass('button-primary');
	InboundQuery('#template-filter a').click(function(){
		InboundQuery("#template-filter a.button-primary").removeClass("button-primary");
		InboundQuery(this).addClass('button-primary');
	});

	InboundQuery('.wp_cta_select_template').click(function(){

		var template = InboundQuery(this).attr('id');
		var label = InboundQuery(this).attr('label');
		var selected_template_id = "#" + template;
		var currentlabel = InboundQuery(".currently_selected").show();
		var current_template = InboundQuery("input#wp_cta_select_template ").val();
		var current_template_meta = "#wp_cta_" + current_template + "_custom_meta_box";
		var current_template_h3 = "#wp_cta_" + current_template + "_custom_meta_box h3";
		var current_template_div = "#wp_cta_" + current_template + "_custom_meta_box .handlediv";
		var open_variation = InboundQuery("#open_variation").val();

		if (open_variation>0) {
			var variation_tag = "-"+open_variation;
		} else {
			var variation_tag = "";
		}
		InboundQuery("#template-box.default_template_highlight").removeClass("default_template_highlight");

		InboundQuery(selected_template_id).parent().addClass("default_template_highlight").prepend(currentlabel);
		InboundQuery(".wp-cta-template-selector-container").fadeOut(500,function(){

			InboundQuery('#template-display-options').fadeOut(500, function(){
			});

			var ajax_data = {
				action: 'wp_cta_get_template_meta',
				selected_template: template,
				post_id: wp_cta_post_edit_ui.post_id,
				post: wp_cta_post_edit_ui.post_id,
			};

			InboundQuery.ajax({
					type: "POST",
					url: wp_cta_post_edit_ui.ajaxurl,
					data: ajax_data,
					dataType: 'html',
					timeout: 7000,
					success: function (response) {

						InboundQuery('#wp_cta_template_select_meta_box .input').remove();
						InboundQuery('#wp_cta_template_select_meta_box .inside').remove();

						InboundQuery('#wp_cta_template_select_meta_box h3').remove();

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

						InboundQuery('#wp_cta_template_select_meta_box').append(html);

					},
					error: function(request, status, err) {
						alert(status);
					}
				});
				InboundQuery(".wrap").fadeIn(500, function(){
			});
		});

		InboundQuery(current_template_meta).appendTo("#template-display-options");
		InboundQuery('#wp_cta_metabox_select_template h3').first().html('Current Active Template: '+label);
		InboundQuery('#wp_cta_select_template').val(template);
		InboundQuery(".Old-Template").hide();

		InboundQuery(current_template_div).css("display","none");
		InboundQuery(current_template_h3).css("background","#f8f8f8");
		InboundQuery(current_template_meta).show().appendTo("#template-display-options").removeClass("postbox").addClass("Old-Template");
		//alert(template);
		//alert(label);
	});


	InboundQuery('#wp-cta-cancel-selection').click(function(){
		InboundQuery(".wp-cta-template-selector-container").fadeOut(500,function(){
			InboundQuery(".wrap").fadeIn(500, function(){
			});
		});

	});

	$("#blag").select2();

	InboundQuery("body").on('click', '.calc', function () {
		image_exists = InboundQuery("#content_ifr").contents().find('img').length;
		if (image_exists > 0){
		width = InboundQuery("#content_ifr").contents().find('img').width() + 15;
		height = InboundQuery("#content_ifr").contents().find('img').height() + 15;
		round_height = Math.ceil(height);
		round_width = Math.ceil(width);
		InboundQuery(".cta-width").val(round_width);
		InboundQuery(".cta-height").val(round_height);
		} else {
			alert('No image found. For more complex templates you need to enter height and width manually. You can use free browser plugins like "measureit" to measure screen pixels');
		}
	});

	// the_content default overwrite
	InboundQuery('#overwrite-content').click(function(){
		if (confirm('Are you sure you want to overwrite what is currently in the main edit box above?')) {
			var default_content = InboundQuery(".default-content").text();
			InboundQuery("#content_ifr").contents().find("body").html(default_content);
		} else {
	// Do nothing!
	}
	});

	// Colorpicker fix
	InboundQuery('.jpicker').one('mouseenter', function () {
		InboundQuery(this).jPicker({
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
			//InboundQuery(this).attr('rel', all.hex);
			InboundQuery(this).parent().find(".wp-cta-success-message").remove();
			InboundQuery(this).parent().find(".new-save-wp-cta").show();
			InboundQuery(this).parent().find(".new-save-wp-cta-frontend").show();

			//InboundQuery(this).attr('value', all.hex);
		});
	});

	if (InboundQuery(".wp-cta-template-selector-container").css("display") == "none"){
		InboundQuery(".currently_selected").hide(); }
	else {
		InboundQuery(".currently_selected").show();
	}

	// Add current title of template to selector
	var selected_template = InboundQuery('#wp_cta_select_template').val();
	//alert(selected_template);
	var selected_template_id = "#" + selected_template;
	var currentlabel = InboundQuery(".currently_selected");



	InboundQuery('#wp-cta-change-template-button').live('click', function () {
		InboundQuery(".wrap").fadeOut(500,function(){

			InboundQuery(".wp-cta-template-selector-container").fadeIn(500, function(){
				InboundQuery(".currently_selected").show();
				InboundQuery('#wp-cta-cancel-selection').show();
			});
			InboundQuery("#template-filter li a").first().click();
		});
	});

	/* Move Slug Box
	var slugs = InboundQuery("#edit-slug-box");
	InboundQuery('#main-title-area').after(slugs.show());
	*/
	// Background Options
	InboundQuery('.current_lander .background-style').live('change', function () {
		var input = InboundQuery(".current_lander .background-style option:selected").val();
		if (input == 'color') {
			InboundQuery('.current_lander tr.background-color').show();
			InboundQuery('.current_lander tr.background-image').hide();
			InboundQuery('.background_tip').hide();
		}
		else if (input == 'default') {
			InboundQuery('.current_lander tr.background-color').hide();
			InboundQuery('.current_lander tr.background-image').hide();
			InboundQuery('.background_tip').hide();
		}
		else if (input == 'custom') {
			var obj = InboundQuery(".current_lander tr.background-style td .wp_cta_tooltip");
			obj.removeClass("wp_cta_tooltip").addClass("background_tip").html("Use the custom css block at the bottom of this page to set up custom CSS rules");
			InboundQuery('.background_tip').show();
		}
		else {
			InboundQuery('.current_lander tr.background-color').hide();
			InboundQuery('.current_lander tr.background-image').show();
			InboundQuery('.background_tip').hide();
		}

	});

	// Check BG options on page load
	InboundQuery(document).ready(function () {
		var input2 = InboundQuery(".current_lander .background-style option:selected").val();
		if (input2 == 'color') {
			InboundQuery('.current_lander tr.background-color').show();
			InboundQuery('.current_lander tr.background-image').hide();
		} else if (input2 == 'custom') {
			var obj = InboundQuery(".current_lander tr.background-style td .wp_cta_tooltip");
			obj.removeClass("wp_cta_tooltip").addClass("background_tip").html("Use the custom css block at the bottom of this page to set up custom CSS rules");
			InboundQuery('.background_tip').show();
		} else if (input2 == 'default') {
			InboundQuery('.current_lander tr.background-color').hide();
			InboundQuery('.current_lander tr.background-image').hide();
		} else {
			InboundQuery('.current_lander tr.background-color').hide();
			InboundQuery('.current_lander tr.background-image').show();
		}
	});

	//Stylize lead's wp-list-table
	var cnt = $("#leads-table-container").contents();

	//remove inputs from wp-list-table
	InboundQuery('#leads-table-container-inside input').each(function(){
		InboundQuery(this).remove();
	});

	var post_status = InboundQuery("#original_post_status").val();

	if (post_status === "draft") {
		// InboundQuery( ".nav-tab-wrapper.a_b_tabs .wp-cta-ab-tab, #tabs-add-variation").hide();
		InboundQuery(".new-save-wp-cta-frontend").on("click", function(event) {
			event.preventDefault();
			alert("Must publish this page before you can use the visual editor!");
		});
		var subbox = InboundQuery("#submitdiv");
		InboundQuery("#wp_cta_ab_display_stats_metabox").before(subbox);
		InboundQuery("body").on('click', '#content-html', function () {
			// alert('Ut oh! Hit publish to use text editor OR refresh the page.');
		});
	} else {
		InboundQuery("#publish").val("Update All");

	}

function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
}




	InboundQuery('#main-title-area input').on("change keyup", function (e) {
		// iframe content change needs its own change function $("#iFrame").contents().find("#someDiv")
		// media uploader needs its own change function
		var this_id = InboundQuery(this).attr("id");
		var current_view = InboundQuery("#wp-cta-current-view").text();
		if (current_view !== "0") {
			this_id = this_id + '-' + current_view;
		}
		var parent_el = InboundQuery(this).parent();
		InboundQuery(parent_el).find(".wp-cta-success-message").remove();
		InboundQuery(parent_el).find(".new-save-wp-cta").remove();
		var ajax_save_button = InboundQuery('<span class="button-primary new-save-wp-cta" id="' + this_id + '" style="margin-left:10px">Update</span>');
		//console.log(parent_el);
		InboundQuery(ajax_save_button).appendTo(parent_el);
	});




	var nonce_val = wp_cta_post_edit_ui.wp_call_to_action_meta_nonce; // NEED CORRECT NONCE
	InboundQuery("body").on('click', '.new-save-wp-cta', function () {

		InboundQuery('body').css('cursor', 'wait');
		InboundQuery(this).parent().find(".wp-cta-success-message").hide();
		var input_type = InboundQuery(this).attr('data-field-type');

		console.log(input_type);
		var this_meta_id = InboundQuery(this).attr("id");
		console.log(this_meta_id);
		// prep data
		if (input_type == "text" || input_type == "number" ||	input_type == "colorpicker") {
			var meta_to_save = InboundQuery(this).parent().find("input").val();
		} else if (input_type == "textarea") {
			var meta_to_save = InboundQuery(this).parent().find("textarea").val();
		} else if (input_type == "select") {
			var meta_to_save = InboundQuery(this).parent().find("select").val();
		} else if (input_type == "dropdown") {
			var meta_to_save = InboundQuery(this).parent().find("select").val();
		} else if (input_type == "radio") {
			var meta_to_save = InboundQuery(this).parent().find("input:checked").val();
		} else if (input_type == "checkbox") {
			var meta_to_save = InboundQuery(this).parent().find('input[type="checkbox"]:checked').val();
		} else if (input_type == "editor") {
			var meta_to_save = InboundQuery(this).parent().find('textarea').val();
		} else if (input_type == "iframe") {
			var meta_to_save = InboundQuery(".iframe-options-"+this_meta_id+" iframe").contents().find('body').html();
		} else {
			var meta_to_save = "";
		}
		console.log(meta_to_save);
		// if data exists save it

		var post_id = InboundQuery("#post_ID").val();

		function do_reload_preview() {
		var cache_bust =	generate_random_cache_bust(35);
		var reload_url = parent.window.location.href;
		reload_url = reload_url.replace('cta-template-customize=on','');
		//alert(reload_url);
		var current_variation_id = InboundQuery("#wp-cta-current-view").text();

		// var reload = InboundQuery(parent.document).find("#lp-live-preview").attr("src");
		var new_reload = reload_url + "&live-preview-area=" + cache_bust + "&wp-cta-variation-id=" + current_variation_id;
		//alert(new_reload);
		InboundQuery(parent.document).find("#wp-cta-live-preview").attr("src", new_reload);

		var iframe_w = InboundQuery('.cta-width').val();
		var iframe_h = InboundQuery('.cta-height').val();
		if (typeof (iframe_w) != "undefined" && iframe_w != null && iframe_w != "") {
		var iframe_h = InboundQuery('.cta-height').val() || "100%";
		InboundQuery(parent.document).find("#wp-cta-live-preview").css('width', iframe_w).css('height', iframe_h);
		}


		// console.log(new_reload);
	}
		var frontend_status = InboundQuery("#frontend-on").val();
		setTimeout(function() {
		do_reload_preview();
				}, 1000);

		InboundQuery.ajax({
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
				InboundQuery('body').css('cursor', 'default');
				//alert(data);
				// InboundQuery('.wp-cta-form').unbind('submit').submit();
				//var worked = '<span class="success-message-map">Success! ' + this_meta_id + ' set to ' + meta_to_save + '</span>';
				var worked = '<span class="wp-cta-success-message">Updated!</span>';
				var s_message = InboundQuery(self).parent();
				InboundQuery(worked).appendTo(s_message);
				InboundQuery(self).hide();
				InboundQuery("#switch-wp-cta").text("0");
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
