/* This is the customizer preview window */

var InboundCustomizerPreview = (function () {

  var Preview = {
    init:  function () {
      this.disableLinks();
    },
    disableLinks: function () {
      jQuery('a').each(function(e){
          console.log('Links disabled in editor');
          jQuery(this).on('click', function (e) {
              e.preventDefault();
          });
      });
    },
    resizeLandingPageView: function(parentBody){
          var body = jQuery('body'),
          height = body.height(),
          options = {
            tranformOrigin: '0px 0px',
            zoom: 0.7
          };

          //jQuery(document).find("meta[name=viewport]").remove();
          var frame = parentBody.find('.inbound-customizer-preview')
          var minHeight = frame.height();
          var fixed = (minHeight - 32) / .7;
          body.css({
           'transform-origin': options.tranformOrigin,
           '-webkit-transform-origin': options.tranformOrigin,
           '-moz-transform-origin': options.tranformOrigin,
           '-o-transform-origin': options.tranformOrigin,
           'transform': 'scale(' + options.zoom + ')',
           '-webkit-transform': 'scale(' + options.zoom + ')',
           '-moz-transform': 'scale(' + options.zoom + ')',
           '-o-transform': 'scale(' + options.zoom + ')',
           'width': '100%',
           'min-height': fixed
          });

          parentBody.find('.inbound-customizer-preview').css({
                   'max-width': '100%'
          });
    }
  };

  return Preview;

})();

jQuery(document).ready(function($) {

      var parentBody = $(parent.document).find('body');

      InboundCustomizerPreview.init();

      if(parentBody.hasClass('landing-page')) {
         InboundCustomizerPreview.resizeLandingPageView(parentBody);
         jQuery(parent.window).resize(function() {
              console.log('resize')
              InboundCustomizerPreview.resizeLandingPageView(parentBody);
         });
      }

 });
