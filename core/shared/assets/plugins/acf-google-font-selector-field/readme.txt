=== ACF: Google Font Selector ===
Contributors: danielpataki
Tags: acf, fonts, google
Requires at least: 3.5
Tested up to: 4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A field for Advanced Custom Fields which allows users to select Google fonts with advanced options

== Description ==

This plugin allows you to create a Google font selector field with different options. The plugin also creates the font request in the theme header to autoload the fonts if you'd like. Font variants and charsets can be selected separately to make font loading more flexible and optimized.

Font options added to any options page will always be enqueued. Any fonts added to post pages will only be enqueued when that specific post is displayed.

= Google API Key =

To grab the font list from Google you'll need an API key. This is free and easy to do, take a look at [this guide](https://developers.google.com/api-client-library/python/guide/aaa_apikeys) to get an API key.

= ACF Compatibility =

This ACF field type is compatible with both *ACF 4* and *ACF 5*.

= Thanks =

* [Advanced Custom Fields](http://www.advancedcustomfields.com/) for the awesome base plugin.
* [Iconjam](https://www.iconfinder.com/Icojam) for the T icon.

== Installation ==

= Automatic Installation =

Installing this plugin automatically is the easiest option. You can install the plugin automatically by going to the plugins section in WordPress and clicking Add New. Type "ACF Google Font Selector" in the search bar and install the plugin by clicking the Install Now button.

= Manual Installation =

1. Copy the `acf-google-font-selector-field` folder into your `wp-content/plugins` folder
2. Activate the Google Font Selector plugin via the plugins admin page
3. Create a new field via ACF and select the Role Selector type
4. Please refer to the description for more info regarding the field type settings


== Usage ==

Once installed the list of Google Fonts will be retrieved from a static file included in the plugin. If you would like the list to be pulled from the Google API you will need to define your API key. You can do this in Settings->Google Font Selector in the admin.

= For Developers =

There are a few more advanced controls you can set to make the plugin do your bidding. If you would like to hard-code the API key and disable users from seeing the nag screen and setting panel you can define the `ACFGFS_API_KEY` constant.

`define( 'ACFGFS_API_KEY', 'your_google_api_key' );`

The `ACFGFS_REFRESH` constant can controls how frequently the plugin checks the Google API for updates. The value is in seconds, 86400 would be a day. The default is set to 7 days.

`define( 'ACFGFS_REFRESH', 259200 );`

If you would like to disable the automatic enqueueing of fonts you can use the `ACFGFS_NOENQUEUE` constant. The fonts are only enqueued automatically when this constant is not defined. Define the constant to disable enqueueing.

`define( 'ACFGFS_NOENQUEUE', true );`

If you want to modify the fonts that are loaded on a page you can use the `acfgfs/enqueued_fonts` filter. This should return an array of fonts with variants and subsets needed, something like this:

`array(
    'font' => 'Open Sans',
    'variants' => array( 'regular', '700' ),
    'subsets' => array( 'latin' )
)`

New in 3.0.1 is the ability to control the fonts displayed in the dropdown. If you only want to give your users access to a smaller portion of Google fonts you can use the `acfgfs/font_dropdown_array` filter to modify the array that is used to generate the dropdown. Please return an array where the key and the value are both the names of the font.

`add_filter( 'acfgfs/font_dropdown_array', 'my_font_list' );
function my_font_list( $fonts ) {
    $fonts = array(
        'Raleway' => 'Raleway',
        'Lato' => 'Lato'
    );
    return $fonts;
}
`


== Screenshots ==

1. ACF control for field creation
2. The user-facing font settings

== Changelog ==

= 3.0.1 (2015-04-26) =
* Added acfgfs/font_dropdown_array filter
* Fixed a faulty font preview mechanism when multiple font options are added
* Fixed an error when web safe fonts were selected

= 3.0.0 (2015-04-21) =
* Updated for WordPress 4.2
* Font previews in the backend
* Added Hungarian translation
* Complete rewritten and better documented
* Uses transients to store fonts
* Better behaviour on font change

= 2.2.1 =
* Updated for WordPress 4.1

= 2.2 =
* Much more efficient font enqueueing
* Separated out common functions: ie: code is better :)

= 2.1 =

* Font requests are now merged properly
* Added field checks and syncing

= 2.0 =

* Complete rewrite, fonts will need to be set up again
* Font loading is now much better and selectable
* Dropped ACF 3 support
* Added ACF 5 support

= 1.0 =

* Initial Release.
