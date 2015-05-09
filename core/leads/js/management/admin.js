jQuery(document).ready(function($) {

	/* fade wrap in */
	jQuery(".wrap").fadeIn(1000);

	/* initiate table sorter */
	var table = jQuery("#lead-manage-table").length;
	if (table > 0) {
		new Tablesort(document.getElementById('lead-manage-table'));
	}

	/* hide/reveal date range selector */
	jQuery("body").on('change', '#range', function () {
 		var value = jQuery(this).val();
		switch( value ) {
			case 'all':
				jQuery('.custom-range').hide();
				break;
			case 'custom':
				jQuery('.custom-range').show();
				break;
		}
	});


	/* initiate selectable */
	jQuery( "#the-list" ).selectable({
	  //appendTo: "#the-list",
	  cancel: "a, i, .lead-email",
	  stop: function( event, ui ) {
		console.log('stopped');
		jQuery('.ui-selected input[type=checkbox]').each(function(){
			//jQuery(this).attr('checked', true);
			var el = jQuery(this);
			check_boxes(el);
		});
	  },
	  selected: function( event, ui ) {

	  },
	  unselected: function( event, ui ) {

	  }
	});


	jQuery("body").on('click', '#the-list tr', function () {
		var checkbox = jQuery(this).find('input');
		var checked = checkbox.attr('checked');
		if (checked) {
		   checkbox.attr('checked', false);
		   jQuery(this).removeClass('SELECTED-ROW');
		   jQuery(this).removeClass('ui-selected');
		  	jQuery(this).find('.ui-selected').removeClass('ui-selected');
		} else {
		   checkbox.attr('checked', true);
		   jQuery(this).addClass('SELECTED-ROW');
		}
	 });

	function check_boxes(el) {
		var checkbox = jQuery(el);
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

		var checkbox = jQuery(this);
		var checked = checkbox.attr('checked');
		if (checked) {
		   checkbox.attr('checked', false);
		} else {
		   checkbox.attr('checked', true);
		}
	 });


	jQuery("body").on('click', '.remove-from-taxonomy', function () {
		var clicked = jQuery(this);
		var lead_id = clicked.attr('data-lead-id');
		var taxonomy = clicked.attr('data-taxonomy');
		var taxonomy_id = clicked.attr('data-taxonomy-id');
		jQuery.ajax({
			type: 'POST',
			context: this,
			url: bulk_manage_leads.admin_url,
			data: {
					action: 'leads_delete_from_taxonomy',
					lead_id: lead_id,
					taxonomy: taxonomy,
					taxonomy_id: list_id
				},
			success: function(data){
					jQuery(this).parent().remove();
				   },
			error: function(MLHttpRequest, textStatus, errorThrown){
					//alert(MLHttpRequest+' '+errorThrown+' '+textStatus);
					//die();
				}
		 });
	});


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
		jQuery('.inbound-lead-filters').css('margin-top', "10px");
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
		var end = jQuery('#end-of-list').length;

		if (end == 1) {
	     	//clearInterval(interval);
	     	console.log('all leads loaded');
	     	jQuery(".lead-spinner").addClass('leads-done');
	     	return false;
	    }

	    console.log('running ajax');

		var data = {
			page: 'lead_management',
			action: 'leads_ajax_load_more_leads',
			order: jQuery('#order').val(),
			orderby: jQuery('#orderby').val(),
			relation: jQuery('#relation').val(),
			range: jQuery('#range').val(),
			day_start: jQuery('#day_start').val(),
			month_start: jQuery('#month_start').val(),
			year_start: jQuery('#year_start').val(),
			day_end: jQuery('#day_end').val(),
			month_end: jQuery('#month_end').val(),
			year_end: jQuery('#year_end').val(),
			t: jQuery('#t').val(),
			paged: parseInt(jQuery('#paged-current').text()) + 1,
		}

		for ( tax in bulk_manage_leads.taxonomies ) {
			if (jQuery('#' + tax).val()) {
				data[tax] = jQuery('#' + tax).val();
			}
		}
        console.log(data);
		jQuery.ajax({
			type: 'POST',
			context: this,
			url: bulk_manage_leads.admin_url,
			data: data,
			success: function(data){

				var num = parseInt(jQuery('#paged-current').text()) + 1;
				jQuery('#paged-current').text(num);

				if (data != "0") {
					jQuery("#the-list").append(data);
				} else {
					jQuery('#end-of-list').remove();
					jQuery("#lead-manage-table").after('<div id="end-of-list" style="width:100%; text-align:center;">End of list!</div>');
				}

				var new_count = jQuery('#the-list tr').length;

				var total_count = parseInt(jQuery("#lead-total-found").text());

				if (new_count != total_count){
					var total_count_display = "/" + total_count;
				} else {
					var total_count_display = "";
				}
				jQuery("#lead-count-text").text("Displaying " + new_count + total_count_display + " Leads");

			},
			error: function(MLHttpRequest, textStatus, errorThrown){

			}
		}).done(function() {
		   run_lead_pull_again();
		});
	}


	jQuery("body").on('change', '#bulk-lead-controls', function () {
 		var value = jQuery(this).val();
 		console.log(value);
 		jQuery(".action").hide();
 		jQuery("#" + value).show();
	});

	 // Fix Thickbox width/hieght
	jQuery(function($) {
		tb_position = function() {
			var tbWindow = jQuery('#TB_window');
			var width = jQuery(window).width();
			var H = jQuery(window).height();
			var W = ( 1720 < width ) ? 1720 : width;

			if ( tbWindow.size() ) {
				tbWindow.width( W - 250 ).height( H - 105 );
				jQuery('#TB_iframeContent').width( W - 250 ).height( H - 105 );
				tbWindow.css({'margin-left': '-' + parseInt((( W - 250 ) / 2),10) + 'px'});
				if ( typeof document.body.style.maxWidth != 'undefined' )
					tbWindow.css({'top':'40px','margin-top':'0'});
				//jQuery('#TB_title').css({'background-color':'#fff','color':'#cfcfcf'});
			};

			return jQuery('a.thickbox').each( function() {
				var href = jQuery(this).attr('href');
				if ( ! href ) return;
				href = href.replace(/&width=[0-9]+/g, '');
				href = href.replace(/&height=[0-9]+/g, '');
				jQuery(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );
			});

		};

		jQuery('a.thickbox').click(function(){
			if ( typeof tinyMCE != 'undefined' &&  tinyMCE.activeEditor ) {
				tinyMCE.get('content').focus();
				tinyMCE.activeEditor.windowManager.bookmark = tinyMCE.activeEditor.selection.getBookmark('simple');
			}

		});

		jQuery(window).resize( function() { tb_position() } );
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