/* This code runs inside the wordpress editor screen when customizer active */
var InboundCustomizerEditor = (function () {

  var EditorCode = {
    init:  function () {
        this.handleEditorSave();
    },
    /* when options saved */
    handleEditorSave: function() {

        jQuery('body').on( 'submit', 'form', function() {
          setTimeout( function() {
            //parent.location.reload();
            jQuery(parent.document).find("#inbound-customizer-overlay").fadeIn(300);

            window.parent.InboundCustomizerParent.togglePreviewReload();

          }, 100 );


        });
    },
    hideOverlay: function() {
        var overlay = jQuery(parent.document).find("#inbound-customizer-overlay");
        if(overlay.is(':visible')) {
            overlay.fadeOut(400);
        }
    }
  };

  return EditorCode;

})();

jQuery(document).ready(function($) {
    // start
    InboundCustomizerEditor.init();
    /* on page load, hide overlay if visible */
    InboundCustomizerEditor.hideOverlay();
});
