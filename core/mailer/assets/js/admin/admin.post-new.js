jQuery(document).ready(function ($) {
	
	
	jQuery('.mailer-template-selector-container').css('display','block');

	jQuery('.inbound_email_select_template').click(function(){
		var template = jQuery(this).attr('id');
		jQuery('.selected-template').val( template );
	});
	// filter items when filter link is clicked

	jQuery('#template-filter a').click(function(){
		var selector = jQuery(this).attr('data-filter');
		$(".template-item-boxes").fadeOut(500);
			setTimeout(function() {
			$(selector).fadeIn(500);
			}, 500);
		return false;
	});

	jQuery('.inbound_email_select_template').click(function(){
		var template = jQuery(this).attr('id');
		var label = jQuery(this).attr('label');
		jQuery(".mailer-template-selector-container").fadeOut(500,function(){
			jQuery(".wrap").fadeIn(500, function(){
			});
		});

		jQuery('#inbound_email_metabox_select_template h3').html('Current Active Template: '+label);
		jQuery('#inbound_email_select_template').val(template);
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
		if ( typeof tinyMCE != 'undefined' &&	tinyMCE.activeEditor ) {
			tinyMCE.get('content').focus();
			tinyMCE.activeEditor.windowManager.bookmark = tinyMCE.activeEditor.selection.getBookmark('simple');
		}

	});

	$(window).resize( function() { tb_position() } );
});
});