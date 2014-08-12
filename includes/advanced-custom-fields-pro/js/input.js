

/* **********************************************
     Begin event-manager.js
********************************************** */

( function( window, undefined ) {
	"use strict";
	var document = window.document;

	/**
	 * Handles managing all events for whatever you plug it into. Priorities for hooks are based on lowest to highest in
	 * that, lowest priority hooks are fired first.
	 */
	var EventManager = function() {
		/**
		 * Maintain a reference to the object scope so our public methods never get confusing.
		 */
		var MethodsAvailable = {
			removeFilter : removeFilter,
			applyFilters : applyFilters,
			addFilter : addFilter,
			removeAction : removeAction,
			doAction : doAction,
			addAction : addAction
		};

		/**
		 * Contains the hooks that get registered with this EventManager. The array for storage utilizes a "flat"
		 * object literal such that looking up the hook utilizes the native object literal hash.
		 */
		var STORAGE = {
			actions : {},
			filters : {}
		};

		/**
		 * Adds an action to the event manager.
		 *
		 * @param action Must contain namespace.identifier
		 * @param callback Must be a valid callback function before this action is added
		 * @param priority Defaults to 10
		 */
		function addAction( action, callback, priority ) {
			if( _validateNamespace( action ) === false || typeof callback !== 'function' ) {
				return MethodsAvailable;
			}

			priority = parseInt( ( priority || 10 ), 10 );
			_addHook( 'actions', action, callback, priority );
			return MethodsAvailable;
		}

		/**
		 * Performs an action if it exists. You can pass as many arguments as you want to this function; the only rule is
		 * that the first argument must always be the action.
		 */
		function doAction( /* action, arg1, arg2, ... */ ) {
			var args = Array.prototype.slice.call( arguments );
			var action = args.shift();

			if( _validateNamespace( action ) === false ) {
				return MethodsAvailable;
			}

			_runHook( 'actions', action, args );

			return MethodsAvailable;
		}

		/**
		 * Removes the specified action if it contains a namespace.identifier & exists.
		 *
		 * @param action The action to remove
		 */
		function removeAction( action ) {
			if( _validateNamespace( action ) === false ) {
				return MethodsAvailable;
			}

			_removeHook( 'actions', action );
			return MethodsAvailable;
		}

		/**
		 * Adds a filter to the event manager.
		 *
		 * @param filter Must contain namespace.identifier
		 * @param callback Must be a valid callback function before this action is added
		 * @param priority Defaults to 10
		 */
		function addFilter( filter, callback, priority ) {
			if( _validateNamespace( filter ) === false || typeof callback !== 'function' ) {
				return MethodsAvailable;
			}

			priority = parseInt( ( priority || 10 ), 10 );
			_addHook( 'filters', filter, callback, priority );
			return MethodsAvailable;
		}

		/**
		 * Performs a filter if it exists. You should only ever pass 1 argument to be filtered. The only rule is that
		 * the first argument must always be the filter.
		 */
		function applyFilters( /* filter, filtered arg, arg2, ... */ ) {

			var args = Array.prototype.slice.call( arguments );
			var filter = args.shift();

			if( _validateNamespace( filter ) === false ) {
				return MethodsAvailable;
			}

			return _runHook( 'filters', filter, args );
		}

		/**
		 * Removes the specified filter if it contains a namespace.identifier & exists.
		 *
		 * @param filter The action to remove
		 */
		function removeFilter( filter ) {
			if( _validateNamespace( filter ) === false ) {
				return MethodsAvailable;
			}

			_removeHook( 'filters', filter );
			return MethodsAvailable;
		}

		/**
		 * Removes the specified hook by resetting the value of it.
		 *
		 * @param type Type of hook, either 'actions' or 'filters'
		 * @param hook The hook (namespace.identifier) to remove
		 * @private
		 */
		function _removeHook( type, hook ) {
			if( STORAGE[ type ][ hook ] ) {
				STORAGE[ type ][ hook ] = [];
			}
		}

		/**
		 * Validates that the hook has both a namespace and an identifier.
		 *
		 * @param hook The hook we are checking for namespace and identifier for.
		 * @return {Boolean} False if it does not contain both or is incorrect. True if it has an appropriate namespace & identifier.
		 * @private
		 */
		function _validateNamespace( hook ) {
			if( typeof hook !== 'string' ) {
				return false;
			}
			var identifier = hook.replace( /^\s+|\s+$/i, '' ).split( '.' );
			var namespace = identifier.shift();
			identifier = identifier.join( '.' );

			return ( namespace !== '' && identifier !== '' );
		}

		/**
		 * Adds the hook to the appropriate storage container
		 *
		 * @param type 'actions' or 'filters'
		 * @param hook The hook (namespace.identifier) to add to our event manager
		 * @param callback The function that will be called when the hook is executed.
		 * @param priority The priority of this hook. Must be an integer.
		 * @private
		 */
		function _addHook( type, hook, callback, priority ) {
			var hookObject = {
				callback : callback,
				priority : priority
			};

			// Utilize 'prop itself' : http://jsperf.com/hasownproperty-vs-in-vs-undefined/19
			var hooks = STORAGE[ type ][ hook ];
			if( hooks ) {
				hooks.push( hookObject );
				hooks = _hookInsertSort( hooks );
			}
			else {
				hooks = [ hookObject ];
			}

			STORAGE[ type ][ hook ] = hooks;
		}

		/**
		 * Use an insert sort for keeping our hooks organized based on priority. This function is ridiculously faster
		 * than bubble sort, etc: http://jsperf.com/javascript-sort
		 *
		 * @param hooks The custom array containing all of the appropriate hooks to perform an insert sort on.
		 * @private
		 */
		function _hookInsertSort( hooks ) {
			var tmpHook, j, prevHook;
			for( var i = 1, len = hooks.length; i < len; i++ ) {
				tmpHook = hooks[ i ];
				j = i;
				while( ( prevHook = hooks[ j - 1 ] ) &&  prevHook.priority > tmpHook.priority ) {
					hooks[ j ] = hooks[ j - 1 ];
					--j;
				}
				hooks[ j ] = tmpHook;
			}

			return hooks;
		}

		/**
		 * Runs the specified hook. If it is an action, the value is not modified but if it is a filter, it is.
		 *
		 * @param type 'actions' or 'filters'
		 * @param hook The hook ( namespace.identifier ) to be ran.
		 * @param args Arguments to pass to the action/filter. If it's a filter, args is actually a single parameter.
		 * @private
		 */
		function _runHook( type, hook, args ) {
			var hooks = STORAGE[ type ][ hook ];
			if( typeof hooks === 'undefined' ) {
				if( type === 'filters' ) {
					return args[0];
				}
				return false;
			}

			for( var i = 0, len = hooks.length; i < len; i++ ) {
				if( type === 'actions' ) {
					hooks[ i ].callback.apply( undefined, args );
				}
				else {
					args[ 0 ] = hooks[ i ].callback.apply( undefined, args );
				}
			}

			if( type === 'actions' ) {
				return true;
			}

			return args[ 0 ];
		}

		// return all of the publicly available methods
		return MethodsAvailable;

	};
	
	window.wp = window.wp || {};
	window.wp.hooks = new EventManager();

} )( window );


/* **********************************************
     Begin acf.js
********************************************** */

/*
*  input.js
*
*  All javascript needed for ACF to work
*
*  @type	awesome
*  @date	1/08/13
*
*  @param	N/A
*  @return	N/A
*/

var acf = {
	
	// vars
	l10n				: {},
	o					: {},
	
	
	// functions
	get					: null,
	update				: null,
	_e					: null,
	get_atts			: null,
	get_fields			: null,
	get_uniqid			: null,
	serialize_form		: null,
	
	
	// hooks
	add_action			: null,
	remove_action		: null,
	do_action			: null,
	add_filter			: null,
	remove_filter		: null,
	apply_filters		: null,
	
	
	// modules
	validation			:	null,
	conditional_logic	:	null,
	media				:	null,
	
	
	// fields
	fields				:	{
		date_picker		:	null,
		color_picker	:	null,
		image			:	null,
		file			:	null,
		wysiwyg			:	null,
		gallery			:	null,
		relationship	:	null
	}
};

(function($){
	
	
	/*
	*  Functions
	*
	*  These functions interact with the o object, and events
	*
	*  @type	function
	*  @date	23/10/13
	*  @since	5.0.0
	*
	*  @param	$n/a
	*  @return	$n/a
	*/
	
	$.extend(acf, {
		
		update : function( k, v ){
				
			this.o[ k ] = v;
			
		},
		
		get : function( k ){
			
			return this.o[ k ] || null;
			
		},
		
		_e : function( context, string ){
			
			// defaults
			string = string || false;
			
			
			// get context
			var r = this.l10n[ context ] || false;
			
			
			// get string
			if( string )
			{
				r = r[ string ] || false;
			}
			
			
			// return
			return r || '';
			
		},
		
		get_fields : function( args, $el, all ){
			
			// debug
			//console.log( 'acf.get_fields(%o, %o, %o)', args, $el, all );
			//console.time("acf.get_fields");
			
			
			// defaults
			args = args || {};
			$el = $el || $('body');
			all = all || false;
			
			
			// vars
			var selector = '.acf-field';
			
			
			// add selector
			for( k in args ) {
				
				selector += '[data-' + k + '="' + args[k] + '"]';
				
			}
			
			
			// get fields
			var $fields = $el.find(selector);
			
			
			// is current $el a field?
			// this is the case when editing a field group
			if( $el.is( selector ) ) {
			
				$fields = $fields.add( $el );
				
			}
			
			
			//console.log('get_fields(%o, %s, %b). selector = %s', $el, field_type, allow_filter, selector);
			//console.log( $el );
			//console.log( $fields );
			
			// filter out fields
			if( !all ) {
			
				$fields = $fields.filter(function(){
					
					return acf.apply_filters('is_field_ready_for_js', true, $(this));			

				});
				
			}
			
			
			//console.timeEnd("acf.get_fields");
			
			
			// return
			return $fields;
							
		},
		
		get_field : function( field_key, $el ){
			
			// defaults
			$el = $el || $('body');
			
			
			// get fields
			var $fields = this.get_fields({ key : field_key }, $el, true);
			
			
			// validate
			if( !$fields.exists() )
			{
				return false;
			}
			
			
			// return
			return $fields.first();
			
		},
		
		get_the_field : function( $el ){
			
			return $el.parent().closest('.acf-field');
			
		},
		
		get_closest_field : function( $el ){
			
			return $el.closest('.acf-field');
			
		},
		
		get_field_wrap : function( $el ){
			
			return $el.closest('.acf-field');
			
		},
		
		/*
get_field_data : function( $el, name ){
			
			// defaults
			name = name || false;
			
			
			// vars
			$field = this.get_field_wrap( $el );
			
			
			// return
			return this.get_data( $field, name );
			
		},
*/
		
		get_field_key : function( $field ){
		
			return this.get_data( $field, 'key' );
			
		},
		
		get_field_type : function( $field ){
		
			return this.get_data( $field, 'type' );
			
		},
		
		
		get_data : function( $el, name ){
			
			//console.log('get_data(%o, %o)', name, $el);
			// defaults
			name = name || false;
			
			
			// vars
			var self = this,
				data = false;
			
			
			// specific data-name
			if( name ) {
			
				data = $el.attr('data-' + name)
				
				// convert ints (don't worry about floats. I doubt these would ever appear in data atts...)
        		if( $.isNumeric(data) ) {
        			
        			if( data.match(/[^0-9]/) ) {
	        			
	        			// leave value if it contains such characters: . + - e
	        			
        			} else {
	        			
	        			data = parseInt(data);
	        			
        			}
	        		
        		}
        		
			} else {
				
				// all data-names
				data = {};
				
				$.each( $el[0].attributes, function( i, attr ) {
			        
			        // bail early if not data-
		        	if( attr.name.substr(0, 5) !== 'data-' ) {
		        	
		        		return;
		        		
		        	}
		        	
		        	
		        	// vars
		        	name = attr.name.replace('data-', '');
		        	
		        	
		        	// add to atts
		        	data[ name ] = self.get_data( $el, name );
		        	
		        });
			}
			
			
			// return
	        return data;
				
		},
		
		is_field : function( $el, args ){
			
			// var
			var r = true;
			
			
			// check $el class
			if( ! $el.hasClass('acf-field') )
			{
				r = false;
			}
			
			
			// check args (data attributes)
			$.each( args, function( k, v ) {
				
				if( $el.attr('data-' + k) != v )
				{
					r = false;
				}
				
			});
			
			
			// return
			return r;
			
		},
		
		is_sub_field : function( $field, args ) {
			
			// defaults
			args = args || false;
			
			
			// var
			var r = false;
			
			
			// find parent
			$parent = $field.parent().closest('.acf-field');
			
			
			if( $parent.exists() ) {
			
				r = true;
				
				
				// check args (data attributes)
				if( args ) {
					
					r = this.is_field( $parent, args );
					
				}
				
			}
			
			
			// return
			return r;
			
		},
		
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
			
			
			// filter our hidden field groups
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
					
					if( typeof(callback) == 'function' )
					{
						callback();
					}
					
					
				});
				
					
			}, 250);
			
		},
		
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
				
				if( typeof(callback) == 'function' )
				{
					callback();
				}
				
			});
			
			
		},
		
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
		
		open_popup : function( args ){
			
			// vars
			$popup = $('body > #acf-popup');
			
			
			// already exists?
			if( $popup.exists() )
			{
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
			
			
			if( args.width )
			{
				$popup.find('.acf-popup-box').css({
					'width'			: args.width,
					'margin-left'	: 0 - (args.width / 2),
				});
			}
			
			if( args.height )
			{
				$popup.find('.acf-popup-box').css({
					'height'		: args.height,
					'margin-top'	: 0 - (args.height / 2),
				});	
			}
			
			if( args.title )
			{
				$popup.find('.title h3').html( args.title );
			}
			
			if( args.content )
			{
				$popup.find('.inner').html( args.content );
			}
			
			if( args.loading )
			{
				$popup.find('.loading').show();
			}
			else
			{
				$popup.find('.loading').hide();
			}
			
			return $popup;
		},
		
		close_popup : function(){
			
			// vars
			$popup = $('#acf-popup');
			
			
			// already exists?
			if( $popup.exists() )
			{
				$popup.remove();
			}
			
			
		},
		
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
		
		prepare_for_ajax : function( args ) {
			
			// nonce
			args.nonce = acf.get('nonce');
			
			
			// filter for 3rd party customization
			args = acf.apply_filters('prepare_for_ajax', args);	
			
			
			// return
			return args;
			
		},
		
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
			
		}
				
	});
	
	
	/*
	*  Hooks
	*
	*  These functions act as wrapper functions for the included event-manager JS library
	*  Wrapper functions will ensure that future changes to event-manager do not disrupt
	*  any custom actions / filter code written by users
	*
	*  @type	functions
	*  @date	30/11/2013
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	$.extend(acf, {
		
		add_action : function() {
			
			// allow multiple action parameters such as 'ready append'
			var actions = arguments[0].split(' ');
			
			for( k in actions )
			{
				// prefix action
				arguments[0] = 'acf.' + actions[ k ];
				
				wp.hooks.addAction.apply(this, arguments);
			}
			
			return this;
		},
		
		remove_action : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeAction.apply(this, arguments);
			
			return this;
		},
		
		do_action : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.doAction.apply(this, arguments);
			
			return this;
		},
		
		add_filter : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.addFilter.apply(this, arguments);
			
			return this;
		},
		
		remove_filter : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			wp.hooks.removeFilter.apply(this, arguments);
			
			return this;
		},
		
		apply_filters : function() {
			
			// prefix action
			arguments[0] = 'acf.' + arguments[0];
			
			return wp.hooks.applyFilters.apply(this, arguments);
		}
		
	});
    
	
	/*
	*  Exists
	*
	*  @description: returns true / false		
	*  @created: 1/03/2011
	*/
	
	$.fn.exists = function()
	{
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
	    
	}
	
	
	/*
	*  3.5 Media
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 16/01/13
	*/
	
	acf.media = {
		
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
				}),
				
				// edit image functionality
				new wp.media.controller.EditImage()
				
			];
			
			
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
				if( args.library == 'uploadedTo' ) {
					
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
		
		init : function(){
			
			// bail early if wp.media does not exist (field group edit page)
			if( typeof wp == 'undefined' )
			{
				return false;
			}
			
			
			// validate prototype
			if( ! acf.isset(wp, 'media', 'view', 'AttachmentCompat', 'prototype') )
			{
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
				if( _this.ignore_render )
				{
					return this;	
				}
				
				
				// run the old render function
				this.orig_render();
				
				
				// add button
				setTimeout(function(){
					
					// vars
					var $media_model = _this.$el.closest('.media-modal');
					
					
					// is this an edit only modal?
					if( $media_model.hasClass('acf-media-modal') )
					{
						return;	
					}
					
					
					// does button already exist?
					if( $media_model.find('.media-frame-router .acf-expand-details').exists() )
					{
						return;	
					}
					
					
					// create button
					var button = $([
						'<a href="#" class="acf-expand-details">',
							'<span class="is-closed"><span class="acf-icon small"><i class="acf-sprite-left"></i></span>' + acf.l10n.core.expand_details +  '</span>',
							'<span class="is-open"><span class="acf-icon small"><i class="acf-sprite-right"></i></span>' + acf.l10n.core.collapse_details +  '</span>',
						'</a>'
					].join('')); 
					
					
					// add events
					button.on('click', function( e ){
						
						e.preventDefault();
						
						if( $media_model.hasClass('acf-expanded') )
						{
							$media_model.removeClass('acf-expanded');
						}
						else
						{
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
			
				var data = {},
					names = {};
				
				if ( event )
					event.preventDefault();
					
					
				_.each( this.$el.serializeArray(), function( pair ) {
				
					// initiate name
					if( pair.name.slice(-2) === '[]' )
					{
						// remove []
						pair.name = pair.name.replace('[]', '');
						
						
						// initiate counter
						if( typeof names[ pair.name ] === 'undefined'){
							
							names[ pair.name ] = -1;
							//console.log( names[ pair.name ] );
							
						}
						
						
						names[ pair.name ]++
						
						pair.name += '[' + names[ pair.name ] +']';
						
						
					}
 
					data[ pair.name ] = pair.value;
				});
 
				this.ignore_render = true;
				this.model.saveCompat( data );
				
			};
			
			
			// update the wp.media.view.settings.post.id setting
			setTimeout(function(){
			
				// Hack for CPT without a content editor
				try
				{
					// post_id may be string (user_1) and therefore, the uploaded image cannot be attached to the post
					if( $.isNumeric(acf.o.post_id) )
					{
						wp.media.view.settings.post.id = acf.o.post_id;
					}
					
				} 
				catch(e)
				{
					// one of the objects was 'undefined'...
				}
				
				
				// setup fields
				//$(document).trigger('acf/setup_fields', [ $(document) ]);
				
			}, 10);
			
			
		}
	};
	
	acf.add_action('load', function(){
		
		acf.media.init();
		
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
		
	acf.conditional_logic = {
		
		items : {},
		triggers : {},
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// events
			$(document).on('change', '.acf-field input, .acf-field textarea, .acf-field select', function(){
				
				_this.change( $(this) );
				
			});
			
			
			// actions
			acf.add_action('append', function( $el ){
				
				_this.render( $el );
				
			});
			
			
			// debug
			//console.log( 'conditional_logic.init(%o)', this );
			
			
			// render
			_this.render();
			
		},
		
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
		
		change : function( $input ){
			
			// debug
			//console.log( 'conditional_logic.change(%o)', $input );
			
			
			// vars
			var $field	= acf.get_field_wrap( $input ),
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
				var $targets = acf.get_fields({key : target_key}, $parent, true);
				
				
				this.render_fields( $targets );
				
			}
			
		},
		
		render : function( $el ){
			
			// debug
			//console.log('render(%o)', $el);
			
			
			// defaults
			$el = $el || $('body');
			
			
			// get targets
			var $targets = acf.get_fields( {}, $el, true );
			
			
			// render fields
			this.render_fields( $targets );
			
		},
		
		render_fields : function( $targets ) {
		
			// reference
			var self = this;
			
			
			// loop over targets and render them			
			$targets.each(function(){
					
				self.render_field( $(this) );
				
			});
			
			
			// repeater hide column
			
			// action for 3rd party customization
			//acf.do_action('conditional_logic_render_field');
			
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
			
			// vars
			var key = acf.get_field_key( $field );
							
			
			// add class
			$field.removeClass( 'hidden-by-conditional-logic' );
			
			
			// remove "disabled"
			// ignore inputs which have a class of 'acf-disabled'. These inputs are disabled for life
			$field.find('input, textarea, select').not('.acf-disabled').removeAttr('disabled');
			
			
			// action for 3rd party customization
			acf.do_action('conditional_logic_show_field', $field );
			acf.do_action('show_field', $field );
			
		},
		
		hide_field : function( $field ){
			
			// debug
			//console.log( 'conditional_logic.hide_field(%o)', $field );
			
			
			// vars
			var key = acf.get_field_key( $field );
			
			
			// add class
			$field.addClass( 'hidden-by-conditional-logic' );
			
			
			// add "disabled"
			$field.find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// action for 3rd party customization
			acf.do_action('conditional_logic_hide_field', $field );
			acf.do_action('hide_field', $field );
			
		},
		
		get_visibility : function( $target, rule ){
			
			//console.log( 'conditional_logic.get_visibility(%o, %o)', $target, rule );
			
			// vars
			//var $search = acf.is_sub_field( $target ) ? $target.parent() : $('body');
			//console.log( '$search %o', $search );
			
			// vars
			var $triggers = acf.get_fields({key : rule.field}, false, true),
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
		
	}; 
	
	acf.add_action('ready', function(){
		
		acf.conditional_logic.init();
		
	}, 100);
	
	
	
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
		if( $('#acf-form-data input[name="_acfchanged"]').exists() )
		{
			$('#acf-form-data input[name="_acfchanged"]').val(1);
		}
		
	});
	
	
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
	
	
})(jQuery);

/* **********************************************
     Begin ajax.js
********************************************** */

(function($){
	
	acf.ajax = {
		
		o : {
			action 			:	'acf/post/get_field_groups',
			post_id			:	0,
			page_template	:	0,
			page_parent		:	0,
			page_type		:	0,
			post_format		:	0,
			post_taxonomy	:	0,
			lang			:	0,
		},
		
		update : function( k, v ){
			
			this.o[ k ] = v;
			return this;
			
		},
		
		get : function( k ){
			
			return this.o[ k ] || null;
			
		},
		
		init : function(){
			
			// bail early if ajax is disabled
			if( ! acf.get('ajax') )
			{
				return false;	
			}
			
			
			// vars
			this.update('post_id', acf.o.post_id);
			
			
			// MPML
			if( $('#icl-als-first').length > 0 )
			{
				var href = $('#icl-als-first').children('a').attr('href'),
					regex = new RegExp( "lang=([^&#]*)" ),
					results = regex.exec( href );
				
				// lang
				this.update('lang', results[1]);
				
			}
			
			
			// add triggers
			this.add_events();
		},
		
		fetch : function(){
			
			// reference
			var _this = this;
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: acf.prepare_for_ajax( this.o ),
				type		: 'post',
				dataType	: 'json',
				success		: function( json ){
					
					if( acf.is_ajax_success( json ) ) {
						
						_this.render( json.data );
						
					}
					
				}
			});
			
		},
		
		render : function( json ){
			
			// hide all metaboxes
			$('.acf-postbox').addClass('acf-hidden');
			$('.acf-postbox-toggle').addClass('acf-hidden');
			
			
			// show the new postboxes
			$.each(json, function( k, field_group ){
				
				// vars
				var $el = $('#acf-' + field_group.key),
					$toggle = $('#adv-settings .acf_postbox-toggle[for="acf-' + field_group.key + '-hide"]');
				
				
				// classes
				$el.removeClass('acf-hidden hide-if-js');
				$toggle.removeClass('acf-hidden hide-if-js');
				$toggle.find('input[type="checkbox"]').attr('checked', 'checked');
				
				
				// replace HTML if needed
				$el.find('.acf-replace-with-fields').each(function(){
					
					$(this).replaceWith( field_group.html );
					
					acf.do_action('append', $el);
					
				});
				
				
				// update style if needed
				if( k === 0 )
				{
					$('#acf-style').html( field_group.style );
				}
				
			});
			
		},
		
		sync_taxonomy_terms : function(){
			
			// vars
			var values = [];
			
			
			$('.categorychecklist, .acf-taxonomy-field').each(function(){
				
				// vars
				var $el = $(this),
					$checkbox = $el.find('input[type="checkbox"]').not(':disabled'),
					$radio = $el.find('input[type="radio"]').not(':disabled'),
					$select = $el.find('select').not(':disabled'),
					$hidden = $el.find('input[type="hidden"]').not(':disabled');
				
				
				// bail early if not a field which saves taxonomy terms to post
				if( $el.is('.acf-taxonomy-field') && $el.attr('data-load_save') != '1' ) {
					
					return;
					
				}
				
				
				// bail early if in attachment
				if( $el.closest('.media-frame').exists() ) {
					
					return;
				
				}
				
				
				// checkbox
				if( $checkbox.exists() ) {
					
					$checkbox.filter(':checked').each(function(){
						
						values.push( $(this).val() );
						
					});
					
				} else if( $radio.exists() ) {
					
					$radio.filter(':checked').each(function(){
						
						values.push( $(this).val() );
						
					});
					
				} else if( $select.exists() ) {
					
					$select.find('option:selected').each(function(){
						
						values.push( $(this).val() );
						
					});
					
				} else if( $hidden.exists() ) {
					
					$hidden.each(function(){
						
						// ignor blank values or those which contain a comma (select2 multi-select)
						if( ! $(this).val() || $(this).val().indexOf(',') > -1 ) {
							
							return;
							
						}
						
						values.push( $(this).val() );
						
					});
					
				}
								
			});
	
			
			// filter duplicates
			values = values.filter (function (v, i, a) { return a.indexOf (v) == i });
			
			
			// update screen
			this.update( 'post_taxonomy', values ).fetch();
			
		},
		
		add_events : function(){
			
			// reference
			var _this = this;
			
			
			// page template
			$(document).on('change', '#page_template', function(){
				
				var page_template = $(this).val();
				
				_this.update( 'page_template', page_template ).fetch();
			    
			});
			
			
			// page parent
			$(document).on('change', '#parent_id', function(){
				
				var page_type = 'parent',
					page_parent = 0;
				
				
				if( $(this).val() != "" ) {
				
					page_type = 'child';
					page_parent = $(this).val();
					
				}
				
				_this.update( 'page_type', page_type ).update( 'page_parent', page_parent ).fetch();
			    
			});
			
			
			// post format
			$(document).on('change', '#post-formats-select input[type="radio"]', function(){
				
				var post_format = $(this).val();
				
				if( post_format == '0' )
				{
					post_format = 'standard';
				}
				
				_this.update( 'post_format', post_format ).fetch();
				
			});
			
			
			// post taxonomy
			$(document).on('change', '.categorychecklist input, .acf-taxonomy-field input, .acf-taxonomy-field select', function(){
				
				// a taxonomy field may trigger this change event, however, the value selected is not
				// actually a term relationship, it is meta data
				var $el = $(this).closest('.acf-taxonomy-field');
				
				if( $el.exists() && $el.attr('data-load_save') != '1' ) {
					
					return;
					
				}
				
				
				// this may be triggered from editing an image in a popup. Popup does not support correct metaboxes so ignore this
				if( $(this).closest('.media-frame').exists() ) {
					
					return;
				
				}
				
				
				// set timeout to fix issue with chrome which does not register the change has yet happened
				setTimeout(function(){
					
					_this.sync_taxonomy_terms();
				
				}, 1);
				
				
			});
			
			
			
			// user role
			/*
			$(document).on('change', 'select[id="role"][name="role"]', function(){
				
				_this.update( 'user_role', $(this).val() ).fetch();
				
			});
			*/
			
		}
		
	};
	
	
	/*
	*  Document Ready
	*
	*  Initialize the object
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).ready(function(){
		
		// initialize
		acf.ajax.init();
		
	});


	
})(jQuery);

/* **********************************************
     Begin color-picker.js
********************************************** */

(function($){
	
	acf.fields.color_picker = {
		
		timeout : null,
		
		init : function( $input ){
			
			// reference
			var self = this;
			
			
			// vars
			var $hidden = $input.clone();
			
			
			// modify hidden
			$hidden.attr({
				'type'	: 'hidden',
				'class' : '',
				'id'	: '',
				'value'	: ''
 			});
 			
 			
 			// append hidden
 			$input.before( $hidden );
 			
 			
 			// iris
			$input.wpColorPicker({
				
				change: function( event, ui ){
			
					self.change( $input, $hidden );
					
				}
				
			});
			
		},
		
		change : function( $input, $hidden ){
			
			if( this.timeout ) {
				
				clearTimeout( this.timeout );
				
			}
			
			
			this.timeout = setTimeout(function(){
				
				$hidden.trigger('change');
				
			}, 1000);
			
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
		
		acf.get_fields({ type : 'color_picker'}, $el).each(function(){
			
			acf.fields.color_picker.init( $(this).find('input[type="text"]') );
			
		});
		
	});	

})(jQuery);

/* **********************************************
     Begin date-picker.js
********************************************** */

(function($){
	
	/*
	*  Date Picker
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.date_picker = {
		
		init : function( $el ){
			
			// vars
			var $input = $el.find('input[type="text"]'),
				$hidden = $el.find('input[type="hidden"]');
			
			
			// get options
			var o = acf.get_data( $el );
			
			
			// get and set value from alt field
			$input.val( $hidden.val() );
			
			
			// create options
			var args = $.extend( {}, acf.l10n.date_picker, { 
				dateFormat		:	'yymmdd',
				altField		:	$hidden,
				altFormat		:	'yymmdd',
				changeYear		:	true,
				yearRange		:	"-100:+100",
				changeMonth		:	true,
				showButtonPanel	:	true,
				firstDay		:	o.first_day
			});
			
			
			// filter for 3rd party customization
			args = acf.apply_filters('date_picker_args', args, $el);
			
			
			// add date picker
			$input.addClass('active').datepicker( args );
			
			
			// now change the format back to how it should be.
			$input.datepicker( "option", "dateFormat", o.display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if( $('body > #ui-datepicker-div').exists() )
			{
				$('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
			}
			
		},
		
		blur : function( $input ){
			
			if( !$input.val() )
			{
				$input.siblings('input[type="hidden"]').val('');
			}
			
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
		
		acf.get_fields({ type : 'date_picker'}, $el).each(function(){
			
			acf.fields.date_picker.init( $(this).find('.acf-date_picker') );
			
		});
		
	});
		
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	$(document).on('blur', '.acf-date_picker input[type="text"]', function( e ){
		
		acf.fields.date_picker.blur( $(this) );
					
	});
	

})(jQuery);

/* **********************************************
     Begin file.js
********************************************** */

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

/* **********************************************
     Begin google-map.js
********************************************** */

(function($){
	
	/*
	*  Location
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.google_map = {
		
		$el : null,
		$input : null,
		
		o : {},
		
		ready : false,
		geocoder : false,
		map : false,
		maps : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find input
			this.$input = this.$el.find('.value');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// get map
			if( this.maps[ this.o.id ] )
			{
				this.map = this.maps[ this.o.id ];
			}
			
				
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// geocode
			if( !this.geocoder )
			{
				this.geocoder = new google.maps.Geocoder();
			}
			
			
			// google maps is loaded and ready
			this.ready = true;
			
			
			// render map
			this.render();
					
		},
		render : function(){
			
			// reference
			var _this	= this,
				_$el	= this.$el;
			
			
			// vars
			var args = {
        		zoom		: parseInt(this.o.zoom),
        		center		: new google.maps.LatLng(this.o.lat, this.o.lng),
        		mapTypeId	: google.maps.MapTypeId.ROADMAP
        	};
			
			// create map	        	
        	this.map = new google.maps.Map( this.$el.find('.canvas')[0], args);
	        
	        
	        // add search
			var autocomplete = new google.maps.places.Autocomplete( this.$el.find('.search')[0] );
			autocomplete.map = this.map;
			autocomplete.bindTo('bounds', this.map);
			
			
			// add dummy marker
	        this.map.marker = new google.maps.Marker({
		        draggable	: true,
		        raiseOnDrag	: true,
		        map			: this.map,
		    });
		    
		    
		    // add references
		    this.map.$el = this.$el;
		    
		    
		    // value exists?
		    var lat = this.$el.find('.input-lat').val(),
		    	lng = this.$el.find('.input-lng').val();
		    
		    if( lat && lng )
		    {
			    this.update( lat, lng ).center();
		    }
		    
		    
			// events
			google.maps.event.addListener(autocomplete, 'place_changed', function( e ) {
			    
			    // reference
			    var $el = this.map.$el;


			    // manually update address
			    var address = $el.find('.search').val();
			    $el.find('.input-address').val( address );
			    $el.find('.title h4').text( address );
			    
			    
			    // vars
			    var place = this.getPlace();
			    
			    
			    // validate
			    if( place.geometry )
			    {
			    	var lat = place.geometry.location.lat(),
						lng = place.geometry.location.lng();
						
						
				    _this.set({ $el : $el }).update( lat, lng ).center();
			    }
			    else
			    {
				    // client hit enter, manually get the place
				    _this.geocoder.geocode({ 'address' : address }, function( results, status ){
				    	
				    	// validate
						if( status != google.maps.GeocoderStatus.OK )
						{
							console.log('Geocoder failed due to: ' + status);
							return;
						}
						
						if( !results[0] )
						{
							console.log('No results found');
							return;
						}
						
						
						// get place
						place = results[0];
						
						var lat = place.geometry.location.lat(),
							lng = place.geometry.location.lng();
							
							
					    _this.set({ $el : $el }).update( lat, lng ).center();
					    
					});
			    }
			    
			});
		    
		    
		    google.maps.event.addListener( this.map.marker, 'dragend', function(){
		    	
		    	// reference
			    var $el = this.map.$el;
			    
			    
		    	// vars
				var position = this.map.marker.getPosition(),
					lat = position.lat(),
			    	lng = position.lng();
			    	
				_this.set({ $el : $el }).update( lat, lng ).sync();
			    
			});
			
			
			google.maps.event.addListener( this.map, 'click', function( e ) {
				
				// reference
			    var $el = this.$el;
			    
			    
				// vars
				var lat = e.latLng.lat(),
					lng = e.latLng.lng();
				
				
				_this.set({ $el : $el }).update( lat, lng ).sync();
			
			});

			
			
	        // add to maps
	        this.maps[ this.o.id ] = this.map;
	        
	        
		},
		
		update : function( lat, lng ){
			
			// vars
			var latlng = new google.maps.LatLng( lat, lng );
		    
		    
		    // update inputs
			this.$el.find('.input-lat').val( lat );
			this.$el.find('.input-lng').val( lng ).trigger('change');
			
			
		    // update marker
		    this.map.marker.setPosition( latlng );
		    
		    
			// show marker
			this.map.marker.setVisible( true );
		    
		    
	        // update class
	        this.$el.addClass('active');
	        
	        
	        // validation
			this.$el.closest('.acf-field').removeClass('error');
			
			
	        // return for chaining
	        return this;
		},
		
		center : function(){
			
			// vars
			var position = this.map.marker.getPosition(),
				lat = this.o.lat,
				lng = this.o.lng;
			
			
			// if marker exists, center on the marker
			if( position )
			{
				lat = position.lat();
				lng = position.lng();
			}
			
			
			var latlng = new google.maps.LatLng( lat, lng );
				
			
			// set center of map
	        this.map.setCenter( latlng );
		},
		
		sync : function(){
			
			// reference
			var $el	= this.$el;
				
			
			// vars
			var position = this.map.marker.getPosition(),
				latlng = new google.maps.LatLng( position.lat(), position.lng() );
			
			
			this.geocoder.geocode({ 'latLng' : latlng }, function( results, status ){
				
				// validate
				if( status != google.maps.GeocoderStatus.OK )
				{
					console.log('Geocoder failed due to: ' + status);
					return;
				}
				
				if( !results[0] )
				{
					console.log('No results found');
					return;
				}
				
				
				// get location
				var location = results[0];
				
				
				// update h4
				$el.find('.title h4').text( location.formatted_address );

				
				// update input
				$el.find('.input-address').val( location.formatted_address ).trigger('change');
				
			});
			
			
			// return for chaining
	        return this;
		},
		
		locate : function(){
			
			// reference
			var _this	= this,
				_$el	= this.$el;
			
			
			// Try HTML5 geolocation
			if( ! navigator.geolocation )
			{
				alert( acf.l10n.google_map.browser_support );
				return this;
			}
			
			
			// show loading text
			_$el.find('.title h4').text(acf.l10n.google_map.locating + '...');
			_$el.addClass('active');
			
		    navigator.geolocation.getCurrentPosition(function(position){
		    	
		    	// vars
				var lat = position.coords.latitude,
			    	lng = position.coords.longitude;
			    	
				_this.set({ $el : _$el }).update( lat, lng ).sync().center();
				
			});

				
		},
		
		clear : function(){
			
			// update class
	        this.$el.removeClass('active');
			
			
			// clear search
			this.$el.find('.search').val('');
			
			
			// clear inputs
			this.$el.find('.input-address').val('');
			this.$el.find('.input-lat').val('');
			this.$el.find('.input-lng').val('');
			
			
			// hide marker
			this.map.marker.setVisible( false );
		},
		
		edit : function(){
			
			// update class
	        this.$el.removeClass('active');
			
			
			// clear search
			var val = this.$el.find('.title h4').text();
			
			
			this.$el.find('.search').val( val ).focus();
			
		},
		
		refresh : function(){
			
			// trigger resize on div
			google.maps.event.trigger(this.map, 'resize');
			
			// center map
			this.center();
			
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
		
		//vars
		var $fields = acf.get_fields({ type : 'google_map'}, $el);
		
		
		// validate
		if( !$fields.exists() )
		{
			return;
		}
		
		
		// validate google
		if( typeof google === 'undefined' )
		{
			$.getScript('https://www.google.com/jsapi', function(){
			
			    google.load('maps', '3', { other_params: 'sensor=false&libraries=places', callback: function(){
			    
			        $fields.each(function(){
					
						acf.fields.google_map.set({ $el : $(this).find('.acf-google-map') }).init();
						
					});
			        
			    }});
			});
			
		}
		else
		{
			$fields.each(function(){
				
				acf.fields.google_map.set({ $el : $(this).find('.acf-google-map') }).init();
				
			});
			
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
	
	$(document).on('click', '.acf-google-map a[data-name="clear-location"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.google_map.set({ $el : $(this).closest('.acf-google-map') }).clear();
		
		$(this).blur();
		
	});
	
	
	$(document).on('click', '.acf-google-map a[data-name="find-location"]', function( e ){
		
		e.preventDefault();
		
		acf.fields.google_map.set({ $el : $(this).closest('.acf-google-map') }).locate();
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf-google-map .title h4', function( e ){
		
		e.preventDefault();
		
		acf.fields.google_map.set({ $el : $(this).closest('.acf-google-map') }).edit();
			
	});
	
	$(document).on('keydown', '.acf-google-map .search', function( e ){
		
		// prevent form from submitting
		if( e.which == 13 )
		{
		    return false;
		}
			
	});
	
	$(document).on('blur', '.acf-google-map .search', function( e ){
		
		// vars
		var $el = $(this).closest('.acf-google-map');
		
		
		// has a value?
		if( $el.find('.input-lat').val() )
		{
			$el.addClass('active');
		}
			
	});
	
	acf.add_action('show_field', function( $field ){
		
		// validate
		if( ! acf.fields.google_map.ready )
		{
			return;
		}
		
		
		// validate
		if( acf.is_field($field, {type : 'google_map'}) )
		{
			acf.fields.google_map.set({ $el : $field.find('.acf-google-map') }).refresh();
		}
		
	});
	

})(jQuery);

/* **********************************************
     Begin image.js
********************************************** */

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

/* **********************************************
     Begin oembed.js
********************************************** */

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
	
	/*
acf.add_action('ready append', function( $el ){
		
		
		// add tabs
		acf.get_fields({ type : 'oembed'}, $el).each(function(){
			
			acf.fields.oembed.add_oembed( $(this) );
			
		});
		
		
		// activate first tab
		acf.fields.tab.refresh( $el );
		
	});
*/
	
	
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

/* **********************************************
     Begin post_object.js
********************************************** */



/* **********************************************
     Begin radio.js
********************************************** */

(function($){
	
	/*
	*  Radio
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.radio = {
	
		change : function( $radio ){
			
			// vars
			var $ul = $radio.closest('ul'),
				$val = $ul.find('input[type="radio"]:checked'),
				$other = $ul.find('input[type="text"]');
			
			
			if( $val.val() == 'other' )
			{
				$other.removeAttr('disabled');
				$other.attr('name', $val.attr('name'));
			}
			else
			{
				$other.attr('disabled', 'disabled');
				$other.attr('name', '');
			}
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
	
	$(document).on('change', '.acf-radio-list input[type="radio"]', function( e ){
		
		acf.fields.radio.change( $(this) );
		
	});
	

})(jQuery);

/* **********************************************
     Begin relationship.js
********************************************** */

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

/* **********************************************
     Begin select.js
********************************************** */

(function($){
	
	acf.fields.select = {
		
		init : function( $select ){
			
			// validate $select
			if( ! $select.exists() )
			{
				return false;
			}
			
			
			// vars
			var o = acf.get_data( $select );
			
			
			// bail early if no ui
			if( ! o.ui )
			{
				return false;
			}
			
			
			// vars
			var $field = acf.get_field_wrap( $select ),
				$input = $select.siblings('input');
			
			
			// select2 args
			var args = {
				width			: '100%',
				allowClear		: o.allow_null,
				placeholder		: o.placeholder,
				multiple		: o.multiple,
				data			: [],
				escapeMarkup	: function( m ){ return m; }
			};
			
			
			// customize HTML for selected choices
			if( o.multiple )
			{
				args.formatSelection = function( object, $div ){
					
					$div.parent().append('<input type="hidden" class="acf-select2-multi-choice" name="' + $select.attr('name') + '" value="' + object.id + '" />');
					
					return object.text;
				}
			}
			
			
			// remove the blank option as we have a clear all button!
			if( o.allow_null )
			{
				args.placeholder = o.placeholder;
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
				if( $(this).parent().is('optgroup') )
				{
					parent = $(this).parent().attr('label');
				}
				
				
				// append to choices
				if( ! optgroups[ parent ] )
				{
					optgroups[ parent ] = [];
				}
				
				optgroups[ parent ].push({
					id		: $(this).attr('value'),
					text	: $(this).text()
				});
				
			});
			

			$.each( optgroups, function( label, children ){
				
				if( label == '_root' )
				{
					$.each( children, function( i, child ){
						
						args.data.push( child );
						
					});
				}
				else
				{
					args.data.push({
						text		: label,
						children	: children
					});
				}
							
			});

			
			// re-order options
			$.each( selection, function( k, value ){
				
				$.each( args.data, function( i, choice ){
					
					if( value == choice.id )
					{
						initial_selection.push( choice );
					}
					
				});
							
			});
			
			
			// ajax
			if( o.ajax )
			{
				args.ajax = {
					url			: acf.get('ajaxurl'),
					dataType	: 'json',
					type		: 'get',
					cache		: true,
					data		: function (term, page) {
						
						// Allow for dynamic action because post_object and user fields use this JS
						var action = 'acf/fields/' + acf.get_data($field, 'type') + '/query';
						
						
						// vars
						var data = {
							action		: action,
							field_key	: acf.get_data($field, 'key'),
							nonce		: acf.get('nonce'),
							post_id		: acf.get('post_id'),
							s			: term
						};
						
						
						// return
						return data;
						
					},
					results		: function (data, page) {
					
						// vars
						return {
							results : data
						};
						
					}
				};
				
				args.initSelection = function (element, callback) {
					
					// single select requires 1 val, not an array
					if( ! o.multiple )
					{
						initial_selection = initial_selection[0];
					}
					
						        
			        // callback
			        callback( initial_selection );
			        
			    };
			}
			
			
			// filter for 3rd party customization
			args = acf.apply_filters( 'select2_args', args, $field );
			
			
			// add select2
			$input.select2( args );

			
			// reorder DOM
			$input.select2('container').before( $input );
			
			
			// multiple
			if( o.multiple )
			{
				// clear input value (allow nothing to be saved) - only for multiple
				//$input.val('');
				
				
				// sortable
				$input.select2('container').find('ul.select2-choices').sortable({
					 //containment: 'parent',
					 start: function() {
					 	$input.select2("onSortStart");
					 },
					 update: function() {
					 	$input.select2("onSortEnd");
					 }
				});
			}
			
			
			// make sure select is disabled (repeater / flex may enable it!)
			$select.attr('disabled', 'disabled').addClass('acf-disabled');
		},
		
		remove : function( $select ){
		
			if( acf.get_data( $select, 'ui' ) ) {
				
				$select.siblings('.select2-container').remove();

			}
						
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
		
		acf.get_fields({ type : 'select'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
		acf.get_fields({ type : 'user'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
		acf.get_fields({ type : 'post_object'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
		acf.get_fields({ type : 'page_link'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
		acf.get_fields({ type : 'taxonomy'}, $el).each(function(){
			
			acf.fields.select.init( $(this).find('select') );
			
		});
		
	});
	
	acf.add_action('remove', function( $el ){
		
		acf.get_fields({ type : 'select'}, $el).each(function(){
			
			acf.fields.select.remove( $(this).find('select') );
			
		});
		
	})
	
	

})(jQuery);

/* **********************************************
     Begin tab.js
********************************************** */

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

/* **********************************************
     Begin validation.js
********************************************** */

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

/* **********************************************
     Begin wysiwyg.js
********************************************** */

(function($){
	
	/*
	*  WYSIWYG
	*
	*  jQuery functionality for this field type
	*
	*  @type	object
	*  @date	20/07/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf.fields.wysiwyg = {
		
		$el : null,
		$textarea : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find textarea
			this.$textarea = this.$el.find('textarea');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// add ID
			this.o.id = this.$textarea.attr('id');
			
			
			// return this for chaining
			return this;
			
		},
		has_tinymce : function(){
		
			var r = false;
			
			if( typeof(tinyMCE) == "object" )
			{
				r = true;
			}
			
			return r;
			
		},
		get_toolbar : function(){
			
			// safely get toolbar
			if( acf.isset( this, 'toolbars', this.o.toolbar ) ) {
				
				return this.toolbars[ this.o.toolbar ];
				
			}
			
			
			// return
			return false;
			
		},
		
		init : function(){
			
			// vars
			var toolbar = this.get_toolbar(),
				command = 'mceAddControl',
				setting = 'theme_advanced_buttons{i}';
			
			
			// backup
			var _settings = $.extend( {}, tinyMCE.settings );
			
			
			// v4 settings
			if( tinymce.majorVersion == 4 ) {
				
				command = 'mceAddEditor';
				setting = 'toolbar{i}';
				
			}
			
			
			// add toolbars
			if( toolbar ) {
					
				for( var i = 1; i < 5; i++ ) {
					
					// vars
					var v = '';
					
					
					// load toolbar
					if( acf.isset( toolbar, 'theme_advanced_buttons' + i ) ) {
						
						v = toolbar['theme_advanced_buttons' + i];
						
					}
					
					
					// update setting
					tinyMCE.settings[ setting.replace('{i}', i) ] = v;
					
				}
				
			}
			
			
			// hook for 3rd party customization
			tinyMCE.settings = acf.apply_filters('wysiwyg_tinymce_settings', tinyMCE.settings, this.o.id);
			
			
			// add editor
			tinyMCE.execCommand( command, false, this.o.id);
			
			
			// add events (click, focus, blur) for inserting image into correct editor
			this.add_events();
				
			
			// restore tinyMCE.settings
			tinyMCE.settings = _settings;
			
			
			// set active editor to null
			wpActiveEditor = null;
					
		},
		
		add_events : function(){
		
			// vars
			var id = this.o.id,
				editor = tinyMCE.get( id );
			
			
			// validate
			if( !editor )
			{
				return;
			}
			
			
			// vars
			var	$container = $('#wp-' + id + '-wrap'),
				$body = $( editor.getBody() ),
				$textarea = $( editor.getElement() );
	
			
			// events
			$container.on('click', function(){
				
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('focus', function(){
			
				wpActiveEditor = id;
		
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('blur', function(){
			
				wpActiveEditor = null;
				
				// update the hidden textarea
				// - This fixes a but when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!

				// save to textarea	
				editor.save();
				
				
				// trigger change on textarea
				$textarea.trigger('change');
				
			});
			
			
		},
		destroy : function(){
			
			// vars
			var id = this.o.id,
				command = 'mceRemoveControl';
			
			
			// Remove tinymce functionality.
			// Due to the media popup destroying and creating the field within such a short amount of time,
			// a JS error will be thrown when launching the edit window twice in a row.
			try {
				
				// vars
				var editor = tinyMCE.get( id );
				
				
				// validate
				if( !editor ) {
					
					return;
					
				}
				
				
				// v4 settings
				if( tinymce.majorVersion == 4 ) {
					
					command = 'mceRemoveEditor';
					
				}
				
				
				// store value
				var val = editor.getContent();
				
				
				// remove editor
				tinyMCE.execCommand(command, false, id);
				
				
				// set value
				this.$textarea.val( val );
				
				
			} catch(e) {
				
				//console.log( e );
				
			}
			
			
			// set active editor to null
			wpActiveEditor = null;
			
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
	
	acf.add_action('ready', function( $el ){
		
		// validate
		if( ! acf.fields.wysiwyg.has_tinymce() ) {
		
			return;
			
		}
		
		
		// vars
		var $the_content = $('#wp-content-wrap'),
			$acf_content = $('#wp-acf_content-wrap'),
			mode = 'tmce';
		
		
		// move editor to bottom of page
		if( $acf_content.exists() ) {
			
			$acf_content.parent().appendTo('body');
			
		}
				
		
		// events
		acf.add_action('remove', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstart', function( $el ){
			
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
			
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstop', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
				
			});
			
		}).add_action('append', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
				
			});
			
		}).add_action('load', function( $el ){
			
			// vars
			var $fields = acf.get_fields({ type : 'wysiwyg'}, $el);
			
			
			// define mode
			if( $acf_content.exists() && $acf_content.hasClass('html-active') ) {
				
				mode = 'html';
				
			}
			
			
			// Add events to content editor
			if( $the_content.exists() ) {
			
				acf.fields.wysiwyg.set({ $el : $the_content }).add_events();
				
			}
			
			
			// temp change wysiwyg to visual tab
			setTimeout(function(){
				
				// trigger click on hidden WYSIWYG (to get in HTML mode)
				if( $acf_content.exists() && mode == 'html' ) {
					
					$acf_content.find('#acf_content-tmce').trigger('click');
				}
				
			}, 1);
			
			
			// destroy all WYSIWYG fields
			// This hack will fix a problem when the WP popup is created and hidden, then the ACF popup (image/file field) is opened
			setTimeout(function(){
				
				$fields.each(function(){
					
					acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
					
				});
				
			}, 10);
			
			
			// initialize all WYSIWYG fields
			setTimeout(function(){
				
				$fields.each(function(){
					
					acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
					
				});
				
			}, 11);
			
			
			setTimeout(function(){
				
				// trigger click on hidden WYSIWYG (to get in HTML mode)
				if( $acf_content.exists() && mode == 'html' ) {
					
					$acf_content.find('#acf_content-html').trigger('click');
				}				
				
			}, 12);
			
			
		});
		
		
	});
	
	
	/*
	*  Full screen
	*
	*  @description: this hack will hide the 'image upload' button in the WYSIWYG full screen mode if the field has disabled image uploads!
	*  @since: 3.6
	*  @created: 26/02/13
	*/
	
	$(document).on('click', '.acf-wysiwyg-wrap .mce_fullscreen', function(){
		
		// vars
		var $wrap = $(this).closest('.acf-wysiwyg-wrap');
		
		
		if( ! acf.get_data( $wrap, 'upload' ) ) {
		
			$('#mce_fullscreen_container #mce_fullscreen_add_media').hide();
			
		}
		
	});
	

})(jQuery);
