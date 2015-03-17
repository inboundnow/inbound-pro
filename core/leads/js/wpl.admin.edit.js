function getUrlVars() {
   var vars = [], hash;
   var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
   for(var i = 0; i < hashes.length; i++)
   {
	 hash = hashes[i].split('=');
	 vars.push(hash[0]);
	 vars[hash[0]] = hash[1];
   }
   return vars;
}

function getUrlVar(name){
           return getUrlVars()[name];
}

InboundQuery(document).ready(function($) {

	// Getting URL var by its nam
	var byName = getUrlVar('tab');
	setTimeout(function() {
	     $('#poststuff').fadeIn(300);
	     $('#postcustom .hndle span').text('Raw Data');
	}, 300);

	// Set setting Tab
	setTimeout(function() {
	    InboundQuery("#" + byName).click();
	}, 300);

	/* Update Setting URL */
	InboundQuery("body").on('click', '.nav-tab', function () {
	  var this_id = InboundQuery(this).attr('id');
	  if (history.pushState) {
	      var newurl = window.location.href.replace(/tab=([^"]*)/g, 'tab=' + this_id);
	      var current_tab = newurl.match(/tab=([^"]*)/g);
	      if (typeof (current_tab) != "undefined" && current_tab != null && current_tab != "") {
	      	var current_tab = current_tab[0].replace("tab=","");
	      	window.history.pushState({path:newurl},'',newurl);
	      } else {
	      	var newurl = window.location.href + '&tab=' + this_id;
	      	window.history.pushState({path:newurl},'',newurl);
	      }

	  }
	});

	var rawData = InboundQuery("#postcustom");
	rawData.show();
	InboundQuery("#raw-data-display").append(rawData);

	InboundQuery('#wplead_list_category-add-toggle').hide();

	InboundQuery('.row-actions').each(function() {
		var jQuerylist = InboundQuery(this);
		var jQueryfirstChecked = jQuerylist.parent().parent().find('.column-first-name');

		if ( !jQueryfirstChecked.html() )
			return;

		jQuerylist.appendTo(jQueryfirstChecked);
	});
	var session_count = InboundQuery('.wpleads-conversion-tracking-table').length;
	InboundQuery('.conversion-tracking-header h2 span.visit-number').each(function(i){
		var sess = session_count - i;
		InboundQuery(this).text(sess);

	});
	var session_view = jQuery.cookie("lead-session-view-choice");
	setTimeout(function() {
	InboundQuery("#wplead_metabox_conversion h3, #wplead_metabox_conversion .hndle").unbind('click');
		if(session_view === "hide_sessions") {
		InboundQuery('.minimize-paths').click();
		}
	}, 800);

	InboundQuery("body").on('mouseenter', '.recent-conversion-item', function () {
		$(this).find('.lead-timeline-img').addClass('active-hover');
	});

	InboundQuery("body").on('mouseleave', '.recent-conversion-item', function () {
		$(this).find('.lead-timeline-img').removeClass('active-hover');
	});

	InboundQuery("body").on('click', '.minimize-paths', function () {

		var text = InboundQuery(this).text();
		if (text === "Shrink Session View"){
			InboundQuery('.session-item-holder, .time-on-page-label').hide();

			InboundQuery(".toggle-conversion-list").text("+");
			InboundQuery(this).text("Expand Session View");
			jQuery.cookie("lead-session-view-choice", "hide_sessions", { path: '/', expires: 7 });

		} else {
			InboundQuery('.session-item-holder,.time-on-page-label').show();
			InboundQuery(".toggle-conversion-list").text("-");
			InboundQuery(this).text("Shrink Session View");
			jQuery.cookie("lead-session-view-choice", "show_sessions", { path: '/', expires: 7 });
		}
	});
	InboundQuery('.session-item-holder').each(function(i){
		var count = InboundQuery(this).find('.lp-page-view-item').length;
		InboundQuery(this).find('.marker').each(function(i){
			InboundQuery(this).text(count - i);
		});
	});
	InboundQuery("#wpleads_lead_tab_main_inner").fadeIn(1000);
	InboundQuery('.touchpoint-value').each(function() {
		var touch_val = InboundQuery(this).text();

		if ( touch_val != "0" ) {
			InboundQuery(this).parent().show();
		}
		InboundQuery(this).find(".touchpoint-minute").show();
	});

	var hideempty = InboundQuery("#touch-point span:visible").length;
	var hideago = InboundQuery("#session-time-since:visible").length;

	if (hideempty === 0) {
		//InboundQuery("#touch-point").html("<strong>Moments ago</strong>")
	}

	if (hideago === 0) {
		//InboundQuery("#session-time-since").text("Just Now!");
	}

	InboundQuery("#submitdiv .hndle").text("Update Lead Information");
	var html = '<a class="add-new-h2" href="edit.php?post_type=wp-lead">Back</a>';
	InboundQuery('.add-new-h2').before(html);

	//populate country
	InboundQuery('.wpleads-country-dropdown').val(InboundQuery('#hidden-country-value').val());

	InboundQuery('.add-new-link').on('click', function(e){
		var count = InboundQuery('#wpleads_websites-container .wpleads_link').size();
		var true_count = count+1;
		var html = '<input name="wpleads_websites['+count+']" class="wpleads_link" type="text" size="70" value="" />';
		InboundQuery('#wpleads_websites-container').append(html);
	});

	InboundQuery('.wpleads_remove_link').live('click',function(e){
		var this_id = InboundQuery(this).attr('id');
		InboundQuery('#wpleads_websites-'+this_id).remove();
	});

	jQuery.fn.tagcloud.defaults = {
	  size: {start: 10, end: 25, unit: 'pt'},
	  color: {start: '#bbb', end: '#2a95c5'}
	};

	InboundQuery(function () {
	  InboundQuery('#lead-tag-cloud a').tagcloud();
	});

	InboundQuery('#wpleads_main_container input, #wpleads_main_container textarea').each(

        function(){
   			// hide empty fields
   		 if( InboundQuery(this).val() ) {
 			InboundQuery(this).parent().parent().show();
            }
          if( !InboundQuery(this).val() ) {
 			InboundQuery(this).parent().parent().hide().addClass('hidden-lead-fields');
            }

        }
    );


	if (InboundQuery('#wpleads-td-wpleads_websites').hasClass('hidden-lead-fields')) {
		InboundQuery('.wpleads_websites').hide().addClass('hidden-lead-fields');
	}


	InboundQuery("#activity-data-display nav li").click(function() {
		InboundQuery(".active").removeClass("active");
		InboundQuery(this).addClass("active");
	});

	InboundQuery("#show-hidden-fields").click(function() {
		var $this = $(this);
		if($this.text() === "Show Empty Fields") {
			$this.text("Hide Empty Fields");
		} else {
			$this.text("Show Empty Fields");
		}
		InboundQuery(".hidden-lead-fields").toggle();
		InboundQuery("#add-notes").hide();
	});

	var notesarea = InboundQuery("#wpleads-td-wpleads_notes").text();
	if (notesarea === "") {
		InboundQuery("#wpleads-td-wpleads_notes textarea").hide().addClass('hidden-lead-fields');
		var expandnotes = "<span id='add-notes'>No Notes. Click here to add some.</span>";
		InboundQuery(expandnotes).appendTo(InboundQuery("#wpleads-td-wpleads_notes"));

	}

	InboundQuery("#add-notes").click(function() {
		InboundQuery("#wpleads-td-wpleads_notes textarea").toggle();
		InboundQuery("#add-notes").hide();
	});

	 InboundQuery(".conversion-tracking-header").on("click", function(event){
	 //	alert("yes");
	var link = InboundQuery(this).find(".toggle-conversion-list");
	var conversion_log = InboundQuery(this).parent().find(".session-item-holder, .session-stats, .time-on-page-label").toggle();

		  if (InboundQuery(conversion_log).is(":visible")) {
					 link.text('-');
				} else {
					 link.text('+');
				}
	});

	InboundQuery("body").on('click', '#conversion-total', function () {
		InboundQuery("#tabs-wpleads_lead_tab_conversions").click();
    });


 	var textchange = InboundQuery("#timestamp").html().replace("Published", "Created");
  	InboundQuery('#timestamp').html(textchange);
	var pageviews = InboundQuery(".marker").size();
	var totalconversions = InboundQuery(".wpleads-conversion-tracking-table").size();
	var conversion_empty = InboundQuery("#conversion-total").text();
	var views_empty = InboundQuery("#p-view-total").text();
	if (views_empty === ""){
		InboundQuery("#p-view-total").text(pageviews);
	}

	var conversion_empty = InboundQuery("#conversion-total").text();
	InboundQuery('h2 .nav-tab').eq(0).css("margin-left", "10px");

	InboundQuery("#message.updated").text("Lead Updated").css("padding", "10px");
	InboundQuery('.wpleads-conversion-tracking-table').each(function() {
		var number_of_pages = InboundQuery(this).find('.lp-page-view-item').size();
		InboundQuery(this).find("#pages-view-in-session").text(number_of_pages);
		if (number_of_pages == 1) {
		   InboundQuery(this).find(".session-stats-header").hide();
		   InboundQuery(this).find("#session-pageviews").hide();
		   }
	});

	// view toggles
	InboundQuery(".view-this-lead-session a").on("click", function(event){
	var s_number = InboundQuery(this).attr("rel");
	var correct_session = ".session_id_" + s_number;
	console.log(correct_session);
	InboundQuery(".conversion-session-view").hide();
	InboundQuery(correct_session).show();
	});


	// Sort by date. http://stackoverflow.com/questions/7211704/jquery-order-by-date-in-data-attribute
	InboundQuery(document).ready(function($) {
		InboundQuery("#activity-data-display .recent-conversion-item").sort(function(a,b){
			return new Date(InboundQuery(a).attr("data-date")) > new Date(InboundQuery(b).attr("data-date"));
		}).each(function(){
		var clone = InboundQuery(this).clone().addClass("cloned-item");
			InboundQuery("#all-lead-history").append(clone);

		})
		//InboundQuery(".cloned-item").wrap("<li>");
		InboundQuery(".lead-item-num, .lead-activity").hide();


		 var reviews = InboundQuery('#all-lead-history .recent-conversion-item');
		reviews.tsort({ attr: 'data-date', order: 'desc' });
		InboundQuery('#newest-event').click(function(){
			var which_sort = InboundQuery(".event-order-list").attr("data-change-sort");
			var the_list = InboundQuery(which_sort + ' .recent-conversion-item');
			the_list.tsort({ attr: 'data-date', order: 'desc' });
			InboundQuery('.lead-sort-active').removeClass('lead-sort-active');
			InboundQuery(this).addClass('lead-sort-active');
		});

		InboundQuery('#oldest-event').click(function(){
			var which_sort = InboundQuery(".event-order-list").attr("data-change-sort");
			var the_list = InboundQuery(which_sort + ' .recent-conversion-item');
			the_list.tsort({ attr: 'data-date', order: 'asc' });
			InboundQuery('.lead-sort-active').removeClass('lead-sort-active');
			InboundQuery(this).addClass('lead-sort-active');
		});

    InboundQuery('#highest').click(function(){
			reviews.tsort({ attr: 'data-rating', order: 'desc' });
		});

		InboundQuery('#lowest').click(function(){
			reviews.tsort({ attr: 'data-rating', order: 'asc' });
		});
	});

	// activity toggles
	InboundQuery("body").on('click', '.lead-activity-toggle', function (event) {
		event.preventDefault();
		var toggle_this = InboundQuery(this).attr("href");
		InboundQuery(".event-order-list").attr("data-change-sort", toggle_this);
		InboundQuery(".lead-activity").hide();
		InboundQuery("#all-lead-history").hide();
		var which_sort = InboundQuery(".event-order-list").attr("data-change-sort");
		var the_list = InboundQuery(which_sort + ' .recent-conversion-item');
        the_list.tsort({ attr: 'data-date', order: 'desc' });
		InboundQuery(toggle_this).fadeIn(700);
		InboundQuery(".lead-item-num").show();
    });

    InboundQuery("body").on('click', '.lead-activity-show-all', function () {
    	event.preventDefault();
    	InboundQuery(".lead-activity").hide();
    	InboundQuery(".event-order-list").attr("data-change-sort", "#all-lead-history");
    	InboundQuery("#all-lead-history").fadeIn(700);
    	InboundQuery(".lead-item-num").hide();
    });


	InboundQuery(".possible-map-value").on("click", function(event){
	InboundQuery(".toggle-val").removeClass("toggle-val");
	InboundQuery(this).toggleClass("toggle-val");
	});

	var null_lead_status = InboundQuery("#current-lead-status").text();

	if (null_lead_status === "") {
	var post_id = InboundQuery("#post_ID").val();
	jQuery.ajax({
				type: 'POST',
				url: wp_lead_map.ajaxurl,
				context: this,
				data: {
					action: 'wp_leads_auto_mark_as_read',
					page_id: post_id,
					//nonce: nonce_val
				},

				success: function(data){
					var self = this;
							//alert(data);
							// InboundQuery('.lp-form').unbind('submit').submit();
							var worked = '<span class="success-message-map" style="display: inline-block;margin-top: -1px;margin-left: 20px;padding:4px 25px 4px 20px;position: absolute;">This Lead has been marked as read/viewed.</span>';
							var s_message = InboundQuery("#lead-top-area");
							InboundQuery(worked).appendTo(s_message);
							// alert("This lead is marked as read.");
						   },

				error: function(MLHttpRequest, textStatus, errorThrown){
					alert("Error thrown not sure why");
					}
			});

	}

});