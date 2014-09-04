jQuery(document).ready(function ($) {

	jQuery('#lp-template-selector-container').css('display','block');

	//remove inputs from wp-list-table
    jQuery('#leads-table-container-inside input').each(function(){
        jQuery(this).remove();
    });

	jQuery("#submitdiv").siblings().hide();

    jQuery("#title-prompt-text").text("Name Your New Landing Page");

    var titledescription = jQuery("<span id='descriptor'>This will be the administrative title your landing page, the main headline is created in the next step</span>");
    jQuery(titledescription).appendTo("#titlewrap");

    jQuery("#save-action input").addClass("button-primary button-large").css("margin-bottom", "10px").attr("value", "Create Landing Page");

    var sidebar = jQuery("#side-sortables");
    jQuery(sidebar).appendTo("#titlediv");

    var tempdiv = jQuery("<div id='templates' class='postbox'><h3 class='hndle'>Current Template: <span id='ctemp'></span></h3><div id='lp_the_image'><span id='timage'><img src='' id='c_temp'></span></div><div id='template_current'></div></div>");

	jQuery(tempdiv).appendTo("#titlewrap");
	var changebutton = jQuery("#lp_template_change");

	jQuery(changebutton).appendTo("#templates");
	jQuery("#lp_template_change a").removeClass("button-primary").addClass("button");

	// New Sidebar
	jQuery("#postbox-container-1").html("<div class='postbox'><center><h3>Download Additional Templates</h3><a target='_blank' href='/wp-admin/edit.php?post_type=landing-page&page=lp_store'><img src='"+lp_post_new_ui.LANDINGPAGES_URLPATH+"images/get-wordpress-templates.png'></a><a target='_blank' href='/wp-admin/edit.php?post_type=landing-page&page=lp_store' class='button new-lp-button button-primary button-large'>Download Landing Page Templates</a></center></div><div class='postbox'><center><h3>Need Custom Template Design?</h3><a target='_blank' href='/wp-admin/edit.php?post_type=landing-page&page=lp_store'><img src='"+lp_post_new_ui.LANDINGPAGES_URLPATH+"/images/get-custom-setup.png'></a><a target='_blank' href='http://www.inboundnow.com/landing-pages/custom-wordpress-landing-page-setup/' class='button new-lp-button button-primary button-large'>Get Custom Template Setup</a></center></div>");

    jQuery('.lp_select_template').click(function(){
		jQuery(".mceIframeContainer iframe#content_ifr").css("height", "100%");
		jQuery("#wp-content-editor-container .mceStatusbar").css("display", "none");
    });

	jQuery('.lp_select_template').click(function(){

        var template = jQuery(this).attr('id');
		var selected_template_id = "#" + template;
        var label = jQuery(this).attr('label');
        var template_image = "#" + template + " .template-thumbnail";
        var template_img_obj = jQuery(template_image).attr("src");

        jQuery("#ctemp").text(label);
        jQuery("#template_current").html('<input type="hidden" name="lp-selected-template" value="'+template+'"><input type="hidden" value="1" name="lp_post_new">');
		jQuery("#timage #c_temp").attr("src", template_img_obj);
		jQuery("#submitdiv .hndle span").text("Create Landing Page");

    });

	jQuery('#lp-change-template-button').live('click', function () {
        jQuery(".wrap").fadeOut(500,function(){

            jQuery(".lp-template-selector-container").fadeIn(500, function(){
                jQuery('#lp-cancel-selection').show();
            });
            jQuery("#template-filter li a").first().click();
        });
    });

    // filter items when filter link is clicked
    jQuery('#template-filter a').click(function(){
      var selector = jQuery(this).attr('data-filter');
      jQuery("ul#template-filter li").removeClass('button-primary');
      jQuery(this).parent().addClass('button-primary');
      $(".template-item-boxes").fadeOut(500);
      setTimeout(function() {
       $(selector).fadeIn(500);
      }, 500);

      return false;
    });

    jQuery('.lp_select_template').click(function(){
        var template = jQuery(this).attr('id');
        var label = jQuery(this).attr('label');
        jQuery(".lp-template-selector-container").fadeOut(500,function(){
            jQuery(".wrap").fadeIn(500, function(){
            });
        });

        jQuery('#lp_metabox_select_template h3').html('Current Active Template: '+label);
        jQuery('#lp_select_template').val(template);
        //alert(template);
        //alert(label);
    });


	jQuery("#template-box a").live('click', function () {

		setTimeout(function() {
		 jQuery('#TB_window iframe').contents().find("#customize-controls").hide();
			jQuery('#TB_window iframe').contents().find(".wp-full-overlay.expanded").css("margin-left", "0px");
		}, 1200);

    });

    // Fix Thickbox width
    jQuery(function($) {
		tb_position = function() {
			var tbWindow = $('#TB_window');
			var width = $(window).width();
			var H = $(window).height();
			var W = ( 1720 < width ) ? 1720 : width;

			if ( tbWindow.size() ) {
				tbWindow.width( W - 50 ).height( H - 45 );
				$('#TB_iframeContent').width( W - 50 ).height( H - 75 );
				tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
				if ( typeof document.body.style.maxWidth != 'undefined' )
					tbWindow.css({'top':'40px','margin-top':'0'});
				//$('#TB_title').css({'background-color':'#fff','color':'#cfcfcf'});
			};

			return $('a.thickbox').each( function() {
				var href = $(this).attr('href');
				if ( ! href ) return;
				href = href.replace(/&width=[0-9]+/g, '');
				href = href.replace(/&height=[0-9]+/g, '');
				$(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
			});
		};

		jQuery('a.thickbox').click(function(){
			if ( typeof tinyMCE != 'undefined' &&  tinyMCE.activeEditor ) {
				tinyMCE.get('content').focus();
				tinyMCE.activeEditor.windowManager.bookmark = tinyMCE.activeEditor.selection.getBookmark('simple');
			}

		});

		$(window).resize( function() { tb_position() } );
	});

	var nonce_val = lp_post_new_ui.wp_landing_page_meta_nonce; // NEED CORRECT NONCE

	var nonce_html = '<input type="hidden" value="74910e3045" name="wp-landing-page-meta-nonce">';

	jQuery('form').prepend(nonce_html);

});