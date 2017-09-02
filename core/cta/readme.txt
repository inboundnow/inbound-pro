=== WordPress Calls to Action ===

Contributors: Hudson Atwell, David Wells, Giulio Dapreala, ahmedkaludi, Matt Bisset
Donate link: mailto:hudson@inboundnow.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: Call to action, Calls to Action, CTA, inbound marketing, call outs, ad management, split testing, a b test, a b testing, a/b test, a/b testing, popups, email list, landing page, pop up, list building, inbound now, wp-call-to-actions, cpa, click tracking, ad placement, banner ads, slide in call outs, fly ins, like to download, social media share buttons
Requires at least: 3.8
Tested up to: 4.8.1
Stable Tag: 3.3.3

Create Calls to Action for your WordPress site. Monitor and improve conversion rates, run A/B split tests, customize your own CTA templates and more.

== Description ==

> WordPress Calls to Action works as a standalone plugin or hand in hand with [WordPress Landing Pages](http://wordpress.org/plugins/landing-pages/ "Learn more about Landing Pages") & [WordPress Leads](http://wordpress.org/plugins/leads/ "Learn more about WordPress Leads") to create a powerful & free lead generation system for your business.

This plugin creates calls to action for your WordPress site. It gives site owners the ability to monitor and track conversion rates, run a/b or multivariate split tests on calls to action, and most importantly increase lead flow!

The calls to action plugin was specifically designed with inbound marketing best practices in mind and will help you drive & convert more leads on your site.

Calls to action are an ideal way to convert more of your passive website visitors into active leads or email list subscribers.

http://www.youtube.com/watch?v=-qaYgwV7p-8

= Highlights =

* Create beautiful calls to action on your WordPress site.
* Visual Editor to view changes being made on the fly!
* Create Popups Calls to Actions for improved conversion rates
* Track conversion rates on your calls to action for continual optimization.
* Easily clone existing calls to action and run A/B Split tests on variations.
* Gather lead intelligence and track lead activity with <a href="http://wordpress.org/plugins/leads/screenshots/">WordPress Leads</a>
* Easily implement your own custom call to action design or use our library of custom call to action designs


= Developers & Designers =

We built the calls to action plugin as a framework! Need A/B testing out of the box implemented for your existing designs? Use WordPress Calls to Action to quickly spin up new calls to action that have all the functionality your clients will need.

You can quickly take your existing designs and implement them using our <a href="http://docs.inboundnow.com/landing-pages/dev">templating framework</a>.

The plugin is also fully extendable and has a number of actions, filters, and hooks available for use. If a hook doesn't exist, simply ask and we can implement custom changes.


[Contribute to Code](https://github.com/inboundnow/inbound-pro/core/cta "Follow & Contribute to core development on GitHub")
 |
[Follow on Twitter ](https://twitter.com/inboundnow "Stay Notified")

== Installation ==

1. Upload `wp-call-to-actions` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
*Can I create my own call to action designs?,
*Yes! You can learn how to <a href="http://plugins.inboundnow.com/docs/dev/creating-templates/">create your own call to action template here</a>.

== Screenshots ==

1. Convert more web traffic with calls to action to drive the visitor into a landing page or capture their email address right in the call to action
2. Create Popup Calls to action and capture more emails
3. Easy to use templates
4. Visual fronted editing to see live changes on the fly
5. Choose from a ton of pre-made templates, use your existing design, or design your own call to action template. The system is a framework that allows for as many templates as you want.
6. Track conversion rates and continuously improve your marketing
7. Easy placement within pages/posts/custom post types
8. Global placements via your sites sidebar

== Changelog ==

= 3.3.3 =
* Fixing issues with broken impression tracking when leads is not installed.
* Fixing issue with clean stats not resetting conversions.

= 3.3.1 =
* Addign methods to Inbound_Events class.

= 3.2.9 =
* Preventing lead data recovery AJAX script from firing more than once per session.

= 3.2.8 =
* Updating docblocks
* [Restored] Impression tracking was failing in select theme environments.

= 3.2.6 =
* Updating compatibility to WordPress 4.8
* Updating shared files

= 3.2.5 =
* Removing jQuery reference from frontend tracking JS.
* Checks if event table already exists before trying to create it.
* Fixing issue with double optin not allowing custom template select for Inbound Pro subscribers.
* Fixing issue with ability to mass delete leads from lead listing page.
* Better select2 compatibility to prevent plugin conflicts using legacy select2

= 3.2.2 =
* Debugging split testing disable feature.
* Debugging new CTA impression tracking system & Inbound Forms's datedropdown selector.

= 3.1.9 =
* Adding 'sticky ctas' which will also reduce overall AJAX calls
* Removing legacy shared components and modifying plugin shared tables
* Consolidating AJAX CTA impression tracking AJAX call into inbound_track_lead AJAX call to reduce overall server resource usage.
* Changing AJAX get variation call to POST instead of GET to shake possible caching.
* Inbound Form's date dropdown now updates correct number of dates as month/year options are changed.

= 3.1.3 =
* Compatibility with Avada theme.

= 3.1.2 =
* Expanding use of nonces inside of Inbound Forms for better security.

= 3.1.1 =
* Adding support for custom classes inside of Inbound Forms
* Removing square brackets from new lead notification email subject to prevent form render errors
* Fixing lost marketing button CSS
* No longer prompting users to download legacy CTAs

= 3.0.9 =
* Removing modernizer js assets from call to action templates
* Restoring Popup capabilities with Yoast SEO

= 3.0.8 =
* Adding lead sources to Leads API
* Moved field mapping select input to a more visable location.
* Improved New Lead Notification email report

= 3.0.6 =
* FireFox support for datetime picker.
* Moved field mapping select input to a more visable location.
* Improved New Lead Notification email report

= 3.0.4 =
* [templates] Removing header tags from CTA for search engine optimization.

= 3.0.3 =
* [bug fixes] Shared database routine component debugged. Running proper SQL now.

= 3.0.2 =
* [refactor] We stopped storing classes into global variables.
* [bug fixes] Serveral context-dependant bug fixes in place

= 2.9.1 =
* [improvement] updating shared components for minor bug fixes including a fix for Inbound Form submissions when using Firefox

= 2.8.8 =
* [fix] Fixing Inbound Forms error when Leads is not activated

= 2.8.7 =
* [fix] fixing include file for Inbound Forms when wp-config.php is outside of it's normal location.

= 2.8.6 =
* [fix] checks if mb_convert_encoding exists before it attempts to make use of it.

= 2.8.5 =
* [fix] admin emails sending twice during form submission.
* [fix] Fixing issue with [inbound_button] shortcode having wrong closing tag

= 2.8.4 =
* Better utf-8 encoding for CTA renderings.
* Improving CSS for CTA selection page.
* [fix] making sure form submissions are being displayed as stats

= 2.8.2 =
* Preventing spiders directly accessing tracking links from inflating conversion statistics.
* Adding nofollow automatically to tracked links.
* Fixing issue with pause/playing not working.
* Fixing clear stats issue within the CTA edit page.

= 2.7.7 =
* Fixing issue with tracking link not recording conversions if lead not registered.
* Adding spinner to inbound form submit button on submission. 

= 2.7.5 =
* Sanitizing GET/REQUEST/POST variables when applicable.
* Upgrading Sabberworm CSS parsing class to be PHP Compatible.

= 2.7.4 =
* Fixing Redner issue with Thumbnail CTA

= 2.7.3 =
* Adding user role capabilities that support the wp-call-to-action post type.
* Fixing potential fatal when leads is not installed.
* Making variation discovery completely dependant on WordPress ajax.

= 2.7.2 =
* UI improvements. Removing select templates from core.
* Fixing issue with CTAs not saving

= 2.7.1 =
* Bug Fix for Popups
* Code refactoring

= 2.6.6 =
* Fixing dynamic shortcodes
* Disabling async.

= 2.6.5 =
* Fixing dynamic shortcodes
* Popups always show for admin. Only show once for visitor.

= 2.6.3 =
* Changing menu items

= 2.6.1 =
* Preparing for Inbound Pro.
* Fixing issue with mobile CTA rendering on IOS devices.

= 2.5.4 =
* Fix issue with broken select2

= 2.5.3 =
* Loading ACF4 after theme is loaded to prevent conflicts.
* Upgraded SaberWorm class to work with PHP7

= 2.5.1 =
* security fix

= 2.5.0 =
* Updates to Metabox system for better usability
* Refresh on visual editor for better UX
* Update to shortcode setup in the system for better UX
* Improved tracked link error handling for certain WordPress themes.
* Updates to social gate call to actions (solving errors with tracked links)
* Fixes issue with new lead notification email headers being broken.
* Improved UI for form editor concerning 'Advanced Options' button.
* Added default values to 'Range' field type when left empty
* New filter to change priority of CTA placement's the_content hook. Lowered default priority to 5.
* Removed remote dependency on fontawesome API call for shared ACF Bootsrapping in shared.
* Use template thumbnails instead of screenshots when in local mode on cta listing page.
* New 'Marketing' button setup.
* Code refactoring. Moved many files into /assets/ folder.
* Better support for WordPress 4.3

= 2.4.3 =
* Temporarily disabling geolocation services

= 2.4.2 =
* Security patch for Firefox

= 2.4.0 =
* Security patch

= 2.3.7 =
* Solves broken tracked links for users not running Leads plugin.

= 2.3.6 =
* Security patch

= 2.3.4 =
* Preparation for Inbound Attachments
* Bug Fixes and General Improvements.

= 2.3.3 =
* Fixing white screen of death issues with other plugin conflicts
* Improvements on NoConflict jQuery Class

= 2.3.2 =
* Debugging release issues with 2.3.1
* Security Update

= 2.3.1 =
* Inbound Forms trim end whitespaces on inputs
* Fix issue with broken paginations.
* General bug fixes.
* Use WordPress Ajax URL instead of custom ajax url.
* See https://github.com/inboundnow/cta/issues?q=label%3A2.3.1+is%3Aclosed for full list of changes

= 2.3.0 =
* Conversion tracking bug fixed for CTAs

= 2.2.9 =
* Conversion tracking bug fixed for CTAs
* Safari ajax bug fix

= 2.2.8 =
* Even more security updates! Security for the win!

= 2.2.7 =
* security update

= 2.2.6 =
* Fix double lead notification email

= 2.2.5 =
* Fixed double email submission on contact form 7

= 2.2.4 =
* Added form field exclusions

= 2.2.2 =
* See changelog here: https://github.com/inboundnow/cta/issues?q=is%3Aclosed+is%3Aissue+label%3Av2.2.2

= 2.2.1 =
* Improved form email typo detection
* Improved Template Styles
* Improved frontend editor
* Fixed content wysiwyg scroll freezing bug

= 2.2.0 =
* French - 100% Translated
* Converted code standards to CLASS based system.
* Better code documentation.
* New filter: inbound-email-post-params

= 2.1.4 =
* "Translation 100% for Romanian." - FX Benard

= 2.1.3 =
* Fix: Lead email notifications now working again.

= 2.1.2 =
* Major code refactoring for improved developer experience.
* Added: Button to clear all cta stats at once
* Fix: Open cta links in new window


= 2.0.9 =
* Bug Fix: Check all required fields

= 2.0.8 =
* Bug Fix: Improvement to our custom ajax handler script module.ajax-get-variation.php


= 2.0.7 =
* Fix to insert marketing shortcode popup

= 2.0.6 =
* Updated & added language packs
* Added button to clear all landing page stats.
* Added 'do-not-track' class listener for disabling link tracking in CTAs.
* Converted serveral non class modules to class instances
* Added popups back to CTA display options.
* Optimized CTA Tracking JS.
* Bug Fix: Marketing Button
* Bug Fix: CTA Preview Mode

= 2.0.5 =
* Bug Fix - Remove JS Alert

= 2.0.4 =
* Temporary fix for shortcodes disappearing from wordpress 3.8 to 3.9
* Performance improvements on analytics and lead tracking

= 2.0.3 =
* Added Variation ID Attribute to Shortcode
* Misc bug fixes

= 2.0.2 =
* Misc bug fixes

= 2.0.1 =
* Misc bug fixes
* Update bug on checkbox forms
* Adding better compatibility for JS errors

= 2.0.0 =
* Faster Call to Action load times with inline CTAs
* Better A/B Testing functionality
* Mobile responsive Call to action templates
* A new & improved call to action templating engine
* All around code improvements and bug fixes

= 1.3.0 =
* Updated Popup Functionality
* Updated CTA Editing Preview Screen
* Fixed missing height/width issues
* Added New HTML Lead Email Template with clickable links for faster lead management
* Added Button Shortcodes!
* Added HTML field option to form tool
* Added Divider Option to Form tool
* Added multi column support to icon list shortcode
* Added Font Awesome Icons option to Inbound Form Submit buttons
* Added Social Sharing Shortcode

= 1.2.5 =
* Bug fix - missing trackingObj

= 1.2.4 =
* Added feature request form to all plugin admin pages. Submit your feature requests today! =)

= 1.2.3 =
* Bug fixes for form creation issues
* Bug fixes for safari page tracking not firing
* Added quick menu to WP admin bar for quicker marketing!

= 1.2.2 =
* Updated: Styles for WordPress 3.8

= 1.2.0 =

* Added: Added email confirmation support to Inbound Forms tool
* Added: Added additional popup call to action options
* Added: Added Slide out Call to Action option for calls to action to slide out from left or right side of screen
* Added: Fancy List and Column shortcodes

= 1.1.1 =

* Added: Added InboundNow form creation and management system (beta)
* Added: Support for InboundNow cross plugin extensions
* Added: 'header' setting component to global settings.

Released

= 1.0.1 =

Released
