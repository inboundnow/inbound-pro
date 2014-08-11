<?php
require_once ('ConstantContact.php');
require_once ('config.php');
session_start ();

// Istantiate a new oAuth2 object by passing in all the necesssary
// information to authenticate
$oAuth2 = new CTCTOauth2 ( $apikey, $consumersecret, $verificationURL, $_GET ["code"] );

// trade your code in for an access token by doing a POST
$token = $oAuth2->getAccessToken ();

// store information into the array to pass into the DataStore object
$sessionConsumer = array ('username' => $_GET ["username"], 'access_token' => $token );

$Datastore = new CTCTDataStore ();
$Datastore->addUser ( $sessionConsumer );
if(isset($_SESSION["backto"]))
{
	// if you set a return url and have stored it into backto
	$returnURL = $_SESSION["backto"];
}
// refresh the page to where you want to send them
header('Location:' . $returnURL);
?>


<a href="https://ryandavis.co/CTCT-OAuth2/index.php">You have
	authenticated, Go back</a>