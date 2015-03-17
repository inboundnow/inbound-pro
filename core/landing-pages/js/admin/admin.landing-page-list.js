InboundQuery(document).ready(function($) {
   // Code for landing page list view
	var cats = InboundQuery("#landing_page_category option").length;
	if ( cats === 0 ){
		InboundQuery("#landing_page_category").hide();
	}

    InboundQuery('.lp-letter').each(function(){
        var draft = InboundQuery(this).text();
         if ( draft === "" ){
      InboundQuery(this).parent().parent().hide();
      }
    });

    InboundQuery(".lp-impress-num").each(function(){
    var empty = InboundQuery(this).text();
     if ( empty === "" || empty === "0" ){
      InboundQuery(this).parent().parent().find(".lp-letter").css("color", "#ccc");
      InboundQuery(this).parent().html("<span class='lp-no-stats'>no stats yet</span>");
      }
    });
    /* List tour */
	 var tourbutton = '<a class="" id="lp-tour" style="font-size:13px;">Need help? Take the tour</a>';
    InboundQuery(tourbutton).appendTo("h2:eq(0)");
    InboundQuery("body").on('click', '#lp-tour', function () {
        var tour = InboundQuery("#lp-tour-style").length;
         if ( tour === 0 ) {
            InboundQuery('head').append("<link rel='stylesheet' id='lp-tour-style' href='/wp-content/plugins/landing-pages/css/admin-tour.css' type='text/css' /><script type='text/javascript' src='/wp-content/plugins/landing-pages/js/admin/tour/tour.post-list.js'></script><script type='text/javascript' src='/wp-content/plugins/landing-pages/js/admin/intro.js'></script>");
          }
        setTimeout(function() {
                introJs().start(); // start tour
        }, 300);

    });
	/*InboundQuery(".lp-varation-stat-ul").each(function(){
    var length = InboundQuery(this).find("li").length;
     if ( length < 3 ){
      InboundQuery(this).find("li").first().css("padding-top", "18px");
      }
    });
*/
InboundQuery("body").on('mouseenter', 'tr.type-landing-page', function () {
    InboundQuery(this).find(".no-stats-yet").show();
    });
InboundQuery("body").on('mouseleave', 'tr.type-landing-page', function () {
    InboundQuery(this).find(".no-stats-yet").hide();
    });
      InboundQuery(".variation-winner-is").each(function(){
    var target = InboundQuery(this).text();
      InboundQuery("." + target).addClass("winner-lp").attr("data-lp", "Current Winner");
    });

    var hidestats = "<span id='hide-stats'>(Hide Stats)</span><span class='show-stats show-stats-top'>Show Stats</span>";
    InboundQuery("#stats").append(hidestats);

    InboundQuery("body").on('click', '#hide-stats', function () {
      InboundQuery(".lp-varation-stat-ul").each(function(){
        InboundQuery(this).hide();
    });
      InboundQuery(".show-stats").show();
      InboundQuery("#hide-stats").hide();
    });

    InboundQuery("body").on('click', '.show-stats-top', function () {
      InboundQuery(".lp-varation-stat-ul").each(function(){
        InboundQuery(this).show();
    });
      InboundQuery(".show-stats").hide();
      InboundQuery("#hide-stats").show();
    });

    InboundQuery("body").on('click', '.show-stats', function () {
      InboundQuery(this).hide();
      InboundQuery(this).parent().find(".lp-varation-stat-ul").show();
    });

	InboundQuery('.lp-letter, .cr-number, .qtip').on('mouseenter', function(event) {
	  // Bind the qTip within the event handler
	  var text_in_tip = InboundQuery(this).attr("data-notes");
	  var letter = InboundQuery(this).attr("data-letter");
	  var status = "<span class='lp-paused'>" + InboundQuery(this).parent().attr("rel") + "</span>";
	  var winner = "<span class='lp-win'>" + InboundQuery(this).parent().attr("data-lp") + "</span>";
	  InboundQuery(this).qtip({
		overwrite: false, // Make sure the tooltip won't be overridden once created
		  content: {
			  text: text_in_tip,
			  title: {
				text: 'Variation ' + letter + "<span class='lp-extra'>" + status + winner + "</span>" + "<span class='lp-pop-close'>close</span>"
			  }
			},
		position: {
			  my: 'bottom center', // Use the corner...
			  at: 'top center', // ...and opposite corner
			  viewport: InboundQuery(window)
			},
		style: {
			  classes: 'qtip-shadow qtip-jtools',
			},
		show: {
		  event: event.type, // Use the same show event as the one that triggered the event handler
		  ready: true, // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
		  solo: true
		},
		hide: 'unfocus'
    //hide: { when: { event: 'inactive' }, delay: 1200 }
	  }, event); // Pass through our original event to qTip
	})

	InboundQuery('.lp-letter').on('mouseleave', function(event) {


	});

	InboundQuery("body").on("click", ".lp-pop-close", function(event) {
		InboundQuery(this).parent().parent().parent().hide();
	});

	InboundQuery("body").on("click", ".lp-pop-preview a", function(event) {
		InboundQuery(this).parent().parent().parent().parent().hide();
	});

	 // Fix Thickbox width/hieght
    InboundQuery(function($) {
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

        InboundQuery('a.thickbox').click(function(){
            if ( typeof tinyMCE != 'undefined' &&  tinyMCE.activeEditor ) {
                tinyMCE.get('content').focus();
                tinyMCE.activeEditor.windowManager.bookmark = tinyMCE.activeEditor.selection.getBookmark('simple');
            }

        });

        $(window).resize( function() { tb_position() } );
    });

 });