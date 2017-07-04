/**
 * Marketing Button JS
 */
var MarketingButton = (function () {

  var _privateMethod = function () {};

  var inbound_buttons_loaded = false;
  var Public = {
    init: function () {
        // add listeners to iframes
        this.waitForEditorLoad();
        this.attachHandlers();
    },
    attachHandlers: function(){
        this.closePopupHandler();
        this.triggerPopupHandler();
        this.insertShortcodeHandler();
        this.launchShortcodeWindow();
        this.backButtonHandler();
    },
    triggerPopupHandler: function() {
        var that = this;
        jQuery("body").on('click', '.open-marketing-button-popup', function (e) {
            e.preventDefault();
            var id = jQuery(this).attr('data-editor');
            jQuery('#iframe-target').attr('current-editor', id);

            if(document.getElementById(id) && document.getElementById(id).parentNode.parentNode.parentNode.style.display !== "none") {
                console.log('iframe display');
            } else {
                id = id.replace("_ifr", "");
                jQuery('#iframe-target').attr('current-editor', id);
                console.log('textarea display');
            }

            console.log('editor id', id);
            /*var pos = that.getCursorPosition(iframeTarget);*/

            /* Run popup here */
            jQuery.magnificPopup.open({
              items: {
                src: '#inbound-marketing-popup', // can be a HTML string, jQuery object, or CSS selector
                type: 'inline',
                callbacks: {
                    open: function() {
                      jQuery('.inbound-short-list').show();
                    },
                    close: function() {
                      jQuery("#iframe-target").html('');
                    }
                }
              }
            });

        });
        // on marketing button click, grab ID to insert to
    },
    waitForEditorLoad: function() {
        var that = this;
        jQuery(".acf_postbox .field_type-wysiwyg iframe")
            .waitUntilExists(function(){
                console.log('wait');
                if(!inbound_buttons_loaded) {
                    // do stuff with editor

                    that.addButtonsToACFNormal();

                    inbound_buttons_loaded = true;
                }
            });
    },
    launchShortcodeWindow: function(){
        jQuery(".launch-marketing-shortcode").on('click', function (e) {
            var $this = jQuery(this);
            var type = $this.attr('data-launch-sc');
            var url = inbound_load.image_dir + 'popup.php?popup=' + type + '&width=' + 900 + "&path=" + encodeURIComponent(inbound_load.image_dir);
            //$('shortcode-frame').attr('href', url);
            // hide list
            jQuery('.inbound-short-list').hide();
            var iframe = "<iframe src="+url+">";
            //$('#iframe-target').html('');
            //$('#iframe-target').html(iframe);
            jQuery.ajax({
              url: url,
              success:function(data){
                jQuery('#iframe-target').html(data);
              }
            });
        });
    },
    /* Add buttons to normal ACF */
    addButtonsToACFNormal: function(){
        console.log('add buttons');
        jQuery('.acf_postbox .field_type-wysiwyg').each(function(){
            var $this = jQuery(this);
            var label = $this.find('label');
            var iframeID = $this.find('iframe').attr('id');
            //console.log('iframe', iframeID);
            var marButton = '<a data-editor="'+iframeID+'" href="#inbound-marketing-popup" class="button inbound-marketing-button open-marketing-button-popup" title="Marketing"><span class="wp-media-buttons-icon" id="inboundnow-media-button"></span>Marketing</a>';
            jQuery(marButton).appendTo(label);
        });

    },

    insertShortcodeHandler: function() {
        var that = this;
        jQuery("body").on('click', '#marketing-insert-shortcode', function () {
             console.log('Insert the shortcode dudddee');
             // insert into content
             var shortcode = jQuery('#_inbound_shortcodes_newoutput').html();
             var id = jQuery('#iframe-target').attr('current-editor');

             if(id.indexOf("_ifr") > -1) {
                var iframeTarget = document.getElementById(id).contentWindow.document.body;
                var type = "iframe";
             } else {
                var iframeTarget = jQuery("#" + id);
                var type = "textarea";
             }

             setTimeout(function() {

                  if(type === "iframe") {
                    that.insertContent(shortcode, iframeTarget);
                  } else {
                    that.insertTextAreaContent(shortcode, iframeTarget);
                  }

                  jQuery.magnificPopup.close();

             }, 300);
        });
    },
    closePopupHandler: function() {
        var that = this;
        jQuery("body").on('click', '#cancel_marketing_button', function () {
                jQuery.magnificPopup.close();
                jQuery("#iframe-target").html('');
                jQuery('.inbound-short-list').show();
        });
    },
    insertTextAreaContent: function(text, selector) {
          var cursorPos = selector.prop('selectionStart');
          var v = selector.val();
          var textBefore = v.substring(0,  cursorPos );
          var textAfter  = v.substring( cursorPos, v.length );
          selector.val( textBefore + text + textAfter );
    },
    insertContent: function(text, iframe) {
                    var sel, range, html;
                    var doc = iframe.ownerDocument || iframe.document;
                    var win = doc.defaultView || doc.parentWindow;
                    sel = win.getSelection();
                    if (sel && sel.rangeCount > 0) {
                        console.log('Content inserted!');
                        range = sel.getRangeAt(0);
                        range.deleteContents();
                        var textNode = document.createTextNode(text);
                        range.insertNode(textNode);
                        range.setStartAfter(textNode);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } else {
                        console.log('havent clicked in box yet');
                        /* focus for the user to insert the content */
                        iframe.focus();
                        console.log('run this again');
                        this.insertContent(text, iframe);
                    }
    },
    backButtonHandler: function() {
        jQuery("body").on('click', '.marketing-back-button', function () {
             // toggle display
             jQuery("#iframe-target").html('');
             jQuery('.select2-drop').remove();
             jQuery('.inbound-short-list').show();
        });
    },
    getCursorPosition: function (iframe) {
        var caretOffset = 0,
        doc = iframe.ownerDocument || iframe.document,
        win = doc.defaultView || doc.parentWindow,
        sel;

        if (typeof win.getSelection != "undefined") {
            sel = win.getSelection();
            if (sel.rangeCount > 0) {
                var range = win.getSelection().getRangeAt(0);
                var preCaretRange = range.cloneRange();
                preCaretRange.selectNodeContents(iframe);
                preCaretRange.setEnd(range.endContainer, range.endOffset);
                caretOffset = preCaretRange.toString().length;
            }
        } else if ( (sel = doc.selection) && sel.type != "Control") {
            var textRange = sel.createRange();
            var preCaretTextRange = doc.body.createTextRange();
            preCaretTextRange.moveToElementText(iframe);
            preCaretTextRange.setEndPoint("EndToEnd", textRange);
            caretOffset = preCaretTextRange.text.length;
        }

        return caretOffset;
    }
  };

  return Public;

})();

jQuery(document).ready(function($) {
    MarketingButton.init();

});

(function ($) {

/**
* @function
* @property {object} jQuery plugin which runs handler function once specified element is inserted into the DOM
* @param {function} handler A function to execute at the time when the element is inserted
* @param {bool} shouldRunHandlerOnce Optional: if true, handler is unbound after its first invocation
* @example $(selector).waitUntilExists(function);
*/

$.fn.waitUntilExists = function (handler, shouldRunHandlerOnce, isChild) {
    var found       = 'found';
    var $this       = $(this.selector);
    var $elements   = $this.not(function () { return $(this).data(found); }).each(handler).data(found, true);

    if (!isChild) {
        (window.wait_until_exists = window.wait_until_exists || {})[this.selector] =  window.setInterval(function () {
                $this.waitUntilExists(handler, shouldRunHandlerOnce, true);
            }, 500)
        ;
    } else if (shouldRunHandlerOnce && $elements.length) {
        window.clearInterval(window.wait_until_exists[this.selector]);
    }

    return $this;
}

}(jQuery));
