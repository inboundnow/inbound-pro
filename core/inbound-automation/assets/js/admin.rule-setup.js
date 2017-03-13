var InboundRulesJs = ( function() {

	var rule_id; /* placeholder for current rule ID */
	var trigger; /* placeholder for selected trigger */
	var ladda_trigger_filters; /* placeholder for ladda button */
	var ladda_add_action_block; /* placeholder for ladda button */
	var ladda_add_actions; /* placeholder for ladda button */
	var current_element; /* placeholder for current element being processed */
	var target_container; /* placeholder for html placements */
	var block_id; /* placeholder for current action block id  */
	var action_type; /* placeholder for current action type */

	var construct = {
		/**
		*  Initialize JS Class
		*/
		init: function() {
			/* Toggle cosmetics */
			jQuery('#minor-publishing').hide();
			jQuery('#publish').val('Save Rule');
			jQuery('#slugdiv').remove();

			/* disable dragging
            jQuery('.meta-box-sortables').sortable({
                disabled: true
            });*/

            jQuery('.postbox .hndle').css('cursor', 'pointer');

			/* Load Listeners */
			InboundRulesJs.listeners_navigation();
			InboundRulesJs.listeners_select_trigger();
			InboundRulesJs.listeners_add_trigger_filters();
			InboundRulesJs.listeners_add_action();
			InboundRulesJs.listeners_add_action_filter();

			/* Loads correct filters given trigger */
			InboundRulesJs.load_trigger_filters();
			InboundRulesJs.load_actions();
			InboundRulesJs.load_action_filters();

            /* make sure conditional fields render correctly */
            InboundRulesJs.toggle_conditional_fields();

		},

		/**
		 *  Loads listeners for toggling navigation
		 */
		listeners_navigation: function() {
			/* Switch Nav Containers on Tab Click */
			jQuery('body').on( 'click' , '.navlink' , function() {

				var container_id = this.id;

				/* Toggle correct nav tab */
				jQuery('.nav-pills li').removeClass('active');
				jQuery(this).parent('li').addClass('active');

				/* Toggle correct UI container */
				jQuery( '.nav-container' ).removeClass('nav-reveal');
				jQuery( '.nav-container' ).addClass('nav-hide');

                /* if Actions tab clicked show all action blocks */
				if (container_id == 'actions-container') {
					jQuery("div[id^='inbound_automation_actions_']").show();
                } else {
                    jQuery("div[id^='inbound_automation_actions_']").hide();
                    jQuery( '.'+container_id ).addClass('nav-reveal');
                }
			});

			/*  This js will hide/reveal action settings based on their hide/reveal rule definitions */
			jQuery('body').on('change' , '.table-action select, .table-action input[type="checkbox"], .table-action input[type="radio"]' , function() {
				var selector =  jQuery( this ).attr('data-id');
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
			    var action = jQuery(this);
                swal({
                    title: "Are you sure?",
                    text: "Are you sure you want to delete this action?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    closeOnConfirm: false
                }, function(){
                    var block_id = action.data('block-id');
                    var child_id = action.data('child-id');
                    jQuery('.action-sub-wrapper[data-block-id="'+block_id+'"][data-child-id="'+child_id+'"]').remove();
                    swal("Deleted!", "The action has been deleted.", "success");
                });
			});



            /* Deletes Filter */
            jQuery('body').on( 'click' , '.delete-filter' , function() {
                var row =  jQuery(this).parent().parent().parent().parent();
                swal({
                    title: "Are you sure?",
                    text: "Are you sure you want to delete this filter?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    closeOnConfirm: false
                }, function(){
                    row.remove();
                    swal("Deleted!", "The filter has been deleted.", "success");
                });

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


            /* Moves Action Block Up In Order */
            jQuery('body').on( 'click' , '.up-action-order' , function() {
                InboundRulesJs.block_id = jQuery(this).attr('data-block-id');
                InboundRulesJs.child_id = jQuery(this).attr('data-child-id');
                InboundRulesJs.action_type = jQuery(this).attr('data-action-type');
                InboundRulesJs.change_action_order('up' );
            });

            /* Moves Action Block Down In Order */
            jQuery('body').on( 'click' , '.down-action-order' , function() {
                InboundRulesJs.block_id = jQuery(this).attr('data-block-id');
                InboundRulesJs.child_id = jQuery(this).attr('data-child-id');
                InboundRulesJs.action_type = jQuery(this).attr('data-action-type');
                InboundRulesJs.change_action_order('down');
            });

		},
		/**
		 *  Loads listerns for toggling selected triggers
		 */
		listeners_select_trigger: function() {
			/* Update Trigger Condition */
			jQuery('body').on('change', '#trigger-dropdown' , function() {

				/* remove trigger filters on trigger change*/
				jQuery('.filter-container').remove();

				/* repopulate trigger filter dropdown */
				InboundRulesJs.load_trigger_filters();
				InboundRulesJs.load_actions();
				InboundRulesJs.load_action_filters();

			});
		},
		/**
		 *  Load listeners to add trigger filters to rule profile
		 */
		listeners_add_trigger_filters: function() {

			/* Adds Trigger Argument Filters to Trigger Conditions */
			jQuery('body').on( 'click' , '.add-trigger-filter' , function() {/* add spinner to button */
				if ( typeof InboundRulesJs.ladda_trigger_filters == 'undefined' ) {
					InboundRulesJs.ladda_trigger_filters = Ladda.create(document.querySelector( '#add-trigger-filter-button' ));
				}

				InboundRulesJs.ladda_trigger_filters.toggle();


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
						'trigger_id' : jQuery('#trigger-dropdown').val(),
						'trigger_filter_id' : jQuery(this).attr('id'),
						'action_block_id' : null,
						'child_id' : child_id,
						'defaults' : null
				} , 'html' , 'add_trigger_filter' );

			});

		},
        /**
         *  Adds listener to add trigger action filters
         */
        listeners_add_action_filter: function() {

            /* Adds Action DB Lookup Filters to Action Conditions */
            jQuery('body').on( 'click' , '.add-action-filter' , function() {

				/* remove well message if present */
				jQuery('.action-contitions-well').remove();

                var filter_id = jQuery(this).attr('data-value');
                var block_id = jQuery(this).parent().parent().parent().attr('data-block-id');
                var child_id = jQuery( "body" ).find( '.action-block-filters-container .table-filter:last' ).attr( 'data-child-id' );

                /* Create Original Filter Id */
                if ( typeof child_id == 'undefined' ) {
                    child_id = 1;
                } else {
                    child_id = parseInt(child_id) + 1;
                }

                /* Get html of trigger action filter */
                InboundRulesJs.run_ajax(  {
                    'action' : 'automation_build_db_lookup_filter',
                    'action_filter_id' : filter_id,
                    'action_block_id' : block_id,
                    'child_id' : child_id,
                    'defaults' : null
                } , 'html' , 'add_action_filter' );

            });
        },
		/**
		 *  Loads trigger action listeners
		 */
		listeners_add_action: function() {

			/* Adds Actions to Action Block Contaner*/
			jQuery('body').on( 'click' , '.add-action' , function() {


                var action_name = jQuery(this).attr('data-value');
                var block_id = jQuery(this).parent().parent().parent().attr('data-block-id');
                InboundRulesJs.action_type = jQuery(this).parent().parent().parent().attr('data-action-type');

                var child_id = jQuery( "body" ).find( '.action-block-then-actions-container .table-action:last' ).attr( 'data-child-id' );

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
					'action_type' : InboundRulesJs.action_type,
					'action_block_id' : block_id,
					'child_id' : child_id,
					'defaults' : null
				} , 'html' , 'add_action' );

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
         *  Adds trigger action filter HTML
         */
        add_action_filter: function( html ) {
            jQuery('.action-block-filters-container').append(html);
        },
		/**
		 *  Add action block HTML
		 */
		add_action_block: function( html ) {

			/* Disable spinner */
			InboundRulesJs.ladda_add_action_block.toggle();

			jQuery('#postbox-container-2').append(html);

			/* load available actions into action block */
			InboundRulesJs.load_actions();

			/* load DB filters into action block */
			InboundRulesJs.load_action_filters();
		},

		/**
		  *  Adds trigger action html
		  */
		add_action: function( html ) {

			/* Reveal Trigger Evaluation Options */
			jQuery('.action-block-'+ InboundRulesJs.action_type +'-actions-container').append(html);

            /* make sure conditional fields render correctly */
            InboundRulesJs.toggle_conditional_fields();
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
         *  Loads lead lookup filters based on selected trigger
         */
        load_action_filters: function() {

            this.trigger = jQuery('#trigger-dropdown').find(":selected").val();

            /* Hide Evaluation Options When 'Select Trigger' is Selected */
            if (this.trigger == '-1') {
                jQuery('.trigger-filter-evaluate').addClass('nav-hide');
            }

            /* disable filter dropdown momentarily */
            jQuery('.dropdown-add-filters').prop( 'disabled' , true );

            /* Run Ajax Call */
            InboundRulesJs.run_ajax({
                'action' : 'automation_get_db_lookup_filters',
                'trigger' : this.trigger
            } , 'json' , 'populate_action_filters' );

        },
        /**
         *  Loads available action blocks
         */
        load_actions: function() {

            this.trigger = jQuery('#trigger-dropdown').find(":selected").val();

            /* Run Ajax Call */
            var result = InboundRulesJs.run_ajax({
                'action' : 'automation_get_actions',
                'trigger' : this.trigger
            } , 'json' , 'populate_actions' );

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

		},
        /**
         *  Populates loaded data into input
         */
        populate_action_filters: function( filters ) {

            /* clear old options */
            jQuery('.action-filter-options').remove();

            /* populate new options */
            var html = '<ul class="dropdown-menu action-filter-options" role="menu" aria-labelledby="menu1">';
            var len = filters.length;

            for (var i = 0; i< len; i++) {
                html += '<li><a class="add-action-filter" data-value="'+filters[i].id+'">' + filters[i].label + '</a></li>';
            }

            jQuery('.trigger-filters').append(html);

            /* enable select box */
            jQuery('.trigger-filter-select-dropdown').prop( 'disabled' , false );

            /* disabled processing animation */
            this.ladda_trigger_filters.toggle();

            html  += '</ul>';

            /* add html to selet box */
            jQuery('.dropdown-add-filters').append(html);

        },
		/**
		 *
		 */
		populate_actions: function( actions ) {
            /* clear old options */
            jQuery('.action-options').remove();

            /* populate new options */
            var html = '<ul class="dropdown-menu action-options" role="menu" aria-labelledby="menu1">';
            var len = actions.length;

            for (var i = 0; i< len; i++) {
                html += '<li><a class="add-action" data-value="'+actions[i].id+'">' + actions[i].label + '</a></li>';
            }

			jQuery('.dropdown-add-actions').append(html);

		},
        /**
         * Promote action order
         */
        change_action_order: function (direction ) {

            var action = jQuery('.action-block-'+ InboundRulesJs.action_type +'-actions-container .action-sub-wrapper[data-block-id="'+ InboundRulesJs.block_id +'"][data-child-id="'+ InboundRulesJs.child_id +'"]');

            switch (direction) {
                case 'up':
                    action.prev().before(action);
                    break;
                case 'down':
                    action.next().after(action);
                    break;
            }

            InboundRulesJs.rebuild_priority_counts();
        },
        /**
         * Rebuilds action priority counters based on current positions
         */
        rebuild_priority_counts: function() {

            var block_id = 1;
            var child_id = 1;
            jQuery('.action-block-wrapper').each( function( index , element ) {

                /* rebuild action filters */
                child_id = 1;
                jQuery(this).find('.table-filter').each( function( i , el ) {

                    jQuery(this).attr('data-block-id' , block_id );
                    jQuery(this).attr('data-child-id' , child_id );

                    /* change data attributes */
                    jQuery(this).find('*').each(function() {
                        jQuery(this).attr('data-block-id', block_id );
                        jQuery(this).attr('data-child-id', child_id );

                        /* change name attributes if applicable */
                        var attr = jQuery(this).attr('name');
                        var id = jQuery(this).attr('data-id')
                        if ( typeof attr != 'undefined' && typeof id != 'undefined' ) {
                            var new_attr = id + '['+block_id+']['+child_id+']';
                            jQuery(this).attr( 'name', new_attr );
                        }
                    });

                    child_id++;
                });

                /* rebuild then actions */
                child_id = 1;
                jQuery(this).find('.action-sub-wrapper[data-action-type=then]').each( function( i , el ) {

                    jQuery(this).attr('data-block-id' , block_id );
                    jQuery(this).attr('data-child-id' , child_id );

                    /* change data attributes */
                    jQuery(this).find('*').each(function() {
                        jQuery(this).attr('data-block-id', block_id );
                        jQuery(this).attr('data-child-id', child_id );

                        /* change name attributes if applicable */
                        var attr = jQuery(this).attr('name');
                        if ( typeof attr != 'undefined' ) {
                            var new_attr;
                            if ( attr.indexOf("[]") > -1 ) {
                                new_attr = jQuery(this).attr('data-id') + '['+block_id+'][then]['+child_id+'][]';
                            } else {
                                new_attr = jQuery(this).attr('data-id') + '['+block_id+'][then]['+child_id+']';
                            }
                            jQuery(this).attr( 'name', new_attr );
                        }
                    });

                    child_id++;
                });

                /* rebuild else actions */
                child_id = 1;
                jQuery(this).find('.action-sub-wrapper[data-action-type=else]').each( function( i , el ) {

                    jQuery(this).attr('data-block-id' , block_id );
                    jQuery(this).attr('data-child-id' , child_id );

                    /* change data attributes */
                    jQuery(this).find('*').each(function() {
                        jQuery(this).attr('data-block-id', block_id );
                        jQuery(this).attr('data-child-id', child_id );

                        /* change name attributes if applicable */
                        var attr = jQuery(this).attr('name');
                        if ( typeof attr != 'undefined' ) {
                            var new_attr = jQuery(this).attr('data-id') + '['+block_id+'][else]['+child_id+']';
                            jQuery(this).attr( 'name', new_attr );
                        }

                    });

                    child_id++;
                });

                block_id++;
            });

        },
        /**
         * Makes sure conditional action fields are displaying correctly
         */
        toggle_conditional_fields: function() {

            jQuery('.tr-action-child').each( function( i, el ) {

                var  attr = jQuery(this).attr('data-reveal-selector');
                if ( typeof attr != 'undefined' ) {
                    jQuery(this).parent().find('[data-id='+attr+']').each( function() {
                        jQuery(this).trigger('change');
                    });
                }
            });

            /* init select2 */
            jQuery('select.select2').select2();
        },
        /**
         * Delete logs
         */
         delete_logs: function() {

            /* Run Ajax Call */
            var result = InboundRulesJs.run_ajax({
                'action' : 'automation_clear_logs',
                'rule_id' : InboundRulesJs.rule_id
            } , 'html' , 'clear_logs' );

        },
        /**
         * Clear logs
         */
        clear_logs: function() {
            jQuery('.tablesorter tr:gt(0)').remove();
            window.location.reload(true);
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
	}


	return construct;

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function() {

    /* Toggle Log Content - expands log data */
    if (inbound_rules.hook != 'admin_page_inbound_rule_logs') {
        InboundRulesJs.init();
        return;
    }

    jQuery('body').on('click', '.toggle-log-content', function () {
        var log_id = jQuery(this).attr('data-id');
        jQuery('#log-content-' + log_id).toggle();
    });

    /* Clears logs */
    jQuery('body').on( 'click' , '#clear-logs' , function() {
        jQuery(this).text('Clearing... please wait.');
        InboundRulesJs.rule_id = jQuery(this).data('rule-id');
        InboundRulesJs.delete_logs();
    });

    /* Reload logs iframe */
    jQuery('body').on( 'click' , '#refresh-logs' , function() {
        jQuery(this).text('Refreshing... please wait.');
        window.location.reload(true);
    });

});