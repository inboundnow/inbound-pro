(function($){
	
	acf.fields.color_picker = acf.field.extend({
		
		type: 'color_picker',
		timeout: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		focus: function(){
			
			this.$input = this.$field.find('input[type="text"]');
			
		},
		
		initialize: function(){
			
			// reference
			var self = this;
			
			
			// vars
			var $hidden = this.$input.clone();
			
			
			// modify hidden
			$hidden.attr({
				'type'	: 'hidden',
				'class' : '',
				'id'	: '',
				'value'	: ''
 			});
 			
 			
 			// append hidden
 			this.$input.before( $hidden );
 			
 			
 			// iris
			this.$input.wpColorPicker({
				
				change: function( event, ui ){
			
					if( self.timeout ) {
				
						clearTimeout( self.timeout );
						
					}
					
					
					self.timeout = setTimeout(function(){
						
						$hidden.trigger('change');
						
					}, 1000);
					
				}
				
			});
			
		}
		
	});
	

})(jQuery);
