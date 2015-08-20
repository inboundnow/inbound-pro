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
    }
  };

  return Preview;

})();

jQuery(document).ready(function($) {
      InboundCustomizerPreview.init();
 });
