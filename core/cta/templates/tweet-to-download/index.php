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

#wp-cta-content { background-color: #{{content-color}};}

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
</style>

<div id="wp-cta-content" style="width:{{width}};height:{{height}}; margin: auto;">
  <div id="extra-text-area">{{header-text}}</div>
  <div id="inbound-share-model">
            <p><span href="#" id="tweetLink"><img src="{{template-urlpath}}img/tweet-image.png" title="Click and Share on Twitter to activate the download" id="linkedshare" ></span></p>
  <div id="arrow-down"></div>

  <span id="placeholder-span" class="downloadButton" title="click the above share button to activate the download">Download</span>

  <a href="{{download-url}}" style="display:none;" class="downloadButton active" title="Thanks! Click to Download">Download</a>

          <script>
          jQuery(document).ready(function($) {

            jQuery("#feedburnerform").removeClass('wpl-track-me');
            jQuery(".downloadButton").removeAttr('href');
            jQuery(".downloadButton").addClass('prevent-default');
            setTimeout(function() {
               jQuery("#feedburnerform").removeClass('wpl-track-me');
               jQuery(".downloadButton").removeAttr('href');
               jQuery(".downloadButton").addClass('prevent-default');
                }, 1000);

      jQuery("body").on('click', '.prevent-default', function (event) {
          event.preventDefault();
          console.log('clicked');
          });
      $('#tweetLink').tweetAction({
              text:       '{{$share_text}}',
              url:        '{{$share_url}}',
              via:        '{{$twittername}}',
              related:    '{{$twittername}}'
          },function(){
          $("#placeholder-span").hide();
          // When the user closes the pop-up window:
          $('.prevent-default').removeClass('prevent-default');
          var the_link = jQuery("#the_link").attr('href');
          var link_target = jQuery("#the_link").hasClass('external-new-tab');
          if (link_target === true){
            $('a.downloadButton').addClass('external-new-tab');
          }
          $('a.downloadButton')
                  .show()
                  .attr('href', the_link)
                  .attr('title', 'Thanks! Click to Download');

      });

  });
          </script>
          <script src="{{template-urlpath}}js/jquery.tweetAction.js"></script>
          <a id="the_link" style="display:none;" href="{{download-url}}"></a>
     </div>

</body>