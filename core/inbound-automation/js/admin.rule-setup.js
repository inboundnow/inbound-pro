var InboundRulesJs = ( function() {
	
	var trigger; /* placeholder for selected trigger */
	var ladda_trigger_filters; /* placeholder for ladda button */
	var ladda_add_action_block; /* placeholder for ladda button */
	var ladda_add_actions; /* placeholder for ladda button */
	var ladda_add_action_filters; /* placeholder for ladda button */
	var current_element; /* placeholder for current element being processed */
	var target_container; /* placeholder for html placements */
	
	var construct = {
		/**
		*  Initialize JS Class
		*/
		init: function() {
			/* Toggle cosmetics */
			jQuery('#minor-publishing').hide();
			jQuery('#publish').val('Save Rule');
	
			/* Load Accordions */
			InboundRulesJs.load_accordions();
			
			/* Load Listeners */
			InboundRulesJs.load_navigation_listeners();
			InboundRulesJs.load_trigger_listeners();
			InboundRulesJs.load_trigger_filter_listeners();
			InboundRulesJs.load_action_block_listeners();
			InboundRulesJs.load_trigger_action_listeners();
			InboundRulesJs.load_trigger_action_filter_listeners();
			
			/* Loads correct filters given trigger */
			InboundRulesJs.load_trigger_filters();
			InboundRulesJs.load_trigger_actions();
			InboundRulesJs.load_trigger_action_filters();
			
		},
		
		/**
		 *  Loads listeners for toggling navigation
		 */
		load_navigation_listeners: function() {
			/* Switch Nav Containers on Tab Click */
			jQuery('body').on( 'click' , '.navlink' , function() {

				var container_id = this.id;
				
				/* Toggle correct nav tab */
				jQuery('.nav-pills li').removeClass('active');
				jQuery(this).parent('li').addClass('active');
				
				/* Toggle correct UI container */
				jQuery( '.nav-container' ).removeClass('nav-reveal');
				jQuery( '.nav-container' ).addClass('nav-hide');
				jQuery( '.'+container_id ).addClass('nav-reveal');

			});
			
			/* Toggle Log Content - expands log data */
			jQuery('body').on( 'click' , '.toggle-log-content' , function() {
				var log_id = jQuery(this).attr('data-id');
				jQuery('#log-content-' + log_id ).toggle();
			});
			
			/*  This js will hide/reveal action settings based on their hide/reveal rule definitions */
			jQuery('body').on('change' , '.table-action select, .table-action input[type="checkbox"], .table-action input[type="radio"]' , function() {
				var selector =  jQuery( this ).data('id');
				var child_id =  jQuery( this ).data('child-id');
				var value = jQuery( this ).val();
				
				jQuery("table[data-child-id='"+child_id+"']").find("[data-reveal-selector='" + selector + "']").each(function() {
					
					var evaluate = jQuery(this).data('reveal-value');
					if (value == evaluate) {
						jQuery(this).show();
					} else {
						jQuery(this).hide();
					}
				});
			});
			
			
			/* Deletes Action */
			jQuery('body').on( 'click' , '.delete-action' , function() {
				if(confirm('Are sure want to delete this sub-action?')){
					var block_id = jQuery(this).data('block-id');
					var child_id = jQuery(this).data('child-id');
					jQuery('.action-sub-wrapper[data-block-id="'+block_id+'"][data-child-id="'+child_id+'"]').remove();
				} else {	
					return false;
				}
			});
	
			
		},
		/**
		 *  Loads listerns for toggling selected triggers
		 */
		load_trigger_listeners: function() {
			/* Update Trigger Condition */
			jQuery('body').on('change', '#trigger-dropdown' , function() {

				/* remove trigger filters on trigger change*/
				jQuery('.filter-container').remove();

				/* repopulate trigger filter dropdown */
				InboundRulesJs.load_trigger_filters();
				InboundRulesJs.load_trigger_actions();
				InboundRulesJs.load_trigger_action_filters();

			});					
		},
		/**
		 *  Load listeners to add trigger filters to rule profile
		 */
		load_trigger_filter_listeners: function() {		
			
			/* Adds Trigger Argument Filters to Trigger Conditions */
			jQuery('body').on( 'click' , '.add-trigger-filter' , function() {/* add spinner to button */
				if ( typeof InboundRulesJs.ladda_trigger_filters == 'undefined' ) {
					InboundRulesJs.ladda_trigger_filters = Ladda.create(document.querySelector( '#add-trigger-filter-button' ));
				}
				InboundRulesJs.ladda_trigger_filters.toggle();
				
				var filter_id = jQuery(this).attr('id');
				var filter_input_filter_id_name = 'trigger_argument_id';
				var filter_input_key_name = 'trigger_filter_key';
				var filter_input_compare_name = 'trigger_filter_compare';
				var filter_input_value_name = 'trigger_filter_value';

				var child_id = jQuery( "body" ).find( '#argument-filters-container .table-filter:last' ).attr( 'data-child-id' );


				/* Create Original Filter Id */
				if ( typeof child_id == 'undefined' ) {
					child_id = 1;
				} else {
					child_id = parseInt(child_id) + 1;
				}

				/* Get html of selected trigger filter being added to rule */
				InboundRulesJs.run_ajax( {
						'action' : 'automation_build_trigger_filter',
						'filter_id' : filter_id,
						'action_block_id' : null,
						'child_id' : child_id,
						'filter_input_filter_id_name' : 'trigger_argument_id',
						'filter_input_key_name' : 'trigger_filter_key',
						'filter_input_compare_name' : 'trigger_filter_compare',
						'filter_input_value_name' : 'trigger_filter_value',
						'defaults' : null
				} , 'html' , 'add_trigger_filter' );
			
			});
			
		},
		/**
		 *  Adds Action Block Listeners
		 */
		load_action_block_listeners: function() {		
			/* Adds Action Block*/
			jQuery('body').on( 'click' , '.add-action-block' , function() {
				/* Add spinner to button */
				if ( typeof InboundRulesJs.ladda_add_action_block == 'undefined' ) {
					InboundRulesJs.ladda_add_action_block = Ladda.create(document.querySelector( '#add-action-block-button' ));			
				}
				InboundRulesJs.ladda_add_action_block.toggle();
				
				var action_block_type = this.id;
				var action_block_id = jQuery("body").find('.action-block:last').attr('data-action-block-id');

				/* Create Original Filter Id */
				if ( typeof action_block_id == 'undefined' ) {
					action_block_id = 1;
				} else {
					action_block_id = parseInt(action_block_id) + 1;
				}
				
				/* Get html of selected trigger filter being added to rule */
				InboundRulesJs.run_ajax( {
					'action' : 'automation_build_action_block',
					'action_block_type' : action_block_type,
					'action_block_id' : action_block_id
				} , 'html' , 'add_action_block' );
				
				
			});
		},
		/**
		 *  Loads trigger action listeners
		 */
		load_trigger_action_listeners: function() {
			
			/* Adds Actions to Action Block Contaner*/
			jQuery('body').on( 'click' , '.add-action' , function() {
				
				/* Create spinning affect */
				if ( typeof InboundRulesJs.ladda_add_action == 'undefined' ) {
					InboundRulesJs.ladda_add_actions =  Ladda.create( this );
				}
				InboundRulesJs.ladda_add_actions.toggle();
				
				var dropdown_id = jQuery(this).attr('data-dropdown-id');
				var action_name = jQuery( '#' + dropdown_id ).find( ":selected" ).val();

				InboundRulesJs.target_container = jQuery(this).attr('data-action-container');
				var input_action_name_name = jQuery(this).attr('data-input-action-type-name');

				var action_block_id = jQuery(this).attr('data-action-block-id');
				var action_type = jQuery(this).attr('data-action-type');
				var child_id = jQuery( "body" ).find( '#' + InboundRulesJs.target_container + ' .table-action:last' ).attr( 'data-child-id' );


				/* Create Original Filter Id */
				if ( typeof child_id == 'undefined' ) {
					child_id = 1;
				} else {
					child_id = parseInt(child_id) + 1;
				}

				/* Get html of of action filter */
				InboundRulesJs.run_ajax(  {
					'action' : 'automation_build_action',
					'action_name' : action_name,
					'action_type' : action_type,
					'action_block_id' : action_block_id,
					'child_id' : child_id,
					'input_action_name_name' : input_action_name_name,
					'defaults' : null
				} , 'html' , 'add_trigger_action' );
				
			});
		
		},
		
		/**
		 *  Adds listener to add trigger action filters
		 */
		load_trigger_action_filter_listeners: function() {			

			/* Adds Action DB Lookup Filters to Action Conditions */
			jQuery('body').on( 'click' , '.add-action-filter' , function() {
				
				/* Add spinner to button */
				if ( typeof InboundRulesJs.ladda_add_action_filters == 'undefined' ) {
					InboundRulesJs.ladda_add_action_filters = Ladda.create(document.querySelector( '.add-action-filter' ));
				}
				InboundRulesJs.ladda_add_action_filters.toggle();
				
				var dropdown_id = jQuery(this).attr('data-dropdown-id');
				var filter_id = jQuery( '#' + dropdown_id ).find( ":selected" ).val();

				InboundRulesJs.target_container = jQuery(this).attr('data-filter-container');
				var filter_input_filter_id_name = jQuery(this).attr('data-filter-input-filter-id');
				var filter_input_key_name = jQuery(this).attr('data-filter-input-key-name');
				var filter_input_compare_name = jQuery(this).attr('data-filter-input-compare-name');
				var filter_input_value_name = jQuery(this).attr('data-filter-input-value-name');

				var child_id = jQuery( "body" ).find( '#' + InboundRulesJs.target_container + ' .table-filter:last' ).attr( 'data-child-id' );

				/* Create Original Filter Id */
				if ( typeof child_id == 'undefined' ) {
					child_id = 1;
				} else {
					child_id = parseInt(child_id) + 1;
				}

				
				/* Get html of trigger action filter */
				InboundRulesJs.run_ajax(  {
					'action' : 'automation_build_db_lookup_filter',
					'filter_id' : filter_id,
					'action_block_id' : null,
					'child_id' : child_id,
					'filter_input_filter_id_name' : filter_input_filter_id_name,
					'filter_input_key_name' : filter_input_key_name,
					'filter_input_compare_name' : filter_input_compare_name,
					'filter_input_value_name' : filter_input_value_name,
					'defaults' : null
				} , 'html' , 'add_trigger_action_filter' );

			});

		
		},
		/**
		 *  Adds new trigger filter to trigger filters container
		 */
		add_trigger_filter: function( html ) {

			/* Disable ladda button spinner */
			InboundRulesJs.ladda_trigger_filters.toggle();
			
			/* Reveal Trigger Evaluation Options */
			jQuery('.trigger-filter-evaluate').removeClass('nav-hide');
			jQuery('#argument-filters-container').append(html);
		},
		
		/**
		 *  Add action block HTML
		 */
		add_action_block: function( html ) {

			/* Disable spinner */
			InboundRulesJs.ladda_add_action_block.toggle();
			
			jQuery('.actions-container').append(html);
			var index = 0;
			index = jQuery(".actions-container h3").length - 1; 
			
			jQuery(".actions-container").accordion({
				header: '> div > h3',
				collapsible: true,
				active: index,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h3",
				stop: function( event, ui ) {
					ui.item.children( "h3" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			}); 
			jQuery('.actions-container').accordion('refresh');
			
			/* load available actions into action block */
			InboundRulesJs.load_trigger_actions();
			
			/* load DB filters into action block */
			InboundRulesJs.load_trigger_action_filters();
		
		},
		
		/**
		 *  Adds trigger action filter HTML 
		 */
		add_trigger_action_filter: function( html ) {
			jQuery('#'+ InboundRulesJs.target_container).append(html);
			var index = 0;
			index = jQuery("#" + InboundRulesJs.target_container + " h4").length - 1; 
			jQuery(".action-block-filters-container").accordion({
				header: '> div > h4',
				collapsible: true,
				active: index,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h4",
				stop: function( event, ui ) {
					ui.item.children( "h3" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			});
			jQuery('.action-block-filters-container').accordion('refresh');
			
			/* Stop spinner */
			if ( typeof InboundRulesJs.ladda_add_action_filters != 'undefined' ) {
				InboundRulesJs.ladda_add_action_filters.toggle();
			}
			
		},		
		
		/**
		  *  Adds trigger action html
		  */
		add_trigger_action: function( html ) {
			/* Disable Spinner */
			InboundRulesJs.ladda_add_actions.toggle();
			
			/* Reveal Trigger Evaluation Options */
			jQuery('#'+ InboundRulesJs.target_container).append(html);
			var index = 0;
			index = jQuery('#'+ InboundRulesJs.target_container +" h4").length - 1; 
			jQuery('.action-block-actions-container').accordion({
				header: '> div > h4',
				collapsible: true,
				active: index,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h4",
				stop: function( event, ui ) {
					ui.item.children( "h4" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			});
			jQuery(".action-block-actions-container").accordion("refresh");
			
			/*  Initialize Select2	*/
			jQuery('.select2').select2();

			
		},
		/**
		*  Loads the filters available for the selected trigger
		*/
		load_trigger_filters: function() {
			/* enabable processing anaimation */
			if ( typeof InboundRulesJs.ladda_trigger_filters == 'undefined' ) {
				this.ladda_trigger_filters = Ladda.create(document.querySelector( '#add-trigger-filter-button' ));
			}
			this.ladda_trigger_filters.toggle();
			
			this.trigger = jQuery('#trigger-dropdown').find(":selected").val();

			/* Hide Evaluation Options When 'Select Trigger' is Selected */
			if (this.trigger == '-1') {
				jQuery('.trigger-filter-evaluate').addClass('nav-hide');
			}

			/* disable filter dropdown momentarily */
			jQuery('.trigger-filter-select-dropdown').prop( 'disabled' , true );

			/* Run Ajax Call */
			InboundRulesJs.run_ajax({
				'action' : 'automation_get_trigger_arguments',
				'trigger' : this.trigger
			} , 'json' , 'populate_trigger_filters' );

			
		},
		/**
		 *  Populates trigger filters based on selected trigger
		 */
		populate_trigger_filters: function( filters ) {
			
			/* clear old options */
			jQuery('.trigger-filters').empty();
			
			/* populate new options */
			var html = '';
			var len = filters.length;

			for (var i = 0; i< len; i++) {
				html += '<li><a class="add-trigger-filter" id="' + filters[i].id + '">' + filters[i].label + '</a></li>';
			}
			
			jQuery('.trigger-filters').append(html);

			/* enable select box */
			jQuery('.trigger-filter-select-dropdown').prop( 'disabled' , false );
			
			/* disabled processing animation */
			this.ladda_trigger_filters.toggle();
		
		},		
		/**
		 *  Loads lead lookup filters based on selected trigger
		 */
		load_trigger_action_filters: function() {
			
			/* enabable processing anaimation */
			if ( jQuery('.add-action-filter').length ) {
				if ( typeof InboundRulesJs.ladda_add_action_filters == 'undefined' ) {
					InboundRulesJs.ladda_add_action_filters = Ladda.create(document.querySelector( '.add-action-filter' ));
				}
				InboundRulesJs.ladda_add_action_filters.toggle();
			}
			
			this.trigger = jQuery('#trigger-dropdown').find(":selected").val();

			/* Hide Evaluation Options When 'Select Trigger' is Selected */
			if (this.trigger == '-1') {
				jQuery('.trigger-filter-evaluate').addClass('nav-hide');
			}

			/* disable filter dropdown momentarily */
			jQuery('.action-filter-select-dropdown').prop( 'disabled' , true );

			/* Run Ajax Call */
			InboundRulesJs.run_ajax({
				'action' : 'automation_get_db_lookup_filters',
				'trigger' : this.trigger
			} , 'json' , 'populate_trigger_action_filters' );
		
		},
		/**
		 *  Populates loaded data into input
		 */
		populate_trigger_action_filters: function( filters ) {

			jQuery('.add-action-block').show();
					
			/* Hide Conditional Action Block Options When No DB Lookups Present */
			if ( filters[0].id == 0 ) {							
				jQuery('#if-then').hide();
				jQuery('#if-then-else').hide();
				jQuery('#while').hide();
			}
			
			/* clear old options */
			jQuery('.action-filter-select-dropdown option:gt(0)').remove();

			/* populate new options */
			var html = '';
			var len = filters.length;

			for (var i = 0; i< len; i++) {
				html += '<option value="' + filters[i].id + '">' + filters[i].label + '</option>';
			}	

			/* add html to selet box */
			jQuery('.action-filter-select-dropdown').append(html);

			/* enable select box */
			jQuery('.action-filter-select-dropdown').prop( 'disabled' , false );
			
			/* disable ladda animation */
			if ( typeof InboundRulesJs.ladda_add_action_filters != 'undefined') {
				InboundRulesJs.ladda_add_action_filters.toggle();
			}
		
		},		
		/**
		 *  Loads available action blocks
		 */
		load_trigger_actions: function() {
			/* enabable processing anaimation */
			if ( document.querySelector( '.add-action' ) ) {
				if ( typeof InboundRulesJs.ladda_add_actions == 'undefined' ) {
					InboundRulesJs.ladda_add_actions = Ladda.create(document.querySelector( '.add-action' ));
				}
				InboundRulesJs.ladda_add_actions.toggle();
			}
			
			this.trigger = jQuery('#trigger-dropdown').find(":selected").val();

			/* disable filter dropdown momentarily */
			jQuery('.action-select-dropdown').prop( 'disabled' , true );

			/* Run Ajax Call */
			var result = InboundRulesJs.run_ajax({
				'action' : 'automation_get_trigger_actions',
				'trigger' : this.trigger
			} , 'json' , 'populate_trigger_actions' );
			
			/* hides/reveals correct action settings */
			jQuery('.table-action select, .table-action input[type="checkbox"], .table-action input[type="radio"]' ).trigger('change');
		
			/* loads select2 on action settings */
			if (jQuery('.select2').length) {
				jQuery('.select2').select2();
			}
		},
		/**
		 *  
		 */
		populate_trigger_actions: function( results ) {

			/* clear old options */
			jQuery('.action-select-dropdown option:gt(0)').remove();

			/* populate new options */
			var html = '';
			var len = results.length;

			for (var i = 0; i< len; i++) {
				html += '<option value="' + results[i].id + '">' + results[i].label + '</option>';
			}
			
			jQuery('.action-select-dropdown').append(html);


			/* enable select box */
			jQuery('.action-select-dropdown').prop( 'disabled' , false );
			
			/* disable ladda animation */
			if ( typeof InboundRulesJs.ladda_add_actions != 'undefined' ) {
				InboundRulesJs.ladda_add_actions.toggle();
			}
			
		},
		/**
		 *  Runs AJAX
		 */
		run_ajax: function( data, dataType , callback ) {
			
			jQuery.ajax({
				type: "GET",
				url: ajaxurl,
				dataType: dataType,
				async:true,
				data : data,
				success: function( result ) {
					InboundRulesJs[callback](result);
				}
			});		
		},
		/**
		 *  Loads accordions
		 */
		load_accordions: function() {
			
			/* Turn on accordion for actions */	
			jQuery(".actions-container").accordion({
				header: '> div > h3',
				collapsible: true,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h3",
				stop: function( event, ui ) {
					ui.item.children( "h3" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			});
			
			/* Turn on accordion for action blocks */	
			jQuery('.action-block-actions-container').accordion({
				header: '> div > h4',
				collapsible: true,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h4",
				stop: function( event, ui ) {
					ui.item.children( "h4" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			});
			
			/* Turn on Accordion for action filters */
			jQuery(".action-block-filters-container").accordion({
				header: '> div > h4',
				collapsible: true,
				heightStyle: "content"
			}).sortable({
				axis: "y",
				handle: "h4",
				stop: function( event, ui ) {
					ui.item.children( "h3" ).triggerHandler( "focusout" );
					jQuery( this ).accordion( "refresh" );
				}
			});
		
		},
		
	}
	
	
	return construct;			

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function() {
	
	InboundRulesJs.init();

	

	/* Deletes Filter */
	jQuery('body').on( 'click' , '.delete-filter' , function() {
		if(confirm('Are sure want to delete this filter?')){
			var del_id = jQuery(this).attr('id');
			del_id = del_id.replace('delete-filter-','');
			jQuery(this).parent().parent().parent().parent().remove();
			jQuery('h4#header-filter-' + del_id).remove();
		} else {	
			return false;
		}
	});

	/* Deletes Action Block */
	jQuery('body').on( 'click' , '.delete-action-block' , function() {
		if(confirm('Are sure want to delete this action block?')){
			var del_id = jQuery(this).attr('id');
			del_id = del_id.replace('delete-action-block-','');
			jQuery('#action-block-' + del_id).remove();
			//jQuery('h3#header-block-' + del_id).remove();
		} else {	
			return false;
		}
	});
	



	
	
	
});