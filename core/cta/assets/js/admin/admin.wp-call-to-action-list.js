jQuery(document).ready(function($) {
   // Code for landing page list view
	var cats = jQuery("#wp_call_to_action_category option").length;
	if ( cats === 0 ){
		jQuery("#wp_call_to_action_category").hide();
	}

    jQuery('.wp-cta-letter').each(function(){
        var draft = jQuery(this).text();
         if ( draft === "" ){
      jQuery(this).parent().parent().hide();
      }
    });

    jQuery(".wp-cta-impress-num").each(function(){
    var empty = jQuery(this).text();
     if ( empty === "" || empty === "0" ){
      jQuery(this).parent().parent().find(".wp-cta-letter").css("color", "#ccc");
      jQuery(this).parent().html("<span class='wp-cta-no-stats'>no stats yet</span>");
      }
    });


    jQuery("body").on('mouseenter', 'tr.type-wp-call-to-action', function () {
        jQuery(this).find(".no-stats-yet").show();
    });

    jQuery("body").on('mouseleave', 'tr.type-wp-call-to-action', function () {
        jQuery(this).find(".no-stats-yet").hide();
    });

    jQuery(".variation-winner-is").each(function(){
         var target = jQuery(this).text();
         jQuery("." + target).addClass("winner-wp-cta").attr("data-wp-cta", "Current Winner");
    });

    var hidestats = "<span id='hide-stats'>(Hide Stats)</span><span class='show-stats show-stats-top'>Show Stats</span>";
    jQuery("#cta_stats").append(hidestats);

    jQuery("body").on('click', '#hide-stats', function () {
        jQuery(".wp-cta-varation-stat-ul").each(function(){
            jQuery(this).hide();
        });
        jQuery(".show-stats").show();
        jQuery("#hide-stats").hide();
    });

    jQuery("body").on('click', '.show-stats-top', function () {
      jQuery(".wp-cta-varation-stat-ul").each(function(){
        jQuery(this).show();
    });
      jQuery(".show-stats").hide();
      jQuery("#hide-stats").show();
    });

    jQuery("body").on('click', '.show-stats', function () {
      jQuery(this).hide();
      jQuery(this).parent().find(".wp-cta-varation-stat-ul").show();
    });

	jQuery('.wp-cta-letter, .cr-number, .qtip').on('mouseenter', function(event) {
	  // Bind the qTip within the event handler
	  var text_in_tip = jQuery(this).attr("data-notes");
	  var letter = jQuery(this).attr("data-letter");
	  var status = "<span class='wp-cta-paused'>" + jQuery(this).parent().attr("rel") + "</span>";
	  var winner = "<span class='wp-cta-win'>" + jQuery(this).parent().attr("data-wp-cta") + "</span>";
	  jQuery(this).qtip({
		overwrite: false, // Make sure the tooltip won't be overridden once created
		  content: {
			  text: text_in_tip,
			  title: {
				text: 'Variation ' + letter + "<span class='wp-cta-extra'>" + status + winner + "</span>" + "<span class='wp-cta-pop-close'>close</span>"
			  }
			},
		position: {
			  my: 'bottom center', // Use the corner...
			  at: 'top center', // ...and opposite corner
			  viewport: jQuery(window)
			},
		style: {
			  classes: 'qtip-shadow qtip-jtools',
			},
		show: {
		  event: event.type, // Use the same show event as the one that triggered the event handler
		  ready: true, // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
		  solo: true
		},
		//hide: 'unfocus'
        hide: { when: { event: 'inactive' }, delay: 1200 }
	  }, event); // Pass through our original event to qTip
	})

	jQuery('.wp-cta-letter').on('mouseleave', function(event) {


	});

	jQuery("body").on("click", ".wp-cta-pop-close", function(event) {
		jQuery(this).parent().parent().parent().hide();
	});

	jQuery("body").on("click", ".wp-cta-pop-preview a", function(event) {
		jQuery(this).parent().parent().parent().parent().hide();
	});

	 // Fix Thickbox width/hieght
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

 });