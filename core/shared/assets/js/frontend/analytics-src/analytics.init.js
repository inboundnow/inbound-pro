/**
 * # _inbound
 *
 * This main the _inbound class
 *
 * @author David Wells <david@inboundnow.com>
 * @contributors Hudson Atwell <hudson@inboundnow.com>
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