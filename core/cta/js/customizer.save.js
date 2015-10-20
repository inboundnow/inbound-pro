jQuery(document).ready(function($) {

	var window_width = jQuery(window).width();
	var parent_window = parent.document.width;
	iframe_size = parent_window * 0.334;
	new_size = parent_window * 0.70;
	new_new_size = parent_window - new_size;
	editor_size = iframe_size * 0.85 + "px";
	// Resize Functions
	$("#wp_content_resize, #wp-cta-conversion-area_ifr").height(210);
	   setTimeout(function() {
	jQuery("#wp-cta-conversion-area_ifr, #wp-cta-conversion-area_tbl, #wp_content_ifr, .mceLayout, .mceIframeContainer iframe").height(150);
	jQuery(".wp-editor-container table").css("max-width", editor_size);
		jQuery('iframe').contents().find("body").each(function(){
			jQuery(this).css("max-width", editor_size);
		});
	}, 1000);

	//jQuery("body").width(iframe_size);
	//jQuery("#wpcontent").width(iframe_size);

	// On keystroke have save button show
	jQuery('#wp-cta-frontend-options-container input, #wp-cta-frontend-options-container textarea').on("keyup", function (e) {
		var this_id = jQuery(this).attr("id");
		var parent_el = jQuery(this).parent();
		jQuery(parent_el).find(".wp-cta-success-message").remove();
		jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
		var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="' + this_id + '" style="margin-left:10px">Update</span>');
		//console.log(parent_el);
		jQuery(ajax_save_button).appendTo(parent_el);
	});

	// On change have save button show
	jQuery('#wp-cta-frontend-options-container input, #wp-cta-frontend-options-container select, #wp-cta-frontend-options-container textarea').on("change", function (e) {
		var this_id = jQuery(this).attr("id");
		var parent_el = jQuery(this).parent();
		jQuery(parent_el).find(".wp-cta-success-message").remove();
		jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
		var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="' + this_id + '" style="margin-left:10px">Update</span>');
		//console.log(parent_el);
		jQuery(ajax_save_button).appendTo(parent_el);
	});

	// wysiwyg on keyup save action
	setTimeout(function() {
	jQuery('.wp-call-to-action-option-row iframe').contents().find('body').on("keyup", function (e) {
		var thisclass = jQuery(this).attr("class");
		var this_class_dirty = thisclass.replace("mceContentBody ", "");
		var this_class_cleaner = this_class_dirty.replace("wp-editor", "");
		var clean_1 = this_class_cleaner.replace("post-type-wp-call-to-action", "");
		var clean_2 = clean_1.replace(/[.\s]+$/g, ""); // remove trailing whitespace
		var clean_spaces = clean_2.replace(/\s{2,}/g, ' '); // remove more than one space
		var this_id =  clean_spaces.replace(/[.\s]+$/g, ""); // remove trailing whitespace
		console.log(this_id);
		var parent_el = jQuery( "." + this_id + " .wp-call-to-action-table-headerz");
		jQuery(parent_el).find(".wp-cta-success-message").remove();
		jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
		var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="' + this_id + '" style="margin-left:10px;">Update</span>');
		//console.log(parent_el);
		jQuery(ajax_save_button).appendTo(parent_el);
	});
	}, 4000);

	// Prep Data and Save
	var nonce_val = wp_cta_post_edit_ui.wp_call_to_action_meta_nonce; // NEED CORRECT NONCE
	jQuery(document).on('mousedown', '.new-save-wp-cta-frontend', function () {
		//alert('here');
		var type_input = jQuery(this).parent().find("input").attr("type");
		var type_select = jQuery(this).parent().find("select");
		// var the_conversion_area_editor = jQuery(this).parent().parent().find('#wp-cta-conversion-area_ifr').length;
		jQuery(this).parent().find(".wp-cta-success-message").hide();
	   // var the_content_editor = jQuery(this).parent().parent().find('#wp_content_ifr').length;
		var type_wysiwyg = jQuery(this).parent().parent().find('iframe').length;

		var type_textarea = jQuery(this).parent().find("textarea");
		if (typeof (type_input) != "undefined" && type_input !== null) {
			var type_of_field = type_input;
		} else if (typeof (type_wysiwyg) != "undefined" && type_wysiwyg !== null && type_wysiwyg === 1) {
			var type_of_field = 'wysiwyg';
		} else if (typeof (type_textarea) != "undefined" && type_textarea !== null) {
			var type_of_field = 'textarea';
		} else {
			(typeof (type_select) != "undefined" && type_select)
			var type_of_field = 'select';
		}
		// console.log(type_of_field); // type of input
		var new_value_meta_input = jQuery(this).parent().find("input").val();
		//console.log(new_value_meta_input);
		var new_value_meta_select = jQuery(this).parent().find("select").val();
		var new_value_meta_textarea = jQuery(this).parent().find("textarea").val();
	   // console.log(new_value_meta_select);
		var new_value_meta_radio = jQuery(this).parent().find("input:checked").val();
		var new_value_meta_checkbox = jQuery(this).parent().find('input[type="checkbox"]:checked').val();
		var new_wysiwyg_meta = jQuery(this).parent().parent().find("iframe").contents().find("body").html();
		// prep data
		if (typeof (new_value_meta_input) != "undefined" && new_value_meta_input !== null && type_of_field == "text") {
			var meta_to_save = new_value_meta_input;
		} else if (typeof (new_value_meta_textarea) != "undefined" && new_value_meta_textarea !== null && type_of_field == "textarea") {
			var meta_to_save = new_value_meta_textarea;
		} else if (typeof (new_value_meta_select) != "undefined" && new_value_meta_select !== null) {
			var meta_to_save = new_value_meta_select;
		} else if (typeof (new_value_meta_radio) != "undefined" && new_value_meta_radio !== null && type_of_field == "radio") {
			var meta_to_save = new_value_meta_radio;
		} else if (typeof (new_value_meta_checkbox) != "undefined" && new_value_meta_checkbox !== null && type_of_field == "checkbox") {
			var meta_to_save = new_value_meta_checkbox;
		} else if (typeof (new_wysiwyg_meta) != "undefined" && new_wysiwyg_meta !== null && type_of_field == "wysiwyg") {
			var meta_to_save = new_wysiwyg_meta;
			//alert('here');
		} else {
			var meta_to_save = "";
		}

		// if data exists save it
		// console.log(meta_to_save);

		var this_meta_id = jQuery(this).attr("id"); // From save button
		// console.log(this_meta_id);
		var post_id = jQuery("#post_ID").text();
		//console.log(post_id);

		// Run Ajax
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

				//alert(data);
				// jQuery('.wp-cta-form').unbind('submit').submit();
				//var worked = '<span class="success-message-map">Success! ' + this_meta_id + ' set to ' + meta_to_save + '</span>';
				var worked = '<span class="wp-cta-success-message">Updated!</span>';
				var s_message = jQuery(self).parent();
				jQuery(worked).appendTo(s_message);
				jQuery(self).parent().find("wp-cta-success-message").remove();
				jQuery(self).hide();
				jQuery('.reload').click();
				//alert("Changes Saved!");
			},

			error: function (MLHttpRequest, textStatus, errorThrown) {
				alert("Ajax not enabled");
			}
		});

		//reload_preview();
		return false;

	});

	function MyFunction(id, this_event)
	{
		var anchor = id;
		element = document.getElementsByTagName("a");
		console.log(anchor);
		var anchorid = "#" + anchor;
		testing = jQuery(anchorid);

		var wrapit = jQuery(anchorid).attr('href');
		jQuery('html, body').stop().animate({
			scrollTop: jQuery(anchorid).offset().top - 100
		}, 500,'easeInOutExpo');
		/**
		if you don't want to use the easing effects:
		$('html, body').stop().animate({
			scrollTop: $($anchor.attr('href')).offset().top
		}, 1000);
	   */
		this_event.preventDefault();
	}


	jQuery('.full-size-view').on('click', function (event) {
		jQuery(parent.document).find("#wp-cta-live-preview").contents().find('html').removeClass("small-html");
		jQuery(parent.document).find("#wp-cta-live-preview").contents().find('html').css("width", "100%");
		jQuery(this).hide();
		jQuery('.shrink-view').show();
		$.cookie("wp-cta-view-choice", "full-size", { path: '/', expires: 7 });
	});

	jQuery('.shrink-view').on('click', function (event) {
		jQuery(parent.document).find("#wp-cta-live-preview").contents().find('html').css("width", "125%");
		jQuery(parent.document).find("#wp-cta-live-preview").contents().find('html').addClass("small-html");
		jQuery(this).hide();

		jQuery('.full-size-view').show();
		$.cookie("wp-cta-view-choice", "shrink", { path: '/', expires: 7 });
	});

	function generate_random_cache_bust(length) {
			var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

			if (! length) {
				length = Math.floor(Math.random() * chars.length);
			}

			var str = '';
			for (var i = 0; i < length; i++) {
				str += chars[Math.floor(Math.random() * chars.length)];
			}
			return str;
	}

	// reload the iframe preview page (for option toggles)
	jQuery('.reload').on('click', function (event) {
		reload_preview();
	});

	var reload_url = parent.window.location.href;

	//alert(jQuery("#current_variation_id").text());
	function reload_preview() {
		var cache_bust =  generate_random_cache_bust(35);
		var reload_url = parent.window.location.href;
		reload_url = reload_url.replace('cta-template-customize=on','');
		//alert(reload_url);
		var current_variation_id = jQuery("#current_variation_id").text();

		// var reload = jQuery(parent.document).find("#wp-cta-live-preview").attr("src");
		var new_reload = reload_url + "&live-preview-area=" + cache_bust + "&wp-cta-variation-id=" + current_variation_id;

		jQuery(parent.document).find("#wp-cta-live-preview").attr("src", new_reload);


		// console.log(new_reload);
	}

	// need rewrite to include the content and the form area
	jQuery('.wp-call-to-action-option-row').on('mouseover', function (event) {
		var $tgt = jQuery(event.target);
		var domElement = jQuery(event.target);
		var current_el_id = jQuery(this).find('input[type="text"], textarea, select').attr("id");
		var finding_the_match = jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_el_id);
		var match_color = jQuery(finding_the_match).css("color");
		jQuery(finding_the_match).addClass("live-preview-active");
		if ( match_color === "rgb(255, 255, 255)") {
			jQuery(finding_the_match).addClass('wp-cta-see-this');
		}
		//jQuery(parent.document).find(".introjs-overlay").show();

	});


	jQuery('.wp-call-to-action-option-row').on('mouseout', function (event) {
		var $tgt = jQuery(event.target);
		 var domElement = jQuery(event.target);
		var current_el_id = jQuery(this).find('input[type="text"], textarea, select').attr("id");
		var finding_the_match = jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_el_id);
		var match_color = jQuery(finding_the_match).css("color");
		jQuery(finding_the_match).removeClass("live-preview-active");
		if ( match_color === "rgb(0, 0, 0)") {
			jQuery(finding_the_match).removeClass('wp-cta-see-this');
		}
		//jQuery(parent.document).find("#new").contents().find(".introjs-overlay").hide();

	});

	jQuery('input[type="text"], textarea, select').each(function(){

		//console.log(current_id);
		jQuery(this).on("keyup", function (e) {
			var current_id = jQuery(this).attr("id");
			var actual_id = "#" + current_id;
			//var current_id = jQuery(this).attr("id");
			var current_value_from_page = $(this, parent.document.body).text();
			//console.log(current_value_from_page);
			var new_value_show = jQuery(this).val();
			//console.log(new_value_show);
			//$(this, parent.document.body).html(new_value_show);
			jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_id).html(new_value_show);
		});

	});

	/* On change add revert button
	 jQuery('input[type="text"], textarea, select').each(function(){

		//console.log(current_id);
		jQuery(this).on("change", function (e) {

		});

	});
	*/


	// Listen to the_content editor
	setTimeout(function() {
	    jQuery('#wp_content_ifr').contents().find("body").on({
			keyup: function() {
				//console.log('new vale');
				var new_value = jQuery(this).html();
				jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#the-content").html(new_value);
				parent_el = jQuery(".the-content-label");
				jQuery(parent_el).find(".wp-cta-success-message").remove();
				jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
				var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="the_content" style="margin-left:10px;">Update</span>');
				jQuery(ajax_save_button).appendTo(parent_el);
			},
			// change not working probably need timeout on clicks
			change: function() {
				//console.log('change logged');
				var new_value = jQuery(this).html();
				jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#the-content").html(new_value);
			}
			//jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_id).html(new_value_show);
	   });

	}, 4000);

/* might still need
	// Listen to form area editor. Note shortcodes won't render correctly
	setTimeout(function() {
	      jQuery('#wp-cta-conversion-area_ifr').contents().find("body").on({
				keyup: function() {
					//console.log('new vale');
					var new_value = jQuery(this).html();
					jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#wp-cta-conversion-area").html(new_value);
					jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#wp_cta_container_form").html(new_value);
					parent_el = jQuery(".wp-cta-conversion-area-label");
					jQuery(parent_el).find(".wp-cta-success-message").remove();
					jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
					var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="wp-cta-conversion-area" style="margin-left:10px;">Update</span>');
					//console.log(parent_el);

					jQuery(ajax_save_button).appendTo(parent_el);
				},
				// change not working probably need timeout on clicks
				change: function() {
					//console.log('change logged');
					var new_value = jQuery(this).html();
					jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#wp-cta-conversion-area").html(new_value);

				}
						//jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_id).html(new_value_show);
		});

	}, 3000);
*/
	jQuery('#wp_content').on("keyup", function (e) {
			//console.log('new vale');
			var new_value = jQuery(this).text();
			jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#the-content").html(new_value);
			//jQuery(parent.document).find("#wp-cta-live-preview").contents().find("#" + current_id).html(new_value_show);
	});

	//jQuery(parent.document).find('#wp_cta_customizer_options').contents().find('#wp_content_ifr').contents().find("body").html();

	// Need to resize or insert custom css into media-uploader iframe
	/**
		tb_position_two = function() {
            var tbWindow = $('#TB_window');
            console.log(tbWindow);

        }
     */

	jQuery('#wp-cta-frontend-options-container .upload_image_button').on('click', function (event) {

			//console.log(parent_input);
			var media_name = jQuery(this).attr('id');
			media_name = media_name.replace('uploader_','');
			var parent_el = jQuery(this).parent().parent();
			jQuery(parent_el).find(".wp-cta-success-message").remove();
			jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
			var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="' + media_name + '" style="position: absolute; top: 0px; right: 34px;">Update</span>');
			setTimeout(function() {
				jQuery("#TB_iframeContent").contents().find('head').append('<link rel="stylesheet" href="/wp-content/plugins/wp-call-to-actions/css/customizer.media-uploader.css" type="text/css" />');
			}, 500);
			setTimeout(function() {
				jQuery("#TB_iframeContent").contents().find('head').append('<link rel="stylesheet" href="/wp-content/plugins/wp-call-to-actions/css/customizer.media-uploader.css" type="text/css" />');
			}, 2000);

		//console.log(parent_el);
		jQuery(ajax_save_button).appendTo(parent_el);
			//alert(media_name);
			jQuery.cookie('media_name', media_name);
			jQuery.cookie('media_init', 1);
			tb_show('', 'media-upload.php?type=image&type=image&amp;TB_iframe=true');
			return false;
		}
	 );

	window.tb_remove = function()
	{
		console.log('new-image-chosen');
		$("#TB_imageOff").unbind("click");
		$("#TB_closeWindowButton").unbind("click");
		$("#TB_window").fadeOut("fast",function(){
			$('#TB_window,#TB_overlay,#TB_HideSelect').remove();
		});
		$("#TB_load").remove();
		if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
			$("body","html").css({height: "auto", width: "auto"});
			$("html").css("overflow","");
		}
		document.onkeydown = "";
		document.onkeyup = "";

		jQuery.cookie('media_init', 0);
		return false;
	}

	window.send_to_editor = function(h) {
		if (jQuery.cookie('media_init')==1)
		{
			var imgurl = jQuery('img',h).attr('src');
			if (!imgurl)
			{
				var array = html.match("src=\"(.*?)\"");
				imgurl = array[1];
			}
			//alert(jQuery.cookie('media_name'));
			jQuery('#' + jQuery.cookie('media_name')).val(imgurl);
			jQuery.cookie('media_init', 0);
			tb_remove();
		}
		else
		{
			var ed, mce = typeof(tinymce) != 'undefined', qt = typeof(QTags) != 'undefined';

			if ( !wpActiveEditor ) {
				if ( mce && tinymce.activeEditor ) {
					ed = tinymce.activeEditor;
					wpActiveEditor = ed.id;
				} else if ( !qt ) {
					return false;
				}
			} else if ( mce ) {
				if ( tinymce.activeEditor && (tinymce.activeEditor.id == 'mce_fullscreen' || tinymce.activeEditor.id == 'wp_mce_fullscreen') )
					ed = tinymce.activeEditor;
				else
					ed = tinymce.get(wpActiveEditor);
			}

			if ( ed && !ed.isHidden() ) {
				// restore caret position on IE
				if ( tinymce.isIE && ed.windowManager.insertimagebookmark )
					ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);

				if ( h.indexOf('[caption') === 0 ) {
					if ( ed.wpSetImgCaption )
						h = ed.wpSetImgCaption(h);
				} else if ( h.indexOf('[gallery') === 0 ) {
					if ( ed.plugins.wpgallery )
						h = ed.plugins.wpgallery._do_gallery(h);
				} else if ( h.indexOf('[embed') === 0 ) {
					if ( ed.plugins.wordpress )
						h = ed.plugins.wordpress._setEmbed(h);
				}

				ed.execCommand('mceInsertContent', false, h);
			} else if ( qt ) {
				QTags.insertContent(h);
			} else {
				document.getElementById(wpActiveEditor).value += h;
			}

			jQuery.cookie('media_init', 0);

			try{tb_remove();}catch(e){};
		}
	}



});
