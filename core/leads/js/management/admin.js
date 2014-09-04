jQuery(document).ready(function($) {

	//setTimeout(function() {
	    $(".wrap").fadeIn(1000);
	//}, 300);
   // put all your jQuery goodness in here.
   var table = $("#lead-manage-table").length;
   if (table > 0) {
   	new Tablesort(document.getElementById('lead-manage-table'));
   }

	// Add a default value for the categories dropdown
	jQuery("#wplead_list_category").prepend('<option value=""></option>');
	if ( !location.href.match(/&wplead_list_category=([0-9]+)/) ) {
		//jQuery("#wplead_list_category")[0].selectedIndex = 0;
	}

	/*jQuery("#clear").click(
		function() {
			jQuery("#s, #t").attr("value", "");
			jQuery("#cat")[0].selectedIndex = 0;
			return false;
		}
	);*/

		// Drag and drop functionality
	    $( "#the-list" ).selectable({
	      //appendTo: "#the-list",
	      cancel: "a, i, .lead-email",
		  stop: function( event, ui ) {
		  	console.log('stopped');
		  	jQuery('.ui-selected input[type=checkbox]').each(function(){
		  		//$(this).attr('checked', true);
		  		var el = $(this);
		  		check_boxes(el);
		  	});
		  },
		  selected: function( event, ui ) {

		  },
		  unselected: function( event, ui ) {

		  }
		});


	jQuery("body").on('click', '#the-list tr', function () {
		var checkbox = $(this).find('input');
		var checked = checkbox.attr('checked');
		if (checked) {
		   checkbox.attr('checked', false);
		   $(this).removeClass('SELECTED-ROW');
		   $(this).removeClass('ui-selected');
		  	$(this).find('.ui-selected').removeClass('ui-selected');
		} else {
		   checkbox.attr('checked', true);
		   $(this).addClass('SELECTED-ROW');
		}
	 });

	function check_boxes(el) {
		var checkbox = $(el);
		var checked = checkbox.attr('checked');
		if (checked) {
		   checkbox.attr('checked', false);
		   checkbox.parent().parent().removeClass('SELECTED-ROW');
		   checkbox.parent().parent().removeClass('ui-selected');
		   checkbox.parent().parent().find('.ui-selected').removeClass('ui-selected');
		} else {
		   checkbox.attr('checked', true);
		   checkbox.parent().parent().addClass('SELECTED-ROW');
		}
	}

	jQuery("body").on('click', '#the-list input[type=checkbox]', function () {

		var checkbox = $(this);
		var checked = checkbox.attr('checked');
		if (checked) {
		   checkbox.attr('checked', false);
		} else {
		   checkbox.attr('checked', true);
		}
	 });


	jQuery("body").on('click', '.remove-from-list', function () {
		var clicked = $(this);
		var lead_id = clicked.attr('data-lead-id');
		var list_id = clicked.attr('data-list-id');
		jQuery.ajax({
		     	type: 'POST',
		     	context: this,
		     	url: bulk_manage_leads.admin_url,
		     	data: {
		     			action: 'leads_delete_from_list',
		     			lead_id: lead_id,
		     			list_id: list_id
		     		},
		     	success: function(data){
		     		    $(this).parent().remove();
		               },
		     	error: function(MLHttpRequest, textStatus, errorThrown){
		     			//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
		     			//die();
		     		}
		     });
	});

	//$(window).scroll(function(){
	//		var percent = $(window).height() * .8;
	//       if  ($(window).scrollTop() == $(document).height() - $(window).height()){
	/*       var end = $('#end-of-list').length;
	        if (end != 1) {
	           jQuery.ajax({
		            	type: 'POST',
		            	context: this,
		            	url: bulk_manage_leads.admin_url,
		            	data: {
		            			action: 'leads_ajax_load_more_leads',
		            			order: $('#order').val(),
		            			orderby: $('#orderby').val(),
		            			cat: parseInt($('#cat').val()),
		            			tag: $('#t').val(),
		            			pull_page: parseInt($('#paged-current').text()) + 1
		            		},
		            	success: function(data){
		            		    var self = this;
		            			console.log(data);
		            			var num = parseInt($('#paged-current').text()) + 1;
		            			$('#paged-current').text(num);
		            			if (data != "0") {
		            			$("#the-list").append(data);
		            			} else {
		            			$('#end-of-list').remove();
		            			$("#lead-manage-table").after('<div id="end-of-list" style="width:100%; text-align:center;">End of list!</div>');
			            			setTimeout(function() {
			            			      introJs().start(); // start tour
			            			}, 1000);
		            			}

		                      },
		            	error: function(MLHttpRequest, textStatus, errorThrown){
		            			//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
		            			//die();
		            		}
		            });
	           console.log('Scroll fired');
	          ;
	        } */
	   // }
	//});


	//interval = setInterval(pull_leads,3000);
	function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
	var ajax_toggle = getParameterByName('submit');
	var run_ajax = false;
	if (typeof (ajax_toggle) != "undefined" && ajax_toggle != null && ajax_toggle != "") {
		var run_ajax = true;
		$('.wrap h2').first().hide();
		$('.inbound-lead-filters').css('margin-top', "10px");
	}
	// run initial lead pull
	setTimeout(function() {
		if (run_ajax){
			pull_leads();
		}
	}, 2000);
	// run on ajax done
	function run_lead_pull_again() {
		pull_leads();
	}
	function pull_leads() {
		 var end = $('#end-of-list').length;
	     if (end == 1) {
	     	//clearInterval(interval);
	     	console.log('all leads loaded');
	     	jQuery(".lead-spinner").addClass('leads-done');
	     	return false;
	     }
	     console.log('running ajax');
	       jQuery.ajax({
	            	type: 'POST',
	            	context: this,
	            	url: bulk_manage_leads.admin_url,
	            	data: {
	            			action: 'leads_ajax_load_more_leads',
	            			order: $('#order').val(),
	            			orderby: $('#orderby').val(),
	            			cat: $('#hidden-cat').val(),
	            			relation: $('#relation').val(),
	            			tag: $('#t').val(),
	            			pull_page: parseInt($('#paged-current').text()) + 1
	            		},
	            	success: function(data){
	            		    var self = this;
	            			//console.log(data);
	            			var num = parseInt($('#paged-current').text()) + 1;
	            			$('#paged-current').text(num);
	            			if (data != "0") {
	            			$("#the-list").append(data);
	            			} else {
	            			$('#end-of-list').remove();
	            			$("#lead-manage-table").after('<div id="end-of-list" style="width:100%; text-align:center;">End of list!</div>');
	            			}
	            			var new_count = jQuery('#the-list tr').length;
	            			var total_count = parseInt(jQuery("#lead-total-found").text());
	            			if (new_count != total_count){
	            				var total_count_display = "/" + total_count;
	            			} else {
	            				var total_count_display = "";
	            			}
	            			$("#lead-count-text").text("Displaying " + new_count + total_count_display + " Leads");


	                      },
	            	error: function(MLHttpRequest, textStatus, errorThrown){
	            			//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
	            			//die();
	            		}
	            }).done(function() {
			       run_lead_pull_again();
			    });
	}


	function format(state) {
		if (!state.id) return state.text; // optgroup
		var href = jQuery("#cta-" + state.id).attr("href");
		return state.text;
		//return state.text + "<a class='thickbox cta-select-preview-link' href='" + href + "'>(view)</a>";
	}
	jQuery("#cat").select2({
		placeholder: "Select One or more lead lists to view",
		allowClear: true,
		formatResult: format,
		formatSelection: format,
		escapeMarkup: function(m) { return m; }
	});
	// show conditional fields
	jQuery('select#orderby').on('change', function () {
		change_select_text()
	});
	//var onload = jQuery('select#wp_cta_content_placement').val();
	change_select_text();

	function change_select_text(){
		var this_val = jQuery("select#orderby").val();
		console.log(this_val);
		if (this_val === 'modified') {
			$('option[value="asc"]').text('Oldest modifed');
			$('option[value="desc"]').text('Most Recently Changed');
		} else if (this_val === 'title'){
			$('option[value="asc"]').text('A to Z');
			$('option[value="desc"]').text('Z to A');
		} else if (this_val === 'date'){
			$('option[value="asc"]').text('Oldest to Newest');
			$('option[value="desc"]').text('Newest to Oldest');
		}
	}

	jQuery("body").on('change', '#bulk-lead-controls', function () {
 		var value = $(this).val();
 		console.log(value);
 		$(".action").hide();
 		$("#" + value).show();
	});

		 // Fix Thickbox width/hieght
	    jQuery(function($) {
	        tb_position = function() {
	            var tbWindow = $('#TB_window');
	            var width = $(window).width();
	            var H = $(window).height();
	            var W = ( 1720 < width ) ? 1720 : width;

	            if ( tbWindow.size() ) {
	                tbWindow.width( W - 250 ).height( H - 105 );
	                $('#TB_iframeContent').width( W - 250 ).height( H - 105 );
	                tbWindow.css({'margin-left': '-' + parseInt((( W - 250 ) / 2),10) + 'px'});
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

	jQuery("#toggle").change(
		function() {
			jQuery("#the-list .lead-select-checkbox").each(
				function() {
					jQuery(this)[0].checked = jQuery("#toggle")[0].checked;
				}
			);

			if ( jQuery(this)[0].checked ) {
				jQuery(this).attr("title", "Select no posts");
			} else {
				jQuery(this).attr("title", "Select all posts");
			}
		}
	);
});
// Table Filter
(function(document) {
	'use strict';

	var LightTableFilter = (function(Arr) {

		var _input;

		function _onInputEvent(e) {
			_input = e.target;
			var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filter);
				});
			});
		}

		function _filter(row) {
			var text = row.textContent.toLowerCase(), val = _input.value.toLowerCase();
			row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
		}

		return {
			init: function() {
				var inputs = document.getElementsByClassName('light-table-filter');
				Arr.forEach.call(inputs, function(input) {
					input.oninput = _onInputEvent;
				});
			}
		};
	})(Array.prototype);

	document.addEventListener('readystatechange', function() {
		if (document.readyState === 'complete') {
			LightTableFilter.init();
		}
	});

})(document);