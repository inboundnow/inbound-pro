(function($){
	
	acf.fields.oembed = {
		
		search : function( $el ){ 
			
			// vars
			var s = $el.find('[data-name="search-input"]').val();
			
			
			// fix missing 'http://' - causes the oembed code to error and fail
			if( s.substr(0, 4) != 'http' )
			{
				s = 'http://' + s;
				$el.find('[data-name="search-input"]').val( s );
			}
			
			
			// show loading
			$el.addClass('is-loading');
			
			
			// AJAX data
			var ajax_data = {
				'action'	: 'acf/fields/oembed/search',
				'nonce'		: acf.get('nonce'),
				's'			: s,
				'width'		: acf.get_data($el, 'width'),
				'height'	: acf.get_data($el, 'height')
			};
			
			
			// abort XHR if this field is already loading AJAX data
			if( $el.data('xhr') )
			{
				$el.data('xhr').abort();
			}
			
			
			// get HTML
			var xhr = $.ajax({
				url: acf.get('ajaxurl'),
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function( html ){
					
					$el.removeClass('is-loading');
					
					
					// update from json
					acf.fields.oembed.search_success( $el, s, html );
					
					
					// no results?
					if( !html )
					{
						acf.fields.oembed.search_error( $el );
					}
					
				}
			});
			
			
			// update el data
			$el.data('xhr', xhr);
			
		},
		
		search_success : function( $el, s, html ){
		
			$el.removeClass('has-error').addClass('has-value');
			
			$el.find('[data-name="value-input"]').val( s );
			$el.find('[data-name="value-title"]').html( s );
			$el.find('[data-name="value-embed"]').html( html );
			
		},
		
		search_error : function( $el ){
			
			// update class
	        $el.removeClass('has-value').addClass('has-error');
			
		},
		
		clear : function( $el ){
			
			// update class
	        $el.removeClass('has-error has-value');
			
			
			// clear search
			$el.find('[data-name="search-input"]').val('');
			
			
			// clear inputs
			$el.find('[data-name="value-input"]').val( '' );
			$el.find('[data-name="value-title"]').html( '' );
			$el.find('[data-name="value-embed"]').html( '' );
			
		},
		
		edit : function( $el ){ 
			
			// update class
	        $el.addClass('is-editing');
	        
	        
	        // set url and focus
	        var url = $el.find('[data-name="value-title"]').text();
	        
	        $el.find('[data-name="search-input"]').val( url ).focus()
			
		},
		
		blur : function( $el ){ 
			
			$el.removeClass('is-editing');
			
			
	        // set url and focus
	        var old_url = $el.find('[data-name="value-title"]').text(),
	        	new_url = $el.find('[data-name="search-input"]').val(),
	        	embed = $el.find('[data-name="value-embed"]').html();
	        
	        
	        // bail early if no valu
	        if( !new_url ) {
		        
		        this.clear( $el );
		        return;
	        }
	        
	        
	        // bail early if no change
	        if( new_url == old_url ) {
		        
		        return;
		        
	        }
	        
	        this.search( $el );
	        
	        			
		}
	};
	
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '.acf-oembed [data-name="search-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.oembed.search( $(this).closest('.acf-oembed') );
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-oembed [data-name="clear-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.oembed.clear( $(this).closest('.acf-oembed') );
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-oembed [data-name="value-title"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.oembed.edit( $(this).closest('.acf-oembed') );
			
	});
	
	$(document).on('keypress', '.acf-oembed [data-name="search-input"]', function( e ){
		
		// don't submit form
		if( e.which == 13 )
		{
			e.preventDefault();
		}
		
	});
	
	
	$(document).on('keyup', '.acf-oembed [data-name="search-input"]', function( e ){
		
		// bail early if no value
		if( ! $(this).val() ) {
			
			return;
			
		}
		
		
		// bail early for directional controls
		if( ! e.which ) {
		
			return;
			
		}
		
		acf.fields.oembed.search( $(this).closest('.acf-oembed') );
		
	});
	
	$(document).on('blur', '.acf-oembed [data-name="search-input"]', function(e){
		
		acf.fields.oembed.blur( $(this).closest('.acf-oembed') );
		
	});
		
	

})(jQuery);
