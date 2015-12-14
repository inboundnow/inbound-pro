/* This is the customizer parent window containing the editor and preview frames */
var InboundCustomizerOffParent = (function () {

  var _privateMethod = function () {};

  var myObject = {
    init:  function () {
        jQuery("#wp-admin-bar-customize a").text( customizer_off.launch_visual_editor );
        jQuery("#wp-admin-bar-customize a").attr( 'href' , customizer_off.url );
    }
  };

  return myObject;

})();

jQuery(document).ready(function($) {
   InboundCustomizerOffParent.init();
});