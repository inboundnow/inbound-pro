/* This code runs inside the wordpress editor screen when customizer active */
var InboundCustomizerEditor = (function () {

  var EditorCode = {
    init:  function () {
        this.handleEditorSave();
        this.acfStyling();
        this.rewriteTabLinks();

        // Scroll handler to save scroll position

        var scrollPoint = localStorage.getItem('inbound-scroll');

        setTimeout(function() {
              window.scrollTo(0, scrollPoint);
        }, 100);


        window.addEventListener('scroll', this.onScroll, false);
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
    acfStyling: function(){
        jQuery('.acf-tab-group a').addClass('button inbound-acf-tab');
    },
    rewriteTabLinks: function() {

        jQuery('.nav-tab-wrapper.a_b_tabs a').each(function(){
            var $this = jQuery(this);
            var permalink = $this.attr('data-permalink');

            if( permalink ) {
                console.log(permalink);
                $this.attr('href', permalink + "&inbound-editor=true&inbound-customizer=on");
            }


        });
    },
    hideOverlay: function() {
        var overlay = jQuery(parent.document).find("#inbound-customizer-overlay");
        if(overlay.is(':visible')) {
            overlay.fadeOut(400);
        }
    },
    listenToIframe: function(){
        /* listens to iframe content changes
        jQuery('.wp-call-to-action-option-row iframe').contents().find('body').on("keyup", function (e) {
            var thisclass = jQuery(this).attr("class");
            var this_class_dirty = thisclass.replace("mceContentBody ", "");
            var this_class_cleaner = this_class_dirty.replace("wp-editor", "");
            var clean_1 = this_class_cleaner.replace("post-type-wp-call-to-action", "");
            var clean_2 = clean_1.replace(/[.\s]+$/g, ""); // remove trailing whitespace
            var clean_spaces = clean_2.replace(/\s{2,}/g, ' '); // remove more than one space
            var this_id =  clean_spaces.replace(/[.\s]+$/g, ""); // remove trailing whitespace
            console.log(this_id);
            var parent_el = jQuery( "." + this_id + " .wp-call-to-action-table-headerz");
            jQuery(parent_el).find(".wp-cta-success-message").remove();
            jQuery(parent_el).find(".new-save-wp-cta-frontend").remove();
            var ajax_save_button = jQuery('<span class="button-primary new-save-wp-cta-frontend" id="' + this_id + '" style="margin-left:10px;">Update</span>');
            //console.log(parent_el);
            jQuery(ajax_save_button).appendTo(parent_el);
        });
         */
    },
    attachHoverListeners: function(){
        var that = this;
        jQuery('.acf-field').each(function(){
            var $this = jQuery(this),
            type = $this.attr('data-type');
            if(type === "text" || type === "wysiwyg") {
                that.onMouseOver($this);
                that.onMouseLeave($this);
            }

        });
    },

    onScroll: function(e) {
      console.log(window.pageYOffset);
      localStorage.setItem('inbound-scroll', window.pageYOffset);
    },
    onMouseOver: function(label){
        label.on('mouseenter', function () {
            console.log('hover');
            var key = label.attr('data-key');

            var parent = jQuery(window.parent.document);
            var previewWindow = parent.find(".inbound-customizer-preview").contents();
            var matchingEl = previewWindow.find('[data-key="'+key+'"]');
            if(matchingEl){
                matchingEl.css({
                    'outline': '1px solid red'
                })
            }
            //console.log(test)
            /* Draw outline here */
        });
    },
    onMouseLeave: function(label){
        label.on('mouseleave', function () {
            console.log('mouseout')
            var key = label.attr('data-key');

            var parent = jQuery(window.parent.document);
            var previewWindow = parent.find(".inbound-customizer-preview").contents();
            var matchingEl = previewWindow.find('[data-key="'+key+'"]');

            if(matchingEl){
                matchingEl.css({
                    'outline': 'none'
                })
            }

            //console.log(test)
            /* Draw outline here */
        });
    }

  };

  return EditorCode;

})();

jQuery(document).ready(function($) {
    // start
    InboundCustomizerEditor.init();
    /* on page load, hide overlay if visible */
    InboundCustomizerEditor.hideOverlay();
    /* show area that is being edited in preview window */
    InboundCustomizerEditor.attachHoverListeners();
});



