(function($){

	acf.fields.tab = {
		
		add_group : function( $wrap ){
			
			// vars
			var html = '';
			
			
			// generate html
			if( $wrap.is('tbody') )
			{
				html = '<tr class="acf-tab-wrap"><td colspan="2"><ul class="acf-hl acf-tab-group"></ul></td></tr>';
			}
			else
			{
				html = '<div class="acf-tab-wrap"><ul class="acf-hl acf-tab-group"></ul></div>';
			}
			
			
			// append html
			acf.get_fields({ type : 'tab'}, $wrap).first().before( html );
			
		},
		
		add_tab : function( $field ){
			
			// vars
			var $wrap	= $field.parent(),
				$tab	= $field.find('.acf-tab'),
				
				key		= acf.get_data( $field, 'key'),
				label 	= $tab.text();
				
				
			// create tab group if it doesn't exist
			if( ! $wrap.children('.acf-tab-wrap').exists() )
			{
				this.add_group( $wrap );
			}
			
			// add tab
			$wrap.children('.acf-tab-wrap').find('.acf-tab-group').append('<li><a class="acf-tab-button" href="#" data-key="' + key + '">' + label + '</a></li>');
			
		},
		
		toggle : function( $a ){
			
			// reference
			var self = this;
			
			
			// vars
			var $wrap	= $a.closest('.acf-tab-wrap').parent(),
				key		= $a.attr('data-key');
			
			
			// classes
			$a.parent('li').addClass('active').siblings('li').removeClass('active');
			
			
			// hide / show
			acf.get_fields({ type : 'tab'}, $wrap).each(function(){
				
				// vars
				var $tab = $(this);
					
				
				if( acf.is_field( $(this), {key : key} ) )
				{
					self.show_tab_fields( $(this) );
				}
				else
				{
					self.hide_tab_fields( $(this) );
				}
				
			});
			
		},
		
		show_tab_fields : function( $field ) {
			
			// debug
			//console.log('show tab fields %o', $field);
			
			$field.nextAll('.acf-field').each(function(){
				
				// bail early if hid another tab
				if( acf.is_field( $(this), {type : 'tab'} ) ) {
					
					return false;
				}
				
				
				$(this).removeClass('hidden-by-tab');
				acf.do_action('show_field', $(this));
				
			});
		},
		
		hide_tab_fields : function( $field ) {
			
			// debug
			//console.log('hide tab fields %o', $field);
			
			$field.nextAll('.acf-field').each(function(){
				
				// bail early if hid another tab
				if( acf.is_field( $(this), {type : 'tab'} ) ) {
					
					return false;
				}
				
				$(this).addClass('hidden-by-tab');
				acf.do_action('hide_field', $(this));
				
			});
		},
		
		refresh : function( $el ){
			
			// reference
			var self = this;
			
			
			// trigger
			$el.find('.acf-tab-group').each(function(){
				
				$(this).find('.acf-tab-button:first').each(function(){
				
					self.toggle( $(this) );
					
				});
				
			});
			
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
		
		// vars
		var refresh = false;
		
		
		// add tabs
		acf.get_fields({ type : 'tab'}, $el).each(function(){
			
			acf.fields.tab.add_tab( $(this) );
			
			refresh = true;
			
		});
		
		
		// activate first tab
		if( refresh ) {
			
			acf.fields.tab.refresh( $el );
			
		}
		
		
	});
	
	
		
	
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
	
	$(document).on('click', '.acf-tab-button', function( e ){
		
		e.preventDefault();
		
		acf.fields.tab.toggle( $(this) );
		
		$(this).trigger('blur');
			
	});
	
	
	acf.add_action('hide_field', function( $field ){
		
		// validate
		if( ! acf.is_field($field, {type : 'tab'}) ) {
		
			return;
			
		}
		
		
		// vars
		var $a = $field.siblings('.acf-tab-wrap').find('a[data-key="' + acf.get_data($field, 'key') + '"]'),
			$li = $a.parent();
			
		
		// bail early if already hidden
		if( $li.is(':hidden') ) {
		
			return;
			
		}
		
		
		// visibility
		$li.hide();
		
		
		// bail early if active tab exists
		if( $li.siblings('.active').exists() ) {
		
			return;
			
		}
		
		
		// if sibling tab exists, click it
		if( $li.siblings(':visible').exists() ) {
			
			$li.siblings(':visible').first().children('a').trigger('click');
			return;
		}
		
		
		// hide fields under this tab
		acf.fields.tab.hide_tab_fields( $field );
		
	});
	
	
	acf.add_action('show_field', function( $field ){
		
		// validate
		if( ! acf.is_field($field, {type : 'tab'}) ) {
		
			return;
			
		}
		
		
		// vars
		var $a = $field.siblings('.acf-tab-wrap').find('a[data-key="' + acf.get_data($field, 'key') + '"]'),
			$li = $a.parent();
			
		
		// if tab is already visible, then ignore the following functionality
		if( $li.is(':visible') ) {
		
			return;
			
		}
		
		
		// visibility
		$li.show();
		
		
		// bail early if this is the active tab
		if( $li.hasClass('active') ) {
		
			return;
			
		}
		
		
		// if the sibling active tab is actually hidden by conditional logic, take ownership of tabs
		if( !$li.siblings(':visible').exists() ) {
		
			// show this tab group
			$a.trigger('click');
			
		}
		

	});
	
	
	acf.add_filter('validation_complete', function( json, $form ){
		
		// show field error messages
		$.each( json.errors, function( k, item ){
		
			var $input = $form.find('[name="' + item.input + '"]').first(),
				$field = acf.get_field_wrap( $input ),
				$tab = $field.prevAll('.acf-field[data-type="tab"]:first');
			
			
			// does tab group exist?
			if( ! $tab.exists() )
			{
				return;
			}

			
			// is this field hidden
			if( $field.hasClass('hidden-by-tab') )
			{
				// show this tab
				$tab.siblings('.acf-tab-wrap').find('a[data-key="' + acf.get_data($tab, 'key') + '"]').trigger('click');
				
				// end loop
				return false;
			}
			
			
			// field is within a tab group, and the tab is already showing
			// end loop
			return false;
			
		});
		
		
		// return
		return json;
				
	});
	
	

})(jQuery);
