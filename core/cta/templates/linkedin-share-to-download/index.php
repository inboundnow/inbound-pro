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

#content { border-radius: {{border-radius}}px;}
#extra-text-area { color: #{{text-color}};}
#content { background-color: #{{content-color}};}
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

<div id="content" style="width:{{width}};height:{{height}}; margin: auto;">
  <div id="extra-text-area">{{header-text}}</div>
  <div id="inbound-share-model">
            <p style="margin-bottom:10px; padding-bottom: 0px;"> <a href="#" id="LILink" class="do-not-track"><img src="{{template-urlpath}}img/linkedshare.png" title="Click and Share on Linkedin to activate the download" id="linkedshare" ></a></p>
  <div id="arrow-down"></div>

  <span class="downloadButton" id="placeholder-span" title="click the above share button to activate the download">Download</span>

  <a href="#" style='display:none;' class="downloadButton active" title="click the above share button to activate the download">Download</a>

          <script src="{{template-urlpath}}js/jquery.tweetAction.js"></script>
          <script>
          jQuery(document).ready(function($) {

              // Using our tweetAction plugin. For a complete list with supported
              // parameters, refer to http://dev.twitter.com/pages/intents#tweet-intent
              jQuery('#LILink').tweetAction({
                  url:        '{{share-url}}'

              },function(){
                  jQuery("#placeholder-span").hide();
                  // When the user closes the pop-up window:
                  var the_link = jQuery("#LI_the_link").attr('href');
                  var link_target = jQuery("#LI_the_link").hasClass('external-new-tab');
                  if (link_target === true){
                    jQuery('a.downloadButton').addClass('external-new-tab');
                    jQuery('a.downloadButton').addClass('do-not-track');
                  }
                  jQuery('a.downloadButton')
                          .show()
                          .attr('href', the_link)
                          .attr('class', 'do-not-track')
                          .attr('title', 'Thanks! Click to Download');

              });

          });
          </script>

          <a id="LI_the_link" class="do-not-track" style="display:none;" href="{{download-url}}"></a>
     </div>
</body>