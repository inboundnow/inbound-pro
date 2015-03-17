(function($){
	
	acf.fields.tab = acf.field.extend({
		
		type: 'tab',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'hide':		'hide',
			'show':		'show'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-tab');
			
		},
		
		initialize: function(){
			
			// add tab group if it doesn't exist
			if( !this.$field.siblings('.acf-tab-wrap').exists() ) {
			
				this.add_group();
				
			}
			
			
			// add tab
			this.add_tab();
			
		},
		
		add_tab : function(){
			
			// vars
			var $group = this.$field.siblings('.acf-tab-wrap');
			
			
			// template
			var html = [
				'<li>',
					'<a class="acf-tab-button" href="#" data-key="' + this.$field.data('key') + '">' + this.$el.text() + '</a>',
				'</li>'].join('');
				
				
			// add tab
			$group.find('ul').append( html );
			
			
			// show first tab, hide others
			if( $group.find('li').length == 1 ) {
				
				$group.find('li').addClass('active');
				
				this.show_tab_fields( this.$field );
				
			} else {
				
				this.hide_tab_fields( this.$field );
				
			}
			
		},
		
		add_group : function(){
			
			// vars
			var $wrap = this.$field.parent(),
				html = '';
			
			
			// generate html
			if( $wrap.is('tbody') ) {
				
				html = '<tr class="acf-tab-wrap"><td colspan="2"><ul class="acf-hl acf-tab-group"></ul></td></tr>';
			
			} else {
			
				html = '<div class="acf-tab-wrap"><ul class="acf-hl acf-tab-group"></ul></div>';
				
				// tab placement
				$wrap.addClass('acf-tp' + this.$el.data('placement').substr(0, 1));
			
			}
			
			
			// append html
			this.$field.before( html );
			
		},
		
		toggle : function( $a ){
			
			// reference
			var self = this;
			
			
			// vars
			var $wrap = $a.closest('.acf-tab-wrap');
				
				
			// add and remove classes
			$a.parent().addClass('active').siblings().removeClass('active');
			
			
			// loop over 
			$wrap.siblings('.acf-field-tab').each(function(){
				
				// show fields
				if( $(this).attr('data-key') === $a.attr('data-key') ) {
					
					self.show_tab_fields( $(this) );
					return;
					
				}
				
				
				// hide fields
				if( ! $(this).hasClass('hidden-by-tab') ) {
					
					self.hide_tab_fields( $(this) );
					return;
					
				}
				
			});
			
			
			// action for 3rd party customization
			acf.do_action('refresh');

		},
		
		show_tab_fields : function( $field ) {
			
			// debug
			//console.log('show tab fields %o', $field);
			
			$field.removeClass('hidden-by-tab');
			
			$field.nextUntil('.acf-field-tab', '.acf-field').each(function(){
				
				// remove class
				$(this).removeClass('hidden-by-tab');
				
				
				// do action
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		hide_tab_fields : function( $field ) {
			
			// debug
			//console.log('hide tab fields %o', $field);
			
			$field.addClass('hidden-by-tab');
			
			$field.nextUntil('.acf-field-tab', '.acf-field').each(function(){
				
				//console.log( $field.nextUntil('.acf-field-tab', '.acf-field') )
				// add class
				$(this).addClass('hidden-by-tab');
				
				
				// do action
				acf.do_action('hide_field', $(this));
				
			});
			
		},
		
		hide: function( $field, context ){
			
			// bail early if no conditional logic
			if( context != 'conditional_logic' ) {
				
				return;
				
			}
			
			
			// vars
			var $a = $field.siblings('.acf-tab-wrap').find('a[data-key="' + $field.data('key') + '"]'),
				$li = $a.parent();
				
			
			// if this tab field was hidden by conditional_logic, disable it's children to prevent validation
			$field.nextUntil('.acf-field-tab', '.acf-field').each(function(){
				
				acf.conditional_logic.hide_field( $(this) );
				
			});
			
			
			// hide li
			$li.addClass('hidden-by-conditional-logic');
			
			
			// select other tab if active
			if( $li.hasClass('active') ) {
				
				$li.siblings().not('hidden-by-conditional-logic').first().children('a').trigger('click');
				
			}
			
		},
		
		show: function( $field, context ){
			
			// bail early if no conditional logic
			if( context != 'conditional_logic' ) {
				
				return;
				
			}
			
			
			// vars
			var $a = $field.siblings('.acf-tab-wrap').find('a[data-key="' + $field.data('key') + '"]'),
				$li = $a.parent();
				
			
			// if this tab field was shown by conditional_logic, enable it's children to allow validation
			$field.nextUntil('.acf-field-tab', '.acf-field').each(function(){
				
				acf.conditional_logic.show_field( $(this) );
				
			});
			
			
			// if tab is already visible, then ignore the following functionality
			if( $li.is(':visible') ) {
			
				return;
				
			}
			
			
			// show li
			$li.removeClass('hidden-by-conditional-logic');
			
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
	
	acf.add_filter('validation_complete', function( json, $form ){
		
		// show field error messages
		$.each( json.errors, function( k, item ){
		
			var $input = $form.find('[name="' + item.input + '"]').first(),
				$field = acf.get_field_wrap( $input ),
				$tab = $field.prevAll('.acf-field-tab:first');
			
			
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
