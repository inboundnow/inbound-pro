(function($){
	
	acf.fields.file = {
		
		edit : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-file-uploader'),
				id = $el.find('[data-name="id"]').val();
			
			
			// popup
			var frame = acf.media.popup({
				'title'			: acf._e('file', 'edit'),
				'button'		: acf._e('file', 'update'),
				'mode'			: 'edit',
				'id'			: id,
				'select'		: function( attachment, i ) {
					
			    	// vars
			    	var file = {
				    	id		:	attachment.id,
				    	title	:	attachment.attributes.title,
				    	name	:	attachment.attributes.filename,
				    	url		:	attachment.attributes.url,
				    	icon	:	attachment.attributes.icon,
				    	size	:	attachment.attributes.filesize
			    	};
			    	
			    	
			    	// add file to field
			        acf.fields.file.add( $el, file );
					
				}
			});
			
			
		},
		
		remove : function( $a ) {
			
			// vars
			var $el = $a.closest('.acf-file-uploader');
			
			
			// set atts
			$el.find('[data-name="icon"]').attr( 'src', '' );
			$el.find('[data-name="title"]').text( '' );
		 	$el.find('[data-name="name"]').text( '' ).attr( 'href', '' );
		 	$el.find('[data-name="size"]').text( '' );
			$el.find('[data-name="id"]').val( '' ).trigger('change');
			
			
			// remove class
			$el.removeClass('has-value');
			
		},
		
		popup : function( $a ) {
			
			// el
			var $el				= $a.closest('.acf-file-uploader'),
				$field			= acf.get_the_field( $el ),
				$repeater		= acf.get_the_field( $field );
			
			
			// vars
			var library 		= acf.get_data( $el, 'library' ),
				multiple		= false;
				
				
			// get parent
			if( $repeater.exists() && acf.is_field($repeater, {type : 'repeater'}) ) {
				
				multiple = true;
				
			}
			
			
			// popup
			var frame = acf.media.popup({
				'title'			: acf._e('file', 'select'),
				'mode'			: 'select',
				'type'			: '',
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
							if( $next.find('.acf-file-uploader.has-value').exists() ) {
								
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
						$el = $next.find('.acf-file-uploader');
						
					}
											
					
			    	// vars
			    	var file = {
				    	id		:	attachment.id,
				    	title	:	attachment.attributes.title,
				    	name	:	attachment.attributes.filename,
				    	url		:	attachment.attributes.url,
				    	icon	:	attachment.attributes.icon,
				    	size	:	attachment.attributes.filesize
			    	};
			    	
			    	
			    	// add file to field
			        acf.fields.file.add( $el, file );
					
				}
			});
			
			
		},

		add : function( $el, file ){
			
			// set atts
			$el.find('[data-name="icon"]').attr( 'src', file.icon );
			$el.find('[data-name="title"]').text( file.title );
		 	$el.find('[data-name="name"]').text( file.name ).attr( 'href', file.url );
		 	$el.find('[data-name="size"]').text( file.size );
			$el.find('[data-name="id"]').val( file.id ).trigger('change');
			
					 	
		 	// set div class
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
	
	$(document).on('click', '.acf-file-uploader [data-name="remove-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.remove( $(this) );
			
	});
	
	$(document).on('click', '.acf-file-uploader [data-name="edit-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.edit( $(this) );
			
	});
	
	$(document).on('click', '.acf-file-uploader [data-name="add-button"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.file.popup( $(this) );
		
	});
	

})(jQuery);
