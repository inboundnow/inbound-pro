(function($){
	
	acf.fields.url = acf.field.extend({
		
		type: 'url',
		$input: null,
		
		actions: {
			'ready':	'render',
			'append':	'render'
		},
		
		events: {
			'keyup input[type="url"]': 'render',
		},
		
		focus: function(){
			
			this.$input = this.$field.find('input[type="url"]');
			
		},
		
		render: function(){
			
			this.$input.parent().removeClass('valid');
			
			if( this.$input.val().substr(0, 4) === 'http' ) {
				
				this.$input.parent().addClass('valid');
				
			}
			
		}
		
	});

})(jQuery);
