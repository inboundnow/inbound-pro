<?php


/* load wp */
require '../../../wp-load.php';
require '../../../wp-admin/includes/plugin.php';

/* load coception addons */
require  LANDINGPAGES_PATH . 'vendor/lucatume/wp-browser/src/Codeception/Module/WPBrowserMethods.php';
require  LANDINGPAGES_PATH . 'vendor/lucatume/wp-browser/src/Codeception/Module/WPBrowser.php';

/* Set current users */
wp_set_current_user( 1 );
global $wpdb;