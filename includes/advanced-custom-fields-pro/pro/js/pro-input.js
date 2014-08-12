(function($){
	
	// comon
	acf.pro = {
		
		init : function(){
			
			// reference
			var self = this;
			
			
			// actions
			acf.add_action('conditional_logic_show_field', function( $field ){
				
				self.conditional_logic_show_field( $field );
				
			});
			
			acf.add_action('conditional_logic_hide_field', function( $field ){
				
				self.conditional_logic_hide_field( $field );
				
			});
			
			acf.add_filter('is_field_ready_for_js', function( ready, $field ){
				
				return self.is_field_ready_for_js( ready, $field );
			    
		    });
			
			return this;
			
		},
		
		is_field_ready_for_js : function( ready, $field ){
			
			// debug
			//console.log('is_field_ready_for_js %o, %b', $field, ready);
			
			
			// repeater sub field
			if( $field.closest('.acf-row.clone').exists() ) {
			
				ready = false;
				
			}
			
			
			// flexible content sub field
			if( $field.closest('.acf-flexible-content > .clones').exists() ) {
			
				ready = false;
				
			}
			
			
			// return
			return ready;
		
		},
		
		conditional_logic_show_field : function( $field ){
			
			// bail early if not a sub field
			if( ! acf.is_sub_field($field) ) {
				
				return;
				
			}
			
			
			// bail early if not a td
			if( ! $field.is('td') ) {
				
				return;
				
			}
			
			
			// vars
			var key = acf.get_field_key( $field ),
				$table = $field.closest('.acf-table'),
				$th = $table.find('> thead > tr > th[data-key="' + key + '"]'),
				$td = $table.find('> tbody > tr:not(.clone) > td[data-key="' + key + '"]');
			
			
			// remove class
			$field.removeClass('appear-empty');
			
			
			// show entire column
			$td.filter('.hidden-by-conditional-logic').addClass('appear-empty');
			$th.removeClass('hidden-by-conditional-logic');
			
			
			// render table
			this.render_table( $table );
			
		},
		
		conditional_logic_hide_field : function( $field ){
			
			// bail early if not a sub field
			if( ! acf.is_sub_field($field) ) {
				
				return;
				
			}
			
			
			// bail early if not a td
			if( ! $field.is('td') ) {
				
				return;
				
			}
			
			
			// vars
			var key = acf.get_field_key( $field ),
				$table = $field.closest('.acf-table'),
				$th = $table.find('> thead > tr > th[data-key="' + key + '"]'),
				$td = $table.find('> tbody > tr:not(.clone) > td[data-key="' + key + '"]');
			
			
			// add class
			$field.addClass('appear-empty');
			
			
			// if all cells are hidden, hide the entire column
			if( $td.filter('.hidden-by-conditional-logic').length == $td.length ) {
				
				$td.removeClass('appear-empty');
				$th.addClass('hidden-by-conditional-logic');
				
			}
			
			
			// render table
			this.render_table( $table );
			
		},
		
		render_table : function( $table ){
			
			// bail early if table is row layout
			if( $table.hasClass('row-layout') ) {
			
				return;
				
			}
			
			
			// vars
			var $th = $table.find('> thead > tr > th'),
				available_width = 100,
				count = 0;
			
			
			// accomodate for order / remove
			if( $th.filter('.order').exists() ) {
				
				available_width = 93;
				
			}
			
			
			// clear widths
			$th.removeAttr('width');
			
			
			// update $th
			$th = $th.not('.order, .remove, .hidden-by-conditional-logic');
				
			
			// set custom widths first
			$th.filter('[data-width]').each(function(){
				
				// bail early if hit limit
				if( (count+1) == $th.length ) {
					
					return false;
					
				}
				
				
				// increase counter
				count++;
				
				
				// vars
				var width = parseInt( $(this).attr('data-width') );
				
				
				// remove from available
				available_width -= width;
				
				
				// set width
				$(this).attr('width', width + '%');
				
			});
			
			
			// set custom widths first
			$th.not('[data-width]').each(function(){
				
				// bail early if hit limit
				if( (count+1) == $th.length ) {
					
					return false;
					
				}
				
				
				// increase counter
				count++;
				
				
				// cal width
				var width = available_width / $th.length;
				
				
				// set width
				$(this).attr('width', width + '%');
				
			});
			
		}
		
	}.init();
	
	acf.fields.repeater = {
		
		// vars	
		o		: {},
		el		: '.acf-repeater',
		
		
		// el
		$field	: null,
		$el		: null,	
		$clone : null,
		
		
		// functions
		set : function( $field ){
			
			// sel $el
			this.$field = $field;
			this.$el = $field.find( this.el ).first();
			

			// find elements
			this.$clone = this.$el.find('> table > tbody > tr.clone');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// return this for chaining
			return this;
			
		},
		
		count : function(){
			
			return this.$el.find('> table > tbody > tr').length - 1;
			
		},

		
		init : function(){
			
			// vars
			var $field = this.$field;
			
			
			// sortable
			if( this.o.max != 1 ) {
				
				this.$el.find('> table > tbody').unbind('sortable').sortable({
				
					items					: '> tr',
					handle					: '> td.order',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					
					start : function (event, ui) {
						
						acf.do_action('sortstart', ui.item, ui.placeholder);
						
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						
						// render
						acf.fields.repeater.set( $field ).render();
						
		   			}
				});
			}
			
			
			// set column widths
			acf.pro.render_table( this.$el.find('> table') );
			
			
			// disable clone inputs
			// Note: Previous attempted to check if input was already disabled, however the browser caches this attribute, 
			// so a refresh would cause the code to fail.
			this.$clone.find('[name]').attr('disabled', 'disabled');
						
			
			// render
			this.render();
				
		},
		
		render : function(){
			
			// update order numbers
			this.$el.find('> table > tbody > tr').each(function(i){
			
				$(this).children('td.order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 )
			{
				this.$el.addClass('empty');
			}
			else
			{
				this.$el.removeClass('empty');
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max )
			{
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
			}
			else
			{
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
			}
			
		},
		
		add : function( $before ){
			
			// defaults
			$before = $before || false;
			
			
			// vars
			var $field = this.$field;
			
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max )
			{
				alert( acf._e('repeater','max').replace('{max}', this.o.max) );
				return false;
			}
			
		
			// create and add the new field
			var new_id = acf.get_uniqid(),
				html = this.$clone.outerHTML();
				
				
			// replace acfcloneindex
			var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
				$html = $( html );
			
			
			// remove clone class
			$html.removeClass('clone');
			
			
			// enable inputs
			$html.find('[name]').removeAttr('disabled');
			
			
			// add row
			if( !$before || !$before.exists() )
			{
				$before = this.$clone;
			}
			
			$before.before( $html );
			
			
			// trigger mouseenter on parent repeater to work out css margin on add-row button
			this.$field.parents('.acf-row').trigger('mouseenter');
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// setup fields
			acf.do_action('append', $html);
			
			
			// return
			return $html;
			
		},
		
		remove : function( $tr ){
			
			// vars
			var $field = this.$field;
			
			
			// validate
			if( this.count() <= this.o.min )
			{
				alert( acf._e('repeater','min').replace('{min}', this.o.min) );
				return false;
			}
			
			
			// animate out tr
			acf.remove_tr( $tr, function(){
				
				// trigger mouseenter on parent repeater to work out css margin on add-row button
				$field.closest('.acf-row').trigger('mouseenter');
				
				
				// render
				acf.fields.repeater.set( $field ).render();
				
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
		
		acf.get_fields({ type : 'repeater'}, $el).each(function(){
			
			acf.fields.repeater.set( $(this) ).init();
			
		});
		
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
	
	$(document).on('click', '.acf-repeater .acf-repeater-add-row', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$before	= false;
			
		
		if( $a.is('.acf-icon') ) {
		
			$before	= $a.closest('.acf-row');
			
		}
		
		
		// remove
		acf.fields.repeater.set( $field ).add( $before );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-repeater .acf-repeater-remove-row', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$tr		= $a.closest('.acf-row');
			
			
		// remove
		acf.fields.repeater.set( $field ).remove( $tr );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('mouseenter', '.acf-repeater .acf-row', function( e ){
		
		// vars
		var $el = $(this).find('> td.remove .acf-repeater-add-row'),
			margin = ( $el.parent().height() / 2 ) + 9; // 9 = padding + border
		
		
		// css
		$el.css('margin-top', '-' + margin + 'px' );
		
	});
	
	
	
	/*
	*  Flexible Content
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	18/08/13
	*
	*/
	
	acf.fields.flexible_content = {
		
		// vars	
		o		: {},
		el		: '.acf-flexible-content',
		
		
		// el
		$field	: null,
		$el		: null,	
		$values : null,
		$clones : null,
		
		
		// functions
		set : function( $field ){
			
			// sel $el
			this.$field = $field;
			this.$el = $field.find( this.el ).first();
			
			
			// find elements
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
			
			// return this for chaining
			return this;
			
		},
		
		count : function(){
			
			return this.$values.children('.layout').length;
			
		},
		
		init : function(){
			
			// refernce
			var _this = this,
				$field = this.$field;
			
			
			// sortable
			if( this.o.max != 1 )
			{
				this.$values.unbind('sortable').sortable({
					
					items					: '> .layout',
					handle					: '> .acf-fc-layout-handle',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					
					start : function (event, ui) {
					
						acf.do_action('sortstart', ui.item, ui.placeholder);
		        		
		   			},
		   			stop : function (event, ui) {
						
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						
						// render
						_this.set( $field ).render();
		   			}
				});
			}
						
			
			// set column widths
			this.$values.find('.acf-table').each(function(){
			
				acf.pro.render_table( $(this) );
				
			});
						
			
			// disable clone inputs
			// Note: Previous attempted to check if input was already disabled, however the browser caches this attribute, 
			// so a refresh would cause the code to fail.
			this.$clones.find('[name]').attr('disabled', 'disabled');
			
			
			// render
			this.render();
			
		},
		
		render : function(){
			
			// update order numbers
			this.$values.children('.layout').each(function( i ){
			
				$(this).find('> .acf-fc-layout-handle .fc-layout-order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 )
			{
				this.$el.addClass('empty');
			}
			else
			{
				this.$el.removeClass('empty');
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max )
			{
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
			}
			else
			{
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
			}
			
		},
		
		validate_add : function( layout ){
			
			var r = true;
			
			// vadiate max
			if( this.o.max > 0 && this.count() >= this.o.max )
			{
				var identifier	= ( this.o.max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max');
				
				// translate
				s = s.replace('{max}', this.o.max);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				r = false;
				
				alert( s );
			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_max		= $a.attr('data-max'),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			layout_max = parseInt(layout_max);
			if( layout_max > 0 && layout_count >= layout_max )
			{
				var identifier	= ( layout_max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max_layout');
				
				// translate
				s = s.replace('{max}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				r = false;
				
				alert( s );
			}
			
			
			// return
			return r;
			
		},
		
		validate_remove : function( layout ){
			
			// vadiate min
			if( this.o.min > 0 && this.count() <= this.o.min )
			{
				var identifier	= ( this.o.min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');
				
				// translate
				s = s.replace('{min}', this.o.min);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				return confirm( s );

			}
			
			
			// vadiate max layout
			
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_min		= $a.attr('data-min'),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			layout_min = parseInt(layout_min);
			if( layout_min > 0 && layout_count <= layout_min )
			{
				var identifier	= ( layout_min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');
				
				// translate
				s = s.replace('{min}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				return confirm( s );
			}
			
			
			// return
			return true;
			
		},
		
		add : function( layout, $before ){
			
			// bail early if validation fails
			if( !this.validate_add( layout ) )
			{
				return;
			}
			
			
			// create and add the new field
			var new_id = acf.get_uniqid(),
				html = this.$clones.children('.layout[data-layout="' + layout + '"]').outerHTML();
				
				
			// replace acfcloneindex
			var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
				$html = $( html );
				
			
			// enable inputs
			$html.find('[name]').removeAttr('disabled');
			
							
			// hide no values message
			this.$el.children('.no-value-message').hide();
			
			
			// add row
			if( $before )
			{
				$before.before( $html );
			}
			else
			{
				this.$values.append( $html ); 
			}
			
			
			// setup fields
			acf.do_action('append', $html);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove : function( $layout ){
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) )
			{
				return;
			}
			
			
			// close field
			var end_height = 0,
				$message = this.$el.children('.no-value-message');
			
			if( $layout.siblings('.layout').length == 0 )
			{
				end_height = $message.outerHeight();
			}
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				if( end_height > 0 )
				{
					$message.show();
				}
				
			}, end_height);
			
		},
		
		toggle : function( $layout ){
			
			if( $layout.attr('data-toggle') == 'closed' )
			{
				$layout.attr('data-toggle', 'open');
				$layout.children('.acf-input-table').show();
			}
			else
			{
				$layout.attr('data-toggle', 'closed');
				$layout.children('.acf-input-table').hide();
			}
			
			
			// sync local storage (collapsed)
			this.sync();
			
		},
		
		sync : function(){
			
			// vars
			var name = 'acf_collapsed_' + acf.get_data(this.$field, 'key'),
				collapsed = [];
			
			this.$values.children('.layout').each(function( i ){
				
				if( $(this).attr('data-toggle') == 'closed' ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			acf.update_cookie( name, collapsed.join('|') );	
			
		},
		
		open_popup : function( $a, in_layout ){
			
			// reference
			var _this = this;
			
			
			// defaults
			in_layout = in_layout || false;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			$popup.find('a').each(function(){
				
				// vars
				var min		= parseInt( $(this).attr('data-min') ),
					max		= parseInt( $(this).attr('data-max') ),
					name	= $(this).attr('data-layout'),
					label	= $(this).text(),
					count	= _this.$values.children('.layout[data-layout="' + name + '"]').length,
					$status = $(this).children('.status');
				
				
				if( max > 0 )
				{
					// find diff
					var available	= max - count,
						s			= acf.l10n.flexible_content.available,
						identifier	= ( available == 1 ) ? 'layout' : 'layouts',
				
					// translate
					s = s.replace('{available}', available);
					s = s.replace('{max}', max);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
					
					
					$status.show().text( available ).attr('title', s);
					
					// limit reached?
					if( available == 0 )
					{
						$status.addClass('warning');
					}
				}
				
				
				if( min > 0 )
				{
					// find diff
					var required	= min - count,
						s			= acf.l10n.flexible_content.required,
						identifier	= ( required == 1 ) ? 'layout' : 'layouts',
				
					// translate
					s = s.replace('{required}', required);
					s = s.replace('{min}', min);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
					
					
					if( required > 0 )
					{
						$status.addClass('warning').show().text( required ).attr('title', s);
					}
					
					
				}
				
			});
			
			
			// add popup
			$a.after( $popup );
			
			
			// within layout?
			if( in_layout )
			{
				$popup.addClass('within-layout');
				$popup.closest('.layout').addClass('popup-open');
			}
			
			
			// vars
			$popup.css({
				'margin-top' : 0 - $popup.height() - $a.outerHeight() - 14,
				'margin-left' : ( $a.outerWidth() - $popup.width() ) / 2,
			});
			
			
			// check distance to top
			var offset = $popup.offset().top;
			
			if( offset < 30 )
			{
				$popup.css({
					'margin-top' : 15
				});
				
				$popup.find('.bit').addClass('top');
			}
			
			
			$popup.children('.focus').trigger('focus');
			
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
		
		acf.get_fields({ type : 'flexible_content'}, $el).each(function(){
			
			acf.fields.flexible_content.set( $(this) ).init();
			
		});
		
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
	
	$(document).on('click', '.acf-flexible-content .acf-fc-add', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			before	= false;
			
		
		// before
		if( $(this).attr('data-before') )
		{
			before = true;
		}
		
		
		// open_popup
		acf.fields.flexible_content.set( $field ).open_popup( $a, before );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-remove', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$layout	= $a.closest('.layout');
			
			
		// remove
		acf.fields.flexible_content.set( $field ).remove( $layout );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-layout-handle', function( e ){
	
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$layout	= $a.closest('.layout');
			
		
		// toggle
		acf.fields.flexible_content.set( $field ).toggle( $layout );
		
		
		// blur
		$(this).blur();
			
	});
	
	$(document).on('click', '.acf-flexible-content .acf-fc-popup li a', function( e ){
		
		e.preventDefault();
		
		
		// vars
		var $a		= $(this),
			$field	= acf.get_field_wrap( $a ),
			$popup	= $a.closest('.acf-fc-popup')
			$layout	= null;
			
			
		// $layout
		if( $popup.hasClass('within-layout') )
		{
			$layout = $popup.closest('.layout');
		}
		
		
		// add
		acf.fields.flexible_content.set( $field ).add( $a.attr('data-layout'), $layout );
		
		
		// blur
		$(this).blur();
		
	});
	
	$(document).on('blur', '.acf-flexible-content .acf-fc-popup .focus', function( e ){
		
		var $popup = $(this).parent();
		
		
		// hide controlls?
		if( $popup.closest('.layout').exists() )
		{
			$popup.closest('.layout').removeClass('popup-open');
		}
		
		
		setTimeout(function(){
			
			$popup.remove();
			
		}, 200);

		
	});
	
	
	/*
	*  Validate
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('acf/validate_field', function( e, field ){
		
		// vars
		var $field = $( field );
		
		
		// validate
		if( ! $field.hasClass('field_type-flexible_content') )
		{
			return;
		}
		
		var $el = $field.find('.acf-flexible-content:first');
		
		
		// required
		$field.data('validation', false);
		$field.data('validation_message', false);
		
		
		if( $el.children('.values').children('.layout').exists() )
		{
			$field.data('validation', true);
		}
		
		
		// min total
		var min = parseInt( $el.attr('data-min') );
		
		if( min > 0 )
		{
			if( $el.children('.values').children('.layout').length < min )
			{
				var identifier	= ( min == 1 ) ? 'layout' : 'layouts',
					s 			= acf.l10n.flexible_content.min;
				
				// translate
				s = s.replace('{min}', min);
				s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
				
				
				$field.data('validation', false);
				$field.data('validation_message', s);
			}
		}
		
		
		// min layout
		var $popup = $( $el.children('.tmpl-popup').html() );
		
		$popup.find('a').each(function(){
			
			// vars
			var min		= parseInt( $(this).attr('data-min') ),
				max		= parseInt( $(this).attr('data-max') ),
				name	= $(this).attr('data-layout'),
				label	= $(this).text(),
				count	= $el.children('.values').children('.layout[data-layout="' + name + '"]').length;
			
			
			if( count < min )
			{
				var identifier	= ( min == 1 ) ? 'layout' : 'layouts',
					s 			= acf.l10n.flexible_content.min_layout;
				
				// translate
				s = s.replace('{min}', min);
				s = s.replace('{label}', '"' + label + '"');
				s = s.replace('{identifier}', acf.l10n.flexible_content[ identifier ]);
				
				$field.data('validation', false);
				$field.data('validation_message', s);
			}
			
		});
		
		
		
		
	});
	
	
	
	/*
	*  Gallery
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	18/08/13
	*
	*/
	
	acf.fields.gallery = {
		
		// vars	
		o		: {},
		el		: '.acf-gallery',
		
		
		// el
		$field	: null,
		$el		: null,	
		
		
		focus : function( $el ){
			
			return this.set( acf.get_closest_field($el) );
				
		},
		
		set : function( $field ){
			
			// sel $el
			this.$field = $field;
			this.$el = $field.find( this.el ).first();
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
			
			// return this for chaining
			return this;
			
		},
		
		get_attachment : function( id ){
			
			// defaults
			id = id || '';
			
			
			// vars
			var selector = '.acf-gallery-attachment';
			
			
			// update selector
			if( id === 'active' ) {
				
				selector += '.active';
				
			} else if( id ) {
				
				selector += '[data-id="' + id  + '"]';
				
			}
			
			
			// return
			return this.$el.find( selector );
			
		},
		
		count : function(){
			
			return this.get_attachment().length;
			
		},

		init : function(){
					
			// sortable
			this.$el.find('.acf-gallery-attachments').unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				forceHelperSize			: true,
				forcePlaceholderSize	: true,
				scroll					: true,
				
				start : function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
								
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			
	   			stop : function (event, ui) {
				
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			}
			});
			
			
			// resizable
			this.$el.unbind('resizable').resizable({
				handles : 's',
				minHeight: 200,
				stop: function(event, ui){
					
					acf.update_user_setting('gallery_height', ui.size.height);
				
				}
			});
			
			
			// render
			this.render();
			
			
			// resize
			this.resize();
					
		},

		render : function() {
			
			// vars
			var $select = this.$el.find('.bulk-actions'),
				$a = this.$el.find('.add-attachment');
			
			
			// disable select
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				$a.addClass('disabled');
				
			} else {
			
				$a.removeClass('disabled');
				
			}
			
		},
		
		sort : function( sort ){
			
			// validate
			if( !sort ) {
			
				return;
				
			}
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: acf.get_data( this.$field, 'key' ),
				post_id		: acf.get('post_id'),
				ids			: [],
				sort		: sort
			});
			
			
			// find and add attachment ids
			this.get_attachment().each(function(){
				
				data.ids.push( $(this).attr('data-id') );
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'get',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.sort_success
			});
			
		},
		
		sort_success : function( json ) {
		
			// validate
			if( !json || !json.success ) {
			
				return;
				
			}
			
			
			// reverse order
			json.data.reverse();
			
			
			// loop over json
			for( i in json.data ) {
				
				var id = json.data[ i ],
					$attachment = this.get_attachment(id);
				
				
				// prepend attachment
				this.$el.find('.acf-gallery-attachments').prepend( $attachment );
				
			};
			
		},
		
		clear_selection : function(){
			
			this.get_attachment().removeClass('active');
		},
		
		select : function( $attachment ){
			
			// bail early if already active
			if( $attachment.hasClass('active') ) {
				
				return;
				
			}
			
			
			// vars
			var id = $attachment.attr('data-id');
			
			
			// clear selection
			this.clear_selection();
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id );
			
			
			// open sidebar
			this.open_sidebar();
			
		},
		
		open_sidebar : function(){
			
			// add class
			this.$el.addClass('sidebar-open');
			
			
			// hide bulk actions
			this.$el.find('.bulk-actions').hide();
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 350 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 349 }, 250);
			
		},
		
		close_sidebar : function(){
			
			// remove class
			this.$el.removeClass('sidebar-open');
			
			
			// vars
			var $select = this.$el.find('.bulk-actions');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// disable sidebar
			this.$el.find('.acf-gallery-side').find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 0 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 0 }, 250, function(){
				
				$select.show();
				
				$(this).find('.acf-gallery-side-data').html( '' );
				
			});
			
		},
		
		fetch : function( id ){
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: acf.get_data( this.$field, 'key' ),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				id			: id
			});
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'get',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.render_fetch
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render_fetch : function( html ){
			
			// bail early if no html
			if( !html ) {
				
				return;	
				
			}
			
			
			// vars
			var $side = this.$el.find('.acf-gallery-side-data');
			
			
			// render
			$side.html( html );
			
			
			// remove acf form data
			$side.find('.compat-field-acf-form-data').remove();
			
			
			// detach meta tr
			var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();
			
			
			// add tr
			$side.find('> table.form-table > tbody').append( $tr );			
			
			
			// remove origional meta table
			$side.find('> .compat-attachment-fields').remove();
			
			
			// setup fields
			acf.do_action('append', $side);
			
		},
		
		save : function(){
			
			// vars
			var $a = this.$el.find('.update-attachment')
				$form = this.$el.find('.acf-gallery-side-data'),
				data = acf.serialize_form( $form );
				
				
			// validate
			if( $a.attr('disabled') ) {
			
				return false;
				
			}
			
			
			// add attr
			$a.attr('disabled', 'disabled');
			$a.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			
			
			// prepare for ajax
			acf.prepare_for_ajax(data);
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				complete	: function( json ){
					
					$a.removeAttr('disabled');
					$a.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		add : function( image ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// append to image data
			image.name = this.$el.find('[data-name="ids"]').attr('name');
			
			
			// template
			var tmpl = acf._e('gallery', 'tmpl'),
				html = _.template(tmpl, image);
			
			
			// append
			this.$el.find('.acf-gallery-attachments').append( html );
			
			
			// render
			this.render();
			
		},
		
		remove : function( id ){
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// update sidebar
			this.close_sidebar();
			
			
			// remove image
			this.get_attachment(id).remove();
			
			
			// close sidebar
			/*
if( this.count() == 0 ) {
			
				this.close_sidebar();
				
			}
*/
			
			
			// render
			this.render();
		},
		
		render_collection : function( frame ){
			
			var self = this;
			
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
				// vars
				var $content	= frame.content.get().$el
					collection	= frame.content.get().collection || null;
					

				
				if( collection ) {
					
					var i = -1;
					
					collection.each(function( item ){
					
						i++;
						
						var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
						
						
						// if image is already inside the gallery, disable it!
						if( self.get_attachment(item.id).exists() ) {
						
							item.off('selection:single');
							$li.addClass('acf-selected');
							
						}
						
					});
					
				}
			
			
			}, 10);

				
		},
		
		popup : function(){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var library = this.o.library,
				preview_size = this.o.preview_size;
			
			
			// reference
			var self = this;
			
			
			// popup
			var frame = acf.media.popup({
				'title'			: acf._e('gallery', 'select'),
				'mode'			: 'select',
				'type'			: 'all',
				'multiple'		: 'add',
				'library'		: library,
				'select'		: function( attachment, i ) {
					
					// is image already in gallery?
					if( self.get_attachment(attachment.id).exists() ) {
					
						return;
						
					}
					
					
			    	// vars
			    	var image = {
				    	'id'	: attachment.id,
				    	'url'	: attachment.attributes.url
			    	};
			    	
			    	
			    	// file?
				    if( attachment.attributes.type != 'image' ) {
				    
					    image.url = attachment.attributes.icon;
					    
				    }
				    
				    
				    // is preview size available?
			    	if( acf.isset(attachment, 'attributes', 'sizes', preview_size) ) {
			    	
				    	image.url = attachment.attributes.sizes[ preview_size ].url;
				    	
			    	}
				    
				    
			    	// add file to field
			        self.add( image );
					
				}
			});
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				self.render_collection( frame );
				
				frame.content.get().collection.on( 'reset add', function(){
				    
					self.render_collection( frame );
				    
			    });
				
			});
			
		},
		
		resize : function(){
			
			// vars
			var min = 100,
				max = 175,
				columns = 4,
				width = this.$el.width();
			
			
			// get width
			for( var i = 0; i < 10; i++ ) {
			
				var w = width/i;
				
				if( min < w && w < max ) {
				
					columns = i;
					break;
					
				}
				
			}
						
			
			// update data
			this.$el.attr('data-columns', columns);
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
		
		acf.get_fields({ type : 'gallery'}, $el).each(function(){
			
			acf.fields.gallery.set( $(this) ).init();
			
		});
		
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
	
	acf.add_action('submit', function( $el ){
		
		acf.get_fields({ type : 'gallery'}, $el).each(function(){
			
			acf.fields.gallery.set( $(this) ).close_sidebar();
			
		});
		
	});
	
	
	$(window).on('resize', function(){
		
		acf.get_fields({ type : 'gallery'}).each(function(){
			
			acf.fields.gallery.set( $(this) ).resize();
			
		});
		
	});
	
	
	
	$(document).on('click', '.acf-gallery .acf-gallery-attachment', function( e ){
		
		// focus field
		acf.fields.gallery.focus( $(this) ).select( $(this) );
		
	});
	
	$(document).on('click', '.acf-gallery .close-sidebar', function( e ){
		
		acf.fields.gallery.focus( $(this) ).close_sidebar();
		
	});	
	
	$(document).on('change', '.acf-gallery-side input, .acf-gallery-side textarea, .acf-gallery-side select', function( e ){
		
		$(this).closest('.acf-gallery-side').find('.update-attachment').trigger('click');
		
	});
	
	$(document).on('click', '.acf-gallery .update-attachment', function( e ){
		
		acf.fields.gallery.focus( $(this) ).save();
		
	});
	
	$(document).on('click', '.acf-gallery .remove-attachment', function( e ){
		
		acf.fields.gallery.focus( $(this) ).remove( $(this).attr('data-id') );
		
		
		// prevent bubble
		return false;
		
	});
	
	$(document).on('click', '.acf-gallery .add-attachment', function( e ){
		
		acf.fields.gallery.focus( $(this) ).popup();
		
	});
	
	$(document).on('change', '.acf-gallery .bulk-actions', function( e ){
		
		// sort
		acf.fields.gallery.focus( $(this) ).sort( $(this).val() );
		
		
		// reset value
		$(this).val('');
		
	});
	
	$(document).on('click', '.acf-gallery .edit-attachment', function( e ){
		
		// vars
		var id = $(this).attr('data-id');
		
		
		// popup
		var frame = acf.media.popup({
			'title'		: acf._e('image', 'edit'),
			'button'	: acf._e('image', 'update'),
			'mode'		: 'edit',
			'id'		: id,
			'select'	: function(){
				
				acf.fields.gallery.fetch( id );
				
			}
		});
		
						
				/*
selection.on('all', function( e ){
					
					console.log( 'selection all: %o', e );
					
				});
				
				
				selection.on('change', function( e ){
					
					console.log( this.$el.find('.media-sidebar .edit-attachment') );
					this.$el.find('.media-sidebar .edit-attachment').trigger('click');
					
					console.log( this );
					
				}, frame);
*/
				
				
	});
	
	

})(jQuery);
