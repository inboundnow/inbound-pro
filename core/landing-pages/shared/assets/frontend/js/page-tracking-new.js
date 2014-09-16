/**
 * Lead Tracking JS
 * http://www.inboundnow.com
 */
var inbound_data = inbound_data || {};
// Ensure global _gaq Google Analytics queue has been initialized.
var _gaq = _gaq || [];
var InboundAnalytics = (function () {

   var debugMode = false;

   var _privateMethod = function () {
      console.log('Run private');
   };

   var App = {
     init: function () {
          InboundAnalytics.Utils.init();
          InboundAnalytics.PageTracking.StorePageView();
          InboundAnalytics.Events.loadEvents();
     },
     /* Debugger Function toggled by var debugMode */
     debug: function(msg,callback){
         //if app not in debug mode, exit immediately
         if(!debugMode || !console){return};
         var msg = msg || false;
         //console.log the message
         if(msg && (typeof msg === 'string')){console.log(msg)};

         //execute the callback if one was passed-in
         if(callback && (callback instanceof Function)){
           callback();
         };
     }
   };

   return App;

 })();


/**
 * Utility functions
 * @param  Object InboundAnalytics - Main JS object
 * @return Object - include util functions
 */
var InboundAnalyticsUtils = (function (InboundAnalytics) {

    InboundAnalytics.Utils =  {
      init: function() {
          this.polyFills();
          this.setUrlParams();
          this.SetUID();
          this.getReferer();

      },
      polyFills: function() {
           /* Console.log fix for old browsers */
           if (!window.console) { window.console = {}; }
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
           /* Event trigger polyfill for IE9 and 10 */
           (function () {
             function CustomEvent ( event, params ) {
               params = params || { bubbles: false, cancelable: false, detail: undefined };
               var evt = document.createEvent( 'CustomEvent' );
               evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
               return evt;
              };

             CustomEvent.prototype = window.Event.prototype;

             window.CustomEvent = CustomEvent;
           })();
      },
      // Create cookie
      createCookie: function(name, value, days, custom_time) {
          var expires = "";
          if (days) {
              var date = new Date();
              date.setTime(date.getTime()+(days*24*60*60*1000));
              expires = "; expires="+date.toGMTString();
          }
          if(custom_time){
             expires = "; expires="+days.toGMTString();
          }
          document.cookie = name+"="+value+expires+"; path=/";
      },
      // Read cookie
      readCookie: function(name) {
          var nameEQ = name + "=";
          var ca = document.cookie.split(';');
          for(var i=0;i < ca.length;i++) {
              var c = ca[i];
              while (c.charAt(0) === ' ') {
                  c = c.substring(1,c.length);
              }
              if (c.indexOf(nameEQ) === 0) {
                  return c.substring(nameEQ.length,c.length);
              }
          }
          return null;
      },
      // Erase cookie
      eraseCookie: function(name) {
          createCookie(name,"",-1);
      },
      getAllCookies: function(){
              var cookies = {};
              if (document.cookie && document.cookie != '') {
                  var split = document.cookie.split(';');
                  for (var i = 0; i < split.length; i++) {
                      var name_value = split[i].split("=");
                      name_value[0] = name_value[0].replace(/^ /, '');
                      cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
                  }
              }
              jQuery.totalStorage('inbound_cookies', cookies); // store cookie data
              return cookies;
      },
      /* Grab URL params and save */
      setUrlParams: function() {
          var urlParams = {},
          local_store = InboundAnalytics.Utils.checkLocalStorage();

            (function () {
              var e,
                d = function (s) { return decodeURIComponent(s).replace(/\+/g, " "); },
                q = window.location.search.substring(1),
                r = /([^&=]+)=?([^&]*)/g;

              while (e = r.exec(q)) {
                if (e[1].indexOf("[") == "-1")
                  urlParams[d(e[1])] = d(e[2]);
                else {
                  var b1 = e[1].indexOf("["),
                    aN = e[1].slice(b1+1, e[1].indexOf("]", b1)),
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

            if (JSON) {
                for (var k in urlParams) {
                  if (typeof urlParams[k] == "object") {
                    for (var k2 in urlParams[k])
                    this.createCookie(k2, urlParams[k][k2], 30);
                  } else {
                    this.createCookie(k, urlParams[k], 30);
                  }
                 }
            }

            if(local_store){
              var pastParams =  jQuery.totalStorage('inbound_url_params');
              var params = this.mergeObjs(pastParams, urlParams);
              jQuery.totalStorage('inbound_url_params', params); // store cookie data
            }
      },
      getUrlParams: function(){
          var local_store = this.checkLocalStorage(),
          get_params = {};
          if(local_store){
            var get_params =  jQuery.totalStorage('inbound_url_params');
          }
          return get_params;
      },
      // Check local storage
      // provate browsing safari fix https://github.com/marcuswestin/store.js/issues/42#issuecomment-25274685
      checkLocalStorage: function() {
        if ('localStorage' in window) {
            try {
              ls = (typeof window.localStorage === 'undefined') ? undefined : window.localStorage;
              if (typeof ls == 'undefined' || typeof window.JSON == 'undefined'){
                supported = false;
              } else {
                supported = true;
              }

            }
            catch (err){
              supported = false;
            }
        }
        return supported;
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
      /* Add days to datetime */
      addDays: function(myDate,days) {
        return new Date(myDate.getTime() + days*24*60*60*1000);
      },
      GetDate: function(){
        var time_now = new Date(),
        day = time_now.getDate() + 1;
        year = time_now.getFullYear(),
        hour = time_now.getHours(),
        minutes = time_now.getMinutes(),
        seconds = time_now.getSeconds(),
        month = time_now.getMonth() + 1;
        if (month < 10) { month = '0' + month; }
        InboundAnalytics.debug('Current Date:',function(){
            console.log(year + '/' + month + "/" + day + " " + hour + ":" + minutes + ":" + seconds);
        });
        var datetime = year + '/' + month + "/" + day + " " + hour + ":" + minutes + ":" + seconds;
        return datetime;
      },
      /* Set Expiration Date of Session Logging */
      SetSessionTimeout: function(){
          var session_check = this.readCookie("lead_session_expire");
          //console.log(session_check);
          if(session_check === null){
            InboundAnalytics.Events.sessionStart(); // trigger 'inbound_analytics_session_start'
          } else {
            InboundAnalytics.Events.sessionActive(); // trigger 'inbound_analytics_session_active'
          }
          var d = new Date();
          d.setTime(d.getTime() + 30*60*1000);

          this.createCookie("lead_session_expire", true, d, true); // Set cookie on page loads
          var lead_data_expiration = this.readCookie("lead_data_expiration");
          if (lead_data_expiration === null){
            /* Set 3 day timeout for checking DB for new lead data for Lead_Global var */
            var ex = this.addDays(d, 3);
            this.createCookie("lead_data_expiration", ex, ex, true);
          }

      },
      getReferer: function(){
        //console.log(expire_time);
        var d = new Date();
        d.setTime(d.getTime() + 30*60*1000);
        var referrer_cookie = InboundAnalytics.Utils.readCookie("wp_lead_referral_site");
        if (typeof (referrer_cookie) === "undefined" || referrer_cookie === null || referrer_cookie === "") {
          var referrer = document.referrer || "NA";
          this.createCookie("wp_lead_referral_site", referrer, d, true); // Set cookie on page loads
        }
      },
      CreateUID: function(length) {
          var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split(''),
          str = '';
          if (! length) {
              length = Math.floor(Math.random() * chars.length);
          }
          for (var i = 0; i < length; i++) {
              str += chars[Math.floor(Math.random() * chars.length)];
          }
          return str;
      },
      SetUID:  function () {
       /* Set Lead UID */

       if(this.readCookie("wp_lead_uid") === null) {
          var wp_lead_uid =  this.CreateUID(35);
          this.createCookie("wp_lead_uid", wp_lead_uid );
          InboundAnalytics.debug('Set UID');
       }
      },
      /* Count number of session visits */
      countProperties: function (obj) {
          var count = 0;
          for(var prop in obj) {
              if(obj.hasOwnProperty(prop))
                  ++count;
          }
          return count;
      },
      mergeObjs:  function(obj1,obj2){
            var obj3 = {};
            for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
            for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
            return obj3;
      },
      trim: function(s) {
          s = s.replace(/(^\s*)|(\s*$)/gi,"");
          s = s.replace(/[ ]{2,}/gi," ");
          s = s.replace(/\n /,"\n"); return s;
      },
      doAjax: function(data, responseHandler, method, async){
      // Set the variables
      var url = wplft.admin_url || "",
      method = method || "POST",
      async = async || true,
      data = data || null,
      action = data.action;

      InboundAnalytics.debug('Ajax Processed:',function(){
           console.log('ran ajax action: ' + action);
      });

      jQuery.ajax({
          type: method,
          url: wplft.admin_url,
          data: data,
          success: responseHandler,
          error: function(MLHttpRequest, textStatus, errorThrown){
            console.log(MLHttpRequest+' '+errorThrown+' '+textStatus);
            InboundAnalytics.Events.analyticsError(MLHttpRequest, textStatus, errorThrown);
          }

        });
    },
    contentLoaded: function(win, fn) {

      var done = false, top = true,

      doc = win.document, root = doc.documentElement,

      add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
      rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
      pre = doc.addEventListener ? '' : 'on',

      init = function(e) {
        if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
        (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
        if (!done && (done = true)) fn.call(win, e.type || e);
      },

      poll = function() {
        try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
        init('poll');
      };

      if (doc.readyState == 'complete') fn.call(win, 'lazy');
      else {
        if (doc.createEventObject && root.doScroll) {
          try { top = !win.frameElement; } catch(e) { }
          if (top) poll();
        }
        doc[add](pre + 'DOMContentLoaded', init, false);
        doc[add](pre + 'readystatechange', init, false);
        win[add](pre + 'load', init, false);
      }

    },
    /* Cross-browser event listening  */
    addListener: function(obj, eventName, listener) {
      if(obj.addEventListener) {
        obj.addEventListener(eventName, listener, false);
      } else if (obj.attachEvent) {
        obj.attachEvent("on" + eventName, listener);
      } else {
        obj['on' + eventName] = listener;
      }
    }

  };

  return InboundAnalytics;

})(InboundAnalytics || {});

/* Fork of jquery.total-storage.js */
var InboundTotalStorage = (function (InboundAnalytics){

  /* Variables I'll need throghout */

  var supported, ls, mod = 'inboundAnalytics';
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
  InboundAnalytics.totalStorage = function(key, value, options){
    return InboundAnalytics.totalStorage.impl.init(key, value);
  };

  InboundAnalytics.totalStorage.setItem = function(key, value){
    return InboundAnalytics.totalStorage.impl.setItem(key, value);
  };

  InboundAnalytics.totalStorage.getItem = function(key){
    return InboundAnalytics.totalStorage.impl.getItem(key);
  };

  InboundAnalytics.totalStorage.getAll = function(){
    return InboundAnalytics.totalStorage.impl.getAll();
  };

  InboundAnalytics.totalStorage.deleteItem = function(key){
    return InboundAnalytics.totalStorage.impl.deleteItem(key);
  };

  /* Object to hold all methods: public and private */

  InboundAnalytics.totalStorage.impl = {

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
          InboundAnalytics.Utils.createCookie((key, value);
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
          return this.parseResult(InboundAnalytics.Utils.readCookie((key));
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
          InboundAnalytics.Utils.eraseCookie((key, null);
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
            items.push({key:key, value:this.parseResult(InboundAnalytics.Utils.readCookie((key))});
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
})(InboundAnalytics || {});

var InboundAnalyticsPageTracking = (function (InboundAnalytics) {

    InboundAnalytics.PageTracking = {

    getPageViews: function () {
        var local_store = InboundAnalytics.Utils.checkLocalStorage();
        if(local_store){
          var page_views = localStorage.getItem("page_views"),
          local_object = JSON.parse(page_views);
          if (typeof local_object =='object' && local_object) {
            this.StorePageView();
          }
          return local_object;
        }
    },
    StorePageView: function() {
          var timeout = this.CheckTimeOut();
          var pageviewObj = jQuery.totalStorage('page_views');
          if(pageviewObj === null) {
            pageviewObj = {};
          }
          var current_page_id = wplft.post_id;
          var datetime = InboundAnalytics.Utils.GetDate();

          if (timeout) {
              // If pageviewObj exists, do this
              var page_seen = pageviewObj[current_page_id];

              if(typeof(page_seen) != "undefined" && page_seen !== null) {
                  pageviewObj[current_page_id].push(datetime);
                  /* Page Revisit Trigger */
                  var page_seen_count = pageviewObj[current_page_id].length;
                  InboundAnalytics.Events.pageRevisit(page_seen_count);

              } else {
                  pageviewObj[current_page_id] = [];
                  pageviewObj[current_page_id].push(datetime);
                  /* Page First Seen Trigger */
                  var page_seen_count = 1;
                  InboundAnalytics.Events.pageFirstView(page_seen_count);
              }

              jQuery.totalStorage('page_views', pageviewObj);

          }
    },
    CheckTimeOut: function() {
        var PageViews = jQuery.totalStorage('page_views');
        if(PageViews === null) {
        var PageViews = {};
        }
        var page_id = wplft.post_id,
        pageviewTimeout = true, /* Default */
        page_seen = PageViews[page_id];
        if(typeof(page_seen) != "undefined" && page_seen !== null) {

            var time_now = InboundAnalytics.Utils.GetDate(),
            vc = PageViews[page_id].length - 1,
            last_view = PageViews[page_id][vc],
            last_view_ms = new Date(last_view).getTime(),
            time_now_ms = new Date(time_now).getTime(),
            timeout_ms = last_view_ms + 30*1000,
            time_check = Math.abs(last_view_ms - time_now_ms),
            wait_time = 30000;

            InboundAnalytics.debug('Timeout Checks =',function(){
                 console.log('Current Time is: ' + time_now);
                 console.log('Last view is: ' + last_view);
                 console.log("Last view milliseconds " + last_view_ms);
                 console.log("time now milliseconds " + time_now_ms);
                 console.log("Wait Check: " + wait_time);
                 console.log("TIME CHECK: " + time_check);
            });

            //var wait_time = Math.abs(last_view_ms - timeout_ms) // output timeout time 30sec;

            if (time_check < wait_time){
              time_left =  Math.abs((wait_time - time_check)) * .001;
              pageviewTimeout = false;
              var status = '30 sec timeout not done: ' + time_left + " seconds left";
            } else {
              var status = 'Timeout Happened. Page view fired';
              this.firePageView();
              pageviewTimeout = true;
              InboundAnalytics.Events.analyticsTriggered();
            }

            //InboundAnalytics.debug('',function(){
                 console.log(status);
            //});
       } else {
          /* Page never seen before */
          this.firePageView();
       }

       return pageviewTimeout;

    },
    firePageView: function() {
      var lead_id = InboundAnalytics.Utils.readCookie('wp_lead_id'),
      lead_uid = InboundAnalytics.Utils.readCookie('wp_lead_uid');

      if (typeof (lead_id) != "undefined" && lead_id != null && lead_id != "") {

        InboundAnalytics.debug('Run page view ajax');

        var data = {
                action: 'wpl_track_user',
                wp_lead_uid: lead_uid,
                wp_lead_id: lead_id,
                page_id: wplft.post_id,
                current_url: window.location.href,
                json: '0'
              };
        var firePageCallback = function(user_id){
                InboundAnalytics.Events.analyticsSaved();
        };
        InboundAnalytics.Utils.doAjax(data, firePageCallback);
      }
    },
    tabSwitch: function() {
        /* test out simplier script
        function onBlur() {
          document.body.className = 'blurred';
        };
        function onFocus(){
          document.body.className = 'focused';
        };

        if (false) { // check for Internet Explorer
          document.onfocusin = onFocus;
          document.onfocusout = onBlur;
        } else {
          window.onfocus = onFocus;
          window.onblur = onBlur;
        }
        */

       var hidden, visibilityState, visibilityChange;

        if (typeof document.hidden !== "undefined") {
          hidden = "hidden", visibilityChange = "visibilitychange", visibilityState = "visibilityState";
        } else if (typeof document.mozHidden !== "undefined") {
          hidden = "mozHidden", visibilityChange = "mozvisibilitychange", visibilityState = "mozVisibilityState";
        } else if (typeof document.msHidden !== "undefined") {
          hidden = "msHidden", visibilityChange = "msvisibilitychange", visibilityState = "msVisibilityState";
        } else if (typeof document.webkitHidden !== "undefined") {
          hidden = "webkitHidden", visibilityChange = "webkitvisibilitychange", visibilityState = "webkitVisibilityState";
        } // if

        var document_hidden = document[hidden];

        document.addEventListener(visibilityChange, function() {
          if(document_hidden != document[hidden]) {
            if(document[hidden]) {
              // Document hidden
              console.log('hidden');
              InboundAnalytics.Events.browserTabHidden();
            } else {
              // Document shown
              console.log('shown');
              InboundAnalytics.Events.browserTabVisible();
            } // if

            document_hidden = document[hidden];
          } // if
        });
    }
  }

    return InboundAnalytics;

})(InboundAnalytics || {});


/**
 * Leads API functions
 * @param  Object InboundAnalytics - Main JS object
 * @return Object - include event triggers
 */
var InboundAnalyticsLeadsAPI = (function (InboundAnalytics) {
    var httpRequest;
    InboundAnalytics.LeadsAPI =  {
      init: function() {

      },
      storeLeadData: function(){
        if(element.addEventListener) {
            element.addEventListener("submit", function(evt){
                evt.preventDefault();
                window.history.back();
            }, true);
        } else {
            element.attachEvent('onsubmit', function(evt){
                evt.preventDefault();
                window.history.back();
            });
        }
      },
      attachFormSubmitEvent: function (){
        for(var i=0; i<window.document.forms.length; i++){
          var form = window.document.forms[i];
          var className = "wpl-track-me";
          if ('classList' in document.documentElement) {
            var hasClass = form.classList.contains(className);
          } else {
            var hasClass = new RegExp('(^|\\s)' + className + '(\\s|$)').test(form.className); /* IE Polyfill */
          }
          /* is tracked form */
          if(hasClass){
            console.log("Has Class", hasClass);
            InboundAnalytics.Utils.addListener(form, 'submit', InboundAnalytics.LeadsAPI.formSubmit );
            console.log(form);
          }

        }
        console.log("RAN attach event");
      },
      makeRequest: function(url) {
          if (window.XMLHttpRequest) { // Mozilla, Safari, ...
            httpRequest = new XMLHttpRequest();
          } else if (window.ActiveXObject) { // IE
            try {
              httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
              try {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
              }
              catch (e) {}
            }
          }

          if (!httpRequest) {
            alert('Giving up :( Cannot create an XMLHTTP instance');
            return false;
          }
          httpRequest.onreadystatechange = InboundAnalytics.LeadsAPI.alertContents;
          httpRequest.open('GET', url);
          httpRequest.send();
        },
        inbound_map_fields: function (el, value, Obj) {
          var formObj = [];
          var $this = el;
          var clean_output = value;
          var label = $this.closest('label').text();
          var exclude = ['credit-card']; // exlcude values from formObj
          var inarray = jQuery.inArray(clean_output, exclude);
          if(inarray == 0){
            return null;
          }
          // Add items to formObj
          formObj.push({
              field_label: label,
              field_name: $this.attr("name"),
              field_value: $this.attr("value"),
              field_id: $this.attr("id"),
              field_class: $this.attr("class"),
              field_type: $this.attr("type"),
              match: clean_output,
              js_selector: $this.attr("data-js-selector")
          });
          return formObj;
        },
       run_field_map_function: function (el, lookingfor) {
         var return_form;
         var formObj = new Array();
         var $this = el;
         var body = jQuery("body");
         var input_id = $this.attr("id") || "NULL";
         var input_name = $this.attr("name") || "NULL";
         var this_val = $this.attr("value");
         var array = lookingfor.split(",");
         var array_length = array.length - 1;

             // Main Loop
             for (var i = 0; i < array.length; i++) {
                 var clean_output = InboundAnalytics.Utils.trim(array[i]);
                 var nice_name = clean_output.replace(/^\s+|\s+$/g,'');
                 var nice_name = nice_name.replace(" ",'_');
                 var in_object_already = nice_name in inbound_data;
                 //console.log(clean_output);

                 if (input_name.toLowerCase().indexOf(clean_output)>-1) {
                   /*  Look for attr name match */
                   var the_map = InboundAnalytics.LeadsAPI.inbound_map_fields($this, clean_output, formObj);
                   InboundAnalytics.LeadsAPI.add_inbound_form_class($this, clean_output);
                   console.log('match name: ' + clean_output);
                   console.log(nice_name in inbound_data);
                    if (!in_object_already) {
                     inbound_data[nice_name] = this_val;
                    }
                 } else if (input_id.toLowerCase().indexOf(clean_output)>-1) {
                  /* look for id match */
                   var the_map = InboundAnalytics.LeadsAPI.inbound_map_fields($this, clean_output, formObj);
                   InboundAnalytics.LeadsAPI.add_inbound_form_class($this, clean_output);
                   console.log('match id: ' + clean_output);

                    if (!in_object_already) {
                      inbound_data[nice_name] = this_val;
                    }

                 } else if ($this.closest('li').children('label').length>0) {
                  /* Look for label name match */
                  var closest_label = $this.closest('li').children('label').html() || "NULL";
                   if (closest_label.toLowerCase().indexOf(clean_output)>-1) {

                     var the_map = InboundAnalytics.LeadsAPI.inbound_map_fields($this, clean_output, formObj);
                     InboundAnalytics.LeadsAPI.add_inbound_form_class($this, clean_output);
                     console.log($this.context);

                     var exists_in_dom = body.find("[data-inbound-form-map='inbound_map_" + nice_name + "']").length;
                     console.log(exists_in_dom);
                     console.log('match li: ' + clean_output);

                     if (!in_object_already) {
                      inbound_data[nice_name] = this_val;
                     }

                   }
                 } else if ($this.closest('div').children('label').length>0) {
                  /* Look for closest div label name match */
                  var closest_div = $this.closest('div').children('label').html() || "NULL";
                   if (closest_div.toLowerCase().indexOf(clean_output)>-1)
                   {
                     var the_map = InboundAnalytics.LeadsAPI.inbound_map_fields($this, clean_output, formObj);
                     InboundAnalytics.LeadsAPI.add_inbound_form_class($this, clean_output);
                     console.log('match div: ' + clean_output);
                     if (!in_object_already) {
                     inbound_data[nice_name] = this_val;
                    }
                   }
                 } else if ($this.closest('p').children('label').length>0) {
                  /* Look for closest p label name match */
                  var closest_p = $this.closest('p').children('label').html() || "NULL";
                   if (closest_p.toLowerCase().indexOf(clean_output)>-1)
                   {
                     var the_map = InboundAnalytics.LeadsAPI.inbound_map_fields($this, clean_output, formObj);
                     InboundAnalytics.LeadsAPI.add_inbound_form_class($this, clean_output);
                     console.log('match p: ' + clean_output);
                     if (!in_object_already) {
                     inbound_data[nice_name] = this_val;
                    }
                   }
                 } else {
                  console.log('Need additional mapping data');
                 }
             }
             return_form = the_map;

         return inbound_data;
       },
       add_inbound_form_class: function(el, value) {
         var value = value.replace(" ", "_");
         var value = value.replace("-", "_");
         el.addClass('inbound_map_value');
         el.attr('data-inbound-form-map', 'inbound_map_' + value);
       },
       inbound_form_type: function(this_form) {
        var inbound_data = inbound_data || {},
        form_type = 'normal';
        if ( this_form.is( ".wpl-comment-form" ) ) {
          inbound_data['form_type'] = 'comment';
          form_type = 'comment';
        } else if ( this_form.is( ".wpl-search-box" ) ) {
          var is_search = true;
          form_type = 'search';
          inbound_data['form_type'] = 'search';
        } else if ( this_form.is( '.wpl-track-me-link' ) ){
          var have_email = readCookie('wp_lead_email');
          console.log(have_email);
          inbound_data['form_type'] = 'link';
          form_type = 'search';
        }
        return form_type;
       },
       grab_all_form_input_vals: function(this_form){
        var post_values = post_values || {},
        inbound_exclude = inbound_exclude || [],
        form_inputs = this_form.find('input,textarea,select');
        inbound_exclude.push('inbound_furl', 'inbound_current_page_url', 'inbound_notify', 'inbound_submitted', 'post_type', 'post_status', 's', 'inbound_form_name', 'inbound_form_id', 'inbound_form_lists');
        var form_type = InboundAnalytics.LeadsAPI.inbound_form_type(this_form),
        inbound_data = inbound_data || {},
        email = inbound_data['email'] || false;

        form_inputs.each(function() {
          var $input = jQuery(this),
          input_type = $input.attr('type'),
          input_val = $input.val();
          if (input_type === 'checkbox') {
            input_checked = $input.attr("checked");
            console.log(input_val);
            console.log(input_checked);
            console.log(post_values[this.name]);
            if (input_checked === "checked"){
            if (typeof (post_values[this.name]) != "undefined") {
              post_values[this.name] = post_values[this.name] + "," + input_val;
              console.log(post_values[this.name]);
            } else {
              post_values[this.name] = input_val;
            }

            }
          }
          if (jQuery.inArray(this.name, inbound_exclude) === -1 && input_type != 'checkbox'){
             post_values[this.name] = input_val;
          }
          if (this.value.indexOf('@')>-1&&!email){
            email = input_val;
            inbound_data['email'] = email;
          }
          if (form_type === 'search') {
            inbound_data['search_keyword'] = input_val.replace('"', "'");
          }
        });
        var all_form_fields = JSON.stringify(post_values);
        return all_form_fields;
       },
       return_mapped_values: function (this_form) {
        // Map form fields
        jQuery(this_form).find('input[type!="hidden"],textarea,select').each(function() {
          console.log('run');
          var this_input = jQuery(this);
          var this_input_val = this_input.val();
          if (typeof (this_input_val) != "undefined" && this_input_val != null && this_input_val != "") {
          var inbound_data = InboundAnalytics.LeadsAPI.run_field_map_function( this_input, "name, first name, last name, email, e-mail, phone, website, job title, company, tele, address, comment");
          }
          return inbound_data;
        });
        return inbound_data;
       },
       inbound_form_submit: function(this_form, e) {
        /* Define Variables */
        var data = inbound_data || {};
        // Dynamic JS object for passing custom values. This can be hooked into by third parties by using the below syntax.
        var pageviewObj = jQuery.totalStorage('page_views');
        data['page_view_count'] = InboundAnalytics.Utils.countProperties(pageviewObj);
        data['leads_list'] = jQuery(this_form).find('#inbound_form_lists').val();
        data['source'] = jQuery.cookie("wp_lead_referral_site") || "NA";
        data['page_id'] = inbound_ajax.post_id;
        data['page_views'] = JSON.stringify(pageviewObj);

        // Map form fields
        var returned_form_data = InboundAnalytics.LeadsAPI.return_mapped_values(this_form); //console.log(returned_form_data);
        var data = InboundAnalytics.Utils.mergeObjs(data,returned_form_data); //console.log(data);
        var this_form = jQuery(this_form);
        // Set variables after mapping
        data['email'] = (!data['email']) ? this_form.find('.inbound-email').val() : data['email'];
        data['form_name'] = this_form.find('.inbound_form_name').val() || "Not Found";
        data['form_id'] = this_form.find('.inbound_form_id').val() || "Not Found";
        data['first_name'] = (!data['first_name']) ? data['name'] : data['first_name'];
        data['last_name'] = data['last_name'] || '';
        data['phone'] = data['phone'] || '';
        data['company'] = data['company'] || '';
        data['address'] = data['address'] || '';

        // Fallbacks for values
        data['name'] = (data['first_name'] && data['last_name']) ? data['first_name'] + " " + data['last_name'] : data['name'];

        if (!data['last_name'] && data['first_name']) {
          var parts = data['first_name'].split(" ");
          data['first_name'] = parts[0];
          data['last_name'] = parts[1];
        }

        /* Store form fields & exclude field values */
        var all_form_fields = InboundAnalytics.LeadsAPI.grab_all_form_input_vals(this_form);
        /* end Store form fields & exclude field values */

        if(data['email']){
           InboundAnalytics.Utils.createCookie("wp_lead_email", data['email'], 365); /* set email cookie */
        }

        //var variation = (typeof (landing_path_info) != "undefined") ? landing_path_info.variation : false;

        if (typeof (landing_path_info) != "undefined") {
          var variation = landing_path_info.variation;
        } else if (typeof (cta_path_info) != "undefined") {
          var variation = cta_path_info.variation;
        } else {
          var variation = 0;
        }

        data['variation'] = variation;
        data['post_type'] = inbound_ajax.post_type;
        data['wp_lead_uid'] = jQuery.cookie("wp_lead_uid") || null;
        data['ip_address'] = inbound_ajax.ip_address;
        data['search_data'] = JSON.stringify(jQuery.totalStorage('inbound_search')) || {};

        var lp_check = (inbound_ajax.post_type === 'landing-page') ? 'Landing Page' : "";
        var cta_check = (inbound_ajax.post_type === 'wp-call-to-action') ? 'Call to Action' : "";
        var page_type = (!cta_check && !lp_check) ? inbound_ajax.post_type : lp_check + cta_check;

        // jsonify data
        var mapped_form_data = JSON.stringify(data);

        var return_data = {};
        var return_data = {
            "action": 'inbound_store_lead',
            "emailTo": data['email'],
            "first_name": data['first_name'],
            "last_name": data['last_name'],
            "phone": data['phone'],
            "address": data['address'],
            "company_name": data['company'],
            "page_views": data['page_views'],
            "form_input_values": all_form_fields,
            "Mapped_Data": mapped_form_data,
            "Search_Data": data['search_data']
        }
        return return_data;
      },
      formSubmit: function (e){
        /*if(!confirm('Are you sure?')) {
          e.returnValue = false;
          if(e.preventDefault) e.preventDefault();
          return false;
        }
        return true;*/
        /*var inbound_data = inbound_data || {},
        this_form = e.target,
        event_type = e.type,
        is_search = false,
        form_type = 'normal';*/

        e.preventDefault(); /* Halt form processing */
        console.log("This works");
        var data = InboundAnalytics.LeadsAPI.inbound_form_submit(e.target, e); // big function for processing
        console.log(data);
        alert('Working');
        //document.getElementById("ajaxButton").onclick = function() { makeRequest('test.html'); };

        /* Final Ajax Call on Submit */
        InboundAnalytics.LeadsAPI.makeRequest('test.html');
      },
       alertContents: function() {
         if (httpRequest.readyState === 4) {
           if (httpRequest.status === 200) {
             alert(httpRequest.responseText);
           } else if(xmlhttp.status == 400) {
             alert('There was an error 400');
           } else {
             alert('There was a problem with the request.');
           }
         }
       },
      getAllLeadData: function(expire_check) {
          var wp_lead_id = InboundAnalytics.Utils.readCookie("wp_lead_id"),
          old_data = jQuery.totalStorage('inbound_lead_data'),
          data = {
            action: 'inbound_get_all_lead_data',
            wp_lead_id: wp_lead_id,
          },
          success = function(returnData){
                    var obj = JSON.parse(returnData);
                    console.log('RAAAAAAn');
                    setGlobalLeadVar(obj);
                    jQuery.totalStorage('inbound_lead_data', obj); // store lead data
          };

          if(!old_data) {
            console.log("No old data");
          }

          if (expire_check === 'true'){
            console.log("Session has not expired");
          }

          if(!old_data && expire_check === null) {
              InboundAnalytics.debug('Go to Database',function(){
                   console.log(expire_check);
                   console.log(old_data);
              });
              InboundAnalytics.Utils.doAjax(data, success);
          } else {
              setGlobalLeadVar(old_data); // set global lead var with localstorage data
              var lead_data_expiration = InboundAnalytics.Utils.readCookie("lead_data_expiration");
              if (lead_data_expiration === null) {
                InboundAnalytics.Utils.doAjax(data, success);
                console.log('localized data old. Pull new from DB');
              }
          }

      },
      getLeadLists: function() {
          var wp_lead_id = InboundAnalytics.Utils.readCookie("wp_lead_id");
          var data = {
                  action: 'wpl_check_lists',
                  wp_lead_id: wp_lead_id,
          };
          var success = function(user_id){
                    jQuery.cookie("lead_session_list_check", true, { path: '/', expires: 1 });
                    console.log("Lists checked");
          };
          InboundAnalytics.Utils.doAjax(data, success);
      }
    };

  return InboundAnalytics;

})(InboundAnalytics || {});

/**
 * Custom Event Triggers for Leads -
 * The below functions illustrate how to use custom javascript callbacks to fire events
 * based on lead data and what they have and have not done on the site.
 */
/**

// Raw Javascript Version - trigger custom function on page view trigger

 window.addEventListener("inbound_analytics_triggered", fireOnPageViewTrigger, false);
 function fireOnPageViewTrigger(){
     alert("page view was triggered");
 }

// jQuery version - trigger custom function on analytics loaded

 jQuery(document).on('inbound_analytics_loaded', function (event, data) {
   console.log("inbound_analytics_loaded");
 });

// Raw Javascript Version - trigger custom function on page first seen

 window.addEventListener("inbound_analytics_page_first_view", page_first_seen_function, false);
 function page_first_seen_function(){
     alert("This is the first time you have seen this page");
 }

// Raw Javascript Version - trigger custom function on page already seen

window.addEventListener("inbound_analytics_page_revisit", page_seen_function, false);
function page_seen_function(e){
    var view_count = e.detail.count;
    console.log("This page has been seen " + e.detail.count + " times");
    if(view_count > 10){
      console.log("Page has been viewed more than 10 times");
    }
}

// jQuery version - trigger custom function on page already seen via jQuery

jQuery(document).on('inbound_analytics_page_revisit', function (event, data) {
  console.log("inbound_analytics_page_revisit action triggered");
  //console.log(data);
  if(data.count > 10){
    console.log("Page has been viewed more than 10 times");
  }
});
*/

/**
 * Event functions
 * @param  Object InboundAnalytics - Main JS object
 * @return Object - include event triggers
 */
// https://github.com/carldanley/WP-JS-Hooks/blob/master/src/event-manager.js
var InboundAnalyticsEvents = (function (InboundAnalytics) {

    InboundAnalytics.Events =  {
      // Create cookie
      loadEvents: function() {
          this.analyticsLoaded();
      },
      triggerJQueryEvent: function(eventName, data){
        if (window.jQuery) {
            var data = data || {};
            jQuery(document).trigger(eventName, data);
           /* var something = (function() {
                var executed = false;
                return function () {
                    if (!executed) {
                        executed = true;
                        console.log(eventName + " RAN");

                    }
                };
            })();*/
        }
      },
      analyticsLoaded: function() {
          var eventName = "inbound_analytics_loaded";
          var loaded = new CustomEvent(eventName);
          window.dispatchEvent(loaded);
          this.triggerJQueryEvent(eventName);
      },
      analyticsTriggered: function() {
          var triggered = new CustomEvent("inbound_analytics_triggered");
          window.dispatchEvent(triggered);
      },
      analyticsSaved: function() {
          var page_view_saved = new CustomEvent("inbound_analytics_saved");
          window.dispatchEvent(page_view_saved);
          console.log('Page View Saved');
      },
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
      },
      pageFirstView: function(page_seen_count) {
          var page_first_view = new CustomEvent("inbound_analytics_page_first_view", {
              detail: {
                count: 1,
                time: new Date(),
              },
              bubbles: true,
              cancelable: true
            }
          );
          window.dispatchEvent(page_first_view);

          console.log('First Ever Page View of this Page');
      },
      pageRevisit: function(page_seen_count) {
          var eventName = "inbound_analytics_page_revisit";
          var data = { count: page_seen_count,
                       time: new Date()
                     };
          var page_revisit = new CustomEvent(eventName, {
              detail: data,
              bubbles: true,
              cancelable: true
            }
          );
          window.dispatchEvent(page_revisit);
          this.triggerJQueryEvent(eventName, data);
          console.log('Page Revisit');
      },
      /* get idle times https://github.com/robflaherty/riveted/blob/master/riveted.js */
      browserTabHidden: function() {
        /* http://www.thefutureoftheweb.com/demo/2007-05-16-detect-browser-window-focus/ */
          var eventName = "inbound_analytics_tab_hidden";
          var tab_hidden = new CustomEvent(eventName);
          window.dispatchEvent(tab_hidden);
          console.log('Tab Hidden');
          this.triggerJQueryEvent(eventName);
      }
      browserTabVisible: function() {
        var eventName = "inbound_analytics_tab_visible";
        var tab_visible = new CustomEvent(eventName);
        window.dispatchEvent(tab_visible);
        console.log('Tab Visible');
        this.triggerJQueryEvent(eventName);
      },
      /* Scrol depth https://github.com/robflaherty/jquery-scrolldepth/blob/master/jquery.scrolldepth.js */
      sessionStart: function() {
          var session_start = new CustomEvent("inbound_analytics_session_start");
          window.dispatchEvent(session_start);
          console.log('Session Start');
      },
      sessionActive: function() {
          var session_active = new CustomEvent("inbound_analytics_session_active");
          window.dispatchEvent(session_active);
          console.log('Session Active');
      },

  };

  return InboundAnalytics;

})(InboundAnalytics || {});

var Lead_Globals = jQuery.totalStorage('inbound_lead_data') || null;
function setGlobalLeadVar(retString){
    Lead_Globals = retString;
}

InboundAnalytics.init(); // run analytics

/* run on ready */
jQuery(document).ready(function($) {
  //record non conversion status
  var in_u = InboundAnalytics.Utils,
  wp_lead_uid = in_u.readCookie("wp_lead_uid"),
  wp_lead_id = in_u.readCookie("wp_lead_id"),
  expire_check = in_u.readCookie("lead_session_expire"); // check for session

  if (expire_check === null) {
     console.log('expired vistor. Run Processes');
    //var data_to_lookup = global-localized-vars;
    if (typeof (wp_lead_id) != "undefined" && wp_lead_id != null && wp_lead_id != "") {
        /* Get Lead_Globals */
        InboundAnalytics.LeadsAPI.getAllLeadData(expire_check);
        /* Lead list check */
        InboundAnalytics.LeadsAPI.getLeadLists();
      }
  }

//window.addEventListener('load',function(){
//    InboundAnalytics.LeadsAPI.attachSubmitEvent(window,InboundAnalytics.LeadsAPI.formSubmit);
//}, false);

in_u.contentLoaded(window, InboundAnalytics.LeadsAPI.attachFormSubmitEvent);

/* Set Session Timeout */
in_u.SetSessionTimeout();

});