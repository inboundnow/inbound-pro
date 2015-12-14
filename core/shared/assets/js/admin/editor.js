/* This code runs inside the wordpress editor screen */
var InboundEditor = (function () {

  var EditorCode = {
    init:  function () {
        this.addToggles();
        this.handleACFToggle();
    },
    addToggles: function(){
       var button = "<a style='position: absolute;left: -75px;cursor: pointer;' class='toggle-acf-areas'>Collapse</a>";
        jQuery('.acf-fc-layout-controlls').each(function(){
            jQuery(this).prepend( button );
        });
    },
    handleACFToggle: function(){
      var that = this;
      jQuery("body").on('click', '.toggle-acf-areas', function (e) {
          e.preventDefault();
          that.toggleACFTabs();
      });
    },
    toggleACFTabs: function(){
      jQuery('.acf-flexible-content .layout').each(function(){
         var $this = jQuery(this);
         var isOpen = $this.attr('data-toggle');
         if(isOpen === "open") {
            $this.find('.acf-fc-layout-handle').click();
         }
      });
    },
    /* when options saved */
    handleEditorSave: function() {

    },

  };

  return EditorCode;

})();

jQuery(document).ready(function($) {
    // start
    InboundEditor.init();

});
