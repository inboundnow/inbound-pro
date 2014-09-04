/**
 * Lead Tracking JS
 * http://www.inboundnow.com
 */

 var InboundAnalytics = (function () {

   var _privateMethod = function () {};

   var myObject = {
     someMethod:  function () {

     },
     anotherMethod:  function () {

     }
   };

   return myObject;

 })();



var IA_PageViews = (function (InboundAnalytics) {

    InboundAnalytics.PageTracking = {
		getPageViews: function () {
			var local_store = InboundAnalytics.Utils.checkLocalStorage();
	    	if(local_store){
	    		var page_views = localStorage.getItem("page_views"),
	    		local_object = JSON.parse(page_views);
	    		if (typeof local_object =='object' && local_object) {
	    			return local_object;
	    		}
	    	}
		},
		CheckTimeOut: function() {
			var PageViews = InboundAnalytics.PageTracking.getPageViews(),
			page_id = wplft.post_id,
			page_seen = PageViews[page_id],
			time_now = wplft.track_time,
			vc = PageViews[page_id].length - 1,
			last_view = PageViews[page_id][vc];

			last_view_formatted = new Date(last_view).getTime();
			time_now_formatted = new Date(time_now).getTime();
			var timeout = last_view_formatted + 30*1000;
			console.log('time now= ' + time_now_formatted);
			console.log('time out= ' + timeout);

			var time_out_check_raw = time_now_formatted > timeout;
			var time_out_check = Date.parse(time_now_formatted) > Date.parse(timeout);

			var time_check = Math.abs(time_now_formatted - timeout);

			var test = time_check * .001;
			console.log(test);
			console.log(time_out_check);
			console.log(time_out_check_raw);


			if(typeof(page_seen) != "undefined" && page_seen !== null) {
				console.log('page seen');
			} else {
				console.log('page not seen');
			}
			console.log(PageViews);
		}
	}

    return InboundAnalytics;

})(InboundAnalytics || {});


/**
 * Utility functions
 * @param  Object InboundAnalytics - Main JS object
 * @return Object - include util functions
 */
var IA_Utils = (function (InboundAnalytics) {

    InboundAnalytics.Utils =  {
    	// Create cookie
	    createCookie: function(name, value, days) {
	        var expires = "";
	        if (days) {
	            var date = new Date();
	            date.setTime(date.getTime()+(days*24*60*60*1000));
	            expires = "; expires="+date.toGMTString();
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
	    // Check local storage
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
	    },

	};

    return InboundAnalytics;

})(InboundAnalytics || {});