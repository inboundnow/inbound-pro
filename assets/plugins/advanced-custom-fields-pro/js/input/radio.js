(function($){
	
	acf.fields.radio = acf.field.extend({
		
		type: 'radio',
		$selected: null,
		$other: null,
		
		actions: {
			'ready':	'render',
			'append':	'render'
		},
		
		events: {
			'change input[type="radio"]': 'render',
		},
		
		focus: function(){
			
			this.$selected = this.$field.find('input[type="radio"]:checked');
			this.$other = this.$field.find('input[type="text"]');
			
		},
		
		render: function(){
			
			if( this.$selected.val() === 'other' ) {
			
				this.$other.removeAttr('disabled').attr('name', this.$selected.attr('name'));
				
			} else {
				
				this.$other.attr('disabled', 'disabled').attr('name', '');
				
			}
			
		}
		
	});	

})(jQuery);
