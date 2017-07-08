/**
 * # Analytics Events
 *
 * Events are triggered throughout the visitors journey through the site. See more on [Inbound Now][in]
 *
 * @author David Wells <david@inboundnow.com>
 * @author Hudson Atwell <hudson@inboundnow.com>
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
