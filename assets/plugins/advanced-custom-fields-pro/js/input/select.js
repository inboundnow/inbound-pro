(function($){
	
	function add_select2( $select, settings ) {
		
		// vars
		settings = $.extend({
			'allow_null':	false,
			'placeholder':	'',
			'multiple':		false,
			'ajax':			false,
			'action':		'',
			'pagination':	false
		}, settings);
		
				
		// vars
		var $input = $select.siblings('input');
		
		
		// select2 args
		var args = {
			width			: '100%',
			allowClear		: settings.allow_null,
			placeholder		: settings.placeholder,
			multiple		: settings.multiple,
			data			: [],
			escapeMarkup	: function( m ){ return m; }
		};
		
		
		// customize HTML for selected choices
		if( settings.multiple ) {
			
			args.formatSelection = function( object, $div ){
				
				$div.parent().append('<input type="hidden" class="acf-select2-multi-choice" name="' + $select.attr('name') + '" value="' + object.id + '" />');
				
				return object.text;
			}
		}
		
		
		// remove the blank option as we have a clear all button!
		if( settings.allow_null ) {
			
			args.placeholder = settings.placeholder;
			$select.find('option[value=""]').remove();
			
		}
		
		
		// vars
		var selection = $input.val().split(','),
			initial_selection = [];
			
		
		// populate args.data
		var optgroups = {};
		
		$select.find('option').each(function( i ){
			
			// var
			var parent = '_root';
			
			
			// optgroup?
			if( $(this).parent().is('optgroup') ) {
			
				parent = $(this).parent().attr('label');
				
			}
			
			
			// append to choices
			if( ! optgroups[ parent ] ) {
			
				optgroups[ parent ] = [];
				
			}
			
			optgroups[ parent ].push({
				id		: $(this).attr('value'),
				text	: $(this).text()
			});
			
		});
		

		$.each( optgroups, function( label, children ){
			
			if( label == '_root' ) {
			
				$.each( children, function( i, child ){
					
					args.data.push( child );
					
				});
				
			} else {
			
				args.data.push({
					text		: label,
					children	: children
				});
				
			}
						
		});

		
		// re-order options
		$.each( selection, function( k, value ){
			
			$.each( args.data, function( i, choice ){
				
				if( value == choice.id ) {
				
					initial_selection.push( choice );
					
				}
				
			});
						
		});
		
		
		// ajax
		if( settings.ajax ) {
			
			args.ajax = {
				url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'post',
				cache		: false,
				data		: function (term, page) {
					
					// vars
					var data = {
						action		: settings.action,
						field_key	: settings.key,
						nonce		: acf.get('nonce'),
						post_id		: acf.get('post_id'),
						s			: term,
						paged		: page
					};

					
					// return
					return data;
					
				},
				results: function(data, page){
					
					// allow null return
					if( !data ) {
						
						data = [];
						
					}
					
					
					// return
					return {
						results	: data
					};
					
				}
			};
			
			if( settings.pagination ) {
				
				args.ajax.results = function( data, page ) {
					
					var i = 0;
					
					// allow null return
					if( !data ) {
						
						data = [];
						
					} else {
						
						$.each(data, function(k, v){
							
							l = 1;
							
							if( typeof v.children !== 'undefined' ) {
								
								l = v.children.length;
								
							}
							
							i += l;
							
						});
						
					}
					
					
					// return
					return {
						results	: data,
						more	: (i >= 20)
					};
					
				};
				
				$input.on("select2-loaded", function(e) { 
					
					// merge together groups
					var label = '',
						$list = null;
						
					$('#select2-drop .select2-results > li > .select2-result-label').each(function(){
						
						if( $(this).text() == label ) {
							
							$list.append( $(this).siblings('ul').children() );
							
							$(this).parent().remove();
							
							return;
						}
						
						
						// update vars
						label = $(this).text();
						$list = $(this).siblings('ul');
						
					});
											
				});	
			}
			
			
			args.initSelection = function (element, callback) {
				
				// single select requires 1 val, not an array
				if( ! settings.multiple ) {
				
					initial_selection = initial_selection[0];
					
				}
				
					        
		        // callback
		        callback( initial_selection );
		        
		    };
		}
		
		
		// attachment z-index fix
		args.dropdownCss = {
			'z-index' : '999999999'
		};
		
		
		// filter for 3rd party customization
		args = acf.apply_filters( 'select2_args', args, $select, settings );
		
		
		// add select2
		$input.select2( args );

		
		// reorder DOM
		$input.select2('container').before( $input );
		
		
		// multiple
		if( settings.multiple ) {
			
			// clear input value (allow nothing to be saved) - only for multiple
			//$input.val('');
			
			
			// sortable
			$input.select2('container').find('ul.select2-choices').sortable({
				 //containment: 'parent',
				 start: function() {
				 	$input.select2("onSortStart");
				 },
				 stop: function() {
				 	$input.select2("onSortEnd");
				 }
			});
		}
		
		
		// make sure select is disabled (repeater / flex may enable it!)
		$select.attr('disabled', 'disabled').addClass('acf-disabled');


	}
	
	function remove_select2( $select ) {
		
		$select.siblings('.select2-container').remove();
		
	}
	
	
	// select
	acf.fields.select = acf.field.extend({
		
		type: 'select',
		pagination: false,
		
		$select: null,
		
		actions: {
			'ready':	'render',
			'append':	'render',
			'remove':	'remove'
		},

		focus: function(){
			
			// focus on $select
			this.$select = this.$field.find('select');
			
			
			// bail early if no select field
			if( !this.$select.exists() ) {
				
				return;
				
			}
			
			
			// get options
			this.o = acf.get_data( this.$select );
			
			
			// customize o
			this.o.pagination = this.pagination;
			this.o.key = this.$field.data('key');	
			this.o.action = 'acf/fields/' + this.type + '/query';
			
		},
		
		render: function(){
			
			// validate ui
			if( !this.$select.exists() || !this.o.ui ) {
				
				return false;
				
			}
			
			
			add_select2( this.$select, this.o );
			
		},
		
		remove: function(){
			
			// validate ui
			if( !this.$select.exists() || !this.o.ui ) {
				
				return false;
				
			}
			
			
			remove_select2( this.$select );
			
		}
		
	});
	
	
	// taxonomy
	acf.fields.taxonomy = acf.fields.select.extend({

		type: 'taxonomy'
		
	});
	
	
	// user
	acf.fields.user = acf.fields.select.extend({
		
		type: 'user'
		
	});	
	
	
	// post_object
	acf.fields.post_object = acf.fields.select.extend({
		
		type: 'post_object',
		pagination: true
		
	});
	
	
	// page_link
	acf.fields.page_link = acf.fields.select.extend({
		
		type: 'page_link',
		pagination: true
		
	});
	

})(jQuery);
