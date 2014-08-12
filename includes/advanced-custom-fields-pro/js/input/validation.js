(function($){
    
	acf.validation = {
		
		// vars
		active	: 1,
		ignore	: 0,
		
		
		// classes
		error_class : 'acf-error',
		message_class : 'acf-error-message',
		
		
		// el
		$trigger : null,
		
		
		// functions
		init : function(){
			
			// bail early if disabled
			if( this.active == 0 )
			{
				return;
			}
			
			
			// add events
			this.add_events();
		},
		
		add_error : function( $field, message ){
			
			// add class
			$field.addClass(this.error_class);
			
			
			// add message
			if( message !== undefined )
			{
				$field.children('.acf-input').children('.' + this.message_class).remove();
				$field.children('.acf-input').prepend('<div class="' + this.message_class + '"><p>' + message + '</p></div>');
			}
			
			
			// hook for 3rd party customization
			acf.do_action('add_field_error', $field);
		},
		
		remove_error : function( $field ){
			
			// var
			$message = $field.children('.acf-input').children('.' + this.message_class);
			
			
			// remove class
			$field.removeClass(this.error_class);
			
			
			// remove message
			setTimeout(function(){
				
				acf.remove_el( $message );
				
			}, 250);
			
			
			// hook for 3rd party customization
			acf.do_action('remove_field_error', $field);
		},
		
		add_warning : function( $field, message ){
			
			this.add_error( $field, message );
			
			setTimeout(function(){
				
				acf.validation.remove_error( $field )
				
			}, 1000);
		},
		
		fetch : function( $form ){
			
			// reference
			var self = this;
			
			
			// vars
			var data = acf.serialize_form( $form, 'acf' );
				
			
			// append AJAX action		
			data.action = 'acf/validate_save_post';
			
				
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				success		: function( json ){
					
					self.complete( $form, json );
					
				}
			});
			
		},
		
		complete : function( $form, json ){
			
			// filter for 3rd party customization
			json = acf.apply_filters('validation_complete', json, $form);
			
			
			// reference
			var self = this;
			
			
			// remove previous error message
			$form.children('.' + this.message_class).remove();
			
			
			// hide ajax stuff on submit button
			if( $('#submitdiv').exists() ) {
				
				// remove disabled classes
				$('#submitdiv').find('.disabled').removeClass('disabled');
				$('#submitdiv').find('.button-disabled').removeClass('button-disabled');
				$('#submitdiv').find('.button-primary-disabled').removeClass('button-primary-disabled');
				
				
				// remove spinner
				$('#submitdiv .spinner').hide();
				
			}
			
			
			// validate json
			if( !json || typeof json.result === 'undefined' || json.result == 1) {
			
				// remove hidden postboxes (this will stop them from being posted to save)
				$form.find('.acf-postbox.acf-hidden').remove();
					
					
				// bypass JS and submit form
				this.ignore = 1;
				
				
				// attempt to find $trigger
				/*
if( ! this.$trigger )
				{
					if( $form.find('.submit input[type="submit"]').exists() )
					{
						this.$trigger = $form.find('.submit input[type="submit"]');
					}
				}
*/
				
				
				// action for 3rd party customization
				acf.do_action('submit', $form);
				
				
				// submit form again
				if( this.$trigger )
				{
					this.$trigger.click();
				}
				else
				{
					$form.submit();
				}
				
				
				// end function
				return;
			}
			
			
			// show error message	
			$form.prepend('<div class="' + this.message_class + '"><p>' + json.message + '</p></div>');
			
			
			// show field error messages
			if( json.errors ) {
				
				for( var i in json.errors ) {
					
					// get error
					var error = json.errors[ i ];
					
					
					// get input
					var $input = $form.find('[name="' + error.input + '"]').first();
					
					
					// if $_POST value was an array, this $input may not exist
					if( ! $input.exists() ) {
						
						$input = $form.find('[name^="' + error.input + '"]').first();
						
					}
					
					
					// now get field
					var $field = acf.get_field_wrap( $input );
					
					
					// add error
					self.add_error( $field, error.message );
					
					
				}
			
			}
			
		},
		
		add_events : function(){
			
			var self = this;
			
			
			// focus
			$(document).on('focus click change', '.acf-field[data-required="1"] input, .acf-field[data-required="1"] textarea, .acf-field[data-required="1"] select', function( e ){

				self.remove_error( $(this).closest('.acf-field') );
				
			});
			
			
			// click save
			if( $('#save-post').exists() ) {
				
				$('#save-post').on('click', function(){
				
					self.ignore = 1;
					self.$trigger = $(this);
					
				});
				
			}
			
			
			
			// click preview
			if( $('#post-preview').exists() ) {
				
				$('#post-preview').on('click', function(){
				
					self.ignore = 1;
					self.$trigger = $(this);
					
				});
				
			}
						
			
			// click submit
			if( $('#submit').exists() ) {
				
				$('#submit').on('click', function(){
				
					self.$trigger = $(this);
					
				});
				
			}
			
			
			// click publish
			if( $('#publish').exists() ) {
				
				$('#publish').on('click', function(){
				
					self.$trigger = $(this);
					
				});
				
			}
			
			
			
			// submit
			$(document).on('submit', 'form', function( e ){
				
				// bail early if this form does not contain ACF data
				if( ! $(this).find('#acf-form-data').exists() ) {
				
					return true;
					
				}
				
				
				// filter for 3rd party customization
				self.ignore = acf.apply_filters('ignore_validation', self.ignore, self.$trigger, $(this) );

				
				// ignore this submit?
				if( self.ignore == 1 ) {
				
					self.ignore = 0;
					return true;
					
				}
				
				
				// bail early if disabled
				if( self.active == 0 ) {
				
					return true;
					
				}
				
				
				// prevent default
				e.preventDefault();
				
				
				// run validation
				self.fetch( $(this) );
								
			});
			
		}
		
	};
	
	acf.add_action('ready', function(){
		
		acf.validation.init();
		
	});
	

})(jQuery);
