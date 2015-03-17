

( function( window, undefined ) {
	"use strict";

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
		 * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
		 * @param [context] Supply a value to be used for this
		 */
		function addAction( action, callback, priority, context ) {
			if( typeof action === 'string' && typeof callback === 'function' ) {
				priority = parseInt( ( priority || 10 ), 10 );
				_addHook( 'actions', action, callback, priority, context );
			}

			return MethodsAvailable;
		}

		/**
		 * Performs an action if it exists. You can pass as many arguments as you want to this function; the only rule is
		 * that the first argument must always be the action.
		 */
		function doAction( /* action, arg1, arg2, ... */ ) {
			var args = Array.prototype.slice.call( arguments );
			var action = args.shift();

			if( typeof action === 'string' ) {
				_runHook( 'actions', action, args );
			}

			return MethodsAvailable;
		}

		/**
		 * Removes the specified action if it contains a namespace.identifier & exists.
		 *
		 * @param action The action to remove
		 * @param [callback] Callback function to remove
		 */
		function removeAction( action, callback ) {
			if( typeof action === 'string' ) {
				_removeHook( 'actions', action, callback );
			}

			return MethodsAvailable;
		}

		/**
		 * Adds a filter to the event manager.
		 *
		 * @param filter Must contain namespace.identifier
		 * @param callback Must be a valid callback function before this action is added
		 * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
		 * @param [context] Supply a value to be used for this
		 */
		function addFilter( filter, callback, priority, context ) {
			if( typeof filter === 'string' && typeof callback === 'function' ) {
				priority = parseInt( ( priority || 10 ), 10 );
				_addHook( 'filters', filter, callback, priority );
			}

			return MethodsAvailable;
		}

		/**
		 * Performs a filter if it exists. You should only ever pass 1 argument to be filtered. The only rule is that
		 * the first argument must always be the filter.
		 */
		function applyFilters( /* filter, filtered arg, arg2, ... */ ) {
			var args = Array.prototype.slice.call( arguments );
			var filter = args.shift();

			if( typeof filter === 'string' ) {
				return _runHook( 'filters', filter, args );
			}

			return MethodsAvailable;
		}

		/**
		 * Removes the specified filter if it contains a namespace.identifier & exists.
		 *
		 * @param filter The action to remove
		 * @param [callback] Callback function to remove
		 */
		function removeFilter( filter, callback ) {
			if( typeof filter === 'string') {
				_removeHook( 'filters', filter, callback );
			}

			return MethodsAvailable;
		}

		/**
		 * Removes the specified hook by resetting the value of it.
		 *
		 * @param type Type of hook, either 'actions' or 'filters'
		 * @param hook The hook (namespace.identifier) to remove
		 * @private
		 */
		function _removeHook( type, hook, callback, context ) {
			if ( !STORAGE[ type ][ hook ] ) {
				return;
			}
			if ( !callback ) {
				STORAGE[ type ][ hook ] = [];
			} else {
				var handlers = STORAGE[ type ][ hook ];
				var i;
				if ( !context ) {
					for ( i = handlers.length; i--; ) {
						if ( handlers[i].callback === callback ) {
							handlers.splice( i, 1 );
						}
					}
				}
				else {
					for ( i = handlers.length; i--; ) {
						var handler = handlers[i];
						if ( handler.callback === callback && handler.context === context) {
							handlers.splice( i, 1 );
						}
					}
				}
			}
		}

		/**
		 * Adds the hook to the appropriate storage container
		 *
		 * @param type 'actions' or 'filters'
		 * @param hook The hook (namespace.identifier) to add to our event manager
		 * @param callback The function that will be called when the hook is executed.
		 * @param priority The priority of this hook. Must be an integer.
		 * @param [context] A value to be used for this
		 * @private
		 */
		function _addHook( type, hook, callback, priority, context ) {
			var hookObject = {
				callback : callback,
				priority : priority,
				context : context
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
			var handlers = STORAGE[ type ][ hook ];
			
			if ( !handlers ) {
				return (type === 'filters') ? args[0] : false;
			}

			var i = 0, len = handlers.length;
			if ( type === 'filters' ) {
				for ( ; i < len; i++ ) {
					args[ 0 ] = handlers[ i ].callback.apply( handlers[ i ].context, args );
				}
			} else {
				for ( ; i < len; i++ ) {
					handlers[ i ].callback.apply( handlers[ i ].context, args );
				}
			}

			return ( type === 'filters' ) ? args[ 0 ] : true;
		}

		// return all of the publicly available methods
		return MethodsAvailable;

	};
	
	window.wp = window.wp || {};
	window.wp.hooks = new EventManager();

} )( window );


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

(function($){
	
	acf.ajax = acf.model.extend({
		
		actions: {
			'ready': 'onReady'
		},
		
		o : {
			action 			: 'acf/post/get_field_groups',
			post_id			: 0,
			page_template	: 0,
			page_parent		: 0,
			page_type		: 0,
			post_format		: 0,
			post_taxonomy	: 0,
			lang			: 0,
		},
		
		update : function( k, v ){
			
			this.o[ k ] = v;
			return this;
			
		},
		
		get : function( k ){
			
			return this.o[ k ] || null;
			
		},
		
		onReady : function(){
			
			// bail early if ajax is disabled
			if( ! acf.get('ajax') ) {
			
				return false;
				
			}
			
			
			// vars
			this.update('post_id', acf.get('post_id'));
			
			
			// MPML
			if( $('#icl-als-first').length > 0 ) {
			
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
		
	});


	
})(jQuery);

(function($){
	
	acf.fields.color_picker = acf.field.extend({
		
		type: 'color_picker',
		timeout: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		focus: function(){
			
			this.$input = this.$field.find('input[type="text"]');
			
		},
		
		initialize: function(){
			
			// reference
			var self = this;
			
			
			// vars
			var $hidden = this.$input.clone();
			
			
			// modify hidden
			$hidden.attr({
				'type'	: 'hidden',
				'class' : '',
				'id'	: '',
				'value'	: ''
 			});
 			
 			
 			// append hidden
 			this.$input.before( $hidden );
 			
 			
 			// iris
			this.$input.wpColorPicker({
				
				change: function( event, ui ){
			
					if( self.timeout ) {
				
						clearTimeout( self.timeout );
						
					}
					
					
					self.timeout = setTimeout(function(){
						
						$hidden.trigger('change');
						
					}, 1000);
					
				}
				
			});
			
		}
		
	});
	

})(jQuery);

(function($){
	
	acf.fields.date_picker = acf.field.extend({
		
		type: 'date_picker',
		$el: null,
		$input: null,
		$hidden: null,
		
		o : {},
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'blur input[type="text"]': 'blur',
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-date_picker');
			this.$input = this.$el.find('input[type="text"]');
			this.$hidden = this.$el.find('input[type="hidden"]');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// get and set value from alt field
			this.$input.val( this.$hidden.val() );
			
			
			// create options
			var args = $.extend( {}, acf.l10n.date_picker, { 
				dateFormat		:	'yymmdd',
				altField		:	this.$hidden,
				altFormat		:	'yymmdd',
				changeYear		:	true,
				yearRange		:	"-100:+100",
				changeMonth		:	true,
				showButtonPanel	:	true,
				firstDay		:	this.o.first_day
			});
			
			
			// filter for 3rd party customization
			args = acf.apply_filters('date_picker_args', args, this.$el);
			
			
			// add date picker
			this.$input.addClass('active').datepicker( args );
			
			
			// now change the format back to how it should be.
			this.$input.datepicker( "option", "dateFormat", this.o.display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if( $('body > #ui-datepicker-div').exists() ) {
			
				$('body > #ui-datepicker-div').wrap('<div class="acf-ui-datepicker" />');
				
			}
			
		},
		
		blur : function(){
			
			if( !this.$input.val() ) {
			
				this.$hidden.val('');
				
			}
			
		}
		
	});
	
})(jQuery);

(function($){
	
	acf.fields.file = acf.field.extend({
		
		type: 'file',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'click [data-name="add"]': 		'add',
			'click [data-name="edit"]': 	'edit',
			'click [data-name="remove"]':	'remove',
			'change input[type="file"]':	'change'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-file-uploader');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// add attribute to form
			if( this.$el.hasClass('basic') ) {
				
				this.$el.closest('form').attr('enctype', 'multipart/form-data');
				
			}
				
		},
		
		add : function() {
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// get repeater
			var $repeater = acf.get_closest_field( $field, 'repeater' );
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('file', 'select'),
				mode:		'select',
				type:		'',
				multiple:	$repeater.exists(),
				library:	this.o.library,
				
				select: function( attachment, i ) {
					
					
					// select / add another image field?
			    	if( i > 0 ) {
			    		
						// vars
						var key = acf.get_field_key( $field ),
							$tr = $field.closest('.acf-row');
						
						
						// reset field
						$field = false;
							
						
						// find next image field
						$tr.nextAll('.acf-row:visible').each(function(){
							
							// get next $field
							$field = acf.get_field( key, $(this) );
							
							
							// bail early if $next was not found
							if( !$field ) {
								
								return;
								
							}
							
							
							// bail early if next file uploader has value
							if( $field.find('.acf-file-uploader.has-value').exists() ) {
								
								$field = false;
								return;
								
							}
								
								
							// end loop if $next is found
							return false;
							
						});
						
						
						
						// add extra row if next is not found
						if( !$field ) {
							
							$tr = acf.fields.repeater.doFocus( $repeater ).add();
							
							
							// bail early if no $tr (maximum rows hit)
							if( !$tr ) {
								
								return false;
								
							}
							
							
							// get next $field
							$field = acf.get_field( key, $tr );
							
						}
						
					}
					
					
					// focus
					self.doFocus( $field );
					
								
			    	// render
					self.render( self.prepare(attachment) );
					
				}
			});
			
		},
		
		prepare: function( attachment ) {
		
			// vars
	    	var file = {
		    	id:		attachment.id,
		    	title:	attachment.attributes.title,
		    	name:	attachment.attributes.filename,
		    	url:	attachment.attributes.url,
		    	icon:	attachment.attributes.icon,
		    	size:	attachment.attributes.filesize
	    	};
	    	
	    	
	    	// return
	    	return file;
			
		},
		
		render : function( file ){
			
			// set atts
			this.$el.find('[data-name="icon"]').attr( 'src', file.icon );
			this.$el.find('[data-name="title"]').text( file.title );
		 	this.$el.find('[data-name="name"]').text( file.name ).attr( 'href', file.url );
		 	this.$el.find('[data-name="size"]').text( file.size );
			this.$el.find('[data-name="id"]').val( file.id ).trigger('change');
			
					 	
		 	// set div class
		 	this.$el.addClass('has-value');
	
		},
		
		edit : function() {
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// vars
			var id = this.$el.find('[data-name="id"]').val();
			
			
			// popup
			var frame = acf.media.popup({
			
				title:		acf._e('file', 'edit'),
				button:		acf._e('file', 'update'),
				mode:		'edit',
				id:			id,
				
				select:	function( attachment, i ) {
					
					// focus
					self.doFocus( $field );
					
					
					// render
			    	self.render( self.prepare(attachment) );
					
				}
			});
			
		},
		
		remove : function() {
			
			// vars
	    	var file = {
		    	id:		'',
		    	title:	'',
		    	name:	'',
		    	url:	'',
		    	icon:	'',
		    	size:	''
	    	};
	    	
	    	
	    	// add file to field
	        this.render( file );
	        
	        
			// remove class
			this.$el.removeClass('has-value');
			
		},
		
		change: function( e ){
			
			this.$el.find('[data-name="id"]').val( e.$el.val() );
			
		}
		
	});
	

})(jQuery);

(function($){
	
	acf.fields.google_map = acf.field.extend({
		
		type: 'google_map',
		$el: null,
		$input : null,
		
		status : '', // '', 'loading', 'ready'
		geocoder : false,
		map : false,
		maps : {},
		pending: $(),
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click a[data-name="clear-location"]': 	'clear',
			'click a[data-name="find-location"]': 	'locate',
			'click .title h4': 						'edit',
			'keydown .search': 						'keydown',
			'blur .search': 						'blur',
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-google-map');
			this.$input = this.$el.find('.value');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// get map
			if( this.maps[ this.o.id ] ) {
				
				this.map = this.maps[ this.o.id ];
				
			}
			
		},
		
		/*
		*  is_ready
		*
		*  This function will ensure google API is available and return a boolean for the current status
		*
		*  @type	function
		*  @date	19/11/2014
		*  @since	5.0.9
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		is_ready: function(){ 
			
			// reference
			var self = this;
			
			
			// debug
			//console.log('is_ready: %o', this.status);
			
			// check
			if( this.status == 'ready' ) {
				
				return true;
				
			} else if( this.status == 'loading' ) {
				
				return false;
				
			} else if( typeof google === 'undefined' ) {
				
				// set status
				self.status = 'loading';
				
				
				// load API
				$.getScript('https://www.google.com/jsapi', function(){
					
					// load maps
				    google.load('maps', '3', { other_params: 'sensor=false&libraries=places', callback: function(){
				    	
				    	// set status
				    	self.status = 'ready';
				    	
				    	
				    	// initialize pending
				    	self.initialize_pending();
				        
				    }});
				    
				});
				
				return false;
					
			} else if( typeof google.maps === 'undefined' ) {
				
				
				// set status
				self.status = 'loading';
				
				
				// load maps
			    google.load('maps', '3', { other_params: 'sensor=false&libraries=places', callback: function(){
			    	
			    	// set status
			    	self.status = 'ready';
			    	
			    	
			    	// initialize pending
			    	self.initialize_pending();
			        
			    }});
				
				return false;
					
			}
			
			
			// google must exist already
			this.status = 'ready';
			
			
			// return
			return true;
			
		},
		
		initialize_pending: function(){
			
			// debug
			//console.log('initialize_pending', this.status);
			
			// reference
			var self = this;
			
			this.pending.each(function(){
				
				self.doFocus( $(this) ).initialize();
				
			});
			
			
			// reset
			this.pending = $();
			
		},
		
		initialize: function(){
			
			// add to pending
			if( !this.is_ready() ) {
				
				this.pending = this.pending.add( this.$field );
				
				return false;
				
			}
			
			
			// load geocode
			if( !this.geocoder ) {
				
				this.geocoder = new google.maps.Geocoder();
				
			}
			
			
			// reference
			var self = this,
				$field = this.$field,
				$el = this.$el;
			
			
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
		    this.map.$field = this.$field;
		    
		    
		    // value exists?
		    var lat = this.$el.find('.input-lat').val(),
		    	lng = this.$el.find('.input-lng').val();
		    
		    if( lat && lng ) {
			    
			    this.update(lat, lng).center();
			    
		    }
		    
		    
			// events
			google.maps.event.addListener(autocomplete, 'place_changed', function( e ) {
			    
			    // reference
			    var $el = this.map.$el,
			    	$field = this.map.$field;
					
					
			    // manually update address
			    var address = $el.find('.search').val();
			    $el.find('.input-address').val( address );
			    $el.find('.title h4').text( address );
			    
			    
			    // vars
			    var place = this.getPlace();
			    
			    
			    // if place exists
			    if( place.geometry ) {
				    
			    	var lat = place.geometry.location.lat(),
						lng = place.geometry.location.lng();
						
					
					self.doFocus( $field ).update( lat, lng ).center();
				    
				    // bail early
				    return;
			    }
			    
			    
			    // client hit enter, manually get the place
			    self.geocoder.geocode({ 'address' : address }, function( results, status ){
			    	
			    	// validate
					if( status != google.maps.GeocoderStatus.OK ) {
						
						console.log('Geocoder failed due to: ' + status);
						return;
						
					} else if( !results[0] ) {
						
						console.log('No results found');
						return;
						
					}
					
					
					// get place
					place = results[0];
					
					var lat = place.geometry.location.lat(),
						lng = place.geometry.location.lng();
						
					
					self.doFocus( $field ).update( lat, lng ).center();
				    
				});
			    
			});
		    
		    
		    google.maps.event.addListener( this.map.marker, 'dragend', function(){
		    	
		    	// reference
			    var $field = this.map.$field;
			    
			    
		    	// vars
				var position = this.map.marker.getPosition(),
					lat = position.lat(),
			    	lng = position.lng();
			    	
				self.doFocus( $field ).update( lat, lng ).sync();
			    
			});
			
			
			google.maps.event.addListener( this.map, 'click', function( e ) {
				
				// reference
			    var $field = this.$field;
			    
			    
				// vars
				var lat = e.latLng.lat(),
					lng = e.latLng.lng();
				
				
				self.doFocus( $field ).update( lat, lng ).sync();
			
			});
			
			
	        // add to maps
	        this.maps[ this.o.id ] = this.map;
	        
		},
		
		update : function( lat, lng ){
			
			// vars
			var latlng = new google.maps.LatLng( lat, lng );
		    
		    
		    // update inputs
		    acf.val( this.$el.find('.input-lat'), lat );
		    acf.val( this.$el.find('.input-lng'), lng );
		    
			
		    // update marker
		    this.map.marker.setPosition( latlng );
		    
		    
			// show marker
			this.map.marker.setVisible( true );
		    
		    
	        // update class
	        this.$el.addClass('active');
	        
	        
	        // validation
			this.$field.removeClass('error');
			
			
	        // return for chaining
	        return this;
		},
		
		center : function(){
			
			// vars
			var position = this.map.marker.getPosition(),
				lat = this.o.lat,
				lng = this.o.lng;
			
			
			// if marker exists, center on the marker
			if( position ) {
				
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
				if( status != google.maps.GeocoderStatus.OK ) {
					
					console.log('Geocoder failed due to: ' + status);
					return;
					
				} else if( !results[0] ) {
					
					console.log('No results found');
					return;
					
				}
				
				
				// get location
				var location = results[0];
				
				
				// update h4
				$el.find('.title h4').text( location.formatted_address );

				
				// update input
				acf.val( $el.find('.input-address'), location.formatted_address );
				
			});
			
			
			// return for chaining
	        return this;
	        
		},
		
		locate : function(){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// Try HTML5 geolocation
			if( ! navigator.geolocation ) {
				
				alert( acf.l10n.google_map.browser_support );
				return this;
				
			}
			
			
			// show loading text
			this.$el.find('.title h4').text(acf.l10n.google_map.locating + '...');
			this.$el.addClass('active');
			
		    navigator.geolocation.getCurrentPosition(function(position){
		    	
		    	// vars
				var lat = position.coords.latitude,
			    	lng = position.coords.longitude;
			    	
				self.doFocus( $field ).update( lat, lng ).sync().center();
				
			});

				
		},
		
		
		clear : function(){
			
			// update class
	        this.$el.removeClass('active');
			
			
			// clear search
			this.$el.find('.search').val('');
			
			
			// clear inputs
			acf.val( this.$el.find('.input-address'), '' );
			acf.val( this.$el.find('.input-lat'), '' );
			acf.val( this.$el.find('.input-lng'), '' );
						
			
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
			
		},
		
		keydown: function( e ){
			
			// prevent form from submitting
			if( e.which == 13 ) {
				
				e.preventDefault();
			    
			}
			
		},
		
		blur: function(){
			
			// has a value?
			if( this.$el.find('.input-lat').val() ) {
				
				this.$el.addClass('active');
				
			}
			
		},
		
		show: function(){
			
			if( this.is_ready() ) {
				
				this.refresh();
				
			}
			
		}
		
	});

})(jQuery);

(function($){
	
	acf.fields.image = acf.field.extend({
		
		type: 'image',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'click [data-name="add"]': 		'add',
			'click [data-name="edit"]': 	'edit',
			'click [data-name="remove"]':	'remove',
			'change input[type="file"]':	'change'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-image-uploader');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// add attribute to form
			if( this.$el.hasClass('basic') ) {
				
				this.$el.closest('form').attr('enctype', 'multipart/form-data');
				
			}
				
		},
		
		add: function() {
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// get repeater
			var $repeater = acf.get_closest_field( this.$field, 'repeater' );
			
			
			// popup
			var frame = acf.media.popup({
				'title'		: acf._e('image', 'select'),
				'mode'		: 'select',
				'type'		: 'image',
				'multiple'	: $repeater.exists(),
				'library'	: this.o.library,
				'select'	: function( attachment, i ) {
					
					// select / add another image field?
			    	if( i > 0 ) {
			    		
			    		// vars
						var key = acf.get_field_key( $field ),
							$tr = $field.closest('.acf-row');
						
						
						// reset field
						$field = false;
						
						
						// find next image field
						$tr.nextAll('.acf-row:visible').each(function(){
							
							// get next $field
							$field = acf.get_field( key, $(this) );
							
							
							// bail early if $next was not found
							if( !$field ) {
								
								return;
								
							}
							
							
							// bail early if next file uploader has value
							if( $field.find('.acf-image-uploader.has-value').exists() ) {
								
								$field = false;
								return;
								
							}
								
								
							// end loop if $next is found
							return false;
							
						});
						
						
						// add extra row if next is not found
						if( !$field ) {
							
							$tr = acf.fields.repeater.doFocus( $repeater ).add();
							
							
							// bail early if no $tr (maximum rows hit)
							if( !$tr ) {
								
								return false;
								
							}
							
							
							// get next $field
							$field = acf.get_field( key, $tr );
							
						}
						
					}
					
					// focus
					self.doFocus( $field );
					
								
			    	// render
					self.render( self.prepare(attachment) );
					
				}
			});
			
			
		},
		
		prepare: function( attachment ) {
		
			// vars
			var image = {
		    	id:		attachment.id,
		    	url:	attachment.attributes.url
	    	};			
			
			
			// check for preview size
			if( acf.isset(attachment.attributes, 'sizes', this.o.preview_size, 'url') ) {
	    	
		    	image.url = attachment.attributes.sizes[ this.o.preview_size ].url;
		    	
	    	}
	    	
	    	
	    	// return
	    	return image;
			
		},
		
		render: function( image ){
			
	    	
			// set atts
		 	this.$el.find('[data-name="image"]').attr( 'src', image.url );
			this.$el.find('[data-name="id"]').val( image.id ).trigger('change');
			
			
			// set div class
		 	this.$el.addClass('has-value');
	
		},
		
		edit: function() {
			
			// reference
			var self = this;
			
			
			// vars
			var id = this.$el.find('[data-name="id"]').val();
			
			
			// popup
			var frame = acf.media.popup({
			
				title:		acf._e('image', 'edit'),
				button:		acf._e('image', 'update'),
				mode:		'edit',
				id:			id,
				
				select:	function( attachment, i ) {
				
			    	self.render( self.prepare(attachment) );
					
				}
				
			});
			
		},
		
		remove: function() {
			
			// vars
	    	var attachment = {
		    	id:		'',
		    	url:	''
	    	};
	    	
	    	
	    	// add file to field
	        this.render( attachment );
	        
	        
			// remove class
			this.$el.removeClass('has-value');
			
		},
		
		change: function( e ){
			
			this.$el.find('[data-name="id"]').val( e.$el.val() );
			
		}
		
	});
	

})(jQuery);

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

(function($){
	
	acf.fields.radio = acf.field.extend({
		
		type: 'radio',
		$selected: null,
		$other: null,
		
		actions: {
			'ready':	'render',
			'append':	'render'
		},
		
		events: {
			'change input[type="radio"]': 'render',
		},
		
		focus: function(){
			
			this.$selected = this.$field.find('input[type="radio"]:checked');
			this.$other = this.$field.find('input[type="text"]');
			
		},
		
		render: function(){
			
			if( this.$selected.val() === 'other' ) {
			
				this.$other.removeAttr('disabled').attr('name', this.$selected.attr('name'));
				
			} else {
				
				this.$other.attr('disabled', 'disabled').attr('name', '');
				
			}
			
		}
		
	});	

})(jQuery);

(function($){
	
	acf.fields.relationship = acf.field.extend({
		
		type: 'relationship',
		
		$el: null,
		$input: null,
		$filters: null,
		$choices: null,
		$values: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'keypress [data-filter]': 			'submit_filter',
			'change [data-filter]': 			'change_filter',
			'keyup [data-filter]': 				'change_filter',
			'click .choices .acf-rel-item': 	'add_item',
			'click [data-name="remove_item"]': 	'remove_item'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-relationship');
			this.$input = this.$el.find('.acf-hidden input');
			this.$choices = this.$el.find('.choices'),
			this.$values = this.$el.find('.values');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// reference
			var self = this,
				$field = this.$field,
				$el = this.$el,
				$input = this.$input;
			
			
			// right sortable
			this.$values.children('.list').sortable({
			
				items:					'li',
				forceHelperSize:		true,
				forcePlaceholderSize:	true,
				scroll:					true,
				
				update:	function(){
					
					$input.trigger('change');
					
				}
				
			});
			
			
			this.$choices.children('.list').scrollTop(0).on('scroll', function(e){
				
				// bail early if no more results
				if( $el.hasClass('is-loading') || $el.hasClass('is-empty') ) {
				
					return;
					
				}
				
				
				// Scrolled to bottom
				if( $(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight ) {
					
					// get paged
					var paged = $el.data('paged') || 1;
					
					
					// update paged
					$el.data('paged', (paged+1) );
					
					
					// fetch
					self.doFocus($field);
					self.fetch();
				}
				
			});
			
			
			/*
var scroll_timer = null;
			var scroll_event = function( e ){
				
				console.log( 'scroll_event' );
				
				if( scroll_timer) {
					
			        clearTimeout( scroll_timer );
			        
			    }
			    
			    
			    scroll_timer = setTimeout(function(){
				    
				    
				    if( $field.is(':visible') && acf.is_in_view($field) ) {
						
						// fetch
						self.doFocus($field);
						self.fetch();
						
						
						$(window).off('scroll', scroll_event);
						
					}
				    
				    
			    }, 100);			    
			    				
				
			};
			
						
			$(window).on('scroll', scroll_event);
			
*/
			// ajax fetch values for left side
			this.fetch();
			
		},
		
		fetch: function(){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// add class
			this.$el.addClass('is-loading');
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.o.xhr ) {
			
				this.o.xhr.abort();
				this.o.xhr = false;
				
			}
			
			
			// add to this.o
			this.o.action = 'acf/fields/relationship/query';
			this.o.field_key = $field.data('key');
			this.o.post_id = acf.get('post_id');
			
			
			// ready for ajax
			var ajax_data = acf.prepare_for_ajax( this.o );
			
			
			// clear html if is new query
			if( ajax_data.paged == 1 ) {
				
				this.$choices.children('.list').html('')
				
			}
			
			
			// add message
			this.$choices.children('.list').append('<p>' + acf._e('relationship', 'loading') + '...</p>');
			
			
			// get results
		    var xhr = $.ajax({
		    
		    	url:		acf.get('ajaxurl'),
				dataType:	'json',
				type:		'post',
				data:		ajax_data,
				
				success: function( json ){
					
					// render
					self.doFocus($field);
					self.render(json);
					
				}
				
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render: function( json ){
			
			// remove loading class
			this.$el.removeClass('is-loading is-empty');
			
			
			// remove p tag
			this.$choices.children('.list').children('p').remove();
			
			
			// no results?
			if( !json || !json.length ) {
			
				// add class
				this.$el.addClass('is-empty');
			
				
				// add message
				if( this.o.paged == 1 ) {
				
					this.$choices.children('.list').append('<p>' + acf._e('relationship', 'empty') + '</p>');
			
				}

				
				// return
				return;
				
			}
			
			
			// get new results
			var $new = $( this.walker(json) );
			
				
			// apply .disabled to left li's
			this.$values.find('.acf-rel-item').each(function(){
				
				$new.find('.acf-rel-item[data-id="' +  $(this).data('id') + '"]').addClass('disabled');
				
			});
			
			
			// underline search match
			if( this.o.s ) {
			
				var s = this.o.s;
				
				$new.find('.acf-rel-item').each(function(){
					
					// vars
					var find = $(this).text(),
						replace = find.replace( new RegExp('(' + s + ')', 'gi'), '<b>$1</b>');
					
					$(this).html( $(this).html().replace(find, replace) );	
									
				});
				
			}
			
			
			// append
			this.$choices.children('.list').append( $new );
			
			
			// merge together groups
			var label = '',
				$list = null;
				
			this.$choices.find('.acf-rel-label').each(function(){
				
				if( $(this).text() == label ) {
					
					$list.append( $(this).siblings('ul').html() );
					
					$(this).parent().remove();
					
					return;
				}
				
				
				// update vars
				label = $(this).text();
				$list = $(this).siblings('ul');
				
			});
			
			
		},
		
		walker: function( data ){
			
			// vars
			var s = '';
			
			
			// loop through data
			if( $.isArray(data) ) {
			
				for( var k in data ) {
				
					s += this.walker( data[ k ] );
					
				}
				
			} else if( $.isPlainObject(data) ) {
				
				// optgroup
				if( data.children !== undefined ) {
					
					s += '<li><span class="acf-rel-label">' + data.text + '</span><ul class="acf-bl">';
					
						s += this.walker( data.children );
					
					s += '</ul></li>';
					
				} else {
				
					s += '<li><span class="acf-rel-item" data-id="' + data.id + '">' + data.text + '</span></li>';
					
				}
				
			}
			
			
			// return
			return s;
			
		},
		
		submit_filter: function( e ){
			
			// don't submit form
			if( e.which == 13 ) {
				
				e.preventDefault();
				
			}
			
		},
		
		change_filter: function( e ){
			
			// vars
			var val = e.$el.val(),
				filter = e.$el.data('filter');
				
			
			// Bail early if filter has not changed
			if( this.$el.data(filter) == val ) {
			
				return;
				
			}
			
			
			// update attr
			this.$el.data(filter, val);
			
			
			// reset paged
			this.$el.data('paged', 1);
		    
		    
		    // fetch
		    this.fetch();
			
		},
		
		add_item: function( e ){
			
			// max posts
			if( this.o.max > 0 ) {
			
				if( this.$values.find('.acf-rel-item').length >= this.o.max ) {
				
					alert( acf._e('relationship', 'max').replace('{max}', this.o.max) );
					
					return;
					
				}
				
			}
			
			
			// can be added?
			if( e.$el.hasClass('disabled') ) {
			
				return false;
				
			}
			
			
			// disable
			e.$el.addClass('disabled');
			
			
			// template
			var html = [
				'<li>',
					'<input type="hidden" name="' + this.$input.attr('name') + '[]" value="' + e.$el.data('id') + '" />',
					'<span data-id="' + e.$el.data('id') + '" class="acf-rel-item">' + e.$el.html(),
						'<a href="#" class="acf-icon small dark" data-name="remove_item"><i class="acf-sprite-remove"></i></a>',
					'</span>',
				'</li>'].join('');
						
			
			// add new li
			this.$values.children('.list').append( html )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove_item : function( e ){
			
			// vars
			var $span = e.$el.parent(),
				id = $span.data('id');
			
			
			// remove
			$span.parent('li').remove();
			
			
			// show
			this.$choices.find('.acf-rel-item[data-id="' + id + '"]').removeClass('disabled');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
		}
		
	});
	

})(jQuery);

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

(function($){
	
	acf.fields.url = acf.field.extend({
		
		type: 'url',
		$input: null,
		
		actions: {
			'ready':	'render',
			'append':	'render'
		},
		
		events: {
			'keyup input[type="url"]': 'render',
		},
		
		focus: function(){
			
			this.$input = this.$field.find('input[type="url"]');
			
		},
		
		render: function(){
			
			this.$input.parent().removeClass('valid');
			
			if( this.$input.val().substr(0, 4) === 'http' ) {
				
				this.$input.parent().addClass('valid');
				
			}
			
		}
		
	});

})(jQuery);

(function($){
    
	acf.validation = acf.model.extend({
		
		actions: {
			'ready 20': 'onReady'
		},
		
		
		// vars
		active	: 1,
		ignore	: 0,
		
		
		// classes
		error_class : 'acf-error',
		message_class : 'acf-error-message',
		
		
		// el
		$trigger : null,
		
		
		// functions
		onReady : function(){
			
			// read validation setting
			this.active = acf.get('validation');
			
			
			// bail early if disabled
			if( !this.active ) {
			
				return;
				
			}
			
			
			// add events
			this.add_events();
		},
		
		add_error : function( $field, message ){
			
			// add class
			$field.addClass(this.error_class);
			
			
			// add message
			if( message !== undefined ) {
				
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
			
			
			// hide ajax stuff on submit button
			var $submit = $('#submitpost').exists() ? $('#submitpost') : $('#submitdiv');
			
			if( $submit.exists() ) {
				
				// remove disabled classes
				$submit.find('.disabled').removeClass('disabled');
				$submit.find('.button-disabled').removeClass('button-disabled');
				$submit.find('.button-primary-disabled').removeClass('button-primary-disabled');
				
				
				// remove spinner
				$submit.find('.spinner').hide();
				
			}
			
			
			// validate json
			if( !json || typeof json.result === 'undefined' || json.result == 1) {
				
				// remove previous error message
				acf.remove_el( $form.children('.' + this.message_class) );
				
			
				// remove hidden postboxes (this will stop them from being posted to save)
				$form.find('.acf-postbox.acf-hidden').remove();
					
					
				// bypass JS and submit form
				this.ignore = 1;
				
				
				// action for 3rd party customization
				acf.do_action('submit', $form);
				
				
				// submit form again
				if( this.$trigger ) {
					
					this.$trigger.click();
				
				} else {
					
					$form.submit();
				
				}
				
				
				// end function
				return;
			}
			
			
			// reset trigger
			this.$trigger = null;
			
			
			// vars
			var $first_field = null;
			
			
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
					this.add_error( $field, error.message );
					
					
					// save as first field
					if( i == 0 ) {
						
						$first_field = $field;
						
					}
					
				}
			
			}
			
				
			// get $message
			var $message = $form.children('.' + this.message_class);
			
			if( !$message.exists() ) {
				
				$message = $('<div class="' + this.message_class + '"><p></p></div>');
				
				$form.prepend( $message );
				
			}
			
			
			// update message text
			$message.children('p').text( json.message );
			
			
			// if message is not in view, scroll to first error field
			if( !acf.is_in_view($message) && $first_field ) {
				
				$("html, body").animate({ scrollTop: ($first_field.offset().top - 32 - 20) }, 500);
				
			}
			
		},
		
		add_events : function(){
			
			var self = this;
			
			
			// focus
			$(document).on('focus click change', '.acf-field[data-required="1"] input, .acf-field[data-required="1"] textarea, .acf-field[data-required="1"] select', function( e ){

				self.remove_error( $(this).closest('.acf-field') );
				
			});
			
			
			// ignore validation
			$(document).on('click', '#save-post, #post-preview', function(){
				
				self.ignore = 1;
				self.$trigger = $(this);
				
			});
			
			
			// save trigger
			$(document).on('click', 'input[type="submit"]', function(){
				
				self.$trigger = $(this);
				
			});
			
			
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
		
	});
	

})(jQuery);

(function($){
	
	acf.fields.wysiwyg = acf.field.extend({
		
		type: 'wysiwyg',
		$el: null,
		$textarea: null,
		toolbars: {},
		
		actions: {
			'ready':		'initialize',
			'append':		'initialize',
			'remove':		'disable',
			'sortstart':	'disable',
			'sortstop':		'enable'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.wp-editor-wrap').last();
			this.$textarea = this.$el.find('textarea');
			
			// get options
			this.o = acf.get_data( this.$el );
			this.o.id = this.$textarea.attr('id');
			
		},
		
		initialize: function(){
			
			// bail early if no tinymce
			if( typeof tinyMCEPreInit === 'undefined' || typeof tinymce === 'undefined' ) {
				
				return false;
				
			}
			
			
			// vars
			var mceInit = this.get_mceInit(),
				qtInit = this.get_qtInit();
			
				
			// append settings
			tinyMCEPreInit.mceInit[ mceInit.id ] = mceInit;
			tinyMCEPreInit.qtInit[ qtInit.id ] = qtInit;
			
			
			// initialize mceInit
			if( this.$el.hasClass('tmce-active') ) {
				
				try {
					
					tinymce.init( mceInit );
					
				} catch(e){}
				
			}
			

			// initialize qtInit
			try {
			
				var qtag = quicktags( qtInit );
				
				this._buttonsInit( qtag );
				
			} catch(e){}
			
		},
		
		get_mceInit : function(){
			
			// reference
			var $field = this.$field;
				
				
			// vars
			var toolbar = this.get_toolbar( this.o.toolbar ),
				mceInit = $.extend({}, tinyMCEPreInit.mceInit.acf_content);
			
			
			// selector
			mceInit.selector = '#' + this.o.id;
			
			
			// id
			mceInit.id = this.o.id; // tinymce v4
			mceInit.elements = this.o.id; // tinymce v3
			
			
			// toolbar
			if( toolbar ) {
				
				var k = (tinymce.majorVersion < 4) ? 'theme_advanced_buttons' : 'toolbar';
				
				for( var i = 1; i < 5; i++ ) {
					
					mceInit[ k + i ] = acf.isset(toolbar, i) ? toolbar[i] : '';
					
				}
				
			}
			
			
			// events
			if( tinymce.majorVersion < 4 ) {
				
				mceInit.setup = function( ed ){
					
					ed.onInit.add(function(ed, event) {
						
						// focus
						$(ed.getBody()).on('focus', function(){
					
							acf.validation.remove_error( $field );
							
						});
						
						$(ed.getBody()).on('blur', function(){
							
							// update the hidden textarea
							// - This fixes a bug when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!
			
							// save to textarea	
							ed.save();
							
							
							// trigger change on textarea
							$field.find('textarea').trigger('change');
							
						});
					
					});
					
				};
			
			} else {
			
				mceInit.setup = function( ed ){
					
					ed.on('focus', function(e) {
				
						acf.validation.remove_error( $field );
						
					});
					
					ed.on('blur', function(e) {
						
						// update the hidden textarea
						// - This fixes a but when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!
		
						// save to textarea	
						ed.save();
						
						
						// trigger change on textarea
						$field.find('textarea').trigger('change');
						
					});
					
					/*
ed.on('ResizeEditor', function(e) {
					    console.log(e);
					});
*/
					
				};
			
			}
			
			
			// disable wp_autoresize_on (no solution yet for fixed toolbar)
			mceInit.wp_autoresize_on = false;
			
			
			// hook for 3rd party customization
			mceInit = acf.apply_filters('wysiwyg_tinymce_settings', mceInit, mceInit.id);
			
			
			// return
			return mceInit;
			
		},
		
		get_qtInit : function(){
				
			// vars
			var qtInit = $.extend({}, tinyMCEPreInit.qtInit.acf_content);
			
			
			// id
			qtInit.id = this.o.id;
			
			
			// hook for 3rd party customization
			qtInit = acf.apply_filters('wysiwyg_quicktags_settings', qtInit, qtInit.id);
			
			
			// return
			return qtInit;
			
		},
		
		/*
		*  disable
		*
		*  This function will disable the tinymce for a given field
		*  Note: txtarea_el is different from $textarea.val() and is the value that you see, not the value that you save.
		*        this allows text like <--more--> to wok instead of showing as an image when the tinymce is removed
		*
		*  @type	function
		*  @date	1/08/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		disable: function(){
			
			try {
				
				// vars
				var ed = tinyMCE.get( this.o.id ),
					txtarea_el = tinyMCE.DOM.get( this.o.id );
					val = txtarea_el.value;
					
				
				// destory
				ed.destroy();
				
				
				// update value
				if( this.$field.find('.wp-editor-wrap').hasClass('html-active') ) {
				
					txtarea_el.value = val;
				
				}

				
			} catch(e) {}
			
		},
		
		enable: function(){
			
			// bail early if html mode
			if( this.$field.find('.wp-editor-wrap').hasClass('html-active') ) {
				
				return;
				
			}
			
			
			try {
				
				tinyMCE.init( tinyMCEPreInit.mceInit[ this.o.id ] );
				
			} catch(e) {}
			
			
		},
		
		get_toolbar : function( name ){
			
			// bail early if toolbar doesn't exist
			if( typeof this.toolbars[ name ] !== 'undefined' ) {
				
				return this.toolbars[ name ];
				
			}
			
			
			// return
			return false;
			
		},
		
		
		/*
		*  _buttonsInit
		*
		*  This function will add the quicktags HTML to a WYSIWYG field. Normaly, this is added via quicktags on document ready,
		*  however, there is no support for 'append'. Source: wp-includes/js/quicktags.js:245
		*
		*  @type	function
		*  @date	1/08/2014
		*  @since	5.0.0
		*
		*  @param	ed (object) quicktag object
		*  @return	n/a
		*/
		
		_buttonsInit: function( ed ) {
			var defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';
	
			canvas = ed.canvas;
			name = ed.name;
			settings = ed.settings;
			html = '';
			theButtons = {};
			use = '';

			// set buttons
			if ( settings.buttons ) {
				use = ','+settings.buttons+',';
			}

			for ( i in edButtons ) {
				if ( !edButtons[i] ) {
					continue;
				}

				id = edButtons[i].id;
				if ( use && defaults.indexOf( ',' + id + ',' ) !== -1 && use.indexOf( ',' + id + ',' ) === -1 ) {
					continue;
				}

				if ( !edButtons[i].instance || edButtons[i].instance === inst ) {
					theButtons[id] = edButtons[i];

					if ( edButtons[i].html ) {
						html += edButtons[i].html(name + '_');
					}
				}
			}

			if ( use && use.indexOf(',fullscreen,') !== -1 ) {
				theButtons.fullscreen = new qt.FullscreenButton();
				html += theButtons.fullscreen.html(name + '_');
			}


			if ( 'rtl' === document.getElementsByTagName('html')[0].dir ) {
				theButtons.textdirection = new qt.TextDirectionButton();
				html += theButtons.textdirection.html(name + '_');
			}

			ed.toolbar.innerHTML = html;
			ed.theButtons = theButtons;
			
		},
		
	});
	

	$(document).ready(function(){
		
		// move acf_content wysiwyg
		if( $('#wp-acf_content-wrap').exists() ) {
			
			$('#wp-acf_content-wrap').parent().appendTo('body');
			
		}
		
	});


})(jQuery);

