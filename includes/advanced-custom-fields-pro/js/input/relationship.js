(function($){
	
	/*
	*  Relationship
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.relationship = {
		
		$el : null,
		$wrap : null,
		$input : null,
		$filters : null,
		$choices : null,
		$values : null,
				
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find elements
			this.$wrap = this.$el.find('.acf-relationship');
			this.$input = this.$wrap.find('.acf-hidden input');
			this.$choices = this.$wrap.find('.choices'),
			this.$values = this.$wrap.find('.values');
			
			
			// get options
			this.o = acf.get_data( this.$wrap );
			
			
			// return this for chaining
			return this;
			
		},
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// right sortable
			this.$values.children('.list').sortable({
				//axis					:	'y',
				items					:	'li',
				forceHelperSize			:	true,
				forcePlaceholderSize	:	true,
				scroll					:	true,
				update					:	function(){
					
					_this.$input.trigger('change');
					
				}
			});
			
			
			// ajax fetch values for left side
			this.fetch();
					
		},
		
		fetch : function(){
			
			// reference
			var _this = this,
				$el = this.$el;
			
			
			// add loading class, stops scroll loading
			this.$choices.children('.list').html('<p>' + acf._e('relationship', 'loading') + '...</p>');
			
			
			// vars
			var data = {
				action		: 'acf/fields/relationship/query',
				field_key	: this.$el.attr('data-key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
			};
			
			
			// merge in wrap data
			$.extend(data, this.o);

			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') )
			{
				this.$el.data('xhr').abort();
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'get',
				cache		: true,
				data		: data,
				success			:	function( json ){
					
					// render
					_this.set({ $el : $el }).render( json );
					
				}
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render : function( json ){
			
			// reference
			var _this = this;
			
			
			// no results?
			if( ! json || ! json.length )
			{
				this.$choices.children('.list').html( '<li><p>' + acf._e('relationship', 'empty') + '</p></li>' );

				return;
			}
			
			
			// append new results
			this.$choices.children('.list').html( this.walker(json) );
			
						
			// apply .disabled to left li's
			this.$values.find('.acf-relationship-item').each(function(){
				
				var id = $(this).attr('data-id');
				
				_this.$choices.find('.acf-relationship-item[data-id="' + id + '"]').addClass('disabled');
				
			});
			
			
			// underline search match
			if( this.o.s )
			{
				var s = this.o.s;
				
				this.$choices.find('.acf-relationship-item:contains("' + s + '")').each(function(){
					
					var html = $(this).html().replace( s, '<span class="match">' + s + '</span>');
					
					$(this).html( html );
				});
				
			}
			
		},
		
		walker : function( data ){
			
			// vars
			var s = '';
			
			
			// loop through data
			if( $.isArray(data) )
			{
				for( var k in data )
				{
					s += this.walker( data[ k ] );
				}
			}
			else if( $.isPlainObject(data) )
			{
				// optgroup
				if( data.children !== undefined )
				{
					s += '<li><span class="acf-relationship-label">' + data.text + '</span><ul class="acf-bl">';
					
						s += this.walker( data.children );
					
					s += '</ul></li>';
				}
				else
				{
					s += '<li><span class="acf-relationship-item" data-id="' + data.id + '">' + data.text + '</span></li>';
				}
			}
			
			
			return s;
		},
		
		add : function( $span ){
			
			// max posts
			if( this.o.max > 0 )
			{
				if( this.$values.find('.acf-relationship-item').length >= this.o.max )
				{
					alert( acf.l10n.relationship.max.replace('{max}', this.o.max) );
					return false;
				}
			}
			
			
			// can be added?
			if( $span.hasClass('disabled') )
			{
				return false;
			}
			
			
			// disable
			$span.addClass('disabled');
			
			
			// template
			var data = {
					value	:	$span.attr('data-id'),
					text	:	$span.html(),
					name	:	this.$input.attr('name')
				},
				tmpl = _.template(acf.l10n.relationship.tmpl_li, data);
			
			
	
			// add new li
			this.$values.children('.list').append( tmpl )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			this.$el.removeClass('error');
			
		},
		remove : function( $span ){
			
			// vars
			var id = $span.attr('data-id');
			
			
			// remove
			$span.parent('li').remove();
			
			
			// show
			this.$choices.find('.acf-relationship-item[data-id="' + id + '"]').removeClass('disabled');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
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
		
		acf.get_fields({ type : 'relationship'}, $el).each(function(){
			
			acf.fields.relationship.set({ $el : $(this) }).init();
			
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
	
	$(document).on('keypress', '.acf-relationship .filters [data-filter]', function( e ){
		
		// don't submit form
		if( e.which == 13 )
		{
			e.preventDefault();
		}
		
	});
	
	
	$(document).on('change keyup', '.acf-relationship .filters [data-filter]', function(e){
		
		// vars
		var val = $(this).val(),
			filter = $(this).attr('data-filter'),
			$wrap = $(this).closest('.acf-relationship');
			$el = $wrap.closest('.acf-field');
			
		
		// Bail early if filter has not changed
		if( $wrap.attr('data-' + filter) == val )
		{
			return;
		}
		
		
		// update attr
		$wrap.attr('data-' + filter, val);
		
	    
	    // fetch
	    acf.fields.relationship.set({ $el : $el }).fetch();
		
	});

	
	$(document).on('click', '.acf-relationship .choices .acf-relationship-item', function( e ){
		
		e.preventDefault();
		
		acf.fields.relationship.set({ $el : $(this).closest('.acf-field') }).add( $(this) );
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-relationship .values .acf-icon', function( e ){
		
		e.preventDefault();
		
		acf.fields.relationship.set({ $el : $(this).closest('.acf-field') }).remove( $(this).closest('.acf-relationship-item') );
		
		$(this).blur();
		
	});
	
	
	
	

})(jQuery);
