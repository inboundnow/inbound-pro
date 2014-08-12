(function($){
	
	var win = null;
	
	$.fn.tweetAction = function(options,callback){
		
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
				height	= 350,
				top		= (window.screen.height - height)/2,
				left	= (window.screen.width - width)/2; 
			
			var config = [
				'scrollbars=yes','resizable=yes','toolbar=no','location=yes',
				'width='+width,'height='+height,'left='+left, 'top='+top
			].join(',');
	
			// Opening a popup window pointing to the twitter intent API:
			win = window.open('http://twitter.com/intent/tweet?'+$.param(options),
						'TweetWindow',config);
			
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