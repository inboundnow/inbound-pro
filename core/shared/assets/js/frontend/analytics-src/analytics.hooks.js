/**
 * # Hooks & Filters
 *
 * This file contains all of the form functions of the main _inbound object.
 * Filters and actions are described below
 *
 * Forked from https://github.com/carldanley/WP-JS-Hooks/blob/master/src/event-manager.js
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */

var _inboundHooks = (function (_inbound) {

	/**
	 * # EventManager
	 *
	 * Actions and filters List
	 * addAction( 'namespace.identifier', callback, priority )
	 * addFilter( 'namespace.identifier', callback, priority )
	 * removeAction( 'namespace.identifier' )
	 * removeFilter( 'namespace.identifier' )
	 * doAction( 'namespace.identifier', arg1, arg2, moreArgs, finalArg )
	 * applyFilters( 'namespace.identifier', content )
	 * @return {[type]} [description]
	 */

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
				//console.log('add filter', filter);
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

	_inbound.hooks = new EventManager();


	/**
	 * Event Hooks and Filters public methods
	 */
	 /*
	 *  add_action
	 *
	 *  This function uses _inbound.hooks to mimics WP add_action
	 *
	 *  ```js
	 *   function Inbound_Add_Action_Example(data) {
	 *       // Do stuff here.
	 *   };
	 *   // Add action to the hook
	 *   _inbound.add_action( 'name_of_action', Inbound_Add_Action_Example, 10 );
	 *   ```
	 */
	 _inbound.add_action = function() {
	  // allow multiple action parameters such as 'ready append'
	  var actions = arguments[0].split(' ');

	  for( k in actions ) {

	    // prefix action
	    arguments[0] = 'inbound.' + actions[ k ];

	    _inbound.hooks.addAction.apply(this, arguments);
	  }

	  return this;

	 };
	 /*
	 *  remove_action
	 *
	 *  This function uses _inbound.hooks to mimics WP remove_action
	 *
	 *  ```js
	 *   // Add remove action 'name_of_action'
	 *   _inbound.remove_action( 'name_of_action');
	 *  ```
	 *
	 */
	 _inbound.remove_action = function() {
	  // prefix action
	  arguments[0] = 'inbound.' + arguments[0];
	  _inbound.hooks.removeAction.apply(this, arguments);

	  return this;

	 };
	 /*
	 *  do_action
	 *
	 *  This function uses _inbound.hooks to mimics WP do_action
	 *  This is used if you want to allow for third party JS plugins to act on your functions
	 *
	 */
	 _inbound.do_action = function() {
	  // prefix action
	  arguments[0] = 'inbound.' + arguments[0];
	  _inbound.hooks.doAction.apply(this, arguments);

	  return this;

	 };
	 /*
	 *  add_filter
	 *
	 *  This function uses _inbound.hooks to mimics WP add_filter
	 *
	 *  ```js
	 *   _inbound.add_filter( 'urlParamFilter', URL_Param_Filter, 10 );
	 *   function URL_Param_Filter(urlParams) {
	 *
	 *   var params = urlParams || {};
	 *   // check for item in object
	 *   if(params.utm_source !== "undefined"){
	 *     //alert('url param "utm_source" is here');
	 *   }
	 *
	 *   // delete item from object
	 *   delete params.utm_source;
	 *
	 *   return params;
	 *
	 *   }
	 *   ```
	 */
	 _inbound.add_filter = function() {
	  // prefix action
	  arguments[0] = 'inbound.' + arguments[0];
	  _inbound.hooks.addFilter.apply(this, arguments);

	  return this;

	 };
	 /*
	 *  remove_filter
	 *
	 *  This function uses _inbound.hooks to mimics WP remove_filter
	 *
	 *   ```js
	 *   // Add remove filter 'urlParamFilter'
	 *   _inbound.remove_action( 'urlParamFilter');
	 *   ```
	 *
	 */
	 _inbound.remove_filter = function() {
	  // prefix action
	  arguments[0] = 'inbound.' + arguments[0];

	  _inbound.hooks.removeFilter.apply(this, arguments);

	  return this;

	 };
	 /*
	 *  apply_filters
	 *
	 *  This function uses _inbound.hooks to mimics WP apply_filters
	 *
	 */
	 _inbound.apply_filters = function() {
	  //console.log('Filter:' + arguments[0] + " ran on ->", arguments[1]);
	  // prefix action
	  arguments[0] = 'inbound.' + arguments[0];

	  return _inbound.hooks.applyFilters.apply(this, arguments);

	 };


    return _inbound;

})(_inbound || {});