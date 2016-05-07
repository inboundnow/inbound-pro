var Subscribe = (function () {
    var nav_background_color,
        nav_font_color,
        contents_background_color,
        contents_font_color;

    /**
     *  Initialize Script
     */
    init = function () {
        setupVars();
        addListeners();
        createNav();

    },
    /**
     * Setup Variables
     */
    setupVars = function() {

    },
    /**
     * Add Listeners
     */
    addListeners = function() {

    },

    /**
     * Create Navigation Elements
     */
    createNav = function () {
        var nav = jQuery("<nav></nav>")attr('id' , 'nav-subscribe').text = 'hello';
        var prompt = jQuery("<div></div>");
        var content = jQuery("<div></div>");

        nav.prepend(content);
        nav.prepend(prompt);

        jQuery('body').prepend(nav);

    },
    expandNav = function () {

    },
    collapseNav = function () {

    }
});

Subscribe.init();