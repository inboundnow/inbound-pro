var InboundProWelcomeJs = ( function() {

	var search_container; /* element that wraps all settings and is shuffle js ready */
	var target; /* target setting if available */
	var input;
	var datatype;
	var value;

	var construct = {
		/**
		*  Initialize JS Class
		*/
		init: function() {
			this.addListeners();
			this.setVars();
			this.setupSearching();
			this.initShuffleJs();
			
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
		 *  Add UI Listeners
		 */
		addListeners: function() {
			
			/* add listeners for non array data changes */
			jQuery( document ).on( 'change unfocus propertychange paste' , 'input[data-field-type!="license-key"],dropdown,radio' , function() {

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

			/* Add listeners for oauth unauthorize buttons */
			jQuery( 'body' ).on( 'click' , '.unauth' , function() {
				/* set static var */
				InboundProWelcomeJs.input = jQuery( this );
				InboundProWelcomeJs.deauthorizeOauth();
			});

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
		 *  Save Data
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