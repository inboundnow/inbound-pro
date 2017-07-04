/*! Inbound Analyticsv1.0.0 | (c) 2017 Inbound Now | https://github.com/inboundnow/cta */
/**
 * # _inbound
 *
 * This main the _inbound class
 *
 * @author David Wells <david@inboundnow.com>
 * @author Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */

var inbound_data = inbound_data || {};
var _inboundOptions = _inboundOptions || {};
/* Ensure global _gaq Google Analytics queue has been initialized. */
var _gaq = _gaq || [];

var _inbound = (function(options) {

    /* Constants */
    var defaults = {
        timeout: ( inbound_settings.is_admin ? 500 : 10000 ),
        formAutoTracking: true,
        formAutoPopulation: true
    };

    var Analytics = {
        /* Initialize individual modules */
        init: function() {
            _inbound.Utils.init();

            _inbound.Utils.domReady(window, function() {
                /* On Load Analytics Events */
                _inbound.DomLoaded();

            });
        },
        DomLoaded: function() {
            _inbound.PageTracking.init();
            /* run form mapping */
            _inbound.Forms.init();
            /* set URL params */
            _inbound.Utils.setUrlParams();
            _inbound.LeadsAPI.init();
            /* run form mapping for dynamically generated forms */
            setTimeout(function() {
                _inbound.Forms.init();
            }, 2000);

            _inbound.trigger('analytics_ready');

        },
        /**
         * Merge script defaults with user options
         * @private
         * @param {Object} defaults Default settings
         * @param {Object} options User options
         * @returns {Object} Merged values of defaults and options
         */
        extend: function(defaults, options) {
            var extended = {};
            var prop;
            for (prop in defaults) {
                if (Object.prototype.hasOwnProperty.call(defaults, prop)) {
                    extended[prop] = defaults[prop];
                }
            }
            for (prop in options) {
                if (Object.prototype.hasOwnProperty.call(options, prop)) {
                    extended[prop] = options[prop];
                }
            }
            return extended;
        },
        /* Debugger Function toggled by var debugMode */
        debug: function(msg, callback) {
            /* legacy */
        },
        deBugger: function(context, msg, callback) {

            if (!console) {
                return;
            }
            //if app not in debug mode, exit immediately
            // check for hash
            var hash = (document.location.hash) ? document.location.hash : '',
                debugHash = hash.indexOf("#debug") > -1,
                msg = msg || false,
                logCookie,
                logAllMessages,
                hashcontext;

            if (hash && hash.match(/debug/)) {
                hash = hash.split('-');
                hashcontext = hash[1];
            }


            logAllMessages = (_inbound.Utils.readCookie("inbound_debug") === "true") ? true : false;
            logCookie = (_inbound.Utils.readCookie("inbound_debug_" + context) === "true") ? true : false;

            if (!logCookie && !debugHash && !logAllMessages) {
                // no logger set. exit.
                return;
            };

            //console.log the message
            if (msg && (typeof msg === 'string')) {

                if (logAllMessages || hashcontext === 'all') {
                    console.log('logAll "' + context + '"  =>', msg)
                } else if (logCookie) {
                    console.log('log "' + context + '" =>', msg)
                } else if (context === hashcontext) {
                    console.log('#log "' + context + '" =>', msg)
                }

            };

            //execute the callback if one was passed-in
            if (callback && (callback instanceof Function)) {
                callback();
            };
        }
    };

    var settings = Analytics.extend(defaults, options);
    /* Set globals */
    Analytics.Settings = settings || {};

    return Analytics;

})(_inboundOptions);
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
/**
 * # _inbound UTILS
 *
 * This file contains all of the utility functions used by analytics
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */

var _inboundUtils = (function(_inbound) {

    var storageSupported,
        corsEnabled = window.XMLHttpRequest && 'withCredentials' in new XMLHttpRequest(),
        toString = Object.prototype.toString,
        currentPage = ('https:' == location.protocol ? 'https://' : 'http://') + location.hostname + location.pathname.replace(/\/$/, "");

    var settings = {
        api_host: currentPage,
        track_pageview: true,
        track_links_timeout: 300,
        cookie_name: '_sp',
        cookie_expiration: 365,
        cookie_domain: (host = location.hostname.match(/[a-z0-9][a-z0-9\-]+\.[a-z\.]{2,6}$/i)) ? host[0] : ''
    };

    _inbound.Utils = {
        init: function() {

            this.polyFills();
            this.checkLocalStorage();
            this.SetUID();
            this.storeReferralData();

        },
        /*! http://stackoverflow.com/questions/951791/javascript-global-error-handling */
        /* Polyfills for missing browser functionality */
        polyFills: function() {
            /* Console.log fix for old browsers */
            if (!window.console) {
                window.console = {};
            }
            var m = [
                "log", "info", "warn", "error", "debug", "trace", "dir", "group",
                "groupCollapsed", "groupEnd", "time", "timeEnd", "profile", "profileEnd",
                "dirxml", "assert", "count", "markTimeline", "timeStamp", "clear"
            ];
            // define undefined methods as noops to prevent errors
            for (var i = 0; i < m.length; i++) {
                if (!window.console[m[i]]) {
                    window.console[m[i]] = function() {};
                }
            }
            /* Event trigger polyfill for IE9 and 10
            (function() {
                function CustomEvent(event, params) {
                    params = params || {
                        bubbles: false,
                        cancelable: false,
                        detail: undefined
                    };
                    var evt = document.createEvent('CustomEvent');
                    evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
                    return evt;
                }

                CustomEvent.prototype = window.Event.prototype;

                window.CustomEvent = CustomEvent;
            })();*/

            /*\
            |*| Polyfill Date.toISOString
            \*/
            if (!Date.prototype.toISOString) {
                (function() {
                    /**
                     * @param {number} text
                     * @returns {?}
                     */
                    function pad(text) {
                            /** @type {string} */
                            var code = String(text);
                            return 1 === code.length && (code = '0' + code), code;
                        }
                        /**
                         * @returns {string}
                         */
                    Date.prototype.toISOString = function() {
                        return this.getUTCFullYear() + '-' + pad(this.getUTCMonth() + 1) + '-' + pad(this.getUTCDate()) + 'T' + pad(this.getUTCHours()) + ':' + pad(this.getUTCMinutes()) + ':' + pad(this.getUTCSeconds()) + '.' + String((this.getUTCMilliseconds() / 1E3).toFixed(3)).slice(2, 5) + 'Z';
                    };
                })();
            }

            /* custom event for ie8+ https://gist.github.com/WebReflection/6693661 */
            try {
                new CustomEvent('?');
            } catch (o_O) {
                /*!(C) Andrea Giammarchi -- WTFPL License*/
                this.CustomEvent = function(
                    eventName,
                    defaultInitDict
                ) {

                    // the infamous substitute
                    function CustomEvent(type, eventInitDict) {
                        var event = document.createEvent(eventName);
                        if (type !== null) {
                            initCustomEvent.call(
                                event,
                                type, (eventInitDict || (
                                    // if falsy we can just use defaults
                                    eventInitDict = defaultInitDict
                                )).bubbles,
                                eventInitDict.cancelable,
                                eventInitDict.detail
                            );
                        } else {
                            // no need to put the expando property otherwise
                            // since an event cannot be initialized twice
                            // previous case is the most common one anyway
                            // but if we end up here ... there it goes
                            event.initCustomEvent = initCustomEvent;
                        }
                        return event;
                    }

                    // borrowed or attached at runtime
                    function initCustomEvent(
                        type, bubbles, cancelable, detail
                    ) {
                        this['init' + eventName](type, bubbles, cancelable, detail);
                        'detail' in this || (this.detail = detail);
                    }

                    // that's it
                    return CustomEvent;
                }(
                    // is this IE9 or IE10 ?
                    // where CustomEvent is there
                    // but not usable as construtor ?
                    this.CustomEvent ?
                    // use the CustomEvent interface in such case
                    'CustomEvent' : 'Event',
                    // otherwise the common compatible one
                    {
                        bubbles: false,
                        cancelable: false,
                        detail: null
                    }
                );
            }
            /* querySelectorAll polyfill for ie7+ */
            if (!document.querySelectorAll) {
                document.querySelectorAll = function(selectors) {
                    var style = document.createElement('style'),
                        elements = [],
                        element;
                    document.documentElement.firstChild.appendChild(style);
                    document._qsa = [];

                    style.styleSheet.cssText = selectors + '{x-qsa:expression(document._qsa && document._qsa.push(this))}';
                    window.scrollBy(0, 0);
                    style.parentNode.removeChild(style);

                    while (document._qsa.length) {
                        element = document._qsa.shift();
                        element.style.removeAttribute('x-qsa');
                        elements.push(element);
                    }
                    document._qsa = null;
                    return elements;
                };
            }

            if (!document.querySelector) {
                document.querySelector = function(selectors) {
                    var elements = document.querySelectorAll(selectors);
                    return (elements.length) ? elements[0] : null;
                };
            }
            /* Innertext shim for firefox https://github.com/duckinator/innerText-polyfill/blob/master/innertext.js */
            if ((!('innerText' in document.createElement('a'))) && ('getSelection' in window)) {
                HTMLElement.prototype.__defineGetter__("innerText", function() {
                    var selection = window.getSelection(),
                        ranges = [],
                        str;

                    // Save existing selections.
                    for (var i = 0; i < selection.rangeCount; i++) {
                        ranges[i] = selection.getRangeAt(i);
                    }

                    // Deselect everything.
                    selection.removeAllRanges();

                    // Select `el` and all child nodes.
                    // 'this' is the element .innerText got called on
                    selection.selectAllChildren(this);

                    // Get the string representation of the selected nodes.
                    str = selection.toString();

                    // Deselect everything. Again.
                    selection.removeAllRanges();

                    // Restore all formerly existing selections.
                    for (var i = 0; i < ranges.length; i++) {
                        selection.addRange(ranges[i]);
                    }

                    // Oh look, this is what we wanted.
                    // String representation of the element, close to as rendered.
                    return str;
                })
            }
        },
        /**
         * Create cookie
         *
         * ```js
         * // Creates cookie for 10 days
         * _inbound.Utils.createCookie( 'cookie_name', 'value', 10 );
         * ```
         *
         * @param  {string} name        Name of cookie
         * @param  {string} value       Value of cookie
         * @param  {string} days        Length of storage
         */
        createCookie: function(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toGMTString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        },
        /**
         * Read cookie value
         *
         * ```js
         * var cookie = _inbound.Utils.readCookie( 'cookie_name' );
         * console.log(cookie); // cookie value
         * ```
         * @param  {string} name name of cookie
         * @return {string}      value of cookie
         */
        readCookie: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        },
        /**
         * Erase cookie
         *
         * ```js
         * // usage:
         * _inbound.Utils.eraseCookie( 'cookie_name' );
         * // deletes 'cookie_name' value
         * ```
         * @param  {string} name name of cookie
         * @return {string}      value of cookie
         */
        eraseCookie: function(name) {
            this.createCookie(name, "", -1);
        },
        /* Get All Cookies */
        getAllCookies: function() {
            var cookies = {};
            if (document.cookie && document.cookie !== '') {
                var split = document.cookie.split(';');
                for (var i = 0; i < split.length; i++) {
                    var name_value = split[i].split("=");
                    name_value[0] = name_value[0].replace(/^ /, '');
                    cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
                }
            }
            _inbound.totalStorage('inbound_cookies', cookies); // store cookie data
            return cookies;
        },
        /* Grab URL params and save */
        setUrlParams: function() {
            var urlParams = {};

            (function() {
                var e,
                    d = function(s) {
                        return decodeURIComponent(s).replace(/\+/g, " ");
                    },
                    q = window.location.search.substring(1),
                    r = /([^&=]+)=?([^&]*)/g;

                while (e = r.exec(q)) {
                    if (e[1].indexOf("[") == "-1")
                        urlParams[d(e[1])] = d(e[2]);
                    else {
                        var b1 = e[1].indexOf("["),
                            aN = e[1].slice(b1 + 1, e[1].indexOf("]", b1)),
                            pN = d(e[1].slice(0, b1));

                        if (typeof urlParams[pN] != "object")
                            urlParams[d(pN)] = {},
                            urlParams[d(pN)].length = 0;

                        if (aN)
                            urlParams[d(pN)][d(aN)] = d(e[2]);
                        else
                            Array.prototype.push.call(urlParams[d(pN)], d(e[2]));

                    }
                }
            })();

            /* Set Param Cookies */
            for (var k in urlParams) {
                /* account for wordpress media uploader bug */
                if (k == 'action') {
                    continue;
                }

                if (typeof urlParams[k] == "object") {
                    for (var k2 in urlParams[k])
                        this.createCookie(k2, urlParams[k][k2], 30);
                } else {
                    this.createCookie(k, urlParams[k], 30);
                }
            }
            /* Set Param LocalStorage */
            if (storageSupported) {
                var pastParams = _inbound.totalStorage('inbound_url_params') || {};
                var params = this.mergeObjs(pastParams, urlParams);
                _inbound.totalStorage('inbound_url_params', params); // store cookie data
            }

            var options = {
                'option1': 'yo',
                'option2': 'woooo'
            };

            _inbound.trigger('url_parameters', urlParams, options);

        },
        getAllUrlParams: function() {
            var get_params = {};
            if (storageSupported) {
                var get_params = _inbound.totalStorage('inbound_url_params');
            }
            return get_params;
        },
        /* Get url param */
        getParameterVal: function(name, string) {
            return (RegExp(name + '=' + '(.+?)(&|$)').exec(string) || [, false])[1];
        },
        // Check local storage
        // provate browsing safari fix https://github.com/marcuswestin/store.js/issues/42#issuecomment-25274685
        checkLocalStorage: function() {
            if ('localStorage' in window) {
                try {
                    ls = (typeof window.localStorage === 'undefined') ? undefined : window.localStorage;
                    if (typeof ls == 'undefined' || typeof window.JSON == 'undefined') {
                        storageSupported = false;
                    } else {
                        storageSupported = true;
                    }

                } catch (err) {
                    storageSupported = false;
                }
            }
            return storageSupported;
            /* http://spin.atomicobject.com/2013/01/23/ios-private-browsing-localstorage/
            var hasStorage;
            hasStorage = function() {
              var mod, result;
              try {
                mod = new Date;
                localStorage.setItem(mod, mod.toString());
                result = localStorage.getItem(mod) === mod.toString();
                localStorage.removeItem(mod);
                return result;
              } catch (_error) {} 
            };
             */
        },
        // http://stackoverflow.com/questions/4391575/how-to-find-the-size-of-localstorage
        showLocalStorageSize: function() {
            function stringSizeBytes(str) {
                return str.length * 2;
            }

            function toMB(bytes) {
                return bytes / 1024 / 1024;
            }

            function toSize(key) {
                return {
                    name: key,
                    size: stringSizeBytes(localStorage[key])
                };
            }

            function toSizeMB(info) {
                info.size = toMB(info.size).toFixed(2) + ' MB';
                return info;
            }

            var sizes = Object.keys(localStorage).map(toSize).map(toSizeMB);

            console.table(sizes);
        },
        /* Add days to datetime */
        addDays: function(myDate, days) {
            return new Date(myDate.getTime() + days * 24 * 60 * 60 * 1000);
        },
        GetDate: function() {
            var timeNow = new Date(),
                d = timeNow.getDate(),
                dPre = (d < 10) ? "0" : "",
                y = timeNow.getFullYear(),
                h = timeNow.getHours(),
                hPre = (h < 10) ? "0" : "",
                min = timeNow.getMinutes(),
                minPre = (min < 10) ? "0" : "",
                sec = timeNow.getSeconds(),
                secPre = (sec < 10) ? "0" : "",
                m = timeNow.getMonth() + 1,
                mPre = (m < 10) ? "0" : "";

            var datetime = y + '/' + mPre + m + "/" + dPre + d + " " + hPre + h + ":" + minPre + min + ":" + secPre + sec;
            /* format 2014/11/13 18:22:02 */
            return datetime;
        },
        /* Set Expiration Date of Session Logging. LEGACY Not in Use */
        SetSessionTimeout: function() {
            var session = this.readCookie("lead_session_expire");
            //console.log(session_check);
            if (!session) {
                //_inbound.trigger('session_start'); // trigger 'inbound_analytics_session_start'
            } else {
                //_inbound.trigger('session_resume'); // trigger 'inbound_analytics_session_active'
            }
            var d = new Date();
            d.setTime(d.getTime() + 30 * 60 * 1000);

            this.createCookie("lead_session_expire", true, d); // Set cookie on page load

        },
        storeReferralData: function() {
            //console.log(expire_time);
            var d = new Date(),
                referrer = document.referrer || "Direct Traffic",
                referrer_cookie = _inbound.Utils.readCookie("inbound_referral_site"),
                original_src = _inbound.totalStorage('inbound_original_referral');

            d.setTime(d.getTime() + 30 * 60 * 1000);

            if (!referrer_cookie) {
                this.createCookie("inbound_referral_site", referrer, d);
            }
            if (!original_src) {
                _inbound.totalStorage('inbound_original_referral', original_src);
            }
        },
        CreateUID: function(length) {
            var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split(''),
                str = '';
            if (!length) {
                length = Math.floor(Math.random() * chars.length);
            }
            for (var i = 0; i < length; i++) {
                str += chars[Math.floor(Math.random() * chars.length)];
            }
            return str;
        },
        generateGUID: function(a) {
            return a ? (a ^ 16 * Math.random() >> a / 4).toString(16) : ([1E7] + -1E3 + -4E3 + -8E3 + -1E11).replace(/[018]/g, guid);
        },
        SetUID: function(leadUID) {
            /* Set Lead UID */
            if (!this.readCookie("wp_lead_uid")) {
                var wp_lead_uid = leadUID || this.CreateUID(35);
                this.createCookie("wp_lead_uid", wp_lead_uid);
            }
        },
        /* Count number of session visits */
        countProperties: function(obj) {
            var count = 0;
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    ++count;
                }
            }
            return count;
        },
        mergeObjs: function(obj1, obj2) {
            var obj3 = {};
            for (var attrname in obj1) {
                obj3[attrname] = obj1[attrname];
            }
            for (var attrname in obj2) {
                obj3[attrname] = obj2[attrname];
            }
            return obj3;
        },
        hasClass: function(className, el) {
            var hasClass;
            if ('classList' in document.documentElement) {
                var hasClass = el.classList.contains(className);
            } else {
                var hasClass = new RegExp('(^|\\s)' + className + '(\\s|$)').test(el.className); /* IE Polyfill */
            }
            return hasClass;
        },
        addClass: function(className, el) {
            if ('classList' in document.documentElement) {
                el.classList.add(className);
            } else {
                if (!this.hasClass(el, className)) {
                    el.className += (el.className ? ' ' : '') + className;
                }
            }
        },
        removeClass: function(className, el) {
            if ('classList' in document.documentElement) {
                el.classList.remove(className);
            } else {
                if (this.hasClass(el, className)) {
                    el.className = el.className.replace(new RegExp('(^|\\s)*' + className + '(\\s|$)*', 'g'), '');
                }
            }
        },
        removeElement: function(el) {
            el.parentNode.removeChild(el);
        },
        trim: function(s) {
            s = s.replace(/(^\s*)|(\s*$)/gi, "");
            s = s.replace(/[ ]{2,}/gi, " ");
            s = s.replace(/\n /, "\n");
            return s;
        },
        ajaxPolyFill: function() {
            if (typeof XMLHttpRequest !== 'undefined') {
                return new XMLHttpRequest();
            }
            var versions = [
                "MSXML2.XmlHttp.5.0",
                "MSXML2.XmlHttp.4.0",
                "MSXML2.XmlHttp.3.0",
                "MSXML2.XmlHttp.2.0",
                "Microsoft.XmlHttp"
            ];

            var xhr;
            for (var i = 0; i < versions.length; i++) {
                try {
                    xhr = new ActiveXObject(versions[i]);
                    break;
                } catch (e) {}
            }
            return xhr;
        },
        ajaxSendData: function(url, callback, method, data, sync) {
            var x = this.ajaxPolyFill();
            /* timeout for safari idiocy */
            setTimeout(function() {
                x.open(method, url, true);
                x.onreadystatechange = function() {
                    if (x.readyState == 4) {
                        callback(x.responseText)
                    }
                };
                if (method == 'POST') {
                    x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                }
                x.send(data);
            }, 100);
        },
        ajaxGet: function(url, data, callback, sync) {
            var query = [];
            for (var key in data) {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            this.ajaxSendData(url + '?' + query.join('&'), callback, 'GET', null, sync)
        },
        ajaxPost: function(url, data, callback, sync) {
            var query = [];
            for (var key in data) {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
            this.ajaxSendData(url, callback, 'POST', query.join('&'), sync)
        },
        /**
         * @param {string} event
         * @param {(Object|null)} properties
         * @param {(Function|null)} callback
         */
        sendEvent: function(event, properties, callback) {
            properties = properties || {};
            async = true;
            var cookieData = getCookie(); /* get cookie data */
            if (cookieData) {
                var key;
                for (key in cookieData) {
                    properties[key] = cookieData[key];
                }
            }
            if (!properties.id) {
                properties.id = getId();
            }
            var props = {
                e: event,
                t: (new Date()).toISOString(),
                kv: properties
            };
            var path = settings.api_host + '/track?data=' + encodeURIComponent(JSON.stringify(props));
            if (corsEnabled) {
                /* CORS */
                var xhr = new XMLHttpRequest();
                xhr.open('GET', path, async);
                xhr.withCredentials = async;
                xhr.send(null);
            } else {
                /* jsonP */
                var el = document.createElement('script');
                el.type = 'text/javascript';
                el.async = async;
                el.defer = async;
                el.src = path;
                var insertAt = document.getElementsByTagName('script')[0];
                insertAt.parentNode.insertBefore(el, insertAt);
            }
            return action(callback), self;
        },
        domReady: function(win, fn) {

            var done = false,
                top = true,

                doc = win.document,
                root = doc.documentElement,

                add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
                rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
                pre = doc.addEventListener ? '' : 'on',

                init = function(e) {
                    if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
                    (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                    if (!done && (done = true)) fn.call(win, e.type || e);
                },

                poll = function() {
                    try {
                        root.doScroll('left');
                    } catch (e) {
                        setTimeout(poll, 50);
                        return;
                    }
                    init('poll');
                };

            if (doc.readyState == 'complete') {

                fn.call(win, 'lazy');

            } else {
                if (doc.createEventObject && root.doScroll) {
                    try {
                        top = !win.frameElement;
                    } catch (e) {}
                    if (top) poll();
                }
                doc[add](pre + 'DOMContentLoaded', init, false);
                doc[add](pre + 'readystatechange', init, false);
                win[add](pre + 'load', init, false);
            }

        },
        /* Cross-browser event listening  */
        addListener: function(element, eventName, listener) {
            if (!element) {
                return;
            }
            //console.log(eventName);
            //console.log(listener);
            if (element.addEventListener) {
                element.addEventListener(eventName, listener, false);
            } else if (element.attachEvent) {
                element.attachEvent("on" + eventName, listener);
            } else {
                element['on' + eventName] = listener;
            }
        },
        removeListener: function(element, eventName, listener) {

            if (element.removeEventListener) {
                element.removeEventListener(eventName, listener, false);
            } else if (element.detachEvent) {
                element.detachEvent("on" + eventName, listener);
            } else {
                element["on" + eventName] = null;
            }
        },
        /*
         * Throttle function borrowed from:
         * Underscore.js 1.5.2
         * http://underscorejs.org
         * (c) 2009-2013 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
         * Underscore may be freely distributed under the MIT license.
         */
        throttle: function(func, wait) {
            var context, args, result;
            var timeout = null;
            var previous = 0;
            var later = function() {
                previous = new Date;
                timeout = null;
                result = func.apply(context, args);
            };
            return function() {
                var now = new Date;
                if (!previous) previous = now;
                var remaining = wait - (now - previous);
                context = this;
                args = arguments;
                if (remaining <= 0) {
                    clearTimeout(timeout);
                    timeout = null;
                    previous = now;
                    result = func.apply(context, args);
                } else if (!timeout) {
                    timeout = setTimeout(later, remaining);
                }
                return result;
            };
        },
        /*
         * Determine which version of GA is being used
         * "ga", "_gaq", and "dataLayer" are the possible globals
         */
        checkTypeofGA: function() {
            if (typeof ga === "function") {
                universalGA = true;
            }

            if (typeof _gaq !== "undefined" && typeof _gaq.push === "function") {
                classicGA = true;
            }

            if (typeof dataLayer !== "undefined" && typeof dataLayer.push === "function") {
                googleTagManager = true;
            }

        },
        /**
         * Caches user's search data in the browser until they can be saved to the database
         */
        cacheSearchData: function(searchData, form) {

            if(storageSupported){
                //store the searches in the local storage
                var stored = _inbound.totalStorage.getItem('inbound_search_storage');
                if(stored){
                    //if there are stored searches, put the new one in the first index
                    stored.unshift(searchData);
                    _inbound.totalStorage.setItem('inbound_search_storage', stored);
                }else{
                    //if there aren't any searches stored, save the current search
                    var store = [searchData];
                    _inbound.totalStorage.setItem('inbound_search_storage', store);
                }
            }else{
                //if local storage is not possible, store the data in a cookie
                var new_search = JSON.stringify(searchData),
                    stored_searches = this.readCookie('inbound_search_storage');
                    
                if(stored_searches){
                    //add the old searches to the new one
                    new_search += ('SPLIT-TOKEN' + stored_searches);
                }
                this.createCookie('inbound_search_storage', new_search, '180');
            }

            _inbound.Forms.releaseFormSubmit(form);
        },
        /**
         * Stores search data to the database on page load. 
         * If successful, it erases the cached searches from the user's browser
         */
        storeSearchData: function(){

            /*if there isn't a lead id or the nonce isn't set, don't try to store the data*/
            if(!inbound_settings.wp_lead_data.lead_id || !inbound_settings.wp_lead_data.lead_nonce){
                return;
            }

            var dataToSend = [],
                localStorageData = _inbound.totalStorage.getItem('inbound_search_storage'),
                cookieStorageData = this.readCookie('inbound_search_storage');
                
            /*if nothing is stored, exit*/
            if(!localStorageData && !cookieStorageData){
                return;
            }
            
            /*if set, add the cookie search data to the data to send*/
            if(cookieStorageData){
                cookieStorageData = cookieStorageData.split('SPLIT-TOKEN');
                
                for(var i in cookieStorageData){
                    //console.log(cookieStorageData[i]);
                    dataToSend.push(JSON.parse(cookieStorageData[i]));
                }
            }
            
            /*if set, add the locally stored data to the data to send*/
            if(localStorageData){
                dataToSend = dataToSend.concat(localStorageData);
            }

            dataToSend.sort(function(a, b){ return a.timestamp - b.timestamp; });

            dataToSend = encodeURIComponent(JSON.stringify(dataToSend));

            var package = {'action' : 'inbound_search_store', 'data' : dataToSend, 'nonce' : inbound_settings.wp_lead_data.lead_nonce, 'lead_id' : inbound_settings.wp_lead_data.lead_id };

            callback = function(status){
                if(status){ status = JSON.parse(status); }

                if(status.success){
                    //log the success!
                    console.log(status.success);
                    //erase the stored data
                    _inbound.Utils.eraseCookie('inbound_search_storage');
                    _inbound.totalStorage.deleteItem('inbound_search_storage');
                }
                
                if(status.error){
                    console.log(status.error);
                }
            };
            this.ajaxPost(inbound_settings.admin_url, package, callback);
        }
    };

    return _inbound;

})(_inbound || {});

/**
 * # Inbound Forms
 *
 * This file contains all of the form functions of the main _inbound object.
 * Filters and actions are described below
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */
/* Finish Exclusions for CC */

/* Launches form class */
var InboundForms = (function(_inbound) {

    var debugMode = false,
        utils = _inbound.Utils,
        no_match = [],
        rawParams = [],
        mappedParams = [],
        callTracker = {},
        settings = _inbound.Settings;

    var FieldMapArray = [
        "first name",
        "last name",
        "name",
        "email",
        "e-mail",
        "phone",
        "website",
        "job title",
        "your favorite food",
        "company",
        "tele",
        "address",
        "comment"
        /* Adding values here maps them */
    ];

    _inbound.Forms = {

        // Init Form functions
        init: function() {
            _inbound.Forms.runFieldMappingFilters();
            _inbound.Forms.formTrackInit();
            _inbound.Forms.searchTrackInit();
        },
        /**
         * This triggers the forms.field_map filter on the mapping array.
         * This will allow you to add or remore Items from the mapping lookup
         *
         * ### Example inbound.form_map_before filter
         *
         * This is an example of how form mapping can be filtered and
         * additional fields can be mapped via javascript
         *
         * ```js
         *  // Adding the filter function
         *  function Inbound_Add_Filter_Example( FieldMapArray ) {
         *    var map = FieldMapArray || [];
         *    map.push('new lookup value');
         *
         *    return map;
         *  };
         *
         *  // Adding the filter on dom ready
         *  _inbound.hooks.addFilter( 'inbound.form_map_before', Inbound_Add_Filter_Example, 10 );
         * ```
         *
         * @return {[type]} [description]
         */
        runFieldMappingFilters: function() {
            FieldMapArray = _inbound.hooks.applyFilters('forms.field_map', FieldMapArray);
            //alert(FieldMapArray);
        },
        debug: function(msg, callback) {
            //if app not in debug mode, exit immediately
            if (!debugMode || !console) {
                return;
            }

            var msg = msg || false;
            //console.log the message
            if (msg && (typeof msg === 'string')) {
                console.log(msg);
            }

            //execute the callback if one was passed-in
            if (callback && (callback instanceof Function)) {
                callback();
            }
        },
        formTrackInit: function() {

            for (var i = 0; i < window.document.forms.length; i++) {
                var trackForm = false;
                var form = window.document.forms[i];
                /* process forms only once */
                if (!form.dataset.formProcessed) {
                    form.dataset.formProcessed = true;
                    trackForm = this.checkTrackStatus(form);
                    if (trackForm) {
                        this.attachFormSubmitEvent(form); /* attach form listener */
                        this.initFormMapping(form);
                    }
                }
            }
        },
        searchTrackInit: function(){
            
            /* exit if searches aren't supposed to be tracked, or this function has already been called */
            if(inbound_settings.search_tracking == 'off' || callTracker['searchTrackInit']){
                return;
            }

            for (var i = 0; i < window.document.forms.length; i++) {
                var trackForm = false;
                var form = window.document.forms[i];
                /* process forms only once */
                if (!form.dataset.searchChecked) {
                    form.dataset.searchChecked = true;
                    trackForm = this.checkSearchTrackStatus(form);
                    if (trackForm) {
                        this.attachSearchFormSubmitEvent(form); /* attach form listener */
                    }
                }
            }

            /* store the search data on init */
            utils.storeSearchData();
            
            /* log that this function has been called */
            callTracker['searchTrackInit'] = true;
        },
        checkTrackStatus: function(form) {
            var ClassIs = form.getAttribute('class');
            if (ClassIs !== "" && ClassIs !== null) {
                if (ClassIs.toLowerCase().indexOf("wpl-track-me") > -1) {
                    return true;
                } else if (ClassIs.toLowerCase().indexOf("inbound-track") > -1) {
                    return true;
                } else {
                    cb = function() { console.log(form); };
                    _inbound.deBugger('forms', "This form not tracked. Please assign on in settings...", cb);
                    return false;
                }
            }
        },
        checkSearchTrackStatus: function(form) {
            var ClassIs = form.getAttribute('class'),
                IdIs = form.getAttribute('id');
            if (ClassIs !== "" && ClassIs !== null) {
                if (ClassIs.toLowerCase().indexOf("search") > -1) {
                    return true;
                }
            }
            if (IdIs !== "" && IdIs !== null) {
                if (IdIs.toLowerCase().indexOf("search") > -1) {
                    return true;
                }
            }else{
                cb = function() { console.log(form); };
                _inbound.deBugger('searches', "This search form is not tracked. Please assign on in settings...", cb);
                return false;
            }
        },
        /* Loop through include/exclude items for tracking */
        loopClassSelectors: function(selectors, action) {
            for (var i = selectors.length - 1; i >= 0; i--) {

                var selector = utils.trim(selectors[i])
                if (selector.indexOf("#") === -1 && selector.indexOf(".") === -1) {
                    // assign ID as default
                    selector = "#" + selector;
                }
                //if(selectors[i] match . or # )
                selector = document.querySelector(selector);
                //console.log("SELECTOR", selector);
                if (selector) {
                    if (action === 'add') {
                        _inbound.Utils.addClass('wpl-track-me', selector);
                        _inbound.Utils.addClass('inbound-track', selector);
                    } else {
                        _inbound.Utils.removeClass('wpl-track-me', selector);
                        _inbound.Utils.removeClass('inbound-track', selector);
                    }
                }
            }
        },
        /* Map field fields on load */
        initFormMapping: function(form) {
            var hiddenInputs = [];

            for (var i = 0; i < form.elements.length; i++) {
                formInput = form.elements[i];

                if (formInput.type === 'hidden') {
                    hiddenInputs.push(formInput);
                    continue;
                }

                //this.ignoreFields(formInput);
                /* Map form fields */
                this.mapField(formInput);
                /* Remember visible inputs */
                this.rememberInputValues(formInput);
                /* Fill visible inputs */
                if (settings.formAutoPopulation && !_inbound.Utils.hasClass( "nopopulate", form ) ) {
                    this.fillInputValues(formInput);
                }

            }

            /* loop hidden inputs */
            for (var n = hiddenInputs.length - 1; n >= 0; n--) {
                formInput = hiddenInputs[n];
                this.mapField(formInput);
            }

            //console.log('mapping on load completed');
        },
                /* Maps data attributes to fields on page load */
        mapField: function(input) {

            var input_id = input.id || false;
            var input_name = input.name || false;
            var label = this.getInputLabel(input);

            if(label){
                //console.log(label[0].innerText);
                var ignoreField = this.ignoreFieldByLabel(label[0].innerText);
                if(ignoreField){
                    input.dataset.ignoreFormField = true;
                    return false;
                }
            }
                 
            /* Loop through all match possiblities */
            for (i = 0; i < FieldMapArray.length; i++) {
                //for (var i = FieldMapArray.length - 1; i >= 0; i--) {
                var found = false;
                var match = FieldMapArray[i];
                var lookingFor = utils.trim(match);
                var nice_name = lookingFor.replace(/ /g, '_');


                //console.log("NICE NAME", nice_name);
                //console.log('looking for match on ' + lookingFor);
                //_inbound.deBugger('forms', 'looking for match on ' + lookingFor + " nice_name= " + nice_name);

                // Check if input has an attached lable using for= tag
                //var $laxbel = $("label[for='" + $element.attr('id') + "']").text();
                //var labxel = 'label[for="' + input_id + '"]';

                /* look for name attribute match */
                if (input_name && input_name.toLowerCase().indexOf(lookingFor) > -1) {
                    found = true;
                    _inbound.deBugger('forms', 'Found matching name attribute for -> ' + lookingFor);

                    /* look for id match */
                } else if (input_id && input_id.toLowerCase().indexOf(lookingFor) > -1) {

                    found = true;
                     _inbound.deBugger('forms', 'Found matching ID attribute for ->' + lookingFor);

                    /* Check siblings for label */
                } else if (label) {
                    //var label = (label.length > 1 ? label[0] : label);
                    //console.log('label', label);
                    if (label[0].innerText.toLowerCase().indexOf(lookingFor) > -1) {

                    found = true;
                    _inbound.deBugger('forms', 'Found matching sibling label for -> ' + lookingFor);

                    }

                } else {
                    /* no match found */
                    //_inbound.deBugger('forms', 'NO Match on ' + lookingFor + " in " + input_name);
                    no_match.push(lookingFor);

                }

                /* Map the field */
                if (found) {
                    this.addDataAttr(input, nice_name);
                    this.removeArrayItem(FieldMapArray, lookingFor);
                    i--; //decrement count
                }

            }
            
            return inbound_data;

        },
        /* prevent default submission temporarily */
        formListener: function(event) {
            //console.log(event);
            event.preventDefault();
            _inbound.Forms.saveFormData(event.target);
            document.body.style.cursor = "wait";
        },
        /* prevent default submission temporarily */
        searchFormListener: function(event) {
            //console.log(event);
            event.preventDefault();
            _inbound.Forms.saveSearchData(event.target);
            //document.body.style.cursor = "wait";
        },
        /* attach form listeners */
        attachFormSubmitEvent: function(form) {
            utils.addListener(form, 'submit', this.formListener);
            var email_input = document.querySelector('.inbound-email');
            /* utils.addListener(email_input, 'blur', this.mailCheck); */
        },
        /* attach search form listener */
        attachSearchFormSubmitEvent: function(form) {
            utils.addListener(form, 'submit', this.searchFormListener);
        },
        /* Ignore CC data */
        ignoreFieldByLabel: function(label) {
            var ignore_field = false;

            if(!label){ return false; }

            // Ignore any fields with labels that indicate a credit card field
            if (label.toLowerCase().indexOf('credit card') != -1 || label.toLowerCase().indexOf('card number') != -1) {
                ignore_field = true;
            }

            if (label.toLowerCase().indexOf('expiration') != -1 || label.toLowerCase().indexOf('expiry') != -1) {
                ignore_field = true;
            }

            if (label.toLowerCase() == 'month' || label.toLowerCase() == 'mm' || label.toLowerCase() == 'yy' || label.toLowerCase() == 'yyyy' || label.toLowerCase() == 'year') {
                ignore_field = true;
            }

            if (label.toLowerCase().indexOf('cvv') != -1 || label.toLowerCase().indexOf('cvc') != -1 || label.toLowerCase().indexOf('secure code') != -1 || label.toLowerCase().indexOf('security code') != -1) {
                ignore_field = true;
            }

            if(ignore_field){
                _inbound.deBugger('forms', 'ignore ' + label);
            }

            return ignore_field;

        },
        /* not implemented yet */
        ignoreFieldByValue: function(value){
            var ignore_field = false;

            if(!value){ return false; }

            if (value.toLowerCase() == 'visa' || value.toLowerCase() == 'mastercard' || value.toLowerCase() == 'american express' || value.toLowerCase() == 'amex' || value.toLowerCase() == 'discover') {
                ignore_field = true;
            }

            // Check if value has integers, strip out spaces, then ignore anything with a credit card length (>16) or an expiration/cvv length (<5)
            var int_regex = new RegExp("/^[0-9]+$/");
            if (int_regex.test(value)) {
                var value_no_spaces = value.replace(' ', '');

                if (this.isInt(value_no_spaces) && value_no_spaces.length >= 16) {
                    ignore_field = true;
                }

            }

            return ignore_field;

        },
        isInt: function(n) {
            return typeof n == "number" && isFinite(n) && n % 1 === 0;
        },
        releaseFormSubmit: function(form) {
            //console.log('remove form listener event');
            document.body.style.cursor = "default";
            utils.removeClass('wpl-track-me', form);
            utils.removeListener(form, 'submit', this.formListener);
            var formClass = form.getAttribute('class');
            if (formClass !== "" && formClass !== null) {
                /* If contact form 7 do this */
                if (formClass.toLowerCase().indexOf("wpcf7-form") != -1) {
                    //alert('release')
                    setTimeout(function() {
                       document.body.style.cursor = "default";
                    }, 300);
                    return true;
                }
            }

            form.submit();
            /* fallback if submit name="submit" */
            setTimeout(function() {
                for (var i = 0; i < form.elements.length; i++) {
                    formInput = form.elements[i];
                    type = formInput.type || false;
                    if (type === "submit" && formInput.name === "submit") {
                        form.elements[i].click();
                    }
                }
            }, 2000);

        },
        saveFormData: function(form) {
            var inputsObject = inputsObject || {};
            for (var i = 0; i < form.elements.length; i++) {

                // console.log(inputsObject);

                formInput = form.elements[i];
                multiple = false;

                if (formInput.name) {

                    if (formInput.dataset.ignoreFormField) {
                        _inbound.deBugger('forms', 'ignore ' + formInput.name);
                        continue;
                    }

                    inputName = formInput.name.replace(/\[([^\[]*)\]/g, "%5B%5D$1");
                    //inputName = inputName.replace(/-/g, "_");
                    if (!inputsObject[inputName]) {
                        inputsObject[inputName] = {};
                    }
                    if (formInput.type) {
                        inputsObject[inputName]['type'] = formInput.type;
                    }
                    if (!inputsObject[inputName]['name']) {
                        inputsObject[inputName]['name'] = formInput.name;
                    }
                    if (formInput.dataset.mapFormField) {
                        inputsObject[inputName]['map'] = formInput.dataset.mapFormField;
                    }


                    switch (formInput.nodeName) {

                        case 'INPUT':
                            value = this.getInputValue(formInput);


                            if (value === false) {
                                continue;
                            }
                            break;

                        case 'TEXTAREA':
                            value = formInput.value;
                            break;

                        case 'SELECT':
                            if (formInput.multiple) {
                                values = [];
                                multiple = true;

                                for (var j = 0; j < formInput.length; j++) {
                                    if (formInput[j].selected) {
                                        values.push(encodeURIComponent(formInput[j].value));
                                    }
                                }

                            } else {
                                value = (formInput.value);
                            }

                            break;
                    }

                    _inbound.deBugger('forms', 'Input Value = ' + value);


                    if (value) {
                        /* inputsObject[inputName].push(multiple ? values.join(',') : encodeURIComponent(value)); */
                        if (!inputsObject[inputName]['value']) {
                            inputsObject[inputName]['value'] = [];
                        }
                        inputsObject[inputName]['value'].push(multiple ? values.join(',') : encodeURIComponent(value));
                        var value = multiple ? values.join(',') : encodeURIComponent(value);

                    }

                }
            }
            _inbound.deBugger('forms', inputsObject);

            //console.log('These are the raw values', inputsObject);
            //_inbound.totalStorage('the_key', inputsObject);
            //var inputsObject = sortInputs(inputsObject);

            var matchCommon = /name|first name|last name|email|e-mail|phone|website|job title|company|tele|address|comment/;

            for (var input in inputsObject) {
                //console.log(input);

                var inputValue = inputsObject[input]['value'];
                var inputMappedField = inputsObject[input]['map'];
                //if (matchCommon.test(input) !== false) {
                //console.log(input + " Matches Regex run mapping test");
                //var map = inputsObject[input];
                //console.log("MAPP", map);
                //mappedParams.push( input + '=' + inputsObject[input]['value'].join(',') );
                //}

                /* Add custom hook here to look for additional values */
                if (typeof(inputValue) != "undefined" && inputValue != null && inputValue != "") {
                    rawParams.push(input + '=' + inputsObject[input]['value'].join(','));
                }

                if (typeof(inputMappedField) != "undefined" && inputMappedField != null && inputsObject[input]['value']) {
                    //console.log('Data ATTR', formInput.dataset.mapFormField);
                    mappedParams.push(inputMappedField + "=" + inputsObject[input]['value'].join(','));
                    if (input === 'email') {
                        var email = inputsObject[input]['value'].join(',');
                        //alert(email);

                    }
                }
            }

            var raw_params = rawParams.join('&');
            _inbound.deBugger('forms', "Stringified Raw Form PARAMS: " + raw_params);

            var mapped_params = mappedParams.join('&');
             _inbound.deBugger('forms', "Stringified Mapped PARAMS" + mapped_params);

            /* Check Use form Email or Cookie */
            var email = utils.getParameterVal('email', mapped_params) || utils.readCookie('wp_lead_email');

            /* Legacy Email map */
            if (!email) {
                email = utils.getParameterVal('wpleads_email_address', mapped_params);
            }

            var fullName = utils.getParameterVal('name', mapped_params);
            var fName = utils.getParameterVal('first_name', mapped_params);
            var lName = utils.getParameterVal('last_name', mapped_params);

            // Fallbacks for empty values
            if (!lName && fName) {
                var parts = decodeURI(fName).split(" ");
                if (parts.length > 0) {
                    fName = parts[0];
                    lName = parts[1];
                }
            }

            if (fullName && !lName && !fName) {
                var parts = decodeURI(fullName).split(" ");
                if (parts.length > 0) {
                    fName = parts[0];
                    lName = parts[1];
                }
            }

            fullName = (fName && lName) ? fName + " " + lName : fullName;

            if(!fName) { fName = ""; }
            if(!lName) { lName = ""; }

            _inbound.deBugger('forms', "fName = " + fName);
            _inbound.deBugger('forms', "lName = " + lName);
            _inbound.deBugger('forms', "fullName = " + fullName);

            //return false;
            var page_views = _inbound.totalStorage('page_views') || {};
            var urlParams = _inbound.totalStorage('inbound_url_params') || {};

            /* check if redirect url is empty */
            var formRedirectUrl = form.querySelectorAll('input[value][type="hidden"][name="inbound_furl"]:not([value=""])');
            var inbound_form_is_ajax = false;
            if(formRedirectUrl.length == 0 || formRedirectUrl[0]['value'] == 'IA=='){
                var inbound_form_is_ajax = true;
            }

            /* get form id */
            var inbound_form_id = form.querySelectorAll('input[value][type="hidden"][name="inbound_form_id"]');
            if(inbound_form_id.length > 0 ){
                inbound_form_id = inbound_form_id[0]['value'];
            } else {
                inbound_form_id = 0;
            }

            var inboundDATA = {
                'email': email
            };

            /* Get Variation ID */
            if (typeof(landing_path_info) != "undefined") {
                var variation = landing_path_info.variation;
            } else if (typeof(cta_path_info) != "undefined") {
                var variation = cta_path_info.variation;
            } else {
                var variation = inbound_settings.variation_id;
            }
            var post_type = inbound_settings.post_type || 'page';
            var page_id = inbound_settings.post_id || 0;
            // data['wp_lead_uid'] = jQuery.cookie("wp_lead_uid") || null;
            // data['search_data'] = JSON.stringify(jQuery.totalStorage('inbound_search')) || {};
            search_data = {};
            /* Filter here for raw */
            formData = {
                'action': 'inbound_lead_store',
                'email': email,
                "full_name": fullName,
                "first_name": fName,
                "last_name": lName,
                'raw_params': raw_params,
                'mapped_params': mapped_params,
                'url_params': JSON.stringify(urlParams),
                'search_data': 'test',
                'page_views': JSON.stringify(page_views),
                'post_type': post_type,
                'page_id': page_id,
                'variation': variation,
                'source': utils.readCookie("inbound_referral_site"),
                'inbound_submitted': inbound_form_is_ajax,
                'inbound_form_id': inbound_form_id,
                'inbound_nonce': inbound_settings.ajax_nonce,
                'event': form
            };

            callback = function(leadID) {
                /* Action Example */

                _inbound.deBugger('forms', 'Lead Created with ID: ' + leadID);
                leadID = parseInt(leadID, 10);
                formData.leadID = leadID;
                /* Set Lead cookie ID */
                if (leadID) {
                    utils.createCookie("wp_lead_id", leadID);
                    _inbound.totalStorage.deleteItem('page_views'); // remove pageviews
                    _inbound.totalStorage.deleteItem('tracking_events'); // remove events
                }

                _inbound.trigger('form_after_submission', formData);

                /* Resume normal form functionality */
                _inbound.Forms.releaseFormSubmit(form);

            }

            _inbound.trigger('form_before_submission', formData);

            utils.ajaxPost(inbound_settings.admin_url, formData, callback);
        },
        saveSearchData: function(form) {
            var inputsObject = inputsObject || {};
            for (var i = 0; i < form.elements.length; i++) {

                //console.log(inputsObject);

                formInput = form.elements[i];
                multiple = false;

                if (formInput.name) {

                    if (formInput.dataset.ignoreFormField) {
                        _inbound.deBugger('searches', 'ignore ' + formInput.name);
                        continue;
                    }

                    inputName = formInput.name.replace(/\[([^\[]*)\]/g, "%5B%5D$1");
                    //inputName = inputName.replace(/-/g, "_");
                    if (!inputsObject[inputName]) {
                        inputsObject[inputName] = {};
                    }
                    if (formInput.type) {
                        inputsObject[inputName]['type'] = formInput.type;
                        
                    }
                    if (!inputsObject[inputName]['name']) {
                        inputsObject[inputName]['name'] = formInput.name;
                    }
                    if (formInput.dataset.mapFormField) {
                        inputsObject[inputName]['map'] = formInput.dataset.mapFormField;
                    }


                    switch (formInput.nodeName) {

                        case 'INPUT':
                            value = this.getInputValue(formInput);


                            if (value === false) {
                                continue;
                            }
                            break;

                        case 'TEXTAREA':
                            value = formInput.value;
                            break;

                        case 'SELECT':
                            if (formInput.multiple) {
                                values = [];
                                multiple = true;

                                for (var j = 0; j < formInput.length; j++) {
                                    if (formInput[j].selected) {
                                        values.push(encodeURIComponent(formInput[j].value));
                                    }
                                }

                            } else {
                                value = (formInput.value);
                            }

                            break;
                    }

                    _inbound.deBugger('searches', 'Input Value = ' + value);


                    if (value) {
                        /* inputsObject[inputName].push(multiple ? values.join(',') : encodeURIComponent(value)); */
                        if (!inputsObject[inputName]['value']) {
                            inputsObject[inputName]['value'] = [];
                        }
                        inputsObject[inputName]['value'].push(multiple ? values.join(',') : encodeURIComponent(value));
                        var value = multiple ? values.join(',') : encodeURIComponent(value);

                    }

                }
            }

            _inbound.deBugger('searches', inputsObject);

            /* create an array of search fields //(not fully implemented) at the moment, it only maps the text in the "search" input types*/
            var searchQuery = [];
            for (var input in inputsObject) {
                var inputValue = inputsObject[input]['value'];
                var inputType = inputsObject[input]['type'];

                /* Add custom hook here to look for additional values */
                if (typeof(inputValue) != "undefined" && inputValue != null && inputValue != "") {
                    // This is for mapping all fields of a search form. The resulting string is processed 
                    // in inbound-pro\classes\admin\report-templates\report.lead-searches-and-comments.php
                    // In the function print_action_popup()
                    // searchQuery.push(input + '|value|' + inputsObject[input]['value'].join(','));
                    
                    // get the search input value
                    if(inputType == 'search'){
                        searchQuery.push('search_text' + '|value|' + inputsObject[input]['value']);
                    }
                }
            }
            /* exit if there isn't a search query */
            if(!searchQuery[0]){
                return;
            }

            var searchString = searchQuery.join('|field|');
            _inbound.deBugger('searches', "Stringified Search Form PARAMS: " + searchString);

            /* Get Variation ID */
            if (typeof(landing_path_info) != "undefined") {
                var variation = landing_path_info.variation;
            } else if (typeof(cta_path_info) != "undefined") {
                var variation = cta_path_info.variation;
            } else {
                var variation = inbound_settings.variation_id;
            }
            var post_type = inbound_settings.post_type || 'page';
            var page_id = inbound_settings.post_id || 0;
            
            var user_UID = utils.readCookie("wp_lead_uid");
            
            /* get the user's email address if possible */
            if(inbound_settings.wp_lead_data.lead_email){
                email = inbound_settings.wp_lead_data.lead_email;
            }else if(utils.readCookie('inbound_wpleads_email_address')){
                email = utils.readCookie('inbound_wpleads_email_address');
            }else{
                email = '';
            }

            /* Filter here for raw */
            searchData = {
                'email': email,
                'search_data': searchString,
                'user_UID': user_UID,
                'post_type': post_type,
                'page_id': page_id,
                'variation': variation,
                'source': utils.readCookie("inbound_referral_site"),
                'ip_address': inbound_settings.ip_address,
                'timestamp': Math.floor((new Date).getTime()/1000),
            };

            /* filter data before caching it in the user's browser */
            _inbound.trigger('search_before_caching', searchData);

            /* cache search data */
            if(inbound_settings.wp_lead_data.lead_id){
                searchData['lead_id'] = inbound_settings.wp_lead_data.lead_id;
                utils.cacheSearchData(searchData, form)
            }else{
                utils.cacheSearchData(searchData, form);
            }
            

        },
        rememberInputValues: function(input) {
            var name = (input.name) ? "inbound_" + input.name : '';
            var type = (input.type) ? input.type : 'text';
            if (type === 'submit' || type === 'hidden' || type === 'file' || type === "password" || input.dataset.ignoreFormField) {
                return false;
            }

            utils.addListener(input, 'change', function(e) {
                if (e.target.name) {
                    /* Check for input type */
                    if (type !== "checkbox") {
                        var value = e.target.value;
                    } else {
                        var values = [];
                        var checkboxes = document.querySelectorAll('input[name="' + e.target.name + '"]');
                        for (var i = 0; i < checkboxes.length; i++) {
                            var checked = checkboxes[i].checked;
                            if (checked) {
                                values.push(checkboxes[i].value);
                            }
                            value = values.join(',');
                        };
                    }
                    //console.log(e.target.nodeName);
                    //console.log('change ' + e.target.name + " " + encodeURIComponent(value));

                    inputData = {
                        name: e.target.name,
                        node: e.target.nodeName.toLowerCase(),
                        type: type,
                        value: value,
                        mapping: e.target.dataset.mapFormField
                    };

                    _inbound.trigger('form_input_change', inputData);
                    /* Set Field Input Cookies */
                    utils.createCookie("inbound_" + e.target.name, encodeURIComponent(value));
                    // _inbound.totalStorage('the_key', FormStore);
                    /* Push to 'unsubmitted form object' */
                }

            });
        },
        fillInputValues: function(input) {
            var name = (input.name) ? "inbound_" + input.name : '';
            var type = (input.type) ? input.type : 'text';
            if (type === 'submit' || type === 'hidden' || type === 'file' || type === "password") {
                return false;
            }
            if (utils.readCookie(name) && name != 'comment') {

                value = decodeURIComponent(utils.readCookie(name));
                if (type === 'checkbox' || type === 'radio') {
                    var checkbox_vals = value.split(',');
                    for (var i = 0; i < checkbox_vals.length; i++) {
                        if (input.value.indexOf(checkbox_vals[i]) > -1) {
                            input.checked = true;
                        }
                    }
                } else {
                    if (value !== "undefined") {
                        input.value = value;
                    }
                }
            }
        },
        getInputLabel: function(input){
            var label;
            if(label = this.siblingsIsLabel(input)){
               return label;
            } else if (label = this.CheckParentForLabel(input)) {
               return label;
            } else {
               //console.log("no label nf", input);
               return false;
            }
        },
        /* Get correct input values */
        getInputValue: function(input) {
            var value = false;

            switch (input.type) {
                case 'radio':
                case 'checkbox':
                    if (input.checked) {
                        value = input.value;
                        //console.log("CHECKBOX VAL", value)
                    }
                    break;

                case 'text':
                case 'hidden':
                default:
                    value = input.value;
                    break;

            }

            return value;
        },
        /* Add data-map-form-field attr to input */
        addDataAttr: function(formInput, match) {

            var getAllInputs = document.getElementsByName(formInput.name);
            for (var i = getAllInputs.length - 1; i >= 0; i--) {
                if (!formInput.dataset.mapFormField) {
                    getAllInputs[i].dataset.mapFormField = match;
                }
            };
        },
        /* Optimize FieldMapArray array for fewer lookups */
        removeArrayItem: function(array, item) {
            if (array.indexOf) {
                index = array.indexOf(item);
            } else {
                for (index = array.length - 1; index >= 0; --index) {
                    if (array[index] === item) {
                        break;
                    }
                }
            }
            if (index >= 0) {
                array.splice(index, 1);
            }
            //_inbound.deBugger('forms', 'removed ' + item + " from array");
            //console.log('removed ' + item + " from array");
            return;
        },
        /* Look for siblings that are form labels */
        siblingsIsLabel: function(input) {
            var siblings = this.getSiblings(input);
            var labels = [];
            for (var i = siblings.length - 1; i >= 0; i--) {
                if (siblings[i].nodeName.toLowerCase() === 'label') {
                    labels.push(siblings[i]);
                }
            };
            /* if only 1 label */
            if (labels.length > 0 && labels.length < 2) {
                return labels;
            }

            return false;
        },
        getChildren: function(n, skipMe) {
            var r = [];
            var elem = null;
            for (; n; n = n.nextSibling)
                if (n.nodeType == 1 && n != skipMe)
                    r.push(n);
            return r;
        },
        getSiblings: function(n) {
            return this.getChildren(n.parentNode.firstChild, n);
        },
        /* Check parent elements inside form for labels */
        CheckParentForLabel: function(element) {
            if (element.nodeName === 'FORM') {
                return null;
            }
            do {
                var labels = element.getElementsByTagName("label");
                if (labels.length > 0 && labels.length < 2) {
                    return element.getElementsByTagName("label");
                }

            } while (element = element.parentNode);

            return null;
        },
        /* Validate Common Email addresses */
        mailCheck: function() {
            var email_input = document.querySelector('.inbound-email');
            if (email_input) {
                //
                utils.addListener(email_input, 'blur', this.mailCheck);

                Mailcheck.run({
                    email: document.querySelector('.inbound-email').value,
                    suggested: function(suggestion) {
                        // callback code

                        var suggest = document.querySelector('.email_suggestion');
                        if (suggest) {
                            utils.removeElement(suggest);
                        }
                        var el = document.createElement("span");
                        el.innerHTML = "<span class=\"email_suggestion\">Did youu mean <b><i id='email_correction' style='cursor: pointer;' title=\"click to update\">" + suggestion.full + "</b></i>?</span>";
                        email_input.parentNode.insertBefore(el, email_input.nextSibling);
                        var update = document.getElementById('email_correction');
                        utils.addListener(update, 'click', function() {
                            email_input.value = update.innerHTML;
                            update.parentNode.parentNode.innerHTML = "Fixed!";
                        });
                    },
                    empty: function() {
                        //$(".email_suggestion").html("No Suggestions :(");
                    }
                });
            }
        }

    };
    /* Mailcheck */
    if (typeof Mailcheck === "undefined") {
        var Mailcheck = {
            domainThreshold: 1,
            topLevelThreshold: 3,

            defaultDomains: ["yahoo.com", "google.com", "hotmail.com", "gmail.com", "me.com", "aol.com", "mac.com",
                "live.com", "comcast.net", "googlemail.com", "msn.com", "hotmail.co.uk", "yahoo.co.uk",
                "facebook.com", "verizon.net", "sbcglobal.net", "att.net", "gmx.com", "mail.com", "outlook.com", "icloud.com"
            ],

            defaultTopLevelDomains: ["co.jp", "co.uk", "com", "net", "org", "info", "edu", "gov", "mil", "ca", "de"],

            run: function(opts) {
                opts.domains = opts.domains || Mailcheck.defaultDomains;
                opts.topLevelDomains = opts.topLevelDomains || Mailcheck.defaultTopLevelDomains;
                opts.distanceFunction = opts.distanceFunction || Mailcheck.sift3Distance;

                var defaultCallback = function(result) {
                    return result;
                };
                var suggestedCallback = opts.suggested || defaultCallback;
                var emptyCallback = opts.empty || defaultCallback;

                var result = Mailcheck.suggest(Mailcheck.encodeEmail(opts.email), opts.domains, opts.topLevelDomains, opts.distanceFunction);

                return result ? suggestedCallback(result) : emptyCallback();
            },

            suggest: function(email, domains, topLevelDomains, distanceFunction) {
                email = email.toLowerCase();

                var emailParts = this.splitEmail(email);

                var closestDomain = this.findClosestDomain(emailParts.domain, domains, distanceFunction, this.domainThreshold);

                if (closestDomain) {
                    if (closestDomain != emailParts.domain) {
                        // The email address closely matches one of the supplied domains; return a suggestion
                        return {
                            address: emailParts.address,
                            domain: closestDomain,
                            full: emailParts.address + "@" + closestDomain
                        };
                    }
                } else {
                    // The email address does not closely match one of the supplied domains
                    var closestTopLevelDomain = this.findClosestDomain(emailParts.topLevelDomain, topLevelDomains, distanceFunction, this.topLevelThreshold);
                    if (emailParts.domain && closestTopLevelDomain && closestTopLevelDomain != emailParts.topLevelDomain) {
                        // The email address may have a mispelled top-level domain; return a suggestion
                        var domain = emailParts.domain;
                        closestDomain = domain.substring(0, domain.lastIndexOf(emailParts.topLevelDomain)) + closestTopLevelDomain;
                        return {
                            address: emailParts.address,
                            domain: closestDomain,
                            full: emailParts.address + "@" + closestDomain
                        };
                    }
                }
                /* The email address exactly matches one of the supplied domains, does not closely
                 * match any domain and does not appear to simply have a mispelled top-level domain,
                 * or is an invalid email address; do not return a suggestion.
                 */
                return false;
            },

            findClosestDomain: function(domain, domains, distanceFunction, threshold) {
                threshold = threshold || this.topLevelThreshold;
                var dist;
                var minDist = 99;
                var closestDomain = null;

                if (!domain || !domains) {
                    return false;
                }
                if (!distanceFunction) {
                    distanceFunction = this.sift3Distance;
                }

                for (var i = 0; i < domains.length; i++) {
                    if (domain === domains[i]) {
                        return domain;
                    }
                    dist = distanceFunction(domain, domains[i]);
                    if (dist < minDist) {
                        minDist = dist;
                        closestDomain = domains[i];
                    }
                }

                if (minDist <= threshold && closestDomain !== null) {
                    return closestDomain;
                } else {
                    return false;
                }
            },

            sift3Distance: function(s1, s2) {
                // sift3: http://siderite.blogspot.com/2007/04/super-fast-and-accurate-string-distance.html
                if (s1 === null || s1.length === 0) {
                    if (s2 === null || s2.length === 0) {
                        return 0;
                    } else {
                        return s2.length;
                    }
                }

                if (s2 === null || s2.length === 0) {
                    return s1.length;
                }

                var c = 0;
                var offset1 = 0;
                var offset2 = 0;
                var lcs = 0;
                var maxOffset = 5;

                while ((c + offset1 < s1.length) && (c + offset2 < s2.length)) {
                    if (s1.charAt(c + offset1) == s2.charAt(c + offset2)) {
                        lcs++;
                    } else {
                        offset1 = 0;
                        offset2 = 0;
                        for (var i = 0; i < maxOffset; i++) {
                            if ((c + i < s1.length) && (s1.charAt(c + i) == s2.charAt(c))) {
                                offset1 = i;
                                break;
                            }
                            if ((c + i < s2.length) && (s1.charAt(c) == s2.charAt(c + i))) {
                                offset2 = i;
                                break;
                            }
                        }
                    }
                    c++;
                }
                return (s1.length + s2.length) / 2 - lcs;
            },

            splitEmail: function(email) {
                var parts = email.trim().split("@");

                if (parts.length < 2) {
                    return false;
                }

                for (var i = 0; i < parts.length; i++) {
                    if (parts[i] === "") {
                        return false;
                    }
                }

                var domain = parts.pop();
                var domainParts = domain.split(".");
                var tld = "";

                if (domainParts.length === 0) {
                    // The address does not have a top-level domain
                    return false;
                } else if (domainParts.length == 1) {
                    // The address has only a top-level domain (valid under RFC)
                    tld = domainParts[0];
                } else {
                    // The address has a domain and a top-level domain
                    for (var i = 1; i < domainParts.length; i++) {
                        tld += domainParts[i] + ".";
                    }
                    if (domainParts.length >= 2) {
                        tld = tld.substring(0, tld.length - 1);
                    }
                }

                return {
                    topLevelDomain: tld,
                    domain: domain,
                    address: parts.join("@")
                };
            },

            // Encode the email address to prevent XSS but leave in valid
            // characters, following this official spec:
            // http://en.wikipedia.org/wiki/Email_address#Syntax
            encodeEmail: function(email) {
                var result = encodeURI(email);
                result = result.replace("%20", " ").replace("%25", "%").replace("%5E", "^")
                    .replace("%60", "`").replace("%7B", "{").replace("%7C", "|")
                    .replace("%7D", "}");
                return result;
            }
        };
    } // End Mailcheck


    return _inbound;

})(_inbound || {});

/**
 * # Analytics Events
 *
 * Events are triggered throughout the visitors journey through the site. See more on [Inbound Now][in]
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 *
 * [in]: http://www.inboundnow.com/
 */

// Add object to _inbound
var _inboundEvents = (function(_inbound) {


    _inbound.trigger = function(trigger, data) {
        _inbound.Events[trigger](data);

    };

    /*!
     *
     * Private Function that Fires & Emits Events
     *
     * There are three options for firing events and they trigger in this order:
     *
     * 1. Vanilla JS dispatch event
     * 2. `_inbound.add_action('namespace', callback, priority)`
     * 3. jQuery Trigger `jQuery.trigger('namespace', callback);`
     *
     * The Event `data` can be filtered before events are triggered
     * with filters. Example: filter_ + "namespace"
     *
     * ```js
     * // Filter Form Data before submissionsz
     * _inbound.add_filter( 'filter_form_before_submission', event_filter_data_example, 10);
     *
     * function event_filter_data_example(data) {
     *     var data = data || {};
     *     // Do something with data
     *     return data;
     * }
     * ```
     *
     * @param  {string} eventName Name of the event
     * @param  {object} data      Data passed to external functions/triggers
     * @param  {object} options   Options for configuring events
     * @return {null}           Nothing returned
     */
    function fireEvent(eventName, data, options) {
        var data = data || {};
        options = options || {};

        /*! defaults for JS dispatch event */
        options.bubbles = options.bubbles || true,
        options.cancelable = options.cancelable || true;

        /*! Customize Data via filter_ + "namespace" */
        data = _inbound.apply_filters('filter_' + eventName, data);

        var is_IE_11 = !(window.ActiveXObject) && "ActiveXObject" in window;

        if( typeof CustomEvent === 'function') {

            var TriggerEvent = new CustomEvent(eventName, {
                detail: data,
                bubbles: options.bubbles,
                cancelable: options.cancelable
            });

        } else {
           var TriggerEvent = document.createEvent("Event");
           TriggerEvent.initEvent(eventName, true, true);
        }

        /*! 1. Trigger Pure Javascript Event See: https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Creating_and_triggering_events for example on creating events */
        window.dispatchEvent(TriggerEvent);
        /*!  2. Trigger _inbound action  */
        _inbound.do_action(eventName, data);
        /*!  3. jQuery trigger   */
        triggerJQueryEvent(eventName, data);

        // console.log('Action:' + eventName + " ran on ->", data);

    }

    function triggerJQueryEvent(eventName, data) {
        if (window.jQuery) {
            var data = data || {};
            /*! try catch here */
            jQuery(document).trigger(eventName, data);
        }
    };

    var universalGA,
        classicGA,
        googleTagManager;

    _inbound.Events = {

        /**
         * # Event Usage
         *
         * Events are triggered throughout the visitors path through the site.
         * You can hook into these custom actions and filters much like WordPress Core
         *
         * See below for examples
         */

        /**
         * Adding Custom Actions
         * ------------------
         * You can hook into custom events throughout analytics. See the full list of available [events below](#all-events)
         *
         * `
         * _inbound.add_action( 'action_name', callback, priority );
         * `
         *
         * ```js
         * // example:
         *
         * // Add custom function to `page_visit` event
         * _inbound.add_action( 'page_visit', callback, 10 );
         *
         * // add custom callback to trigger when `page_visit` fires
         * function callback(pageData){
         *   var pageData =  pageData || {};
         *   // run callback on 'page_visit' trigger
         *   alert(pageData.title);
         * }
         * ```
         *
         * @param  {string} action_name Name of the event trigger
         * @param  {function} callback  function to trigger when event happens
         * @param  {int} priority   Order to trigger the event in
         *
         */

        /**
         * Removing Custom Actions
         * ------------------
         * You can hook into custom events throughout analytics. See the full list of available [events below](#all-events)
         *
         * `
         * _inbound.remove_action( 'action_name');
         * `
         *
         * ```js
         * // example:
         *
         * _inbound.remove_action( 'page_visit');
         * // all 'page_visit' actions have been deregistered
         * ```
         *
         * @param  {string} action_name Name of the event trigger
         *
         */

        /**
         * # Event List
         *
         * Events are triggered throughout the visitors journey through the site
         */

        /**
         * Triggers when analyics has finished loading
         */
        analytics_ready: function() {
            var ops = {
                'opt1': true
            };
            var data = {
                'data': 'xyxy'
            };
            fireEvent('analytics_ready', data, ops);
        },
        /**
         *  Triggers when the browser url params are parsed. You can perform custom actions
         *  if specific url params exist.
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'url_parameters' event
         * _inbound.add_action( 'url_parameters', url_parameters_func_example, 10);
         *
         * function url_parameters_func_example(urlParams) {
         *     var urlParams = urlParams || {};
         *      for( var param in urlParams ) {
         *      var key = param;
         *      var value = urlParams[param];
         *      }
         *      // All URL Params
         *      alert(JSON.stringify(urlParams));
         *
         *      // Check if URL parameter `utm_source` exists and matches value
         *      if(urlParams.utm_source === "twitter") {
         *        alert('This person is from twitter!');
         *      }
         * }
         * ```
         */
        url_parameters: function(data) {
            fireEvent('url_parameters', data);
        },
        /**
         *  Triggers when session starts
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'session_start' event
         * _inbound.add_action( 'session_start', session_start_func_example, 10);
         *
         * function session_start_func_example(data) {
         *     var data = data || {};
         *     // session start. Do something for new visitor
         * }
         * ```
         */
        session_start: function() {
            console.log('');
            fireEvent('session_start');
        },
        /**
         * Triggers when visitor session goes idle for more than 30 minutes.
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'session_end' event
         * _inbound.add_action( 'session_end', session_end_func_example, 10);
         *
         * function session_end_func_example(data) {
         *     var data = data || {};
         *     // Do something when session ends
         *     alert("Hey! It's been 30 minutes... where did you go?");
         * }
         * ```
         */
        session_end: function(clockTime) {
            fireEvent('session_end', clockTime);
            console.log('Session End');
        },
        /**
         *  Triggers if active session is detected
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'session_active' event
         * _inbound.add_action( 'session_active', session_active_func_example, 10);
         *
         * function session_active_func_example(data) {
         *     var data = data || {};
         *     // session active
         * }
         * ```
         */
        session_active: function() {
            fireEvent('session_active');
        },
        /**
         * Triggers when visitor session goes idle. Idling occurs after 60 seconds of
         * inactivity or when the visitor switches browser tabs
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'session_idle' event
         * _inbound.add_action( 'session_idle', session_idle_func_example, 10);
         *
         * function session_idle_func_example(data) {
         *     var data = data || {};
         *     // Do something when session idles
         *     alert('Here is a special offer for you!');
         * }
         * ```
         */
        session_idle: function(clockTime) {
            fireEvent('session_idle', clockTime);
        },
        /**
         *  Triggers when session is already active and gets resumed
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'session_resume' event
         * _inbound.add_action( 'session_resume', session_resume_func_example, 10);
         *
         * function session_resume_func_example(data) {
         *     var data = data || {};
         *     // Session exists and is being resumed
         * }
         * ```
         */
        session_resume: function() {
            fireEvent('session_resume');
        },
        /**
         *  Session emitter. Runs every 10 seconds. This is a useful function for
         *  pinging third party services
         *
         * ```js
         * // Usage:
         *
         * // Add session_heartbeat_func_example function to 'session_heartbeat' event
         * _inbound.add_action( 'session_heartbeat', session_heartbeat_func_example, 10);
         *
         * function session_heartbeat_func_example(data) {
         *     var data = data || {};
         *     // Do something with every 10 seconds
         * }
         * ```
         */
        session_heartbeat: function(clockTime) {
            var data = {
                'clock': clockTime,
                'leadData': InboundLeadData
            };
            fireEvent('session_heartbeat', data);
        },
        /**
         * Triggers Every Page View
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'page_visit' event
         * _inbound.add_action( 'page_visit', page_visit_func_example, 10);
         *
         * function session_idle_func_example(pageData) {
         *     var pageData = pageData || {};
         *     if( pageData.view_count > 8 ){
         *       alert('Wow you have been to this page more than 8 times.');
         *     }
         * }
         * ```
         */
        page_visit: function(pageData) {
            fireEvent('page_view', pageData);
        },
        /**
         * Triggers If the visitor has never seen the page before
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'page_first_visit' event
         * _inbound.add_action( 'page_first_visit', page_first_visit_func_example, 10);
         *
         * function page_first_visit_func_example(pageData) {
         *     var pageData = pageData || {};
         *     alert('Welcome to this page! Its the first time you have seen it')
         * }
         * ```
         */
        page_first_visit: function(pageData) {
            fireEvent('page_first_visit');
            _inbound.deBugger('pages', 'First Ever Page View of this Page');
        },
        /**
         * Triggers If the visitor has seen the page before
         *
         * ```js
         * // Usage:
         *
         * // Add function to 'page_revisit' event
         * _inbound.add_action( 'page_revisit', page_revisit_func_example, 10);
         *
         * function page_revisit_func_example(pageData) {
         *     var pageData = pageData || {};
         *     alert('Welcome back to this page!');
         *     // Show visitor special content/offer
         * }
         * ```
         */
        page_revisit: function(pageData) {

            fireEvent('page_revisit', pageData);

            var logger = function() {
                console.log('pageData', pageData);
                console.log('Page Revisit viewed ' + pageData + " times");
            }
            _inbound.deBugger('pages', status, logger);
        },

        /**
         *  `tab_hidden` is triggered when the visitor switches browser tabs
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function tab_hidden_function( data ) {
         *      alert('The Tab is Hidden');
         * };
         *
         *  // Hook the function up the the `tab_hidden` event
         *  _inbound.add_action( 'tab_hidden', tab_hidden_function, 10 );
         * ```
         */
        tab_hidden: function(data) {
            _inbound.deBugger('pages', 'Tab Hidden');
            fireEvent('tab_hidden');
        },
        /**
         *  `tab_visible` is triggered when the visitor switches back to the sites tab
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function tab_visible_function( data ) {
         *      alert('Welcome back to this tab!');
         *      // trigger popup or offer special discount etc.
         * };
         *
         *  // Hook the function up the the `tab_visible` event
         *  _inbound.add_action( 'tab_visible', tab_visible_function, 10 );
         * ```
         */
        tab_visible: function(data) {
            _inbound.deBugger('pages', 'Tab Visible');
            fireEvent('tab_visible');
        },
        /**
         *  `tab_mouseout` is triggered when the visitor mouses out of the browser window.
         *  This is especially useful for exit popups
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function tab_mouseout_function( data ) {
         *      alert("Wait don't Go");
         *      // trigger popup or offer special discount etc.
         * };
         *
         *  // Hook the function up the the `tab_mouseout` event
         *  _inbound.add_action( 'tab_mouseout', tab_mouseout_function, 10 );
         * ```
         */
        tab_mouseout: function(data) {
            _inbound.deBugger('pages', 'Tab Mouseout');
            fireEvent('tab_mouseout');
        },
        /**
         *  `form_input_change` is triggered when tracked form inputs change
         *  You can use this to add additional validation or set conditional triggers
         *
         * ```js
         * // Usage:
         *
         * ```
         */
        form_input_change: function(inputData) {
            var logger = function() {
                console.log(inputData);
                //console.log('Page Revisit viewed ' + pageData + " times");
            }
            _inbound.deBugger('forms', 'inputData change. Data=', logger);
            fireEvent('form_input_change', inputData);
        },
        /**
         *  `form_before_submission` is triggered before the form is submitted to the server.
         *  You can filter the data here or send it to third party services
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function form_before_submission_function( data ) {
         *      var data = data || {};
         *      // filter form data
         * };
         *
         *  // Hook the function up the the `form_before_submission` event
         *  _inbound.add_action( 'form_before_submission', form_before_submission_function, 10 );
         * ```
         */
        form_before_submission: function(formData) {
            fireEvent('form_before_submission', formData);
        },
        /**
         *  `form_after_submission` is triggered after the form is submitted to the server.
         *  You can filter the data here or send it to third party services
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function form_after_submission_function( data ) {
         *      var data = data || {};
         *      // filter form data
         * };
         *
         *  // Hook the function up the the `form_after_submission` event
         *  _inbound.add_action( 'form_after_submission', form_after_submission_function, 10 );
         * ```
         */
        form_after_submission: function(formData) {

            fireEvent('form_after_submission', formData);

        },
        /**
         *  `search_before_caching` is triggered before the search is stored in the user's browser.
         *  If a lead ID is set, the search data will be saved to the server when the next page loads.
         *  You can filter the data here or send it to third party services
         *
         * ```js
         * // Usage:
         *
         * // Adding the callback
         * function search_before_caching_function( data ) {
         *      var data = data || {};
         *      // filter search data
         * };
         *
         *  // Hook the function up the the `search_before_caching` event
         *  _inbound.add_action( 'search_before_caching', search_before_caching_function, 10 );
         * ```
         */
        search_before_caching: function(searchData) {
            fireEvent('search_before_caching', searchData);
        },
        /*! Scrol depth https://github.com/robflaherty/jquery-scrolldepth/blob/master/jquery.scrolldepth.js */

        analyticsError: function(MLHttpRequest, textStatus, errorThrown) {
            var error = new CustomEvent("inbound_analytics_error", {
                detail: {
                    MLHttpRequest: MLHttpRequest,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                }
            });
            window.dispatchEvent(error);
            console.log('Page Save Error');
        }

    };

    return _inbound;

})(_inbound || {});


function inboundFormNoRedirect(){
	/*button == the button that was clicked, form == the form that button belongs to, formRedirectUrl == the link that the form redirects to, if set*/
	
	/*Get the button...*/
	/*If not an iframe*/
	if(window.frames.frameElement == null){
		var button = document.querySelectorAll('button.inbound-button-submit[disabled]')[0];
	}
	/*If it is an iframe*/
	else if(window.frames.frameElement.tagName.toLowerCase() == "iframe"){
		var button = window.frames.frameElement.contentWindow.document.querySelectorAll('button.inbound-button-submit')[0];
	}

    if ( typeof button == 'undefined' ) {
       return;
    }

	var	form = button.form,
		formRedirectUrl = form.querySelectorAll('input[value][type="hidden"][name="inbound_furl"]:not([value=""])');

	/*If the redirect link is not set, or there is a single space in it, the form isn't supposed to redirect. So set the action for void*/
	if(formRedirectUrl.length == 0 || formRedirectUrl[0]['value'] == 'IA=='){
		form.action = 'javascript:void(0)';
	}
}

_inbound.add_action( 'form_before_submission', inboundFormNoRedirect, 10 );

function inboundFormNoRedirectContent(){
	
	/*If not an iframe*/
	if(window.frames.frameElement == null){
		var button = document.querySelectorAll('button.inbound-button-submit[disabled]')[0];
	}
	/*If it is an iframe*/
	else if(window.frames.frameElement.tagName.toLowerCase() == "iframe"){
		var button = window.frames.frameElement.contentWindow.document.querySelectorAll('button.inbound-button-submit')[0];
    }


    if ( typeof button == 'undefined' ) {
        return;
    }

	var	form = button.form,
		formRedirectUrl = form.querySelectorAll('input[value][type="hidden"][name="inbound_furl"]:not([value=""])'),
		btnBackground = jQuery(button).css('background'),
		btnFontColor = jQuery(button).css('color'),
		btnHeight = jQuery(button).css('height'),
		spinner = button.getElementsByClassName('inbound-form-spinner');
		
	if(formRedirectUrl.length == 0 || formRedirectUrl[0]['value'] == 'IA=='){
		jQuery(spinner).remove();
		jQuery(button).prepend('<div id="redir-check"><i class="fa fa-check-square" aria-hidden="true" style="background='+ btnBackground +'; color='+ btnFontColor +'; font-size:calc('+ btnHeight +' * .42);"></i></div>');
	}
}

_inbound.add_action( 'form_after_submission', inboundFormNoRedirectContent, 10 );

/* LocalStorage Component */
var InboundTotalStorage = (function (_inbound){

  var supported, ls, mod = '_inbound';
  if ('localStorage' in window){
    try {
      ls = (typeof window.localStorage === 'undefined') ? undefined : window.localStorage;
      if (typeof ls == 'undefined' || typeof window.JSON == 'undefined'){
        supported = false;
      } else {
        supported = true;
      }
      window.localStorage.setItem(mod, '1');
      window.localStorage.removeItem(mod);
    }
    catch (err){
      supported = false;
    }
  }

  /* Make the methods public */
  _inbound.totalStorage = function(key, value, options){
    return _inbound.totalStorage.impl.init(key, value);
  };

  _inbound.totalStorage.setItem = function(key, value){
    return _inbound.totalStorage.impl.setItem(key, value);
  };

  _inbound.totalStorage.getItem = function(key){
    return _inbound.totalStorage.impl.getItem(key);
  };

  _inbound.totalStorage.getAll = function(){
    return _inbound.totalStorage.impl.getAll();
  };

  _inbound.totalStorage.deleteItem = function(key){
    return _inbound.totalStorage.impl.deleteItem(key);
  };


  _inbound.totalStorage.impl = {

    init: function(key, value){
      if (typeof value != 'undefined') {
        return this.setItem(key, value);
      } else {
        return this.getItem(key);
      }
    },

    setItem: function(key, value){
      if (!supported){
        try {
          _inbound.Utils.createCookie(key, value);
          return value;
        } catch(e){
          console.log('Local Storage not supported by this browser. Install the cookie plugin on your site to take advantage of the same functionality. You can get it at https://github.com/carhartl/jquery-cookie');
        }
      }
      var saver = JSON.stringify(value);
      ls.setItem(key, saver);
      return this.parseResult(saver);
    },
    getItem: function(key){
      if (!supported){
        try {
          return this.parseResult(_inbound.Utils.readCookie(key));
        } catch(e){
          return null;
        }
      }
      var item = ls.getItem(key);
      return this.parseResult(item);
    },
    deleteItem: function(key){
      if (!supported){
        try {
          _inbound.Utils.eraseCookie(key, null);
          return true;
        } catch(e){
          return false;
        }
      }
      ls.removeItem(key);
      return true;
    },
    getAll: function(){
      var items = [];
      if (!supported){
        try {
          var pairs = document.cookie.split(";");
          for (var i = 0; i<pairs.length; i++){
            var pair = pairs[i].split('=');
            var key = pair[0];
            items.push({key:key, value:this.parseResult(_inbound.Utils.readCookie(key))});
          }
        } catch(e){
          return null;
        }
      } else {
        for (var j in ls){
          if (j.length){
            items.push({key:j, value:this.parseResult(ls.getItem(j))});
          }
        }
      }
      return items;
    },
    parseResult: function(res){
      var ret;
      try {
        ret = JSON.parse(res);
        if (typeof ret == 'undefined'){
          ret = res;
        }
        if (ret == 'true'){
          ret = true;
        }
        if (ret == 'false'){
          ret = false;
        }
        if (parseFloat(ret) == ret && typeof ret != "object"){
          ret = parseFloat(ret);
        }
      } catch(e){
        ret = res;
      }
      return ret;
    }
  };
})(_inbound || {});
/**
 * Leads API functions
 * @param  Object _inbound - Main JS object
 * @return Object - include event triggers
 */
var _inboundLeadsAPI = (function(_inbound) {
    var httpRequest;
    _inbound.LeadsAPI = {
        init: function() {

            var utils = _inbound.Utils,
                wp_lead_uid = utils.readCookie("wp_lead_uid"),
                wp_lead_id = utils.readCookie("wp_lead_id"),
                expire_check = utils.readCookie("lead_session_expire"); // check for session

            if (!expire_check) {
                _inbound.deBugger('leads', 'expired vistor. Run Processes');
                //var data_to_lookup = global-localized-vars;
                if (wp_lead_id) {
                    /* Get InboundLeadData */
                    _inbound.LeadsAPI.getAllLeadData();
                }
            }
        },
        setGlobalLeadData: function(data) {
            InboundLeadData = data;
        },
        getAllLeadData: function(expire_check) {
            var wp_lead_id = _inbound.Utils.readCookie("wp_lead_id"),
                leadData = _inbound.totalStorage('inbound_lead_data'),
                leadDataExpire = _inbound.Utils.readCookie("lead_data_expire");
            data = {
                action: 'inbound_get_all_lead_data',
                wp_lead_id: wp_lead_id
            },
            success = function(returnData) {
                var leadData = JSON.parse(returnData);
                _inbound.LeadsAPI.setGlobalLeadData(leadData);
                _inbound.totalStorage('inbound_lead_data', leadData); // store lead data

                /* Set 3 day timeout for checking DB for new lead data for Lead_Global var */
                var d = new Date();
                d.setTime(d.getTime() + 30 * 60 * 1000);
                var expire = _inbound.Utils.addDays(d, 3);
                _inbound.Utils.createCookie("lead_data_expire", true, expire);

            };

            if (!leadData) {
                // Get New Lead Data from DB
                _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);

            } else {
                // set global lead var with localstorage data
                _inbound.LeadsAPI.setGlobalLeadData(leadData);
                _inbound.deBugger('lead', 'Set Global Lead Data from Localstorage');

                if (!leadDataExpire) {
                    _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);
                    //console.log('Set Global Lead Data from Localstorage');
                     _inbound.deBugger('lead', 'localized data old. Pull new from DB');
                    //console.log('localized data old. Pull new from DB');
                }
            }

        },
        getLeadLists: function() {
            var wp_lead_id = _inbound.Utils.readCookie("wp_lead_id");
            var data = {
                action: 'wpl_check_lists',
                wp_lead_id: wp_lead_id
            };
            var success = function(user_id) {
                _inbound.Utils.createCookie("lead_session_list_check", true, {
                    path: '/',
                    expires: 1
                });
                _inbound.deBugger('lead', "Lists checked");
                //console.log("Lists checked");
            };
            //_inbound.Utils.doAjax(data, success);
            _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);
        }
    };

    return _inbound;

})(_inbound || {});
/**
 * # Page View Tracking
 *
 * Page view tracking
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */
/* Launches view tracking */
var _inboundPageTracking = (function(_inbound) {

    var started = false,
        stopped = false,
        turnedOff = false,
        clockTime = parseInt(_inbound.Utils.readCookie("lead_session"), 10) || 0,
        inactiveClockTime = 0,
        startTime = new Date(),
        clockTimer = null,
        inactiveClockTimer = null,
        idleTimer = null,
        reportInterval,
        idleTimeout,
        utils = _inbound.Utils,
        timeNow = _inbound.Utils.GetDate(),
        lsType = 'page_views',
        Pages = _inbound.totalStorage(lsType) || {},
        /*!
          Todo: Use UTC offset
          var x = new Date();
          var currentTime = x.getTimezoneOffset() / 60;
          console.log(currentTime) // gets UTC offset
        */
        id = inbound_settings.post_id || window.location.pathname,
        analyticsTimeout = _inbound.Settings.timeout || 10000;

    _inbound.PageTracking = {

        init: function(options) {

            if(lsType !== 'page_views') {
                return false; // in admin
            }

            this.CheckTimeOut();
            // Set up options and defaults
            options = options || {};
            reportInterval = parseInt(options.reportInterval, 10) || 10;
            idleTimeout = parseInt(options.idleTimeout, 10) || 3;

            // Basic activity event listeners
            utils.addListener(document, 'keydown', utils.throttle(_inbound.PageTracking.pingSession, 1000));
            utils.addListener(document, 'click', utils.throttle(_inbound.PageTracking.pingSession, 1000));
            utils.addListener(window, 'mousemove', utils.throttle(_inbound.PageTracking.pingSession, 1000));
            //utils.addListener(window, 'scroll',  utils.throttle(_inbound.PageTracking.pingSession, 1000));

            // Page visibility listeners
            _inbound.PageTracking.checkVisibility();

            /* Start Session on page load */
            this.startSession();

        },

        setIdle: function(reason) {
            var reason = reason || "No Movement",
                msg = 'Session IDLE. Activity Timeout due to ' + reason;

            _inbound.deBugger('pages', msg);

            clearTimeout(_inbound.PageTracking.idleTimer);
            _inbound.PageTracking.stopClock();
            _inbound.trigger('session_idle');

        },

        checkVisibility: function() {
            var hidden, visibilityState, visibilityChange;

            if (typeof document.hidden !== "undefined") {
                hidden = "hidden", visibilityChange = "visibilitychange", visibilityState = "visibilityState";
            } else if (typeof document.mozHidden !== "undefined") {
                hidden = "mozHidden", visibilityChange = "mozvisibilitychange", visibilityState = "mozVisibilityState";
            } else if (typeof document.msHidden !== "undefined") {
                hidden = "msHidden", visibilityChange = "msvisibilitychange", visibilityState = "msVisibilityState";
            } else if (typeof document.webkitHidden !== "undefined") {
                hidden = "webkitHidden", visibilityChange = "webkitvisibilitychange", visibilityState = "webkitVisibilityState";
            }

            var document_hidden = document[hidden];

            _inbound.Utils.addListener(document, visibilityChange, function(e) {
                /*! Listen for visibility changes */
                if (document_hidden != document[hidden]) {
                    if (document[hidden]) {
                        // Document hidden
                        _inbound.trigger('tab_hidden');
                        _inbound.PageTracking.setIdle('browser tab switch');
                    } else {
                        // Document shown
                        _inbound.trigger('tab_visible');
                        _inbound.PageTracking.pingSession();
                    }

                    document_hidden = document[hidden];
                }
            });
        },
        clock: function() {
            clockTime += 1;
            var niceTime = clockTime / 60;
            var msg = 'Total time spent on Page in this Session: ' + niceTime.toFixed(2) + " min";
            _inbound.deBugger('pages', msg);
            if (clockTime > 0 && (clockTime % reportInterval === 0)) {

                var d = new Date();
                d.setTime(d.getTime() + 30 * 60 * 1000);
                utils.createCookie("lead_session", clockTime, d); // Set cookie on page load

                /*! every 10 seconds run this */
                //console.log('Session Heartbeat every ' + reportInterval + ' secs');
                _inbound.trigger('session_heartbeat', clockTime);

            }

        },
        inactiveClock: function() {
            inactiveClockTime += 1;
            var TimeUntilTimeOut = (1800 - inactiveClockTime) / 60;
            var msg = 'Time until Session Timeout: ' + TimeUntilTimeOut.toFixed(2) + " min";
            _inbound.deBugger('pages', msg);
            //console.log('Time until Session Timeout: ', TimeUntilTimeOut.toFixed(2) + " min");
            /* Session timeout after 30min */
            if (inactiveClockTime > 1800) {

                // sendEvent(clockTime);
                /*! End session after 30min timeout */
                _inbound.trigger('session_end', InboundLeadData);
                _inbound.Utils.eraseCookie("lead_session");
                /* todo maybe? remove session Cookie */
                inactiveClockTime = 0;
                clearTimeout(inactiveClockTimer);
            }


        },
        stopClock: function() {
            stopped = true;
            clearTimeout(clockTimer);
            clearTimeout(inactiveClockTimer);
            inactiveClockTimer = setInterval(_inbound.PageTracking.inactiveClock, 1000);
        },

        restartClock: function() {
            stopped = false;


            _inbound.trigger('session_resume');
            _inbound.deBugger('pages', 'Activity resumed. Session Active');
            /* todo add session_resume */
            clearTimeout(clockTimer);
            inactiveClockTime = 0;
            clearTimeout(inactiveClockTimer);
            clockTimer = setInterval(_inbound.PageTracking.clock, 1000);
        },

        turnOff: function() {
            _inbound.PageTracking.setIdle();
            turnedOff = true;
        },

        turnOn: function() {
            turnedOff = false;
        },
        /* This start only runs once */
        startSession: function() {
            /* todo add session Cookie */
            // Calculate seconds from start to first interaction
            var currentTime = new Date();
            var diff = currentTime - startTime;


            started = true; // Set global

            // Send User Timing Event
            /* Todo session start here */

            // Start clock
            clockTimer = setInterval(_inbound.PageTracking.clock, 1000);
            //utils.eraseCookie("lead_session");
            var session = utils.readCookie("lead_session");

            if (!session) {
                _inbound.trigger('session_start'); // trigger 'inbound_analytics_session_start'
                var d = new Date();
                d.setTime(d.getTime() + 30 * 60 * 1000);
                _inbound.Utils.createCookie("lead_session", 1, d); // Set cookie on page load
            } else {
                _inbound.trigger('session_active');
                //console.log("count of secs " + session);
                //_inbound.trigger('session_active'); // trigger 'inbound_analytics_session_active'
            }

            this.pingSession();


        },
        resetInactiveFunc: function() {
            inactiveClockTime = 0;
            clearTimeout(inactiveClockTimer);
        },
        /* Ping Session to keep active */
        pingSession: function(e) {


            if (turnedOff) {
                return;
            }

            if (!started) {
                _inbound.PageTracking.startSession();
            }

            if (stopped) {
                _inbound.PageTracking.restartClock();
            }

            clearTimeout(idleTimer);

            idleTimer = setTimeout(_inbound.PageTracking.setIdle, idleTimeout * 1000 + 100);

            if (typeof(e) != "undefined") {
                if (e.type === "mousemove") {
                    _inbound.PageTracking.mouseEvents(e);
                }
            }

        },
        mouseEvents: function(e) {

            if (e.pageY <= 5) {
                _inbound.trigger('tab_mouseout');
            }

        },
        /**
         * Returns the pages viewed by the site visitor
         *
         * ```js
         *  var pageViews = _inbound.PageTracking.getPageViews();
         *  // returns page view object
         * ```
         *
         * @return {object} page view object with page ID as key and timestamp
         */
        getPageViews: function() {
            var local_store = _inbound.Utils.checkLocalStorage();
            if (local_store) {
                var page_views = localStorage.getItem(lsType),
                    local_object = JSON.parse(page_views);
                if (typeof local_object == 'object' && local_object) {
                    //this.triggerPageView();
                }
                return local_object;
            }
        },
        isRevisit: function(Pages) {
            var revisitCheck = false;
            var Pages = Pages || {};
            var pageSeen = Pages[id];
            if (typeof(pageSeen) != "undefined" && pageSeen !== null) {
                revisitCheck = true;
            }
            return revisitCheck;
        },
        triggerPageView: function(pageRevisit) {

            var pageData = {
                title: document.title,
                url: document.location.href,
                path: document.location.pathname,
                count: 1 // default
            };

            if (pageRevisit) {
                /* Page Revisit Trigger */
                Pages[id].push(timeNow);
                pageData.count = Pages[id].length;
                _inbound.trigger('page_revisit', pageData);

            } else {
                /* Page First Seen Trigger */
                Pages[id] = [];
                Pages[id].push(timeNow);
                _inbound.trigger('page_first_visit', pageData);
            }

            _inbound.trigger('page_visit', pageData);

            _inbound.totalStorage(lsType, Pages);

            this.storePageView();

        },
        CheckTimeOut: function() {

            var pageRevisit = this.isRevisit(Pages),
                status,
                timeout;

            /* Default */
            if (pageRevisit) {

                var prev = Pages[id].length - 1,
                    lastView = Pages[id][prev],
                    timeDiff = Math.abs(new Date(lastView).getTime() - new Date(timeNow).getTime());

                timeout = timeDiff > analyticsTimeout;

                if (timeout) {
                    status = 'Timeout Happened. Page view fired';
                    this.triggerPageView(pageRevisit);
                } else {
                    time_left = Math.abs((analyticsTimeout - timeDiff)) * 0.001;
                    status = analyticsTimeout / 1000 + ' sec timeout not done: ' + time_left + " seconds left";
                }

            } else {
                /*! Page never seen before save view */
                this.triggerPageView(pageRevisit);
            }

            _inbound.deBugger('pages', status);
        },
        storePageView: function() {

            /* ignore if page tracking off and page is not a landing page */
			if ( inbound_settings.page_tracking == 'off' && inbound_settings.post_type != 'landing-page' ) {
				return;
			}

            /* Let's try and fire this last - also defines what constitutes a bounce -  */
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(function(){
                    var leadID = ( _inbound.Utils.readCookie('wp_lead_id') ) ? _inbound.Utils.readCookie('wp_lead_id') : '';
                    var lead_uid = ( _inbound.Utils.readCookie('wp_lead_uid') ) ? _inbound.Utils.readCookie('wp_lead_uid') : '';
                    var ctas_loaded = _inbound.totalStorage('wp_cta_loaded');
                    var ctas_impressions = _inbound.totalStorage('wp_cta_impressions');

                    /* now reset impressions */
                    _inbound.totalStorage('wp_cta_impressions' , {} );

                    var data = {
                        action: 'inbound_track_lead',
                        wp_lead_uid: lead_uid,
                        wp_lead_id: leadID,
                        page_id: inbound_settings.post_id,
                        variation_id: inbound_settings.variation_id,
                        post_type: inbound_settings.post_type,
                        current_url: window.location.href,
                        page_views: JSON.stringify(_inbound.PageTracking.getPageViews()),
                        cta_impressions : JSON.stringify(ctas_impressions),
                        cta_history : JSON.stringify(ctas_loaded),
                        json: '0'
                    };

                    var firePageCallback = function(leadID) {
                        //_inbound.Events.page_view_saved(leadID);
                    };
                    //_inbound.Utils.doAjax(data, firePageCallback);

                    _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, firePageCallback);

                } , 200 );


            });


        }
        /*! GA functions
        function log_event(category, action, label) {
          _gaq.push(['_trackEvent', category, action, label]);
        }

        function log_click(category, link) {
          log_event(category, 'Click', $(link).text());
        }
        */
    };

    return _inbound;

})(_inbound || {});
/**
 * # Start
 *
 * Runs init functions
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2 
 */


/* Initialize _inbound */
 _inbound.init();

/* Set Global Lead Data */
InboundLeadData = _inbound.totalStorage('inbound_lead_data') || null;

