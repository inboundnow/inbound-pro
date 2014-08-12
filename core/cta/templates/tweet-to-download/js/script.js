$(document).ready(function(){
	
	// Using our tweetAction plugin. For a complete list with supported
	// parameters, refer to http://dev.twitter.com/pages/intents#tweet-intent
	
	$('#tweetLink').tweetAction({
		text:		'How to make a simple Tweet to Download system',
		url:		'http://tutorialzine.com/2011/05/tweet-to-download-jquery/',
		via:		'tutorialzine',
		related:	'tutorialzine'
	},function(){
		
		// When the user closes the pop-up window:
		
		$('a.downloadButton')
				.addClass('active')
				.attr('href','http://demo.tutorialzine.com/2011/05/tweet-to-download-jquery/tweet_to_download.zip');

	});
	
});