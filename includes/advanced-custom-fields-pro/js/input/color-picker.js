(function($){
	
	acf.fields.color_picker = {
		
		timeout : null,
		
		init : function( $input ){
			
			// reference
			var self = this;
			
			
			// vars
			var $hidden = $input.clone();
			
			
			// modify hidden
			$hidden.attr({
				'type'	: 'hidden',
				'class' : '',
				'id'	: '',
				'value'	: ''
 			});
 			
 			
 			// append hidden
 			$input.before( $hidden );
 			
 			
 			// iris
			$input.wpColorPicker({
				
				change: function( event, ui ){
			
					self.change( $input, $hidden );
					
				}
				
			});
			
		},
		
		change : function( $input, $hidden ){
			
			if( this.timeout ) {
				
				clearTimeout( this.timeout );
				
			}
			
			
			this.timeout = setTimeout(function(){
				
				$hidden.trigger('change');
				
			}, 1000);
			
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
		
		acf.get_fields({ type : 'color_picker'}, $el).each(function(){
			
			acf.fields.color_picker.init( $(this).find('input[type="text"]') );
			
		});
		
	});	

})(jQuery);
