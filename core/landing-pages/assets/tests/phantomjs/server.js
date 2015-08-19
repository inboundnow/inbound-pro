/**
 *  PhantomJs server for calling webpages & processing JS
 *  returns page content
 */

var args = require('system').args;
var address = args[1];
var page = new WebPage();

var renderPage = function(){

    page = require('webpage').create();
    var myArgs = Array.prototype.slice.call(arguments),
        url_str = myArgs[0];

	 console.log('accessing:'+url_str);
	
	page.onError = function(msg){console.log('js error');}
	
    /**
     * From PhantomJS documentation:
     * This callback is invoked when there is a JavaScript console. The callback may accept up to three arguments:
     * the string for the message, the line number, and the source identifier.
     */
    page.onConsoleMessage = function (msg, line, source) {
        console.log('console> ' + msg);
    };

    /**
     * From PhantomJS documentation:
     * This callback is invoked when there is a JavaScript alert. The only argument passed to the callback is the string for the message.
     */
    page.onAlert = function (msg) {
        console.log('alert!!> ' + msg);
    };

    /**
     * Handle Redirection
     */
    page.onNavigationRequested = function(url_sub_str, type, willNavigate, main) {
        if (main && url_sub_str != url_str) {
            url_str = url_sub_str;
            console.log("redirect caught");
            page.close();
            setTimeout(function() {
				renderPage(url_str)
			},1);
        }
    };
	
	page.onResourceReceived = function (response) {
		//console.log('Receive ' + JSON.stringify(response, undefined, 4));
	};
	
	//page.onLoadFinished = function() { console.log(&quot;onLoadFinished FIRED&quot;); }

    /**
     * Open the web page and run RRunner
     */
    page.open(url_str, function(status) {
		console.log('here');
        if (status === 'success') {
            console.log(page.content); 
			page.close();
			setTimeout( function() {
				
				phantom.exit();
			} , 5000);
        } else {
            console.log('failed');			
            phantom.exit();
        }
    });
};


address = 'http://local.wordpress.dev/test.php';
//address = 'http://local.wordpress.dev/';
//address = 'http://www.simpleweb.org/';
renderPage(address);


