/**
 * # Page View Tracking
 *
 * Page view tracking
 *
 * @author David Wells <david@inboundnow.com>
 * @author Hudson Atwell <hudson@inboundnow.com>
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