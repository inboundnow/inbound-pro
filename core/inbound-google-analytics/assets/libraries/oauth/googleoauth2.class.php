<?php
class Inbound_GA_GoogleOauth2 extends Inbound_GA_API_Settings {
	public $refreshtoken;

	function __construct($apiSettings){
		parent::__construct($apiSettings);
	}
	
	/** 
	 * returns session token for calls to API using oauth 2.0
	 */
	function getOauth2Token($code, $refreshtoken = false) {
		
		$oauth2token_url = "https://accounts.google.com/o/oauth2/token";
		
		$clienttoken_post = array(
			"client_id" => $this->clientid,
			"client_secret" => $this->clientsecret
		);
		
		if ($refreshtoken){
			$clienttoken_post["refresh_token"] = $code;
			$clienttoken_post["grant_type"] = "refresh_token";
		}else{
			$clienttoken_post["code"] = $code;	
			$clienttoken_post["redirect_uri"] = $this->redirecturi ;
			$clienttoken_post["grant_type"] = "authorization_code";
		}
		

		$curl = curl_init($oauth2token_url);
	
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$json_response = curl_exec($curl);
		curl_close($curl);
	
		$authObj = json_decode($json_response);

		if (isset($authObj->refresh_token)){
			$this->refreshToken = $authObj->refresh_token;
		}
		
		$authObj = ( isset($authObj->access_token) || isset($authObj->refresh_token ) ) ? $authObj  : "Error occured: " . json_encode($authObj);
		
		return $authObj;
	}
	
	
}
?>