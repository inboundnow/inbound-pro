var acf;

(function($){
	
	
	/*
	*  exists
	*
	*  This function will return true if a jQuery selection exists
	*
	*  @type	function
	*  @date	8/09/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(boolean)
	*/
	
	$.fn.exists = function() {
	
		return $(this).length>0;
		
	};
	
	
	/*
	*  outerHTML
	*
	*  This function will return a string containing the HTML of the selected element
	*
	*  @type	function
	*  @date	19/11/2013
	*  @since	5.0.0
	*
	*  @param	$.fn
	*  @return	(string)
	*/
	
	$.fn.outerHTML = function() {
	    
	    return $(this).get(0).outerHTML;
	    
	};
	
	
	acf = {
		
		// vars
		l10n:	{},
		o:		{},
		
		
		/*
		*  update
		*
		*  This function will update a value found in acf.o
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	k (string) the key
		*  @param	v (mixed) the value
		*  @return	n/a
		*/
		
		update: function( k, v ){
				
			this.o[ k ] = v;
			
		},
		
		
		/*
		*  get
		*
		*  This function will return a value found in acf.o
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	k (string) the key
		*  @return	v (mixed) the value
		*/
		
		get: function( k ){
			
			if( typeof this.o[ k ] !== 'undefined' ) {
				
				return this.o[ k ];
				
			}
			
			return null;
			
		},
		
		
		/*
		*  _e
		*
		*  This functiln will return a string found in acf.l10n
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	k1 (string) the first key to look for
		*  @param	k2 (string) the second key to look for
		*  @return	string (string)
		*/
		
		_e: function( k1, k2 ){
			
			// defaults
			k2 = k2 || false;
			
			
			// get context
			var string = this.l10n[ k1 ] || '';
			
			
			// get string
			if( k2 ) {
			
				string = string[ k2 ] || '';
				
			}
			
			
			// return
			return string;
			
		},
		
		
		/*
		*  add_action
		*
		*  This function uses wp.hooks to mimics WP add_action
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		add_action: function() {
			
			// allow multiple action parameters such as 'ready append'
			var actions = arguments[0].split(' ');
			
			for( k in actions ) {
			
				// prefix action
				arguments[0] = 'acf.' + actions[ k ];
				
				wp.hooks.addAction.apply(this, arguments);
			}
			
			return this;
			
		},
		
		
		/*
		*  remove_action
		*
		*  This function uses wp.hooks to mimics WP remove_action
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		remove_action: function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeAction.apply(this, arguments);
			
			return this;
			
		},
		
		
		/*
		*  do_action
		*
		*  This function uses wp.hooks to mimics WP do_action
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		do_action: function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.doAction.apply(this, arguments);
			
			return this;
			
		},
		
		
		/*
		*  add_filter
		*
		*  This function uses wp.hooks to mimics WP add_filter
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		add_filter: function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.addFilter.apply(this, arguments);
			
			return this;
			
		},
		
		
		/*
		*  remove_filter
		*
		*  This function uses wp.hooks to mimics WP remove_filter
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		remove_filter: function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeFilter.apply(this, arguments);
			
			return this;
			
		},
		
		
		/*
		*  apply_filters
		*
		*  This function uses wp.hooks to mimics WP apply_filters
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	
		*  @return
		*/
		
		apply_filters: function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			return wp.hooks.applyFilters.apply(this, arguments);
			
		},
		
		
		/*
		*  get_selector
		*
		*  This function will return a valid selector for finding a field object
		*
		*  @type	function
		*  @date	15/01/2015
		*  @since	5.1.5
		*
		*  @param	s (string)
		*  @return	(string)
		*/
		
		get_selector: function( s ) {
			
			// defaults
			s = s || '';
			
			
			// vars
			var selector = '.acf-field';
			
			
			// compatibility with object
			if( $.isEmptyObject(s) ) {
				
				s = '';
				
			} else if( $.isPlainObject(s) ) {
				
				for( k in s ) {
				
					s = s[k];
					break;
					
				}
				
			}


			// search
			if( s ) {
				
				// append
				selector += '-' + s.replace('_', '-');
				
				
				// remove potential double up
				selector = selector.replace('field-field-', 'field-');
			
			}
			
			
			// return
			return selector;
			
		},
		
		
		/*
		*  get_fields
		*
		*  This function will return a jQuery selection of fields
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	args (object)
		*  @param	$el (jQuery) element to look within
		*  @param	all (boolean) return all fields or allow filtering (for repeater)
		*  @return	$fields (jQuery)
		*/
		
		get_fields: function( s, $el, all ){
			
			// debug
			//console.log( 'acf.get_fields(%o, %o, %o)', args, $el, all );
			//console.time("acf.get_fields");
			
			
			// defaults
			s = s || '';
			$el = $el || false;
			all = all || false;
			
			
			// vars
			var $fields = $( this.get_selector(s), $el );
			
			
			// is current $el a field?
			// this is the case when editing a field group
			/* is this neeed?
if( $el.is( selector ) ) {
			
				$fields = $fields.add( $el );
				
			}
*/
			
			
			//console.log('get_fields(%o, %o, %o) %o', s, $el, all, $fields);
			
			
			// filter out fields
			if( !all ) {
				
				$fields = acf.apply_filters('get_fields', $fields);
								
			}
			
			
			//console.log('acf.get_fields(%o):', this.get_selector(s) );
			//console.timeEnd("acf.get_fields");
			
			
			// return
			return $fields;
							
		},
		
		
		/*
		*  get_field
		*
		*  This function will return a jQuery selection based on a field key
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	field_key (string)
		*  @param	$el (jQuery) element to look within
		*  @return	$field (jQuery)
		*/
		
		get_field: function( s, $el ){
			
			// defaults
			s = s || '';
			$el = $el || false;
			
			
			// get fields
			var $fields = this.get_fields(s, $el, true);
			
			
			// check if exists
			if( $fields.exists() ) {
			
				return $fields.first();
				
			}
			
			
			// return
			return false;
			
		},
		
		
		/*
		*  get_closest_field
		*
		*  This function will return the closest parent field
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery) element to start from
		*  @param	args (object)
		*  @return	$field (jQuery)
		*/
		
		get_closest_field : function( $el, s ){
			
			// defaults
			s = s || '';
			
			
			// return
			return $el.closest( this.get_selector(s) );
			
		},
		
		
		/*
		*  get_field_wrap
		*
		*  This function will return the closest parent field
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery) element to start from
		*  @return	$field (jQuery)
		*/
		
		get_field_wrap: function( $el ){
			
			return $el.closest( this.get_selector() );
			
		},
		
		
		/*
		*  get_field_key
		*
		*  This function will return the field's key
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$field (jQuery)
		*  @return	(string)
		*/
		
		get_field_key: function( $field ){
		
			return this.get_data( $field, 'key' );
			
		},
		
		
		/*
		*  get_field_type
		*
		*  This function will return the field's type
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$field (jQuery)
		*  @return	(string)
		*/
		
		get_field_type: function( $field ){
		
			return this.get_data( $field, 'type' );
			
		},
		
		
		/*
		*  get_data
		*
		*  This function will return attribute data for a given elemnt
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery)
		*  @param	name (mixed)
		*  @return	(mixed)
		*/
		
		get_data: function( $el, name ){
			
			//console.log('get_data(%o, %o)', name, $el);
			
			
			// get all datas
			if( typeof name === 'undefined' ) {
				
				return $el.data();
				
			}
			
			
			// return
			return $el.data(name);
							
		},
		
		
		/*
		*  get_uniqid
		*
		*  This function will return a unique string ID
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	prefix (string)
		*  @param	more_entropy (boolean)
		*  @return	(string)
		*/
		
		get_uniqid : function( prefix, more_entropy ){
		
			// + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// + revised by: Kankrelune (http://www.webfaktory.info/)
			// % note 1: Uses an internal counter (in php_js global) to avoid collision
			// * example 1: uniqid();
			// * returns 1: 'a30285b160c14'
			// * example 2: uniqid('foo');
			// * returns 2: 'fooa30285b1cd361'
			// * example 3: uniqid('bar', true);
			// * returns 3: 'bara20285b23dfd1.31879087'
			if (typeof prefix === 'undefined') {
				prefix = "";
			}
			
			var retId;
			var formatSeed = function (seed, reqWidth) {
				seed = parseInt(seed, 10).toString(16); // to hex str
				if (reqWidth < seed.length) { // so long we split
					return seed.slice(seed.length - reqWidth);
				}
				if (reqWidth > seed.length) { // so short we pad
					return Array(1 + (reqWidth - seed.length)).join('0') + seed;
				}
				return seed;
			};
			
			// BEGIN REDUNDANT
			if (!this.php_js) {
				this.php_js = {};
			}
			// END REDUNDANT
			if (!this.php_js.uniqidSeed) { // init seed with big random int
				this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
			}
			this.php_js.uniqidSeed++;
			
			retId = prefix; // start with prefix, add current milliseconds hex string
			retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
			retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
			if (more_entropy) {
				// for more entropy we add a float lower to 10
				retId += (Math.random() * 10).toFixed(8).toString();
			}
			
			return retId;
			
		},
		
		
		/*
		*  serialize_form
		*
		*  This function will create an object of data containing all form inputs within an element
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery selection)
		*  @param	prefix (string)
		*  @return	$post_id (int)
		*/
		
		serialize_form : function( $el, prefix ){
			
			// defaults
			prefix = prefix || '';
			
			
			// vars
			var data = {},
				names = {},
				prelen = prefix.length,
				_prefix = '_' + prefix,
				_prelen = _prefix.length;
			
			
			// selector
			$selector = $el.find('select, textarea, input');
			
			
			// filter out hidden field groups
			$selector = $selector.filter(function(){
				
				return $(this).closest('.postbox.acf-hidden').exists() ? false : true;
								
			});
			
			
			// populate data
			$.each( $selector.serializeArray(), function( i, pair ) {
				
				// bail early if name does not start with acf or _acf
				if( prefix && pair.name.substring(0, prelen) != prefix && pair.name.substring(0, _prelen) != _prefix ) {
					
					return;
					
				}
				
				
				// initiate name
				if( pair.name.slice(-2) === '[]' ) {
					
					// remove []
					pair.name = pair.name.replace('[]', '');
					
					
					// initiate counter
					if( typeof names[ pair.name ] === 'undefined'){
						
						names[ pair.name ] = -1;
					}
					
					
					// increase counter
					names[ pair.name ]++;
					
					
					// add key
					pair.name += '[' + names[ pair.name ] +']';
				}
				
				
				// append to data
				data[ pair.name ] = pair.value;
				
			});
			
			
			// return
			return data;
		},
		
		
		/*
		*  remove_tr
		*
		*  This function will remove a tr element with animation
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$tr (jQuery selection)
		*  @param	callback (function) runs on complete
		*  @return	n/a
		*/
		
		remove_tr : function( $tr, callback ){
			
			// vars
			var height = $tr.height(),
				children = $tr.children().length;
			
			
			// add class
			$tr.addClass('acf-remove-element');
			
			
			// after animation
			setTimeout(function(){
				
				// remove class
				$tr.removeClass('acf-remove-element');
				
				
				// vars
				$tr.html('<td style="padding:0; height:' + height + 'px" colspan="' + children + '"></td>');
				
				
				$tr.children('td').animate({ height : 0}, 250, function(){
					
					$tr.remove();
					
					if( typeof(callback) == 'function' ) {
					
						callback();
					
					}
					
					
				});
				
					
			}, 250);
			
		},
		
		
		/*
		*  remove_el
		*
		*  This function will remove an element with animation
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery selection)
		*  @param	callback (function) runs on complete
		*  @param	end_height (int)
		*  @return	n/a
		*/
		
		remove_el : function( $el, callback, end_height ){
			
			// defaults
			end_height = end_height || 0;
			
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute',
				padding		: 0
			});
			
			
			// wrap field
			$el.wrap( '<div class="acf-temp-wrap" style="height:' + $el.outerHeight(true) + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// remove
			$el.parent('.acf-temp-wrap').animate({ height : end_height }, 250, function(){
				
				$(this).remove();
				
				if( typeof(callback) == 'function' ) {
				
					callback();
				
				}
				
			});
			
			
		},
		
		
		/*
		*  isset
		*
		*  This function will return true if an object key exists
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	(object)
		*  @param	key1 (string)
		*  @param	key2 (string)
		*  @param	...
		*  @return	(boolean)
		*/
		
		isset : function(){
			
			var a = arguments,
		        l = a.length,
		        c = null,
		        undef;
			
		    if (l === 0) {
		        throw new Error('Empty isset');
		    }
			
			c = a[0];
			
		    for (i = 1; i < l; i++) {
		    	
		        if (a[i] === undef || c[ a[i] ] === undef) {
		            return false;
		        }
		        
		        c = c[ a[i] ];
		        
		    }
		    
		    return true;	
			
		},
		
		
		/*
		*  maybe_get
		*
		*  This function will attempt to return a value and return null if not possible
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	(object)
		*  @param	key1 (string)
		*  @param	key2 (string)
		*  @param	...
		*  @return	(mixed)
		*/
		
		maybe_get: function(){
			
			var a = arguments,
		        l = a.length,
		        c = null,
		        undef;
			
		    if (l === 0) {
		        return null;
		    }
			
			c = a[0];
			
		    for (i = 1; i < l; i++) {
		    	
		        if (a[i] === undef || c[ a[i] ] === undef) {
		            return null;
		        }
		        
		        c = c[ a[i] ];
		        
		    }
		    
		    return c;
			
		},
		
		
		/*
		*  open_popup
		*
		*  This function will create and open a popup modal
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	args (object)
		*  @return	n/a
		*/
		
		open_popup : function( args ){
			
			// vars
			$popup = $('body > #acf-popup');
			
			
			// already exists?
			if( $popup.exists() ) {
			
				return update_popup(args);
				
			}
			
			
			// template
			var tmpl = [
				'<div id="acf-popup">',
					'<div class="acf-popup-box acf-box">',
						'<div class="title"><h3></h3><a href="#" class="acf-icon acf-close-popup"><i class="acf-sprite-delete "></i></a></div>',
						'<div class="inner"></div>',
						'<div class="loading"><i class="acf-loading"></i></div>',
					'</div>',
					'<div class="bg"></div>',
				'</div>'
			].join('');
			
			
			// append
			$('body').append( tmpl );
			
			
			$('#acf-popup').on('click', '.bg, .acf-close-popup', function( e ){
				
				e.preventDefault();
				
				acf.close_popup();
				
			});
			
			
			// update
			return this.update_popup(args);
			
		},
		
		
		/*
		*  update_popup
		*
		*  This function will update the content within a popup modal
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	args (object)
		*  @return	n/a
		*/
		
		update_popup : function( args ){
			
			// vars
			$popup = $('#acf-popup');
			
			
			// validate
			if( !$popup.exists() )
			{
				return false
			}
			
			
			// defaults
			args = $.extend({}, {
				title	: '',
				content : '',
				width	: 0,
				height	: 0,
				loading : false
			}, args);
			
			
			if( args.width ) {
			
				$popup.find('.acf-popup-box').css({
					'width'			: args.width,
					'margin-left'	: 0 - (args.width / 2),
				});
				
			}
			
			if( args.height ) {
			
				$popup.find('.acf-popup-box').css({
					'height'		: args.height,
					'margin-top'	: 0 - (args.height / 2),
				});	
				
			}
			
			if( args.title ) {
			
				$popup.find('.title h3').html( args.title );
			
			}
			
			if( args.content ) {
			
				$popup.find('.inner').html( args.content );
				
			}
			
			if( args.loading ) {
			
				$popup.find('.loading').show();
				
			} else {
			
				$popup.find('.loading').hide();
				
			}
			
			return $popup;
		},
		
		
		/*
		*  close_popup
		*
		*  This function will close and remove a popup modal
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		close_popup : function(){
			
			// vars
			$popup = $('#acf-popup');
			
			
			// already exists?
			if( $popup.exists() )
			{
				$popup.remove();
			}
			
		},
		
		
		/*
		*  update_user_setting
		*
		*  This function will send an AJAX request to update a user setting
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		update_user_setting : function( name, value ) {
			
			// ajax
			$.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				data		: acf.prepare_for_ajax({
					'action'	: 'acf/update_user_setting',
					'name'		: name,
					'value'		: value
				})
			});
			
		},
		
		
		/*
		*  prepare_for_ajax
		*
		*  This function will prepare data for an AJAX request
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	args (object)
		*  @return	args
		*/
		
		prepare_for_ajax : function( args ) {
			
			// nonce
			args.nonce = acf.get('nonce');
			
			
			// filter for 3rd party customization
			args = acf.apply_filters('prepare_for_ajax', args);	
			
			
			// return
			return args;
			
		},
		
		
		/*
		*  is_ajax_success
		*
		*  This function will return true for a successful WP AJAX response
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	json (object)
		*  @return	(boolean)
		*/
		
		is_ajax_success : function( json ) {
			
			if( json && json.success ) {
				
				return true;
				
			}
			
			return false;
			
		},
		
		update_cookie : function( name, value, days ) {
			
			// defaults
			days = days || 31;
			
			if (days) {
				var date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				var expires = "; expires="+date.toGMTString();
			}
			else var expires = "";
			document.cookie = name+"="+value+expires+"; path=/";
			
		},
		
		get_cookie : function( name ) {
			
			var nameEQ = name + "=";
			var ca = document.cookie.split(';');
			for(var i=0;i < ca.length;i++) {
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1,c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
			}
			return null;
			
		},
		
		delete_cookie : function( name ) {
			
			this.update_cookie(name,"",-1);
			
		},
		
		
		/*
		*  is_in_view
		*
		*  This function will return true if a jQuery element is visible in browser
		*
		*  @type	function
		*  @date	8/09/2014
		*  @since	5.0.0
		*
		*  @param	$el (jQuery)
		*  @return	(boolean)
		*/
		
		is_in_view: function( $el ) {
			
			var docViewTop = $(window).scrollTop();
		    var docViewBottom = docViewTop + $(window).height();
		
		    var elemTop = $el.offset().top;
		    var elemBottom = elemTop + $el.height();
		
		    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
					
		},
		
		
		/*
		*  val
		*
		*  This function will update an elements value and trigger the change event if different
		*
		*  @type	function
		*  @date	16/10/2014
		*  @since	5.0.9
		*
		*  @param	$el (jQuery)
		*  @param	val (mixed)
		*  @return	n/a
		*/
		
		val: function( $el, val ){
			
			// vars
			var orig = $el.val();
			
			
			// update value
			$el.val( val );
			
			
			// trigger change
			if( val != orig ) {
				
				$el.trigger('change');
				
			}
			
		}
		
	};
	
	
	/*
	*  acf.model
	*
	*  This model acts as a scafold for action.event driven modules
	*
	*  @type	object
	*  @date	8/09/2014
	*  @since	5.0.0
	*
	*  @param	(object)
	*  @return	(object)
	*/
	
	acf.model = {
		
		// vars
		actions:	{},
		filters:	{},
		events:		{},
		
		
		extend: function( args ){
			
			// extend
			var model = $.extend( {}, this, args );
			
			
			// setup actions
			$.each(model.actions, function( name, callback ){
				
				// split
				var data = name.split(' ');
				
				
				// add missing priority
				var name = data[0] || '',
					priority = data[1] || 10;
				
				
				// add action
				acf.add_action(name, model[ callback ], priority, model);
			
			});
			
			
			// setup filters
			$.each(model.filters, function( name, callback ){
				
				// split
				var data = name.split(' ');
				
				
				// add missing priority
				var name = data[0] || '',
					priority = data[1] || 10;
				
				
				// add action
				acf.add_filter(name, model[ callback ], priority, model);
			
			});
			
			
			// setup events
			$.each(model.events, function( k, callback ){
				
				// vars
				var event = k.substr(0,k.indexOf(' ')),
					selector = k.substr(k.indexOf(' ')+1);
				
				
				// add event
				$(document).on(event, selector, function( e ){
					
					// appen $el to event object
					e.$el = $(this);
					
					
					// callback
					model[ callback ].apply(model, [e]);
					
				});
				
			});
			
			
			// return
			return model;
			
		}
		
	};
	
	
	
	
	/*
	*  media
	*
	*  This model contains all functionallity to select and edit attachments
	*
	*  @type	function
	*  @date	8/09/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.media = acf.model.extend({
		
		actions: {
			'ready':	'onReady',
			'load':		'onLoad'
		},
		
		popup : function( args ) {
			
			// defaults
			var defaults = {
				'mode'			: 'select', // 'upload'|'edit'
				'title'			: '',		// 'Upload Image'
				'button'		: '',		// 'Select Image'
				'type'			: '',		// 'image'
				'library'		: 'all',	// 'all'|'uploadedTo'
				'multiple'		: false,	// false, true, 'add'
			};
			
			
			// vars
			args = $.extend({}, defaults, args);
			
			
			// frame options
			var options = {
				'title'			: args.title,
				'multiple'		: args.multiple,
				'library'		: {},
				'states'		: [],
			};
			
			
			// add library
			if( args.type ) {
				
				options.library = {
					'type' : args.type
				};
				
			}
			
			
			// limit query
			if( args.mode == 'edit' ) {
				
				options.library = {
					'post__in' : [args.id]
				};
				
			}
			
			
			// add button
			if( args.button ) {
			
				options.button = {
					'text' : args.button
				};
				
			}
			
			
			// add states
			options.states = [
				
				// main state
				new wp.media.controller.Library({
					library		: wp.media.query( options.library ),
					multiple	: options.multiple,
					title		: options.title,
					priority	: 20,
					filterable	: 'all',
					editable	: true,

					// If the user isn't allowed to edit fields,
					// can they still edit it locally?
					allowLocalEdits: true,
				})
				
			];
			
			
			// edit image functionality (added in WP 3.9)
			if( typeof wp.media.controller.EditImage !== 'undefined' ) {
				
				options.states.push( new wp.media.controller.EditImage() );
				
			}
			
			
			// create frame
			var frame = wp.media( options );
			
			
			// log events
			/*
frame.on('all', function( e ) {
				
				console.log( 'frame all: %o', e );
			
			});
*/
			
			
			// edit image view
			// source: media-views.js:2410 editImageContent()
			frame.on('content:render:edit-image', function(){
				
				var image = this.state().get('image'),
					view = new wp.media.view.EditImage( { model: image, controller: this } ).render();
	
				this.content.set( view );
	
				// after creating the wrapper view, load the actual editor via an ajax call
				view.loadEditor();
				
			}, frame);
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				// populate above vars making sure to allow for failure
				try {
					
					var content = frame.content.get(),
						toolbar = content.toolbar,
						filters = toolbar.get('filters');
				
				} catch(e) {
				
					// one of the objects was 'undefined'... perhaps the frame open is Upload Files
					// console.log( 'error %o', e );
					return;
					
				}
				
				
				// uploaded to post
				if( args.library == 'uploadedTo' && $.isNumeric(acf.get('post_id')) ) {
					
					// remove 'uploaded' option
					filters.$el.find('option[value="uploaded"]').remove();
					
					
					// add 'uploadedTo' text
					filters.$el.after('<span class="acf-uploadedTo">' + acf._e('image', 'uploadedTo') + '</span>')
					
					
					// add uploadedTo to filters
					$.each( filters.filters, function( k, v ){
						
						v.props.uploadedTo = acf.get('post_id');
						
					});
				
				}
				
				
				// type = image
				if( args.type == 'image' ) {
					
					// filter only images
					$.each( filters.filters, function( k, v ){
					
						v.props.type = 'image';
						
					});
					
					
					// remove non image options from filter list
					filters.$el.find('option').each(function(){
						
						// vars
						var v = $(this).attr('value');
						
						
						// don't remove the 'uploadedTo' if the library option is 'all'
						if( v == 'uploaded' && args.library == 'all' ) {
						
							return;
							
						}
						
						
						// remove this option
						if( v.indexOf('image') === -1 ) {
						
							$(this).remove();
							
						}
						
					});
					
					
					// set default filter
					filters.$el.val('image');
					
				}
				
				
				// trigger change
				filters.$el.trigger('change')
				
				
			});
			
			
			// select callback
			if( typeof args.select === 'function' ) {
			
			frame.on( 'select', function() {
				
				// reference
				var self = this,
					i = -1;
				
								
				// get selected images
				var selection = frame.state().get('selection');
				
				
				// loop over selection
				if( selection ) {
					
					selection.each(function( attachment ){
						
						i++;
						
						args.select.apply( self, [ attachment, i] );
						
					});
				}
				
			});
			
			}
			
			
			// close
			frame.on('close',function(){
			
				setTimeout(function(){
					
					// detach
					frame.detach();
					frame.dispose();
					
					
					// reset var
					frame = null;
					
				}, 500);
				
			});
			
			
			// edit mode
			if( args.mode == 'edit' ) {
				
			frame.on('open',function() {
				
				// set to browse
				if( this.content.mode() != 'browse' ) {
				
					this.content.mode('browse');
					
				}
				
				
				// add class
				this.$el.closest('.media-modal').addClass('acf-media-modal acf-expanded');
					
				
				// set selection
				var state 		= this.state(),
					selection	= state.get('selection'),
					attachment	= wp.media.attachment( args.id );
				
				
				selection.add( attachment );
						
			}, frame);
			
			frame.on('close',function(){
				
				// remove class
				frame.$el.closest('.media-modal').removeClass('acf-media-modal');
				
			});
				
			}
			
			
			// add button
			if( args.button ) {
			
			/*
			*  Notes
			*
			*  The normal button setting seems to break the 'back' functionality when editing an image.
			*  As a work around, the following code updates the button text.
			*/
			
			frame.on( 'toolbar:create:select', function( toolbar ) {
				
				options = {
					'text'			: args.button,
					'controller'	: this
				};	

				toolbar.view = new wp.media.view.Toolbar.Select( options );
				
				
			}, frame );
					
			}
			
			
			// open popup
			setTimeout(function(){
				
				frame.open();
				
			}, 1);
			
			
			// return
			return frame;
			
		},
		
		onReady: function(){
			
			// vars
			var version = acf.get('wp_version');
			
			
			// bail early if no version
			if( !version ) {
				
				return;
				
			}
			
			
			// use only major version
			if( typeof version == 'string' ) {
				
				version = version.substr(0,1);
				
			}
			
			
			$('body').addClass('acf-wp-' + version);
			
		},
		
		onLoad: function(){
			
			// bail early if wp.media does not exist (field group edit page)
			if( typeof wp == 'undefined' ) {
			
				return false;
				
			}
			
			
			// validate prototype
			if( ! acf.isset(wp, 'media', 'view', 'AttachmentCompat', 'prototype') ) {
			
				return false;
				
			}
			
			
			// vars
			var _prototype = wp.media.view.AttachmentCompat.prototype;
			
			
			// orig
			_prototype.orig_render = _prototype.render;
			_prototype.orig_dispose = _prototype.dispose;
			
			
			// modify render
			_prototype.render = function() {
				
				// reference
				var _this = this;
				
				
				// validate
				if( _this.ignore_render ) {
				
					return this;	
					
				}
				
				
				// run the old render function
				this.orig_render();
				
				
				// add button
				setTimeout(function(){
					
					// vars
					var $media_model = _this.$el.closest('.media-modal');
					
					
					// is this an edit only modal?
					if( $media_model.hasClass('acf-media-modal') ) {
					
						return;	
						
					}
					
					
					// does button already exist?
					if( $media_model.find('.media-frame-router .acf-expand-details').exists() ) {
					
						return;	
						
					}
					
					
					// create button
					var button = $([
						'<a href="#" class="acf-expand-details">',
							'<span class="is-closed"><span class="acf-icon small"><i class="acf-sprite-left"></i></span>' + acf._e('expand_details') +  '</span>',
							'<span class="is-open"><span class="acf-icon small"><i class="acf-sprite-right"></i></span>' + acf._e('collapse_details') +  '</span>',
						'</a>'
					].join('')); 
					
					
					// add events
					button.on('click', function( e ){
						
						e.preventDefault();
						
						if( $media_model.hasClass('acf-expanded') ) {
						
							$media_model.removeClass('acf-expanded');
							
						} else {
							
							$media_model.addClass('acf-expanded');
							
						}
						
					});
					
					
					// append
					$media_model.find('.media-frame-router').append( button );
						
				
				}, 0);
				
				
				// setup fields
				// The clearTimout is needed to prevent many setup functions from running at the same time
				clearTimeout( acf.media.render_timout );
				acf.media.render_timout = setTimeout(function(){
					
					acf.do_action('append', _this.$el);
					
				}, 50);

				
				// return based on the original render function
				return this;
			};
			
			
			// modify dispose
			_prototype.dispose = function() {
				
				// remove
				acf.do_action('remove', this.$el);
				
				
				// run the old render function
				this.orig_dispose();
				
			};
			
			
			// override save
			_prototype.save = function( event ) {
			
				if( event ) {
					
					event.preventDefault();
					
				}
				
				
				// serialize form
				var data = acf.serialize_form(this.$el);
				
				
				// ignore render
				this.ignore_render = true;
				
				
				// save
				this.model.saveCompat( data );
				
			};
			
			
			// update the wp.media.view.settings.post.id setting
			setTimeout(function(){
			
				// Hack for CPT without a content editor
				try {
				
					// post_id may be string (user_1) and therefore, the uploaded image cannot be attached to the post
					if( $.isNumeric(acf.o.post_id) ) {
					
						wp.media.view.settings.post.id = acf.o.post_id;
						
					}
					
				} catch(e) {}
				
			}, 10);
			
			
		}
	});
	
	
	/*
	*  layout
	*
	*  description
	*
	*  @type	function
	*  @date	21/02/2014
	*  @since	3.5.1
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	acf.layout = acf.model.extend({
		
		active: 0,
		
		actions: {
			'refresh': 	'refresh',
		},
		
		refresh: function( $el ){
			
			//console.time('acf.width.render');
			
			// defaults
			$el = $el || false;
			
			
			// loop over visible fields
			$('.acf-fields:visible', $el).each(function(){
				
				// vars
				var $els = $(),
					top = 0,
					height = 0,
					cell = -1;
				
				
				// get fields
				var $fields = $(this).children('.acf-field[data-width]:visible');
				
				
				// bail early if no fields
				if( !$fields.exists() ) {
					
					return;
					
				}
				
				
				// reset fields
				$fields.removeClass('acf-r0 acf-c0').css({'min-height': 0});
				
				
				$fields.each(function( i ){
					
					// vars
					var $el = $(this),
						this_top = $el.position().top;
					
					
					// set top
					if( i == 0 ) {
						
						top = this_top;
						
					}
					
					
					// detect new row
					if( this_top != top ) {
						
						// set previous heights
						$els.css({'min-height': (height+1)+'px'});
						
						// reset
						$els = $();
						top = $el.position().top; // don't use variable as this value may have changed due to min-height css
						height = 0;
						cell = -1;
						
					}
					
											
					// increase
					cell++;
				
					// set height
					height = ($el.outerHeight() > height) ? $el.outerHeight() : height;
					
					// append
					$els = $els.add( $el );
					
					// add classes
					if( this_top == 0 ) {
						
						$el.addClass('acf-r0');
						
					} else if( cell == 0 ) {
						
						$el.addClass('acf-c0');
						
					}
					
				});
				
				
				// clean up
				if( $els.exists() ) {
					
					$els.css({'min-height': (height+1)+'px'});
					
				}
				
				
			});
			
			//console.timeEnd('acf.width.render');

			
		}
		
	});
	
	
	/*
	*  conditional_logic
	*
	*  description
	*
	*  @type	function
	*  @date	21/02/2014
	*  @since	3.5.1
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	acf.conditional_logic = acf.model.extend({
		
		actions: {
			'ready 20': 	'render',
			'append 20': 	'render'
		},
		
		events: {
			'change .acf-field input': 		'change',
			'change .acf-field textarea': 	'change',
			'change .acf-field select': 	'change'
		},
		
		items: {},
		triggers: {},
		cache: {},
		
		add : function( key, groups ){
			
			// debug
			//console.log( 'conditional_logic.add(%o, %o)', key, groups );
			
			
			// reference
			var self = this;
			
			
			// append items
			this.items[ key ] = groups;
			
			
			// populate triggers
			for( var i in groups ) {
				
				var group = groups[i];
				
				for( var k in group ) {
					
					var rule = group[k];
					
					// add rule.field to triggers
					if( typeof this.triggers[rule.field] === 'undefined' ) {
					
						this.triggers[rule.field] = [];
						
					}
					
					
					// ignore trigger if already exists
					if( this.triggers[rule.field].indexOf(key) !== -1 ) {
					
						 continue;
						 
					}
					
					
					// append key to this trigger
					this.triggers[rule.field].push( key );
										
				}
				
			}
			
		},
		
		change : function( e ){
			
			// debug
			//console.log( 'conditional_logic.change(%o)', $input );
			
			
			// vars
			var $input	= e.$el,
				$field	= acf.get_field_wrap( $input ),
				$parent = $field.parent(),
				key		= acf.get_field_key( $field );
			
			
			// bail early if this field does not trigger any actions
			if( typeof this.triggers[key] === 'undefined' ) {
				
				return false;
				
			}
			
			
			// update visibility
			for( var i in this.triggers[ key ] ) {
				
				// get the target key
				var target_key = this.triggers[ key ][ i ];
				
				
				// get targets
				var $targets = acf.get_fields(target_key, $parent, true);
				
				
				this.render_fields( $targets );
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('refresh', $input);
			
		},
		
		render : function( $el ){
			
			// debug
			//console.log('conditional_logic.render(%o)', $el);
			
			
			// defaults
			$el = $el || false;
			
			
			// get targets
			var $targets = acf.get_fields( '', $el, true );
			
			
			// render fields
			this.render_fields( $targets );
			
			
			// action for 3rd party customization
			acf.do_action('refresh', $el);
			
		},
		
		render_fields : function( $targets ) {
		
			// reference
			var self = this;
			
			
			// loop over targets and render them			
			$targets.each(function(){
					
				self.render_field( $(this) );
				
			});
			
			
			// clear cache
			this.cache = {};
			
		},
		
		render_field : function( $field ){
			
			// reference
			var self = this;
			
			
			// vars
			var visibility	= false,
				key			= acf.get_field_key( $field );
				
			
			// bail early if this field does not contain any conditional logic
			if( typeof this.items[key] === 'undefined' ) {
				
				return false;
				
			}
			
			
			// debug
			//console.log( 'conditional_logic.render_field(%o)', $field );
			
			
			// get conditional logic
			var groups = this.items[ key ];
			
			
			// calculate visibility
			for( var i in groups ) {
				
				// vars
				var group		= groups[i],
					match_group	= true;
				
				for( var k in group ) {
					
					var rule = group[k];
					
					if( !self.get_visibility( $field, rule) ) {
						
						match_group = false;
						break;
						
					}
										
				}
				
				
				if( match_group ) {
					
					visibility = true;
					break;
					
				}
				
			}
			
			
			// hide / show field
			if( visibility ) {
				
				self.show_field( $field );					
			
			} else {
				
				self.hide_field( $field );
			
			}
			
		},
		
		show_field : function( $field ){
			
			// add class
			$field.removeClass( 'hidden-by-conditional-logic' );
			
			
			// remove "disabled"
			// ignore inputs which have a class of 'acf-disabled'. These inputs are disabled for life
			$field.find('input, textarea, select').not('.acf-disabled').removeAttr('disabled');
			
			
			// action for 3rd party customization
			acf.do_action('conditional_logic_show_field', $field );
			acf.do_action('show_field', $field, 'conditional_logic' );
			
		},
		
		hide_field : function( $field ){
			
			// debug
			//console.log( 'conditional_logic.hide_field(%o)', $field );
			
			
			// add class
			$field.addClass( 'hidden-by-conditional-logic' );
			
			
			// add "disabled"
			$field.find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// action for 3rd party customization
			acf.do_action('conditional_logic_hide_field', $field );
			acf.do_action('hide_field', $field, 'conditional_logic' );
			
		},
		
		get_visibility : function( $target, rule ){
			
			//console.log( 'conditional_logic.get_visibility(%o, %o)', $target, rule );
			
			// update cache (cache is cleared after render_fields)
			if( !acf.isset(this.cache, rule.field) ) {
				
				//console.log('get_fields(%o)', rule.field);
				
				// get all fields for this field_key and store in cache
				this.cache[ rule.field ] = acf.get_fields(rule.field, false, true);
				
			}
			
			
			// vars
			var $triggers = this.cache[ rule.field ],
				$trigger = null;
			
			
			// bail early if no triggers found
			if( !$triggers.exists() ) {
				
				return false;
				
			}
			
			
			// set $trigger
			$trigger = $triggers.first();
			
			
			// find better $trigger
			if( $triggers.length > 1 ) {
				
				$triggers.each(function(){
					
					// vars
					$parent = $(this).parent();
					
					
					if( $target.closest( $parent ).exists() ) {
						
						$trigger = $(this);
						return false;
					}

				});
				
			}
			
			
			// calculate
			var visibility = this.calculate( rule, $trigger, $target );
			
			
			// return
			return visibility;
		},
		
		calculate : function( rule, $trigger, $target ){
			
			// debug
			//console.log( 'calculate(%o, %o, %o)', rule, $trigger, $target);
			
			
			// vars
			var type = acf.get_data($trigger, 'type');
			
			
			// input with :checked
			if( type == 'true_false' || type == 'checkbox' || type == 'radio' ) {
				
				var exists = $trigger.find('input[value="' + rule.value + '"]:checked').exists();
				
				if( rule.operator == "==" && exists ) {
				
					return true;
					
				} else if( rule.operator == "!=" && !exists ) {
				
					return true;
					
				}
				
			} else if( type == 'select' ) {
				
				// vars
				var $select = $trigger.find('select'),
					data = acf.get_data( $select ),
					val = [];
				
				
				if( data.multiple && data.ui ) {
					
					$trigger.find('.acf-select2-multi-choice').each(function(){
						
						val.push( $(this).val() );
						
					});
					
				} else if( data.multiple ) {
					
					val = $select.val();
					
				} else if( data.ui ) {
					
					val.push( $trigger.find('input').first().val() );
					
				} else {
					
					val.push( $select.val() );
				
				}
				
				
				if( rule.operator == "==" ) {
					
					if( $.inArray(rule.value, val) > -1 ) {
					
						return true;
						
					}
					
				} else {
				
					if( $.inArray(rule.value, val) < 0 ) {
					
						return true;
						
					}
					
				}
				
			}
			
			
			// return
			return false;
			
		}
		
	});
	
	
	
	/*
	*  ready
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(document).ready(function(){
		
		// action for 3rd party customization
		acf.do_action('ready', $('body'));
		
	});
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(window).load(function(){
		
		// action for 3rd party customization
		acf.do_action('load', $('body'));
		
	});
	
	
	/*
	*  preventDefault helper
	*
	*  This function will prevent default of any link with an href of #
	*
	*  @type	function
	*  @date	24/07/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(document).on('click', '.acf-field a[href="#"]', function( e ){
		
		e.preventDefault();
		
	});
	
	
	/*
	*  Force revisions
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	$(document).on('change', '.acf-field input, .acf-field textarea, .acf-field select', function(){
		
		// preview hack
		if( $('#acf-form-data input[name="_acfchanged"]').exists() ) {
		
			$('#acf-form-data input[name="_acfchanged"]').val(1);
			
		}
		
		
		// update setting
		acf.update('changed', true);
		
	});
	
	
	/*
	*  unload
	*
	*  description
	*
	*  @type	function
	*  @date	1/09/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	var unload = function(){
			
		if( acf.get('changed') ) {
			
			return acf._e('unload');
			
		}
		
	};	
	
	
	// add unload if validation fails
	acf.add_filter('validation_complete', function( json, $form ){
		
		if( json.errors ) {
			
			$(window).on('beforeunload', unload);
			
		}
		
		
		// return
		return json;
		
	});
	
	
	// remove unload when submitting form
	$(document).on('submit', 'form', function( e ){
		
		$(window).off('beforeunload', unload);
						
	});
	
	acf.add_action('submit', function( $form ){
		
		$(window).off('beforeunload', unload);
						
	});
	
	
	// add unload event
	$(window).on('beforeunload', unload);
			
	
	/*
	*  Sortable
	*
	*  These functions will hook into the start and stop of a jQuery sortable event and modify the item and placeholder to look seamless
	*
	*  @type	function
	*  @date	12/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('sortstart', function( $item, $placeholder ){
		
		// if $item is a tr, apply some css to the elements
		if( $item.is('tr') )
		{
			// temp set as relative to find widths
			$item.css('position', 'relative');
			
			
			// set widths for td children		
			$item.children().each(function(){
			
				$(this).width($(this).width());
				
			});
			
			
			// revert position css
			$item.css('position', 'absolute');
			
			
			// add markup to the placeholder
			$placeholder.html('<td style="height:' + $item.height() + 'px; padding:0;" colspan="' + $item.children('td').length + '"></td>');
		}
		
	});
	
	
	
	/*
	*  before & after duplicate
	*
	*  This function will modify the DOM before it is cloned. Primarily fixes a cloning issue with select elements
	*
	*  @type	function
	*  @date	16/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('before_duplicate', function( $orig ){
		
		// save select values
		$orig.find('select').each(function(){
			
			$(this).find(':selected').addClass('selected');
			
		});
		
	});
	
	acf.add_action('after_duplicate', function( $orig, $duplicate ){
		
		// restore select values
		$orig.find('select').each(function(){
			
			$(this).find('.selected').removeClass('selected');
			
		});
		
		
		// set select values
		$duplicate.find('select').each(function(){
			
			var $selected = $(this).find('.selected');
			
			$(this).val( $selected.attr('value') );
			
			$selected.removeClass('selected');
			
		});
		
	});
	
	
	/*
	*  field model
	*
	*  description
	*
	*  @type	function
	*  @date	14/08/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('ready', function( $el ){
				
		acf.get_fields('', $el).each(function(){
			
			acf.do_action('ready_field', $(this));
			acf.do_action('ready_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	acf.add_action('append', function( $el ){
				
		acf.get_fields('', $el).each(function(){
			
			acf.do_action('append_field', $(this));
			acf.do_action('append_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	acf.add_action('load', function( $el ){
				
		acf.get_fields('', $el).each(function(){
			
			acf.do_action('load_field', $(this));
			acf.do_action('load_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	
	acf.add_action('remove', function( $el ){
				
		acf.get_fields('', $el).each(function(){
			
			acf.do_action('remove_field', $(this));
			acf.do_action('remove_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	acf.add_action('sortstart', function( $item, $placeholder ){
				
		acf.get_fields('', $item).each(function(){
			
			acf.do_action('sortstart_field', $(this));
			acf.do_action('sortstart_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	acf.add_action('sortstop', function( $item, $placeholder ){
				
		acf.get_fields('', $item).each(function(){
			
			acf.do_action('sortstop_field', $(this));
			acf.do_action('sortstop_field/type=' + acf.get_field_type($(this)), $(this));
			
		});
		
	});
	
	acf.add_action('hide_field', function( $el, context ){
				
		acf.do_action('hide_field/type=' + acf.get_field_type($el), $el, context);
		
	});
	
	acf.add_action('show_field', function( $el, context ){
				
		acf.do_action('show_field/type=' + acf.get_field_type($el), $el, context);
		
	});
	
	
	acf.fields = {};
	acf.field = {
		
		// vars
		type:		'',
		o:			{},
		actions:	{},
		events:		{},
		$field:		null,
		
		extend: function( args ){
			
			// extend
			var model = $.extend( {}, this, args );
			
			
			// setup actions
			$.each(model.actions, function( action, callback ){
				
				// vars
				var action = action + '_field/type=' + model.type;
				
				acf.add_action(action, function(){
					
					[].unshift.apply(arguments, [callback]);
					
					model.doAction.apply(model, arguments);
					
				});
			
			});
			
			
			// setup events
			var context = acf.get_selector(model.type);
			
			$.each(model.events, function( k, callback ){
				
				var event = k.substr(0,k.indexOf(' ')),
					selector = k.substr(k.indexOf(' ')+1);
				
				$(document).on(event, context + ' ' + selector, function( e ){
					
					e.$el = $(this);
					
					model.doEvent.apply(model, [ callback, e ]);
					
				});
				
			});
			
			
			// return
			return model;
			
		},
		
		doFocus: function( $field ){
			
			// focus on $field
			this.$field = $field;
			
			
			// callback
			if( typeof this.focus === 'function' ) {
				
				this.focus();
				
			}
			
			
			// return for chaining
			return this;
			
		},
		
		doAction: function(){
			
			// debug
			//console.log('doAction(%o)', arguments);
			
			
			// remove callback from arguments
			var callback = [].shift.apply(arguments);
			
			
			// focus
			this.doFocus( arguments[0] );
			
			
			// callback
			this[ callback ].apply(this, arguments);
			
		},
		
		doEvent: function( callback, e ){
			
			// debug
			//console.log('doEvent(%o, %o, %o)', callback, $el, e);
			
			
			// focus
			this.doFocus( acf.get_closest_field( e.$el, this.type ) );
			
			
			// callback
			this[ callback ].apply(this, [e]);
			
		},
		
	};
	
	/*
console.time("acf_test_ready");
	console.time("acf_test_load");
	
	acf.add_action('ready', function(){
		
		console.timeEnd("acf_test_ready");
		
	}, 999);
	
	acf.add_action('load', function(){
		
		console.timeEnd("acf_test_load");
		
	}, 999);
*/
	
	
})(jQuery);
