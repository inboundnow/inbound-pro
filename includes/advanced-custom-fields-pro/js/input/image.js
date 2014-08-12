(function($){
	
	acf.fields.image = {
				
		edit : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-image-uploader'),
				id = $el.find('[data-name="value-id"]').val();
			
			
			// popup
			var frame = acf.media.popup({
				'title'			: acf._e('image', 'edit'),
				'button'		: acf._e('image', 'update'),
				'mode'			: 'edit',
				'id'			: id
			});
			
		},
		
		remove : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-image-uploader');
			
			
			// set atts
		 	$el.find('[data-name="value-url"]').attr( 'src', '' );
			$el.find('[data-name="value-id"]').val('').trigger('change');
			
			
			// remove class
			$el.removeClass('has-value');
			
		},
		
		popup : function( $a ) {
			
			// el
			var $el				= $a.closest('.acf-image-uploader'),
				$field			= acf.get_the_field( $el ),
				$repeater		= acf.get_the_field( $field );
			
			
			// vars
			var library 		= acf.get_data( $el, 'library' ),
				preview_size	= acf.get_data( $el, 'preview_size' ),
				multiple		= false;
				
				
			// get parent
			if( $repeater.exists() && acf.is_field($repeater, {type : 'repeater'}) ) {
				
				multiple = true;
				
			}
			
			
			// popup
			var frame = acf.media.popup({
				'title'			: acf._e('image', 'select'),
				'mode'			: 'select',
				'type'			: 'image',
				'multiple'		: multiple,
				'library'		: library,
				'select'		: function( attachment, i ) {
					
					// select / add another image field?
			    	if( i > 0 ) {
			    		
						// vars
						var $tr 	= $field.parent(),
							$next	= false,
							key 	= acf.get_data( $field, 'key' );
							
						
						// find next image field
						$tr.nextAll('.acf-row').not('.clone').each(function(){
							
							// get next $field
							$next = acf.get_field( key, $(this) );
							
							
							// bail early if $next was not found
							if( !$next ) {
								
								return;
								
							}
							
							
							// bail early if next file uploader has value
							if( $next.find('.acf-image-uploader.has-value').exists() ) {
								
								$next = false;
								return;
								
							}
								
								
							// end loop if $next is found
							return false;
							
						});
						
						
						// add extra row if next is not found
						if( !$next ) {
							
							$tr = acf.fields.repeater.set( $repeater ).add();
							
							
							// get next $field
							$next = acf.get_field( key, $tr );
							
						}
						
						
						// update $el
						$el = $next.find('.acf-image-uploader');
						
					}
					
					
			    	// vars
			    	var image_id = attachment.id,
			    		image_url = attachment.attributes.url;
			    	
					
			    	// is preview size available?
			    	if( attachment.attributes.sizes && attachment.attributes.sizes[ preview_size ] ) {
			    	
				    	image_url = attachment.attributes.sizes[ preview_size ].url;
				    	
			    	}
			    	
			    	
			    	// add image to field
			        acf.fields.image.add( $el, image_id, image_url );
					
				}
			});
			
			
		},
		
		add : function( $el, id, url ){
			
			// set atts
		 	$el.find('[data-name="value-url"]').attr( 'src', url );
			$el.find('[data-name="value-id"]').val( id ).trigger('change');
			
			
			// add class
			$el.addClass('has-value');
	
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
	
	$(document).on('click', '.acf-image-uploader [data-name="remove-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.remove( $(this) );
			
	});
	
	$(document).on('click', '.acf-image-uploader [data-name="edit-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.edit( $(this) );
			
	});
	
	$(document).on('click', '.acf-image-uploader [data-name="add-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.image.popup( $(this) );
		
	});
	

})(jQuery);
