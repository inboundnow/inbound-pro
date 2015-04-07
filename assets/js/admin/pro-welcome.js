var InboundProWelcomeJs = ( function() {

	var search_container; /* element that wraps all settings and is shuffle js ready */
	var target; /* target setting if available */
	var input;
	var datatype;
	var value;
	var timer;

	var construct = {
		/**
		*  Initialize JS Class
		*/
		init: function() {
			this.addListeners();
			this.setVars();
			this.setupSearching();
			this.initShuffleJs();
			this.initShuffleCustomFields();

		},
		/**
		 *  Sets static vars
		 */
		setVars: function() {
			this.search_container = jQuery('#grid');
			this.target = InboundProWelcomeJs.getUrlParam('setting');
		},
		/**
		 *  Sets up setting searching
		 */
		setupSearching: function() {
			// Advanced filtering
			jQuery('body').on('keyup change', '.filter-search' , function() {
				var val = this.value.toLowerCase().trim();
				InboundProWelcomeJs.search_container.shuffle('shuffle', function($el, shuffle) {
					var text = $el.data('keywords').toLowerCase();
					return text.indexOf(val) !== -1;
				});
			});
		},
		/**
		 *  Shuffle if there is a 'setting' param
		 */
		initShuffleJs: function() {
			if ( this.target ) {
				jQuery('.filter-search').val(InboundProWelcomeJs.target);
				jQuery('.filter-search').trigger('change');
				jQuery('.filter-search').trigger('keyup');
			}
		},
		/**
		 *  Shuffle if there is a 'setting' param
		 */
		initShuffleCustomFields: function() {

			jQuery(".field-map").sortable( {
				stop: function() {					
					InboundProWelcomeJs.updateCustomFieldPriority();
					InboundProWelcomeJs.updateCustomFields();
				}
			});

		},
		/**
		 *  Add UI Listeners
		 */
		addListeners: function() {

			/* add listeners for non array data changes */
			jQuery( document ).on( 'change unfocus propertychange paste' , 'input[data-special-handler!="true"],dropdown,radio' , function() {

				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );

				/* Save Data on Change */
				switch ( event.type ) {
					case 'paste':
						setTimeout( function() {
							InboundProWelcomeJs.updateSetting();
						} , 250 );
						break;
					default:
						InboundProWelcomeJs.updateSetting();
						break;
				}

			});

			/* add listeners for custom field changes */
			jQuery( document ).on( 'change unfocus propertychange keyup' , 'input[data-field-type="mapped-field"],select[data-field-type="mapped-field"]' , function() {

				/* format field key */
				if ( jQuery(this).hasClass('field-key') ) {
					jQuery(this).val( jQuery(this).val().replace( / /g , '_' ).toLowerCase() );
				}
				
				if (InboundProWelcomeJs.timer == true && event.type != 'propertychange' ) {
					return;
				} 

				InboundProWelcomeJs.timer = true;

				setTimeout( function() {
					InboundProWelcomeJs.updateCustomFields();
					InboundProWelcomeJs.timer = false;
				} , 500 );
				
			});

			/* add listenrs for license key validation */
			jQuery( document ).on( 'keyup' , '.license' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );

				/* dont do squat if the license key does not reach a certain length */
				if (InboundProWelcomeJs.input.val().length < 10 ) {
					return;
				}

				/* Validate License Key */
				InboundProWelcomeJs.validateLicenseKey();

				/* Save Data on Change */
				InboundProWelcomeJs.updateSetting();
			});

			/* add listeners for 'add new custom fields  */
			jQuery( 'body' ).on( 'click' , '.unauth' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );
				InboundProWelcomeJs.deauthorizeOauth();
			});

			/* Add listener to delete custom field */
			jQuery( 'body' ).on( 'click' , '.delete-custom-field' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );
				InboundProWelcomeJs.removeCustomFieldConfirm();
			});

			/* Add listener to delete custom field */
			jQuery( 'body' ).on( 'click' , '.delete-custom-field-confirm' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );
				InboundProWelcomeJs.removeCustomField();
			});

			/* Add listeners for oauth unauthorize buttons */
			jQuery(document).on('submit','#add-new-field-container',function (e) {
				/* prevent the form from doing a submit */
				e.preventDefault();

				InboundProWelcomeJs.addCustomLeadField();
				return false;
			})

			/* Add listeners for oauth authorize button */
			jQuery( 'body' ).on( 'click' , '.oauth' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );
			});


			/* Add listeners for oauth close button */
			jQuery(document).on('tb_unload', '#TB_window', function(e){
				/* listen for success */
				var success = jQuery('iframe').contents().find('.success');

				if (success.length) {
					InboundProWelcomeJs.setAuthorized();
				}
			});

		},
		/**
		 *  Save Input Data
		 */
		updateSetting: function() {
			/* serialize input data */
			var serialized = this.prepareSettingData();

			jQuery.ajax({
				type: "POST",
				url: ajaxurl ,
				data: {
					action: 'inbound_pro_update_setting',
					input: serialized
				},
				dataType: 'html',
				timeout: 10000,
				success: function (response) {

				},
				error: function(request, status, err) {
					alert(status);
				}
			});
		},
		/**
		 *  Save Custom Fields
		 */
		updateCustomFields: function() {
			setTimeout( function() {
				var form = jQuery('#custom-fields-form').clone();
				var originalSelects =  jQuery('#custom-fields-form').find('select');
				
				/* removed disabled attribute from cloned object */
				form.find(':input:disabled').val().replace( / /g , '_' ).toLowerCase();

				/* make sure the select values are correct */
				form.find('select').each(function(index, item) {
					 jQuery(this).val( originalSelects.eq(index).val() );
				});
			
			
				jQuery.ajax({
					type: "POST",
					url: ajaxurl ,
					data: {
						action: 'inbound_pro_update_custom_fields',
						input: form.serialize()
					},
					dataType: 'html',
					timeout: 10000,
					success: function (response) {

					},
					error: function(request, status, err) {
						alert(status);
					}
				});
			} , 500 );
		},
		/**
		 *  Rebuilds priority for custom fields
		 */
		updateCustomFieldPriority:  function() {
			var i = 0;
			setTimeout( function() {
				jQuery.each( jQuery('.map-row') , function() {
					jQuery(this).attr('data-priority' , i );
					jQuery(this).find('.field-priority').val( i );
					i++;
				});
			} , 200 );
		},
		/**
		 *  Deauthorize oauth
		 */
		deauthorizeOauth: function() {
			/* serialize input data */
			var serialized = this.prepareSettingData();

			jQuery.ajax({
				type: "POST",
				url: ajaxurl ,
				data: {
					action: 'revoke_oauth_tokens',
					input: serialized
				},
				dataType: 'html',
				timeout: 10000,
				success: function (response) {
					var group = InboundProWelcomeJs.input.data('field-group');
					var button =jQuery('.oauth[data-field-group="'+group+'"]');
					button.removeClass('hidden');
					InboundProWelcomeJs.input.addClass('hidden');
				},
				error: function(request, status, err) {
					alert(status);
				}
			});
		},
		/**
		 *  Mark as authorized
		 */
		setAuthorized: function() {
			var group = InboundProWelcomeJs.input.data('field-group');

			var button = jQuery('.unauth[data-field-group="'+group+'"]');
			button.removeClass('hidden');

			InboundProWelcomeJs.input.addClass('hidden');
		},
		/**
		 * Prepare a serialized format of settings data
		 */
		prepareSettingData: function() {
			var dataarr = new Array();

			/* get data attributes */
			for(var i in InboundProWelcomeJs.input.data()) {
				var subarr = new Array();
				subarr['name'] = i;
				subarr['value'] = InboundProWelcomeJs.input.data()[i];
				dataarr.push(subarr);
			}

			/* get value */
			switch ( InboundProWelcomeJs.input.data('field-type') ) {
				case 'checkbox':
					 var value = jQuery('#' + InboundProWelcomeJs.input.attr("id") +' input:checkbox:checked').map(function(){  return jQuery(this).val();}).get();
					 break;
				case 'select2':
					 var value = jQuery('#'+ InboundProWelcomeJs.input.attr('id') +' option:selected').map(function(){  return jQuery(this).val();}).get();
					break;
				default:
					var value = InboundProWelcomeJs.input.val();
					break;
			}

			/* set value */
			var subarr = new Array();
			subarr['name'] = 'value';
			subarr['value'] = value;
			dataarr.push(subarr);


			/* get name attr */
			var subarr = new Array();
			subarr['name'] = 'name';
			subarr['value'] = InboundProWelcomeJs.input.attr('name');
			dataarr.push(subarr);

			return jQuery.param( InboundProWelcomeJs.input.serializeArray().concat(dataarr));
		},
		/**
		 *  Validate API Key
		 */
		validateLicenseKey: function() {
			switch(this.pollAPI(InboundProWelcomeJs.input.val())) {
				case true:
					InboundProWelcomeJs.input.removeClass('invalid');
					InboundProWelcomeJs.input.addClass('valid');
					jQuery('.invalid-icon').remove();
					jQuery('.valid-icon').remove();
					jQuery('<i>' , { class:"fa fa-check valid-icon tooltip" , title:"License Key Is Invalid" }).appendTo('.license-key');
					break;
				case false:
					InboundProWelcomeJs.input.removeClass('valid');
					InboundProWelcomeJs.input.addClass('invalid');
					jQuery('.valid-icon').remove();
					jQuery('.invalid-icon').remove();
					jQuery('<i>' , { class:"fa fa-times-circle invalid-icon tooltip" , title:"License Key Is Invalid" }).appendTo('.license-key');
					break;
			}
		},
		/**
		 *  Send license to API for validation
		 */
		pollAPI: function() {
			if ( InboundProWelcomeJs.input.val().indexOf('x') == -1 ) {
				return true;
			} else {
				 return false;
			}
		},

		/**
		 *  Get URL Param
		 */
		getUrlParam: function(sParam){
			var sPageURL = window.location.search.substring(1);
			var sURLVariables = sPageURL.split('&');
			for (var i = 0; i < sURLVariables.length; i++)
			{
				var sParameterName = sURLVariables[i].split('=');
				if (sParameterName[0] == sParam)
				{
					return sParameterName[1];
				}
			}
		},
		/**
		 *  Adds custom lead fields to field map
		 */
		addCustomLeadField: function() {
			/* create a new li and append to list */
			var clone = jQuery(".map-row:last").clone();

			/* discover last priority and next priority in line */
			var priority = clone.data('priority');
			var name = clone.find('input.field-key').val();
			var new_name = jQuery('.map-row-addnew #new-key').val().replace( / /g , '_' ).toLowerCase();
			var next_priority = parseInt(priority) + 1;

			/* update cloned object's priority */
			clone.attr('data-priority' , next_priority );
			clone.find('.field-priority').val(next_priority) ;

			/* change values to custom values */
			clone.find('input.field-key').val( new_name );
			clone.find('input.field-label').val( jQuery('.map-row-addnew #new-label').val() );
			clone.find('select.field-type').val(jQuery('.map-row-addnew #new-type').val());

			/* update name spaces to show priority placement */
			clone.find('input,select').each( function() {
				this.name = this.name.replace( name , new_name );
			});
			
			/* unhide delete button */
			clone.find('.delete-custom-field').removeClass('hidden');
			
			/* removed disabled attribute from mapped key */
			clone.find('input.field-key').removeAttr('disabled');

			/* empty add new container */
			jQuery('.map-row-addnew #new-key').val('');
			jQuery('.map-row-addnew #new-label').val('');

			clone.appendTo(".field-map");
			
			/* run update */
			InboundProWelcomeJs.updateCustomFields();

		},
		/**
		 *  Prompt remove custom field confirmation
		 */
		removeCustomFieldConfirm: function() {
			InboundProWelcomeJs.input.addClass( 'hidden' );
			InboundProWelcomeJs.input = InboundProWelcomeJs.input.closest('.map-row');
			InboundProWelcomeJs.input.find('.delete-custom-field-confirm').removeClass( 'hidden' );
		},
		/**
		 *  Remove custom field
		 */
		removeCustomField: function() {
			InboundProWelcomeJs.input = InboundProWelcomeJs.input.closest('.map-row');
			InboundProWelcomeJs.input.remove();/* run update */
			InboundProWelcomeJs.updateCustomFields();
		}
	}


	return construct;

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function() {

	InboundProWelcomeJs.init();

});