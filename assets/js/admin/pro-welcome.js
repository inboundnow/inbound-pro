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
			this.search_container = InboundQuery('#grid');
			this.target = InboundProWelcomeJs.getUrlParam('setting');
		},
		/**
		 *  Sets up setting searching
		 */
		setupSearching: function() {
			// Advanced filtering
			InboundQuery('body').on('keyup change', '.filter-search' , function() {
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
				InboundQuery('.filter-search').val(InboundProWelcomeJs.target);				
				InboundQuery('.filter-search').trigger('change');
				InboundQuery('.filter-search').trigger('keyup');					
			}
		},
		/**
		 *  Add UI Listeners
		 */
		addListeners: function() {
			
			/* add listeners for non array data changes */
			InboundQuery( document ).on( 'change unfocus propertychange paste' , 'input[data-field-type!="license-key"],dropdown,radio' , function() {

				/* set static var */
				InboundProWelcomeJs.input = InboundQuery( this );
				
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
			InboundQuery( document ).on( 'keyup' , '.license' , function() {
				/* set static var */
				InboundProWelcomeJs.input = InboundQuery( this );

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
			InboundQuery( 'body' ).on( 'click' , '.unauth' , function() {
				/* set static var */
				InboundProWelcomeJs.input = InboundQuery( this );
				InboundProWelcomeJs.deauthorizeOauth();
			});

			/* Add listeners for oauth authorize button */
			InboundQuery( 'body' ).on( 'click' , '.oauth' , function() {
				/* set static var */
				InboundProWelcomeJs.input = InboundQuery( this );
			});


			/* Add listeners for oauth close button */
			jQuery(document).on('tb_unload', '#TB_window', function(e){
				/* listen for success */
				var success = InboundQuery('iframe').contents().find('.success');

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

			InboundQuery.ajax({
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

			InboundQuery.ajax({
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
					var button =InboundQuery('.oauth[data-field-group="'+group+'"]');
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
			
			var button = InboundQuery('.unauth[data-field-group="'+group+'"]');
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
					 var value = InboundQuery('#' + InboundProWelcomeJs.input.attr("id") +' input:checkbox:checked').map(function(){  return InboundQuery(this).val();}).get();
					 break;
				case 'select2':
					 var value = InboundQuery('#'+ InboundProWelcomeJs.input.attr('id') +' option:selected').map(function(){  return InboundQuery(this).val();}).get();
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

			return InboundQuery.param( InboundProWelcomeJs.input.serializeArray().concat(dataarr));
		},
		/**
		 *  Validate API Key
		 */
		validateLicenseKey: function() {
			switch(this.pollAPI(InboundProWelcomeJs.input.val())) {
				case true:
					InboundProWelcomeJs.input.removeClass('invalid');
					InboundProWelcomeJs.input.addClass('valid');
					InboundQuery('.invalid-icon').remove();
					InboundQuery('.valid-icon').remove();
					InboundQuery('<i>' , { class:"fa fa-check valid-icon tooltip" , title:"License Key Is Invalid" }).appendTo('.license-key');
					break;
				case false:
					InboundProWelcomeJs.input.removeClass('valid');
					InboundProWelcomeJs.input.addClass('invalid');
					InboundQuery('.valid-icon').remove();
					InboundQuery('.invalid-icon').remove();
					InboundQuery('<i>' , { class:"fa fa-times-circle invalid-icon tooltip" , title:"License Key Is Invalid" }).appendTo('.license-key');
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
InboundQuery(document).ready(function() {

	InboundProWelcomeJs.init();

});