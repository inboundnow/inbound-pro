jQuery(document).ready(function($) {
	
	var window_width = jQuery(window).width();
	var parent_window = parent.document.width;
	iframe_size = parent_window * 0.334;
	new_size = parent_window * 0.70;
	new_new_size = parent_window - new_size;
	editor_size = iframe_size * 0.85 + "px";
	// Resize Functions
	$("#wp_content_resize, #lp-conversion-area_ifr").height(210);
	   setTimeout(function() {
	jQuery("#lp-conversion-area_ifr, #lp-conversion-area_tbl, #wp_content_ifr, .mceLayout, .mceIframeContainer iframe").height(150);
	jQuery(".wp-editor-container table").css("max-width", editor_size);
		jQuery('iframe').contents().find("body").each(function(){
			jQuery(this).css("max-width", editor_size);
		}); 
	}, 1000);
	
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
		/*
		if you don't want to use the easing effects:
		$('html, body').stop().animate({
			scrollTop: $($anchor.attr('href')).offset().top
		}, 1000);
	   */
		this_event.preventDefault();            
	}
	 

	jQuery('.full-size-view').on('click', function (event) {
		jQuery(parent.document).find("#lp-live-preview").contents().find('html').removeClass("small-html");
		jQuery(parent.document).find("#lp-live-preview").contents().find('html').css("width", "100%");
		jQuery(this).hide();
		jQuery('.shrink-view').show();
		$.cookie("lp-view-choice", "full-size", { path: '/', expires: 7 });
	});

	jQuery('.shrink-view').on('click', function (event) {
		jQuery(parent.document).find("#lp-live-preview").contents().find('html').css("width", "125%");
		jQuery(parent.document).find("#lp-live-preview").contents().find('html').addClass("small-html");
		jQuery(this).hide();

		jQuery('.full-size-view').show();
		$.cookie("lp-view-choice", "shrink", { path: '/', expires: 7 });
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
		reload_url = reload_url.replace('template-customize=on','');
		//alert(reload_url);
		var current_variation_id = jQuery("#current_variation_id").text();
	
		// var reload = jQuery(parent.document).find("#lp-live-preview").attr("src"); 
		var new_reload = reload_url + "&live-preview-area=" + cache_bust + "&lp-variation-id=" + current_variation_id;
		jQuery(parent.document).find("#lp-live-preview").attr("src", new_reload);
		// console.log(new_reload);
	}
			   
	// need rewrite to include the content and the form area
	jQuery('.landing-page-option-row').on('mouseover', function (event) {
		var $tgt = jQuery(event.target);
		var domElement = jQuery(event.target);
		var current_el_id = jQuery(this).find('input[type="text"], textarea, select').attr("id");
		var finding_the_match = jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_el_id);
		var match_color = jQuery(finding_the_match).css("color");
		jQuery(finding_the_match).addClass("live-preview-active");
		if ( match_color === "rgb(255, 255, 255)") {
			jQuery(finding_the_match).addClass('lp-see-this');
		}
		//jQuery(parent.document).find(".introjs-overlay").show();
	 
	});


	jQuery('.landing-page-option-row').on('mouseout', function (event) {
		var $tgt = jQuery(event.target);
		 var domElement = jQuery(event.target);
		var current_el_id = jQuery(this).find('input[type="text"], textarea, select').attr("id");
		var finding_the_match = jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_el_id);
		var match_color = jQuery(finding_the_match).css("color");
		jQuery(finding_the_match).removeClass("live-preview-active");
		if ( match_color === "rgb(0, 0, 0)") {
			jQuery(finding_the_match).removeClass('lp-see-this');
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
			jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_id).html(new_value_show);
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
				jQuery(parent.document).find("#lp-live-preview").contents().find("#the-content").html(new_value);
				parent_el = jQuery(".the-content-label");
				jQuery(parent_el).find(".lp-success-message").remove();
				jQuery(parent_el).find(".new-save-lp-frontend").remove();
				var ajax_save_button = jQuery('<span class="button-primary new-save-lp-frontend" id="the_content" style="margin-left:10px;">Update</span>');
				jQuery(ajax_save_button).appendTo(parent_el);
			},
			// change not working probably need timeout on clicks
			change: function() {
				//console.log('change logged');
				var new_value = jQuery(this).html();
				jQuery(parent.document).find("#lp-live-preview").contents().find("#the-content").html(new_value);
			}
			//jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_id).html(new_value_show);
	   });
		 
	}, 4000);

/* might still need
	// Listen to form area editor. Note shortcodes won't render correctly
	setTimeout(function() {
	      jQuery('#lp-conversion-area_ifr').contents().find("body").on({
				keyup: function() {
					//console.log('new vale');
					var new_value = jQuery(this).html();
					jQuery(parent.document).find("#lp-live-preview").contents().find("#lp-conversion-area").html(new_value);
					jQuery(parent.document).find("#lp-live-preview").contents().find("#lp_container_form").html(new_value);
					parent_el = jQuery(".lp-conversion-area-label");
					jQuery(parent_el).find(".lp-success-message").remove();
					jQuery(parent_el).find(".new-save-lp-frontend").remove();
					var ajax_save_button = jQuery('<span class="button-primary new-save-lp-frontend" id="lp-conversion-area" style="margin-left:10px;">Update</span>');
					//console.log(parent_el);
					
					jQuery(ajax_save_button).appendTo(parent_el);
				},
				// change not working probably need timeout on clicks
				change: function() {
					//console.log('change logged');
					var new_value = jQuery(this).html();
					jQuery(parent.document).find("#lp-live-preview").contents().find("#lp-conversion-area").html(new_value);

				}
						//jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_id).html(new_value_show);
		});
		 
	}, 3000);
*/
	jQuery('#wp_content').on("keyup", function (e) {
			//console.log('new vale');
			var new_value = jQuery(this).text();
			jQuery(parent.document).find("#lp-live-preview").contents().find("#the-content").html(new_value);
			//jQuery(parent.document).find("#lp-live-preview").contents().find("#" + current_id).html(new_value_show);
	});
	 
	//jQuery(parent.document).find('#lp_customizer_options').contents().find('#wp_content_ifr').contents().find("body").html();
	
	// Need to resize or insert custom css into media-uploader iframe 
	/*
		tb_position_two = function() {
            var tbWindow = $('#TB_window');
            console.log(tbWindow);

        }
     */
    
	jQuery('#lp-frontend-options-container .upload_image_button').on('click', function (event) {
			
			//console.log(parent_input); 
			var media_name = jQuery(this).attr('id');
			media_name = media_name.replace('uploader_',''); 
			var parent_el = jQuery(this).parent().parent();
			jQuery(parent_el).find(".lp-success-message").remove();
			jQuery(parent_el).find(".new-save-lp-frontend").remove();
			var ajax_save_button = jQuery('<span class="button-primary new-save-lp-frontend" id="' + media_name + '" style="position: absolute; top: 0px; right: 34px;">Update</span>');
			setTimeout(function() {
				jQuery("#TB_iframeContent").contents().find('head').append('<link rel="stylesheet" href="/wp-content/plugins/landing-pages/css/customizer.media-uploader.css" type="text/css" />');
			}, 500);
			setTimeout(function() {
				jQuery("#TB_iframeContent").contents().find('head').append('<link rel="stylesheet" href="/wp-content/plugins/landing-pages/css/customizer.media-uploader.css" type="text/css" />');
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
