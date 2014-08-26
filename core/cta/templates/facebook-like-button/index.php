<style type="text/css">
	#cta_content {
		border-radius:{{border-radius}}px;
		background-color: #{{background-color}};
		padding-top: 10px;
	}
	#extra-text-area {
		color: #{{text-color}};
		text-align: center;
		text-shadow: none;
		font-size: 1.5em;
		line-height: 1.3em;

	}
	#inbound-share-model {
	  text-align: center;
	}
	.fb_iframe_widget {
		width: 80%;
		margin-bottom: 20px;
		max-width: 300px;
	}
</style>

<div id="cta_content">
	<div id="extra-text-area">{{header-text}}</div>
	<div id="inbound-share-model">
	<img src="{{template-urlpath}}assets/img/{{image-style}}.png" >
	<div id="inbound-fb-like"><div id="fb-root"></div><script src="//connect.facebook.net/en_US/all.js#appId={{fb-app-id}}&amp;xfbml=1"></script><fb:like href="{{share-url}}" send="false" width="{{width}}" height="30" show_faces="false" font=""></fb:like></div>

	<script type="text/javascript"> 

	window.fbAsyncInit = function() { 
       // init the FB JS SDK
		FB.init({
			appId      : '{{fb-app-id}}', // App ID from the App Dashboard
			status     : true, // check the login status upon init?
			cookie     : true, // set sessions cookies to allow your server to access the session?
			xfbml      : true  // parse XFBML tags on this page?
		}); 

		FB.Event.subscribe('edge.create', function( url , html_element ) {
			fb_like_to_download_event( {{cta-id}} , {{variation-id}} );
		});
    }; 
	
	function fb_like_to_download_event( cta_id , vid )
	{

		/***record impressions***/
		var conversion_data = {
			action: 'wp_cta_record_conversion',
			cta_id: cta_id,
			variation_id : vid
		};

		jQuery.post( '{{wordpress-ajaxurl}}', conversion_data, function(response) {

			if (response!=false)
			{
				//alert('Got this from the server: ' + response);
				//form.submit();
				return true;
			}

		});
	}

	</script>
	</div>
</div>
