/**
 * Preset resources for Social Locker
 * for jQuery: http://onepress-media.com/plugin/social-locker-for-jquery/get
 * for Wordpress: http://onepress-media.com/plugin/social-locker-for-wordpress/get
 *
 * Copyright 2012, OnePress, http://onepress-media.com/portfolio
 * Help Desk: http://support.onepress-media.com/
 */

(function ($) {

    /**
    * Text resources.
    */

    if (!$.onepress) $.onepress = {};
    if (!$.onepress.lang) $.onepress.lang = {};

    $.onepress.lang.socialLock = {

        defaultHeader: "This content is locked!",
        defaultMessage: "Please support us, use one of the buttons below to unlock the content.",
        orWait: 'or wait',
        seconds: 's',   
        close: 'Close'
    };

    /**
    * Presets for styles.
    * You can add some options that will be applied when a specified css class is added to the locer.
    */

    if (!$.onepress.presets) $.onepress.presets = {};

    $.onepress.presets['ui-social-locker-starter'] = {
        buttons: {
            layout: 'horizontal',
            counter: true
        },
        effects: {
            flip: false
        }
    };

    $.onepress.presets['ui-social-locker-secrets'] = {        
        buttons: {
            layout: 'horizontal',
            counter: true
        },
        effects: {
            flip: true
        }
    };
    
    $.onepress.presets['ui-social-locker-dandyish'] = {        
        buttons: {
            layout: 'vertical',
            counter: true
        },
        effects: {
            flip: false
        }
    };

    $.onepress.presets['ui-social-locker-glass'] = {
        buttons: {
            layout: 'horizontal',
            counter: true
        },
        effects: {
            flip: false
        }
    };

})(jQuery);;;

/**
* Helper Tools:
* - cookies getter/setter
* - md5 hasher
* - lightweight widget factory
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';

    if (!$.onepress) $.onepress = {};
    if (!$.onepress.tools) $.onepress.tools = {};

    /*
    * Cookie's function.
    * Allows to set or get cookie.
    *
    * Based on the plugin jQuery Cookie Plugin
    * https://github.com/carhartl/jquery-cookie
    *
    * Copyright 2011, Klaus Hartl
    * Dual licensed under the MIT or GPL Version 2 licenses.
    * http://www.opensource.org/licenses/mit-license.php
    * http://www.opensource.org/licenses/GPL-2.0
    */
    $.onepress.tools.cookie = $.onepress.tools.cookie || function (key, value, options) {

        // Sets cookie
        if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
            options = $.extend({}, options);

            if (value === null || value === undefined) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '',
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // Gets cookie.
        options = value || {};
        var decode = options.raw ? function (s) { return s; } : decodeURIComponent;

        var pairs = document.cookie.split('; ');
        for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
            if (decode(pair[0]) === key) return decode(pair[1] || '');
        }
        return null;
    };

    /*
    * jQuery MD5 Plugin 1.2.1
    * https://github.com/blueimp/jQuery-MD5
    *
    * Copyright 2010, Sebastian Tschan
    * https://blueimp.net
    *
    * Licensed under the MIT license:
    * http://creativecommons.org/licenses/MIT/
    * 
    * Based on
    * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
    * Digest Algorithm, as defined in RFC 1321.
    * Version 2.2 Copyright (C) Paul Johnston 1999 - 2009
    * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
    * Distributed under the BSD License
    * See http://pajhome.org.uk/crypt/md5 for more info.
    */
    $.onepress.tools.hash = $.onepress.tools.hash || function (str) {

        var hash = 0;
        if (str.length == 0) return hash;
        for (var i = 0; i < str.length; i++) {
            var charCode = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + charCode;
            hash = hash & hash;
        }
        hash = hash.toString(16);
        hash = hash.replace("-", "0");

        return hash;
    };

    /**
    * Checks does a browers support 3D transitions:
    * https://gist.github.com/3794226
    */
    $.onepress.tools.has3d = $.onepress.tools.has3d || function () {

        var el = document.createElement('p'),
            has3d,
            transforms = {
                'WebkitTransform': '-webkit-transform',
                'OTransform': '-o-transform',
                'MSTransform': '-ms-transform',
                'MozTransform': '-moz-transform',
                'Transform': 'transform'
            };

        // Add it to the body to get the computed style
        document.body.insertBefore(el, null);

        for (var t in transforms) {
            if (el.style[t] !== undefined) {
                el.style[t] = 'translate3d(1px,1px,1px)';
                has3d = window.getComputedStyle(el).getPropertyValue(transforms[t]);
            }
        }

        document.body.removeChild(el);
        return (has3d !== undefined && has3d.length > 0 && has3d !== "none");
    };

    /**
    * Returns true if a current user use a touch device
    * http://stackoverflow.com/questions/4817029/whats-the-best-way-to-detect-a-touch-screen-device-using-javascript
    */
    $.onepress.isTouch = $.onepress.isTouch || function () {

        return !!('ontouchstart' in window) // works on most browsers 
            || !!('onmsgesturechange' in window); // works on ie10
    };

    /**
    * OnePress Widget Factory.
    * Supports:
    * - creating a jquery widget via the standart jquery way
    * - call of public methods.
    */
    $.onepress.widget = function (pluginName, pluginObject) {

        var factory = {

            createWidget: function (element, options) {
                var widget = $.extend(true, {}, pluginObject);

                widget.element = $(element);
                widget.options = $.extend(true, widget.options, options);

                if (widget._init) widget._init();
                if (widget._create) widget._create();

                $.data(element, 'plugin_' + pluginName, widget);
            },

            callMethod: function (widget, methodName) {
                widget[methodName] && widget[methodName]();
            }
        };

        $.fn[pluginName] = function () {
            var args = arguments;
            var argsCount = arguments.length;

            this.each(function () {

                var widget = $.data(this, 'plugin_' + pluginName);

                // a widget is not created yet
                if (!widget && argsCount <= 1) {
                    factory.createWidget(this, argsCount ? args[0] : false);

                    // a widget is created, the public method with no args is being called
                } else if (argsCount == 1) {
                    factory.callMethod(widget, args[0]);
                }
            });
        };
    };

})(jQuery);;;

/**
* Facebook Like Button widget for jQuery
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';
    if ($.fn.facebookButton) return;

    $.onepress.widget("facebookButton", {
        options: {},

        _defaults: {
            // - Properties

            onceEvent: true,

            // - Facebook Options

            // URL to like.
            url: null,
            // Display send button.
            sendButton: false,
            // App Id used to get extended contol tools (optionly).
            // You can create your own app here: https://developers.facebook.com/apps				
            appId: 0,
            // Language of the button labels. By default en_US.
            lang: 'en_US',
            // Button layout, available: standart, button_count, box_count. By default 'standart'.
            layout: 'standart',
            // Button container width in px, by default 450.
            width: 'auto',
            // Show profile pictures below the button. By default 'true'.
            showFaces: false,
            // The verb to display in the button. Only 'like' and 'recommend' are supported. By default 'like'.
            verbToDisplay: "like",
            // The color scheme of the plugin. By default 'light'.
            colorScheme: "light",
            // The font of the button. By default 'tahoma'.
            font: 'tahoma',
            // A label for tracking referrals.
            ref: null,
            comment: true,

            // set to 'count' to hide the count box
            count: 'standart',

            // - Events

            render: null,
            like: null,
            dislike: null
        },

        _create: function () {
            var self = this;

            this._prepareOptions();
            this._setupEvents();

            this.element.data('onepress-facebookButton', this);
            this._createButton();

            $.onepress.connector.connect("facebook", this.options, function (sdk) {
                sdk.render(self.element);
            });
        },

        _prepareOptions: function () {

            var values = $.extend({}, this._defaults);

            if (this.element.data('href') !== undefined) values.url = this.element.data('href');
            if (this.element.data('send') !== undefined) values.sendButton = this.element.data('send');
            if (this.element.data('layout') !== undefined) values.layout = this.element.data('layout');
            if (this.element.data('show_faces') !== undefined) values.showFaces = this.element.data('show_faces');
            if (this.element.data('width') !== undefined) values.width = this.element.data('width');
            if (this.element.data('action') !== undefined) values.verbToDisplay = this.element.data('action');
            if (this.element.data('font') !== undefined) values.font = this.element.data('font');
            if (this.element.data('colorscheme') !== undefined) values.colorScheme = this.element.data('colorscheme');
            if (this.element.data('ref') !== undefined) values.ref = this.element.data('ref');

            values = $.extend(values, this.options);

            this.options = values;
            this.url = (!this.options.url) ? window.location.href : this.options.url;
        },

        _setupEvents: function () {
            var self = this;

            $(document).bind('fb-init', function () {
                if (self.options.init) self.options.init();
            });

            $(document).bind('fb-like', function (e, url) {

                if (self.options.like && self.url == url) {
                    self.options.like(url, self);
                }
            });

            $(document).bind('fb-dislike', function (e, url) {

                if (self.options.dislike && self.url == url) {
                    self.options.dislike(url, self);
                }
            });

            $(this.element).bind('fb-render', function () {

                if (self.options.render) {
                    self.options.render(self.element, [self]);
                }
            });
        },

        /**
        * Generates an html code for the button using specified options.
        */
        _createButton: function () {

            var $wrap;

            if (!this.element.is(".fb-like")) {

                var $button = $("<div class='fake-fb-like'></div>");
                $button.data('facebook-widget', this);

                if (this.options.url) $button.attr("data-href", this.options.url);
                if (this.options.sendButton) $button.attr("data-send", this.options.sendButton);
                if (this.options.width) $button.attr("data-width", this.options.width);
                if (this.options.layout) $button.attr("data-layout", this.options.layout);
                $button.attr("data-show-faces", this.options.showFaces);

                if (this.options.verbToDisplay) $button.attr("data-action", this.options.verbToDisplay);
                if (this.options.font) $button.attr("data-font", this.options.font);
                if (this.options.colorScheme) $button.attr("data-colorscheme", this.options.colorScheme);
                if (this.options.ref) $button.attr("data-ref", this.options.ref);

                this.element.append($button);
                $wrap = this.element;

            } else {
                $wrap = $("<div></div>").append(this.element);
            }

            $wrap.addClass('ui-social-button ui-facebook ui-facebook-like');
            if (this.options.count == 'none') {
                $wrap.addClass('ui-facebook-like-count-none');
                $wrap.addClass('ui-facebook-like-' + this.options.lang);
            }
        },

        getHtmlToRender: function () {

            if (this.element.is(".fb-like")) return this.element.parent();
            return this.element;
        }
    });

})(jQuery);;;

/**
* Google Plus One widget for jQuery
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';
    if ($.fn.googleButton) return;
    
    $.onepress.widget("googleButton", {
        options: {},

        _defaults: {

            // - Google One Plus Options

            // Language of the button labels. By default en-US.
            // https://developers.google.com/+/plugins/+1button/#available-languages
            lang: 'en-US',

            // URL to plus one.
            url: null,
            // small, medium, standard, tall (https://developers.google.com/+/plugins/+1button/#button-sizes)
            size: null,
            // Sets the annotation to display next to the button.
            annotation: null,
            // Button container width in px, by default 450.
            width: null,
            // Sets the horizontal alignment of the button assets within its frame.
            align: "left",
            // Sets the preferred positions to display hover and confirmation bubbles, which are relative to the button.
            // comma-separated list of top, right, bottom, left
            expandTo: "",
            // To disable showing recommendations within the +1 hover bubble, set recommendations to false.    
            recommendations: true,

            // - Events

            render: null,
            like: null,
            dislike: null
        },

        _create: function () {
            var self = this;

            this._prepareOptions();
            this._setupEvents();

            this.element.data('onepress-googleButton', this);
            this._createButton();

            $.onepress.connector.connect("google", this.options, function (sdk) {
                sdk.render(self.element);
            });
        },

        _prepareOptions: function () {

            var values = $.extend({}, this._defaults);

            if (this.element.data('href') !== undefined) values.url = this.element.data('href');
            if (this.element.data('url') !== undefined) values.url = this.element.data('url');
            if (this.element.data('size') !== undefined) values.size = this.element.data('size');
            if (this.element.data('annotation') !== undefined) values.annotation = this.element.data('annotation');
            if (this.element.data('align') !== undefined) values.align = this.element.data('align');
            if (this.element.data('width') !== undefined) values.width = this.element.data('width');
            if (this.element.data('expandTo') !== undefined) values.expandTo = this.element.data('expandTo');
            if (this.element.data('recommendations') !== undefined) values.recommendations = this.element.data('recommendations');

            values = $.extend(values, this.options);
            this.options = values;

            this.url = (!this.options.url) ? window.location : this.options.url;
        },

        _setupEvents: function () {
            var self = this;

            $(document).bind('gp-like', function (e, url) {

                if (self.options.like &&  (self.url == url || (self.url + '/') == url)) {
                    self.options.like(url, self);
                }
            });

            $(document).bind('gp-dislike', function (e, url) {

                if (self.options.dislike && (self.url == url || (self.url + '/') == url)) {
                    self.options.dislike(url, self);
                }
            });

            $(this.element).bind('gl-render', function () {

                if (self.options.render) {
                    self.options.render(self.element, [self]);
                }
            });
        },

        /**
        * Generates an html code for the button using specified options.
        */
        _createButton: function () {

            var $wrap;

            if (!this.element.is(".g-plusone")) {

                var $button = $("<div class='fake-g-plusone'></div>");
                $button.data('facebook-widget', this);

                if (this.options.url) $button.attr("data-href", this.options.url);
                if (this.options.size) $button.attr("data-size", this.options.size);
                if (this.options.annotation) $button.attr("data-annotation", this.options.annotation);
                if (this.options.align) $button.attr("data-align", this.options.align);
                if (this.options.expandTo) $button.attr("data-expandTo", this.options.expandTo);
                if (this.options.recommendations) $button.attr("data-recommendations", this.options.recommendations);

                this.element.append($button);
                $wrap = this.element;

            } else {
                $wrap = $("<div></div>").append(this.element);
            }

            $wrap.addClass('ui-social-button ui-google ui-goole-plusone');
        },

        getHtmlToRender: function () {

            if (this.element.is(".g-plusone")) return this.element.parent();
            return this.element;
        }
    });

})(jQuery);;;

/**
* Twitter Button widget for jQuery
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';
    if ($.fn.twitterButton) return;
    
    $.onepress.widget("twitterButton", {

        options: {},

        _defaults: {

            // - Properties

            // tweet or follow button
            type: 'tweet',

            // - Twitter Options

            // URL of the page to share.
            url: null,
            // Screen name of the user to attribute the Tweet to
            via: null,
            // Default Tweet text
            text: null,
            // Related accounts
            related: null,
            // Count box position (none, horizontal, vertical)
            count: 'horizontal',
            // The language for the Tweet Button
            lang: 'en',
            // URL to which your shared URL resolves
            counturl: null,
            // (left or right)
            alignment: null,
            // The size of the rendered button (medium, large)
            size: 'large',

            // - Events

            like: null,
            tweet: null
        },

        _create: function () {
            var self = this;

            this._prepareOptions();
            this._setupEvents();

            this.element.data('onepress-twitterButton', this);
            this._createButton();

            $.onepress.connector.connect("twitter", this.options, function (sdk) {
                sdk.render(self.element);
            });
            
            /*
            $(function () {
                $.onepress.connector.connect("twitter", this.options);
            });
            */
        },

        _prepareOptions: function () {

            var values = $.extend({}, this._defaults);

            for (var prop in this._defaults) {
                if (this.element.data(prop) !== undefined) values[prop] = this.element.data(prop);
            }

            this.options = $.extend(values, this.options);

            // Url
            if (!this.options.url && $("link[rel='canonical']").length > 0)
                this.options.url = $("link[rel='canonical']").attr('href');

            this.url = this.options.url || window.location.href;
        },

        _setupEvents: function () {
            var self = this;

            $(document).bind('tw-tweet', function (e, target, data) {
                if (self.options.type != 'tweet') return;

                var url = $(target).parent().attr('data-url-to-compare');
                if (self.url == url) {
                    self.options.like && self.options.like(url, target, data, self);
                    self.options.tweet && self.options.tweet(url, target, data, self);
                }
            });
        },

        /**
        * Generates an html code for the button using specified options.
        */
        _createButton: function () {

            var $wrap;

            // What will title be used?
            var title = 'Tweet';

            this.button = $("<a href='https://twitter.com/share'>" + title + "</a>");
            this.button.data('twitter-widget', this);

            this.button.attr("data-url", this.url);
            if (this.options.via) this.button.attr("data-via", this.options.via);
            if (this.options.text) this.button.attr("data-text", this.options.text);
            if (this.options.related) this.button.attr("data-related", this.options.related);
            if (this.options.count) this.button.attr("data-count", this.options.count);
            if (this.options.lang) this.button.attr("data-lang", this.options.lang);
            if (this.options.counturl) this.button.attr("data-counturl", this.options.counturl);
            if (this.options.alignment) this.button.attr("data-alignment", this.options.alignment);
            if (this.options.size) this.button.attr("data-size", this.options.size);

            this.element.addClass('ui-social-button ui-twitter');

            this.element.addClass('ui-twitter-tweet');
            this.button.addClass('twitter-share-button');

            this.element.attr('data-url-to-compare', this.options.url);
            this.element.append(this.button);
        },

        getHtmlToRender: function () {
            return this.button;
        }
    });

})(jQuery);;;

/**
* OnePress Local State Provider
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';

    if (!$.onepress) $.onepress = {};
    if (!$.onepress.providers) $.onepress.providers = {};

    /**
    * Returns a state provide for the Strict Mode.
    */
    $.onepress.providers.clientStoreStateProvider = function (postfix, url, demo, useCookies, cookiesLifetime) {

        this.name = postfix;

        this.useCookies = useCookies;
        this.cookiesLifetime = cookiesLifetime;

        this.url = url;
        this.identity = "page_" + $.onepress.tools.hash(this.url) + "_hash_" + postfix;

        /**
        * Does the provider contain an unlocked state?
        */
        this.isUnlocked = function () {
            if (demo) return false;
            return (this._getValue()) ? true : false;
        };

        /**
        * Does the provider contain a locked state?
        */
        this.isLocked = function () {
            return !this.isUnlocked();
        };

        /**
        * Gets a state and calls the callback with the one.
        */
        this.getState = function (callback) {
            if (demo) return callback(false);
            callback(this.isUnlocked());
        };

        /**
        * Sets state of a locker to provider.
        */
        this.setState = function (value) {
            if (demo) return true;
            return value == "unlocked" ? this._setValue() : this._removeValue();
        };

        this._setValue = function () {
            var self = this;

            return localStorage && !this.useCookies
                ? localStorage.setItem(this.identity, true)
                : $.onepress.tools.cookie(this.identity, true, { expires: self.cookiesLifetime, path: "/" });
        };

        this._getValue = function () {

            if (localStorage && !this.useCookies) {

                var value = localStorage.getItem(this.identity);
                if (value) return value;

                value = $.onepress.tools.cookie(this.identity);
                if (value) this._setValue();

                return value;
            }

            return $.onepress.tools.cookie(this.identity);

        };

        this._removeValue = function () {
            if (localStorage) localStorage.removeItem(this.identity);
            $.onepress.tools.cookie(this.identity, null);
        };
    };

})(jQuery);;;

/**
* SDK Connector for Social Networks:
* - Facebook
* - Twitter
* - Google
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';

    if (!$.onepress) $.onepress = {};

    $.onepress.connector = $.onepress.connector || {

        sdk: [

        // --
        // Facebook 
        // --
            {
            name: 'facebook',
            url: '//connect.facebook.net/{lang}/all.js',
            scriptId: 'facebook-jssdk',
            hasParams: true,
            isRender: true,

            isLoaded: function () {
                return (typeof (window.FB) === "object");
            },

            pre: function () {

                // root for facebook sdk
                $("#fb-root").length == 0 && $("<div id='fb-root'></div>").appendTo($("body"));

                // sets sdk language
                var lang = (this.options && this.options.lang) || "en_US";
                this.url = this.url.replace("{lang}", lang);
            },

            createEvents: function (isLoaded) {
                var self = this;

                var load = function () {

                    window.FB.init({
                        appId: (self.options && self.options.appId) || null,
                        status: true,
                        cookie: true,
                        xfbml: true
                    });

                    window.FB.Event.subscribe('edge.create', function (response) {
                        $(document).trigger('fb-like', [response]);
                    });

                    window.FB.Event.subscribe('edge.remove', function (response) {
                        $(document).trigger('fb-dislike', response);
                    });

                    window.FB.Event.subscribe('xfbml.render', function () {
                        $(document).trigger('fb-render');
                    });

                    // The initialization is executed only one time.
                    // Any others attempts will call an empty function.
                    window.FB.init = function () { };
                    $(document).trigger(self.name + '-init');
                };

                if (isLoaded) { load(); return; }

                if (window.fbAsyncInit) var predefined = window.fbAsyncInit;
                window.fbAsyncInit = function () {
                    load(); predefined && predefined();
                    window.fbAsyncInit = function () { };
                };
            },

            render: function (widget) {

                var api = widget.data('onepress-facebookButton');
                if (!api) return;

                var $html = api.getHtmlToRender();
                $html.find('.fake-fb-like').addClass('fb-like');
                window.FB.XFBML.parse($html[0]);
                widget.trigger('fb-render');
            }
        },

        // --
        // Twitter 
        // --
        {
        name: 'twitter',
        url: '//platform.twitter.com/widgets.js',
        scriptId: 'twitter-wjs',
        hasParams: false,
        isRender: true,

        pre: function () {

            var canonical = ($("link[rel='canonical']").length > 0)
				    ? $("link[rel='canonical']").attr('href')
				    : null;

            $(".twitter-share-button").each(function (index, item) {
                var $item = $(item);
                var $target = $(item).parent();

                if ($target.attr('data-url-to-compare')) return;

                var url = $item.attr("data-url");
                if (!url && canonical) url = canonical;
                url = (!url) ? window.location : url;

                $item.parent().attr('data-url-to-compare', url);
            });
        },

        isLoaded: function () {
            return (typeof (window.__twttrlr) !== "undefined");
        },

        createEvents: function (isLoaded) {
            var self = this;

            var load = function () {

                window.twttr.events.bind('tweet', function (event) {
                    $(document).trigger('tw-tweet', [event.target, event.data]);
                });

                window.twttr.events.bind('follow', function (event) {
                    $(document).trigger('tw-follow', [event.target, event.data]);
                });

                $(document).trigger(self.name + '-init');
            };

            if (isLoaded) { load(); return; }

            if (!window.twttr) window.twttr = {};
            if (!window.twttr.ready) window.twttr = $.extend(window.twttr, { _e: [], ready: function (f) { this._e.push(f); } });
            
            twttr.ready(function (twttr) { load(); });
        },

        /**
        * A twitter buttons works by other way.
        * When the script loaded 
        */
        render: function (widget) {

            var api = widget.data('onepress-twitterButton');
            if (!api) return;

            var $html = api.getHtmlToRender().parent();
            var attemptCounter = 5;

            // Chrome fix
            // If there is SDK script on the same page that is loading now when a tweet button will not appear.
            // Setup special timeout function what will check 5 times when we can render the twitter button.
            var timoutFunction = function () {
                if ($html.find('iframe').length > 0) return;

                if (window.twttr.widgets && window.twttr.widgets.load) {
                    window.twttr.widgets.load($html[0]);
                    widget.trigger('tw-render');
                } else {
                    if (attemptCounter <= 0) return;
                    attemptCounter--;

                    setTimeout(function () {
                        timoutFunction();
                    }, 1000);
                }
            };

            timoutFunction();
        }
    },

    // --
    // Google 
    // --
        {
        name: 'google',
        url: '//apis.google.com/js/plusone.js',
        scriptId: 'google-jssdk',
        hasParams: true,
        isRender: true,

        pre: function () {

            // sets sdk language
            var lang = (this.options && this.options.lang) || "en";
            window.___gcfg = window.___gcfg || { lang: lang };

            window.onepressPlusOneCallback = function (data) {

                if (data.state == "on") {
                    $(document).trigger('gp-like', [data.href]);

                } else if (data.state == "off") {

                    $(document).trigger('gp-dislike', [data.href]);
                }

            };
        },

        isLoaded: function () {
            return (typeof (window.gapi) === "object");
        },


        render: function (widget) {

            var api = widget.data('onepress-googleButton');
            if (!api) return;

            var self = this;

            setTimeout(function () {
                var $html = api.getHtmlToRender();
                self._addCallbackToControl($html);
                $html.find('.fake-g-plusone').addClass('g-plusone');
                window.gapi.plusone.go($html[0]);
                widget.trigger('gp-render');
            }, 100);
        },

        _addCallbackToControl: function ($control) {

            var $elm = (!$control.is(".g-plusone")) ? $control.find(".fake-g-plusone") : $control;

            var callback = $elm.attr("data-callback");
            if (callback && callback != "onepressPlusOneCallback") {
                var newCallback = "__plusone_" + callback;
                window[newCallback] = function (data) {
                    window[callback](data);
                    window.onepressPlusOneCallback(data);
                };
                $elm.attr("data-callback", newCallback);
            } else {
                $elm.attr("data-callback", "onepressPlusOneCallback");
            }
        }
    }
    ],

    // contains dictionary sdk_name => is_sdk_ready (bool)
    _ready: {},

    // contains dictionaty sdk_name => is_sdk_connected (bool)
    _connected: {},

    /**
    * Get SDK object by its name.
    */
    getSDK: function (name) {

        for (var index in this.sdk) if (this.sdk[index].name == name) return this.sdk[index];
        return null;
    },

    /**
    * Checks whether a specified SDK is connected (sdk script is included into a page).
    */
    isConnected: function (sdk) {
        return ($("#" + sdk.scriptId).length > 0 || $("script[src='*" + sdk.url + "']").length > 0);
    },

    /**
    * Gets loading SDK script on a page.
    */
    getLoadingScript: function (sdk) {
        var byId = $("#" + sdk.scriptId);
        var byScr = $("script[src='*" + sdk.url + "']");
        return (byId.length > 0) ? byId : byScr;
    },

    /**
    * Checks whether a specified SQK is loaded and ready to use.
    */
    isLoaded: function (sdk) {
        return this.isConnected(sdk) && sdk.isLoaded && sdk.isLoaded();
    },

    /**
    * Connects SKD if it's needed then calls callback.
    */
    connect: function (name, options, callback) {
        var self = this, sdk = this.getSDK(name);

        if (!sdk) {
            console && console.log('Invalide SDK name: ' + name);
            return;
        }

        sdk.options = options;

        // fire or bind callback
        if (callback) this._ready[name]
                ? callback(sdk)
                : $(document).bind(name + "-init", function () { callback(sdk); });

        if (this._connected[name]) return;

        // sets the default method if it's not specified
        if (!sdk.createEvents) {
            sdk.createEvents = function (isLoaded) {
                var selfSDK = this;

                var load = function () {
                    $(document).trigger(selfSDK.name + '-init');
                };

                if (isLoaded) { load(); return; }

                $(document).bind(selfSDK.name + "-script-loaded", function () {
                    load();
                });
            };
        }

        if (sdk.pre) sdk.pre();

        var loaded = this.isLoaded(sdk);
        var connected = this.isConnected(sdk);

        $(document).bind(name + "-init", function () { self._ready[name] = true; });

        // subscribes to events
        sdk.createEvents(loaded);

        // conencts sdk
        if (!connected) {

            var scriptConnection = function () {

                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.id = sdk.scriptId;
                script.src = sdk.url;

                var bodyElement = document.getElementsByTagName('body')[0];
                bodyElement.appendChild(script);
            };

            sdk.isRender
                ? scriptConnection()
                : $(function () { $(function () { scriptConnection(); }); });
        }

        // subsribes to onload event
        if (!loaded) {

            var loadingScript = this.getLoadingScript(sdk)[0];

            if (loadingScript) {
                loadingScript.onreadystatechange = loadingScript.onload = function () {
                    var state = loadingScript.readyState;
                    if ((!state || /loaded|complete/.test(state))) $(document).trigger(sdk.name + '-script-loaded');
                };
            }
        }

        this._connected[name] = true;
    }
};

})(jQuery);;;

/**
* Social Locker
* for jQuery: http://onepress-media.com/plugin/social-locker-for-jquery/get
* for Wordpress: http://onepress-media.com/plugin/social-locker-for-wordpress/get
*
* Copyright 2012, OnePress, http://onepress-media.com/portfolio
* Help Desk: http://support.onepress-media.com/
*/

(function ($) {
    'use strict';
    if ($.fn.socialLock) return;

    $.onepress.widget("socialLock", {

        options: {},

        // The variable stores a current locker state.
        _isLocked: false,

        // Defauls option's values.
        _defaults: {

            // Url that used to like/tweet/plusone.
            // It's obligatory to check whether a user likes a page.
            url: null,

            // Text above the locker buttons.
            text: {
                header: $.onepress.lang.socialLock.defaultHeader,
                message: $.onepress.lang.socialLock.defaultMessage
            },

            // Extra classes added to the locker.
            style: null,

            // sets whether the locker keep the state of always appears
            demo: false,

            // Social buttons to use
            buttons: {

                // horizontal or vertical
                layout: 'horizontal',

                // an order of the buttons
                order: ["twitter", "facebook", "google"],

                // hide or show counters for the buttons
                counter: true
            },

            // --
            // Locker functionality.
            locker: {

                // Sets wheter a user may remove the locker by a cross placed at the top-right corner.
                close: false,
                // Sets a timer interval to unlock content when the zero is reached.
                // If the value is 0, the timer will not be created. 
                timer: 0,
                // Sets whether the locker appears for mobiles devides.
                mobile: true,

                // force to use cookies insted of a local storage
                useCookies: false,
                // the number of days for cookies life
                cookiesLifetime: 3560
            },

            // -
            // Content that will be showen after unlocking.
            // -
            content: null,

            // --
            // Events set
            events: {

                lock: null,
                unlock: null,
                ready: null,

                unlockByCross: null,
                unlockByTimer: null
            },

            // --
            // Locker effects
            effects: {

                // Turns on the Flip effect.
                flip: false,

                // Turns on the Highlight effect.
                highlight: true
            },

            // --
            // Facebook Options
            facebook: {
                url: null,

                // App Id used to get extended contol tools (optionly).
                // You can create your own app here: https://developers.facebook.com/apps
                appId: null,
                // Language of the button labels. By default en_US.
                lang: 'en_US',
                // The color scheme of the plugin. By default 'light'.
                colorScheme: "light",
                // The font of the button. By default 'tahoma'.
                font: 'tahoma',
                // A label for tracking referrals.
                ref: null
            },

            twitter: {
                url: null,

                // Screen name of the user to attribute the Tweet to
                via: null,
                // Default Tweet text
                text: null,
                // Related accounts
                related: null,
                // The language for the Tweet Button
                lang: 'en',
                // URL to which your shared URL resolves
                counturl: null
            },

            google: {
                url: null,

                // Language of the button labels. By default en-US.
                // https://developers.google.com/+/plugins/+1button/#available-languages
                lang: 'en-US',
                // Sets the annotation to display next to the button.
                annotation: null,
                // To disable showing recommendations within the +1 hover bubble, set recommendations to false.    
                recommendations: true
            }
        },

        /**
        * Enter point to start creating the locker. 
        */
        _create: function () {
            var self = this;

            // parse options
            this._processOptions();

            // don't show locker in ie7
            if ($.browser.msie && parseInt($.browser.version, 10) === 7) {
                this._unlock("ie7"); return;
            }

            // check mobile devices
            if (!this.options.locker.mobile && this._isMobile()) {
                this._unlock("mobile"); return;
            }
            
            // remove buttons that are not supported by mobile devices
            if (this._isMobile()) {
                var twitterIndex = $.inArray("twitter", this.options.buttons.order);
                if (twitterIndex >= 0) this.options.buttons.order.splice(twitterIndex, 1);
            }
            
            // unlock the locker if no buttons are defined
            if (this.options.buttons.order.length == 0) {
                this._unlock("nobuttons"); return;
            }

            // creates provider
            this._controller = this._createProviderController();

            // get state to decide what our next step is
            this._controller.getState(function (state) {
                state ? self._unlock("provider") : self._lock();
                self.options.events.ready && self.options.events.ready(state);
            });
        },

        /**
        * Creates and returns a controler of providers by using the options.
        */
        _createProviderController: function () {
            var self = this;
            this._providers = {};

            var totalCount = 0;

            for (var providerIndex in this.options.buttons.order) {
                var sourceName = this.options.buttons.order[providerIndex];
                if (typeof (sourceName) != 'string') continue;

                var url = this.options[sourceName].url || this.options.url || window.location.href;
                this._providers[sourceName] = new $.onepress.providers.clientStoreStateProvider(
                    sourceName, url, self.options.demo,
                    self.options.locker.useCookies, self.options.locker.cookiesLifetime);
                totalCount++;
            }

            // controller of providers
            return {

                /**
                * Gets result state for all defined providers.
                */
                getState: function (callback) {

                    var counter = totalCount;
                    var resultState = false;

                    for (var name in self._providers) {
                        var provider = self._providers[name];

                        provider.getState(function (state) {
                            counter--; resultState = resultState || state;

                            if (counter == 0) callback(resultState, provider);
                        });
                    }
                }
            };
        },

        /**
        * Processes the locker options.
        */
        _processOptions: function () {
            var style = this.options.style || this._defaults.style || 'ui-social-locker-starter';

            var options = $.extend(true, {}, this._defaults);

            // uses preset options
            if ($.onepress.presets[style]) {
                options = $.extend(true, {}, options, $.onepress.presets[style]);

                if ($.onepress.presets[style].buttons && $.onepress.presets[style].buttons.order) {
                    options.buttons.order = $.onepress.presets[style].buttons.order;
                }
            }

            // users user defined options
            options = $.extend(true, options, this.options);

            if (this.options.buttons && this.options.buttons.order) {
                options.buttons.order = this.options.buttons.order;
            }

            options.effects.flip = options.effects.flip || (options.style == 'ui-social-locker-secrets');

            if (options.buttons.layout == "vertical") {
                options.facebook.layout = "box_count";
                options.twitter.count = "vertical";
                options.twitter.size = "medium";
                options.google.size = "tall";
                options.buttons.counter = true;
            }

            if (options.buttons.layout == "horizontal") {
                options.facebook.layout = "button_count";
                options.twitter.count = "horizontal";
                options.twitter.size = "medium";
                options.google.size = "medium";

                if (!options.buttons.counter) {
                    options.twitter.count = 'none';
                    options.google.annotation = 'none';
                    options.facebook.count = 'none';
                }
            }


            if (typeof options.text != "object" || (!options.text.header && !options.text.message)) {
                options.text = { message: options.text };
            }

            if (options.text.header) {
                options.text.header = (typeof options.text.header === "function" && options.text.header(this)) ||
                                      (typeof options.text.header === "string" && $("<div>" + options.text.header + "</div>")) ||
                                      (typeof options.text.header === "object" && options.text.header.clone());
            }

            if (options.text.message) {
                options.text.message = (typeof options.text.message === "function" && options.text.message(this)) ||
                                       (typeof options.text.message === "string" && $("<div>" + options.text.message + "</div>")) ||
                                       (typeof options.text.message === "object" && options.text.message.clone());
            }

            options.locker.timer = parseInt(options.locker.timer);
            if (options.locker.timer == 0) options.locker.timer = null;

            this.options = options;
        },

        /**
        * Returns true if a current user use a mobile device, else false.
        */
        _isMobile: function () {
            return (/Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent));
        },

        /**
        * Sets an error state.
        */
        _setError: function (text) {
            this._error = true;
            this._errorText = text;

            this.locker && this.locker.hide();

            this.element.html("<strong>[Error]: " + text + "</strong>");
            this.element.show().addClass("ui-social-locker-error");
        },

        // --------------------------------------------------------------------------------------
        // Markups and others.
        // --------------------------------------------------------------------------------------

        /**
        * Creates plugin markup.
        */
        _createMarkup: function () {
            var self = this;

            this.element.addClass("ui-social-locker-content");

            var browser = (jQuery.browser.mozilla && 'mozilla') ||
                          (jQuery.browser.opera && 'opera') ||
                          (jQuery.browser.webkit && 'webkit') || 'msie';

            this.locker = $("<div class='ui-social-locker ui-social-locker-" + browser + "' style='display: none;'></div>");
            this.outerWrap = $("<div class='ui-social-locker-outer-wrap'></div>").appendTo(this.locker);
            this.innerWrap = $("<div class='ui-social-locker-inner-wrap'></div>").appendTo(this.outerWrap);
            this.locker.addClass(this.options.style);

            if (!this.options.buttons.counter) this.locker.addClass('ui-social-locker-no-counters');
            $.onepress.isTouch()
                ? this.locker.addClass('ui-social-locker-touch')
                : this.locker.addClass('ui-social-locker-no-touch');

            var resultText = $("<div class='ui-social-locker-text'></div>");
            if (this.options.text.header) resultText.append(this.options.text.header.addClass('ui-social-locker-strong').clone());
            if (this.options.text.message) resultText.append(this.options.text.message.clone());

            // main locker message
            this.innerWrap.append(resultText.addClass());
            resultText.prepend(($("<div class='ui-social-locker-before-text'></div>")));
            resultText.append(($("<div class='ui-social-locker-after-text'></div>")));

            // creates markup for buttons
            this._createButtonMarkup();

            // bottom locker message
            this.options.bottomText && this.innerWrap.append(this.options.bottomText.addClass('ui-social-locker-bottom-text'));

            // close button and timer if needed
            this.options.locker.close && this._createClosingCross();
            this.options.locker.timer && this._createTimer();

            var after = (this.element.parent().is('a')) ? this.element.parent() : this.element;
            this.locker.insertAfter(after);

            this._markupIsCreated = true;
        },

        /**
        * Creates markup for every social button.
        */
        _createButtonMarkup: function () {
            var self = this;
            this.buttonsWrap = $("<div class='ui-social-locker-buttons'></div>").appendTo(this.innerWrap);

            for (var index in this.options.buttons.order) {
                var buttonName = this.options.buttons.order[index];
                if (typeof (buttonName) != 'string') continue;

                // setup options
                var options = $.extend({}, this.options[buttonName]);
                options.url = this.options[buttonName].url || this.options.url || this.url;
                options._provider = this._providers[buttonName];

                options.like = function () { self._unlock("button", this._provider); };
                options.dislike = function () { self._lock("button", this._provider); };

                // creates button
                var button = $("<div class='ui-social-locker-button ui-social-locker-button-" + buttonName + "'></div>");
                button.data('name', buttonName);
                this.buttonsWrap.append(button);

                var innerWrap = $("<div class='ui-social-locker-button-inner-wrap'></div>").appendTo(button);
                innerWrap[buttonName + "Button"](options);

                var flipEffect = this.options.effects.flip;
                var flipSupport = $.onepress.tools.has3d();

                // addes the flip effect
                (flipEffect && flipSupport && button.addClass("ui-social-locker-flip")) || button.addClass("ui-social-locker-no-flip");
                if (!flipEffect) continue;

                // if it's a touch device
                if ($.onepress.isTouch()) {

                    // if it's a touch device and flip effect enabled.
                    if (flipSupport) {

                        button.click(function () {
                            var btn = $(this);

                            if (btn.hasClass('ui-social-locker-flip-hover')) {
                                btn.removeClass('ui-social-locker-flip-hover');
                            } else {
                                $('.ui-social-locker-flip-hover').removeClass('ui-social-locker-flip-hover');
                                btn.addClass('ui-social-locker-flip-hover');
                            }
                        });

                        // if it's a touch device and flip effect is not enabled.
                    } else {

                        button.click(function () {
                            var overlay = $(this).find(".ui-social-locker-button-overlay");
                            overlay.stop().animate({ opacity: 0 }, 200, function () {
                                overlay.hide();
                            });
                        });

                    }


                    // if it's not a touch device
                } else {

                    if (!flipSupport) {
                        button.hover(
                            function () {
                                var overlay = $(this).find(".ui-social-locker-button-overlay");
                                overlay.stop().animate({ opacity: 0 }, 200, function () {
                                    overlay.hide();
                                });
                            },
                            function () {
                                var overlay = $(this).find(".ui-social-locker-button-overlay").show();
                                overlay.stop().animate({ opacity: 1 }, 200);
                            }
                        );
                    }
                }

                $("<div class='ui-social-locker-button-overlay'></div>").prependTo(innerWrap)
                    .append($("<div class='ui-social-locker-overlay-front'></div>"))
                    .append($("<div class='ui-social-locker-overlay-header'></div>"))
                    .append($("<div class='ui-social-locker-overlay-back'></div>"));

            }
        },

        _makeSimilar: function (overlay, source, dontSubscrtibe) {
            var self = this;

            overlay.css({
                "width": source.outerWidth(false),
                "height": source.outerHeight(false)
            });

            if (!dontSubscrtibe) $(window).resize(function () {
                self._makeSimilar(overlay, source, true);
            });
        },

        _createClosingCross: function () {
            var self = this;

            $("<div class='ui-social-locker-cross' title='" + $.onepress.lang.socialLock.close + "' />")
                .prependTo(this.locker)
                .click(function () {
                    if (!self.close || !self.close(self)) self._unlock("cross", true);
                });
        },

        _createTimer: function () {

            this.timer = $("<span class='ui-social-locker-timer'></span>");
            var timerLabelText = $.onepress.lang.socialLock.orWait;
            var secondLabel = $.onepress.lang.socialLock.seconds;

            this.timerLabel = $("<span class='ui-social-locker-timer-label'>" + timerLabelText + " </span>").appendTo(this.timer);
            this.timerCounter = $("<span class='ui-social-locker-timer-counter'>" + this.options.locker.timer + secondLabel + "</span>").appendTo(this.timer);

            this.timer.appendTo(this.locker);

            this.counter = this.options.locker.timer;
            this._kickTimer();
        },

        _kickTimer: function () {
            var self = this;

            setTimeout(function () {

                if (!self._isLocked) return;

                self.counter--;
                if (self.counter <= 0) {
                    self._unlock("timer");
                } else {
                    self.timerCounter.text(self.counter + $.onepress.lang.socialLock.seconds);

                    // Opera fix.
                    if ($.browser.opera) {
                        var box = self.timerCounter.clone();
                        box.insertAfter(self.timerCounter);
                        self.timerCounter.remove();
                        self.timerCounter = box;
                    }

                    self._kickTimer();
                }
            }, 1000);
        },

        // --------------------------------------------------------------------------------------
        // Lock/Unlock content.
        // --------------------------------------------------------------------------------------

        _lock: function (typeSender, sender) {

            if (this._isLocked || this._stoppedByWatchdog) return;
            if (!this._markupIsCreated) this._createMarkup();

            if (typeSender == "button") sender.setState("locked");

            this.element.hide();
            this.isInline ? this.locker.css("display", "inline-block") : this.locker.fadeIn(1000);

            this._isLocked = true;
            if (this.options.events.lock) this.options.events.lock(typeSender, sender && sender.name);
        },

        _unlock: function (typeSender, sender) {
            var self = this;

            if (!this._isLocked) { this._showContent(true); return false; }
            if (typeSender == "button") sender.setState("unlocked");

            this._showContent(true);

            this._isLocked = false;
            if (typeSender == "timer" && this.options.events.unlockByTimer) return this.options.events.unlockByTimer();
            if (typeSender == "close" && this.options.events.unlockByClose) return this.options.events.unlockByClose();
            if (this.options.events.unlock) this.options.events.unlock(typeSender, sender && sender.name);
        },

        lock: function () {
            this._lock("user");
        },

        unlock: function () {
            this._unlock("user");
        },

        _showContent: function (useEffects) {
            var self = this;

            var effectFunction = function () {
                if (self.locker) self.locker.hide();
                if (!useEffects) { self.element.show(); return; }

                self.element.fadeIn(1000, function () {
                    self.options.effects.highlight && self.element.effect && self.element.effect('highlight', { color: '#fffbcc' }, 800);
                });
            };

            if (!this.options.content) {
                effectFunction();

            } else if (typeof this.options.content === "string") {
                this.element.html(this.options.content);
                effectFunction();

            } else if (typeof this.options.content === "object" && !this.options.content.url) {
                this.element.append(this.options.content.clone().show());
                effectFunction();

            } else if (typeof this.options.content === "object" && this.options.content.url) {

                var ajaxOptions = $.extend(true, {}, this.options.content);

                var customSuccess = ajaxOptions.success;
                var customComplete = ajaxOptions.complete;
                var customError = ajaxOptions.error;

                ajaxOptions.success = function (data, textStatus, jqXHR) {

                    !customSuccess ? self.element.html(data) : customSuccess(self, data, textStatus, jqXHR);
                    effectFunction();
                };

                ajaxOptions.error = function (jqXHR, textStatus, errorThrown) {

                    self._setError("An error is triggered during the ajax request! Text: " + textStatus + " " + errorThrown);
                    customError && customError(jqXHR, textStatus, errorThrown);
                };

                ajaxOptions.complete = function (jqXHR, textStatus) {

                    customComplete && customComplete(jqXHR, textStatus);
                };

                $.ajax(ajaxOptions);

            } else {
                effectFunction();
            }
        }
    });

    /**
    * [obsolete]
    */
    $.fn.sociallocker = function (opts) {

        opts = $.extend({}, opts);
        $(this).socialLock(opts);
    };

})(jQuery);;;

