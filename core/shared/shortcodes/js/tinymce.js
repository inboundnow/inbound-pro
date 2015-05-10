(function() {

// Check Inbound Active Plugins
var indexOf = function(needle) {
    if(typeof Array.prototype.indexOf === 'function') {
        indexOf = Array.prototype.indexOf;
    } else {
        indexOf = function(needle) {
            var i = -1, index = -1;

            for(i = 0; i < this.length; i++) {
                if(this[i] === needle) {
                    index = i;
                    break;
                }
            }

            return index;
        };
    }

    return indexOf.call(this, needle);
};

var active_plugins = inbound_load.inbound_plugins,
    cta_check = 'cta',
    cta_status = indexOf.call(active_plugins, cta_check),
    lp_check = 'landing-pages',
    lp_status = indexOf.call(active_plugins, lp_check),
    leads_check = 'leads',
    leads_status = indexOf.call(active_plugins, leads_check);
// End Check Active Plugins
if (leads_status > -1) {
	//console.log("leads on");
}
if (lp_status > -1) {
	//console.log("lp on");
}
if (cta_status > -1) {
	//console.log("cta on");
}



/* Broken from 3.9 forward
var shortcode_addons = ["landing-pages","cta","leads"]; // Addon example
	tinyMCE.create('tinymce.plugins.InboundShortcodes', {

		init: function(ed, url) {
			ed.addCommand('InboundShortcodesPopup', function(a, params) {
				var popup = params.identifier;
				tb_show( inbound_load.pop_title, inbound_load.image_dir + 'popup.php?popup=' + popup + '&width=' + 900 + "&path=" + inbound_load.image_dir);
				// ajax call
				var userID = jQuery("#user-id").val();
				var clicked = _inbound.utils.readCookie( "inbound_shortcode_trigger");
				console.log("CLICKED");
				if (clicked != "true") {
					jQuery.ajax({
					  type: 'POST',
					  url: ajaxurl,
					  data: {
						action: 'inbound_shortcode_prompt_ajax',
						user_id: userID
					  },
					  success: function(data){
						var self = this;
						console.log('ran notice');
					   }
					});
				}
				_inbound.utils.readCookie( "inbound_shortcode_trigger", true, { path: '/', expires: 365 });
			});
		},
		createControl: function(btn, e) {
			if (btn == 'InboundShortcodesButton') {
				var a = this;

				// adds the tinymce button
				btn = e.createSplitButton('InboundShortcodesButton', {
					title: 'Insert Shortcode',
					image: inbound_load.image_dir + 'shortcodes-blue.png',
					icons: true
				});

				// adds the dropdown to the button
				btn.onRenderMenu.add(function(c, b) {
					b.add({title : 'Inbound Form Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					a.addWithPopup( b, 'Build a Form', 'forms' );
					a.addWithPopup( b, 'Insert Existing Form', 'quick-forms' );
					a.addWithPopup( b, 'Build a Button', 'button' );
					if (cta_status > -1) {
					b.add({title : 'Call to Action Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					a.addWithPopup( b, 'Insert Call to Action', 'call-to-action' ); // to to CTA

					}
					if (lp_status > -1) {
					//b.add({title : 'Landing Page Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					//a.addWithPopup( b, 'Insert Landing Page Lists', 'landing_pages' );
					}
					b.add({title : 'Inbound Style Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					a.addWithPopup( b, 'Social Share', 'social-share' );
					a.addWithPopup( b, 'Insert Icon List', 'lists' );
					a.addWithPopup( b, 'Insert Columns', 'columns' );


					/* Loop for Addon Shortcode KEEP
					myArray = shortcode_addons;
					for(i=0; i<myArray.length; i++) {
						a.addWithPopup( b, myArray[i], myArray[i] );
					}
					*/
					/**
					//a.addWithPopup( b, 'Insert Button Shortcode',  'button' );
					//a.addWithPopup( b, 'Alert', 'alert' );
					//a.addWithPopup( b, 'Call Out', 'callout' );
					//b.add({title : 'Layout Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

					//a.addWithPopup( b, 'Content Box', 'content_box' );
					//a.addWithPopup( b, 'Divider', 'divider' );
					//a.addWithPopup( b, 'Tabs', 'tabs' );

					// Need forking
					//a.addWithPopup( b, 'Heading', 'heading' );
					//a.addWithPopup( b, 'Icon', 'icon' );
					//a.addWithPopup( b, 'Intro', 'intro' );
					//a.addWithPopup( b, 'Lead Paragraph', 'leadp' );

					//a.addWithPopup( b, 'Map', 'gmap' );

					//a.addWithPopup( b, 'Pricing', 'pricing' );
					//a.addWithPopup( b, 'Profile', 'profile' );
					//a.addWithPopup( b, 'Social Links', 'social_links' );

					//a.addWithPopup( b, 'Teaser', 'teaser' );

					//a.addWithPopup( b, 'Video', 'video' );
				});

				return btn;
			}

			return null;
		},

		addWithPopup: function(ed, title, id, sub) {
			ed.add({
				title: title,
				icon: 'editor-icon-' + id,
				onclick: function() {
					tinyMCE.activeEditor.execCommand('InboundShortcodesPopup', false, {
						title: title,
						identifier: id
					});
				}
			});
			// http://www.tinymce.com/wiki.php/API3:class.tinymce.ui.DropMenu
			if (typeof (sub) != "undefined" && sub != null && sub != "") {
				var sub1 = ed.addMenu({title : 'Menu 3'});
				sub1.add({title : 'Menu 1.1', onclick : function() {
				    alert('Item 1.1 was clicked.');
				}});
			}

		},

		addImmediate: function(ed, title, sc) {
			ed.add({
				title: title,

				onclick: function() {
					tinyMCE.activeEditor.execCommand('mceInsertContent', false, sc);
				}
			});
		},

		getInfo: function() {
			return {
				longname: 'Inbound Shortcodes',
				author: 'David Wells',
				authorurl: 'http://www.inboundnow.com/',
				infourl: 'http://www.inboundnow.com/',
				version: '1.0'
			};
		}

	});

	tinymce.PluginManager.add('InboundShortcodes', tinymce.plugins.InboundShortcodes);
*/
})();