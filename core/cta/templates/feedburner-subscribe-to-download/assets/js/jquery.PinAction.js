(function($){
	
	var win = null;
	
	$.fn.SubAction = function(options,callback){
		
		// Default parameters of the tweet popup:
		
		options = $.extend({
			url:window.location.href
		}, options);
		
		return this.click(function(e){
			
			if(win){
				// If a popup window is already shown,
				// do nothing;
				e.preventDefault();
				return;
			}
			
			var width	= 550,
				height	= 520,
				top		= (window.screen.height - height)/2,
				left	= (window.screen.width - width)/2; 
			
			var config = [
				'scrollbars=yes','resizable=yes','toolbar=no','location=yes',
				'width='+width,'height='+height,'left='+left, 'top='+top
			].join(',');
	
	      var emailaddress =  $('#newsletter-text').val();
if (emailaddress == '') {
   alert('Enter Your Email Address!').die();

}

			// Opening a popup window pointing to the twitter intent API:
			win = window.open('http://feedburner.google.com/fb/a/mailverify?'+$.param(options) + '&email=' + emailaddress,
						'TweetWindow',config);
			
			// 	win = window.open('http://feedburner.google.com/fb/a/mailverify?'+$.param(options)+'email=' + $emailaddress,
			//			'TweetWindow',config);
			
			
			// Checking whether the window is closed every 100 milliseconds.
			(function checkWindow(){
				
				try{
					// Opera raises a security exception, so we
					// need to put this code in a try/catch:
					
					if(!win || win.closed){
						throw "Closed!";
					}
					else {
						setTimeout(checkWindow,100);
					}
				}
				catch(e){
					// Executing the callback, passed
					// as an argument to the plugin.
					
					win = null;
					callback();
				}
				
			})();
			
			e.preventDefault();
		});
		
	};
	
})(jQuery);