(function($){
	
	/*
	*  Date Picker
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.date_picker = {
		
		init : function( $el ){
			
			// vars
			var $input = $el.find('input[type="text"]'),
				$hidden = $el.find('input[type="hidden"]');
			
			
			// get options
			var o = acf.get_data( $el );
			
			
			// get and set value from alt field
			$input.val( $hidden.val() );
			
			
			// create options
			var args = $.extend( {}, acf.l10n.date_picker, { 
				dateFormat		:	'yymmdd',
				altField		:	$hidden,
				altFormat		:	'yymmdd',
				changeYear		:	true,
				yearRange		:	"-100:+100",
				changeMonth		:	true,
				showButtonPanel	:	true,
				firstDay		:	o.first_day
			});
			
			
			// filter for 3rd party customization
			args = acf.apply_filters('date_picker_args', args, $el);
			
			
			// add date picker
			$input.addClass('active').datepicker( args );
			
			
			// now change the format back to how it should be.
			$input.datepicker( "option", "dateFormat", o.display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if( $('body > #ui-datepicker-div').exists() )
			{
				$('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
			}
			
		},
		
		blur : function( $input ){
			
			if( !$input.val() )
			{
				$input.siblings('input[type="hidden"]').val('');
			}
			
		}
		
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	acf.add_action('ready append', function( $el ){
		
		acf.get_fields({ type : 'date_picker'}, $el).each(function(){
			
			acf.fields.date_picker.init( $(this).find('.acf-date_picker') );
			
		});
		
	});
		
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	$(document).on('blur', '.acf-date_picker input[type="text"]', function( e ){
		
		acf.fields.date_picker.blur( $(this) );
					
	});
	

})(jQuery);
