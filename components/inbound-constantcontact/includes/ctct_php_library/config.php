<?php
/**
 * 
 * This config.php file is intended to hold you Constant Contact
 * Credentials.  There are two different ways to implement the 
 * oAuth2 authentication from with in this library.
 * 
 *  The first way is from with in session.  This information is not
 *  stored anywhere locally, other than that of the clients browser.
 *  The following code breaks apart the session into the actual 
 *  credentials.  The information could also be sent to a Database
 *  or handled directly as you will see below.
 * 
 */
$Datastore = new CTCTDataStore ();
if (isset ( $_SESSION ['users'] )) {
	foreach ( $_SESSION ['users'] as $user ) {
		if ($user ['username'] != false) {
			$DatastoreUser = $Datastore->lookupUser ( $user ['username'] );
			$username = $user ['username'];
			$accessToken = $user ['access_token'];
		}
	}
}

/**
 * If you already have the credentials, you would not need to store
 * them into the DataStore object.  You can set them here 
 * locally like so:
 * 
 * $username = "username";
 * $accessToken = "access token";
 * 
 */

// Constant Contact API key, obtain one at http://developer.constantcontact.com
$apikey = "";

// Consumer secret is issued with an apikey
$consumersecret = "";

// Redirect URL which is set when creating your API key, this must match up 
// or you will receive an error
$verificationURL = '';

// This URL will be used in the example_verification to bounce back to wherever
// you like.  This would be a way to send back to where the user started
$returnURL = '';