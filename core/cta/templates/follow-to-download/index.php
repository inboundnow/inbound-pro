<style type="text/css">
a.downloadButton , #placeholder-span{
  display:inline-block;
  width:187px;
  height:67px;
  text-indent:-99999px;
  overflow:hidden;
  background:url('{{template-urlpath}}img/buttons.png') no-repeat;
  cursor:default;
  border:none;
  text-decoration:none !important;
}

a.downloadButton.active{
  background-position:left bottom;
  cursor:pointer;
}

#arrow-down {
  width:32px;
  height:36px;
  margin: auto;
  background:url('{{template-urlpath}}img/arrow_down.png') no-repeat;
}

#wp-cta-content { border-radius: {{border-radius}}px;}
#wp-cta-content { background-color: #{{content_color}};}
#extra-text-area { color: #{{text-color}};}
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
#tweetLink {
	cursor:pointer;
}
</style>

<div id="wp-cta-content" style="width:{{width}};height:{{height}}; margin: auto;">
  <div id="extra-text-area">{{header-text}}</div>
  <div id="inbound-share-model">
        <span id="tweetLink"><img src="{{template-urlpath}}follow-image.png" title="Click and Follow to activate the download" ></span>
        <div id="arrow-down"></div>


  <span id="placeholder-span" class="downloadButton" title="click the above share button to activate the download">Download</span>

  <a href="{{download-url}}" style="display:none;" class="downloadButton active" title="Thanks! Click to Download">Download</a>
  <script src="{{template-urlpath}}js/jquery.tweetAction.js"></script>
   <script type="text/javascript">
        jQuery(document).ready(function($) {

			jQuery(".downloadButton").removeAttr('href');
			jQuery(".downloadButton").addClass('prevent-default');
			
			setTimeout(function() {
			   jQuery(".downloadButton").removeAttr('href');
			   jQuery(".downloadButton").addClass('prevent-default');
			}, 1000);

			jQuery("body").on('click', '.prevent-default', function (event) {
				event.preventDefault();
				console.log('Pre-Tweet Click!');
            });
			
      // Using our tweetAction plugin. For a complete list with supported
      // parameters, refer to http://dev.twitter.com/pages/intents#tweet-intent

      jQuery('#tweetLink').tweetAction({
          screen_name: '{{twittername}}'
      },function(){
        jQuery("#placeholder-span").hide();
                  jQuery('.prevent-default').removeClass('prevent-default');
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