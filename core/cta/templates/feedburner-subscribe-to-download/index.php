<style type="text/css">
a.downloadButton , #placeholder-span{
	display:inline-block;
	width:187px;
	height:67px;
	text-indent:-99999px;
	overflow:hidden;
	background:url('{{template-urlpath}}assets/img/buttons.png') no-repeat;
	cursor:default;
	border:none;
	text-decoration:none !important;
}

a.downloadButton.active{
	background-position:left bottom;
	cursor:pointer;
}

#arrow-down {

	width:45px;
	height:36px;
	margin: auto;
	background:url('{{template-urlpath}}assets/img/arrow_down.png') no-repeat;
}

#cta-content {
	border-radius: {{border-radius}};
}

#cta-content {
	background-color: #{{content-color}};
}

#extra-text-area {
	color: #{{text_color}};
}

#extra-text-area {
  text-align: center;
  text-shadow: none;
  font-size: 1.5em;
  line-height: 1.3em;
  padding-right: 5%;
  padding-left: 5%;
  padding-bottom: 2%;
  padding-top: 10px;
}

#inbound-share-model {
  text-align: center;
}
#cta-content .btn.large {
        font-size: 15px;
        margin-bottom: 10px;
}
#cta-content .btn:focus {
        outline: none;
}
#cta-content form {
	margin-bottom: 0px;
	margin-top:2px;
}
#SubLink {
	text-transform: none;
}
</style>

<div id="cta-content" style="width:{{width}};height:{{height}}; margin: auto;">
  <div id="extra-text-area">{{header-text}}</div>
  <div id="inbound-share-model">
		<form name="feedburnerform" id="feedburnerform">

			<input type="hidden" name="rurl" value="{{download-url}}">
			<input type="hidden" name="feedburner" value="{{share-url}}">

			<div style="padding-bottom:5px;">
				<input name="email" id="newsletter-text" type="text" maxlength="100" style="width:220px !important; padding: 10px !important;" placeholder="Email Your Email" value="" class="">
			</div>
			<div>
				<button type="submit" id="SubLink" class="btn primary large bold">Subscribe via Email</button>
			</div>
		</form>

		<div id="arrow-down"></div>
		<span class="downloadButton" id="placeholder-span" title="click the above share button to activate the download">Download</span>
		<a href="{{download-url}}" style="display:none;" class="downloadButton active" title="click the above share button to activate the download">Download</a>
		<script src="{{template-urlpath}}assets/js/jquery.PinAction.js"></script>
			  <script>
			  jQuery(document).ready(function($) {
				jQuery("#feedburnerform").removeClass('wpl-track-me');
				jQuery(".downloadButton").removeAttr('href');
				setTimeout(function() {
				   jQuery("#feedburnerform").removeClass('wpl-track-me');
				   jQuery(".downloadButton").removeAttr('href');
					}, 1000);

				  jQuery('#SubLink').SubAction({
						 uri:  '{{share-url}}'

					 },function(){
					  jQuery("#placeholder-span").hide();
					  // When the user closes the pop-up window:
					  var the_link = jQuery("#the_link").attr('href');
					  var link_target = jQuery("#the_link").hasClass('external-new-tab');
					  if (link_target === true){
						jQuery('a.downloadButton').addClass('external-new-tab');
					  }
					  jQuery('a.downloadButton')
							  .show()
							  .attr('href', the_link)
							  .attr('title', 'Thanks! Click to Download');

				  });

			  });
			  </script>

          <a id="the_link" style="display:none;" href="{{download-url}}"></a>
	</div>
</div>