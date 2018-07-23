/* This is the customizer parent window containing the editor and preview frames */
var InboundCustomizerParent = (function () {

  var _privateMethod = function () {};

  var myObject = {
    init:  function () {
        console.log('parent init');
        jQuery("#wp-admin-bar-edit a").text("Main Edit Screen");
    },
    togglePreviewReload:  function () {
        /* triggered from editor frame */
        setTimeout(function() {
          console.log('reload preview');
          document.getElementById('inbound-customizer-preview-id').src = document.getElementById('inbound-customizer-preview-id').src

        }, 3000);
    }
  };

  return myObject;

})();

jQuery(document).ready(function($) {
   InboundCustomizerParent.init();
});