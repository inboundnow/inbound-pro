<?php
/**
 * Copyright 2012 Zakir Hyder http://blog.jambura.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 https://developer.citrixonline.com/forum/pageing-get-registrants-api
 https://developer.citrixonline.com/forum/resolved-enhancement-oauth-token-expiration-time-extended
 */

if (!function_exists('curl_init')) {
  throw new Exception('Citrix needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Citrix needs the JSON PHP extension.');
}

class LP_Citrix
{
	
	/**
	* Default options for curl.
	*/
	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 60,
	);
	
	/**
	* The Application Api Key.
	*/
	protected $citrix_api_key;

	/**
	* The ID of the citrix user, or 0 if the user is logged out/not authorized.
	*/	
	protected $organizer_key;
  
	/**
	* The OAuth access token received in exchange for a valid authorization
	* code.  null means the access token has yet to be determined.
	*/
	protected $access_token = null;
	
	public function __construct($citrix_api_key) 
	{
		if (!session_id()) 
		{
		  session_start();
		}
		
		$this->set_citrix_api_key($citrix_api_key);

		if(isset($_SESSION['citrix_access_token']) and $_SESSION['citrix_access_token'] != '' and isset($_SESSION['citrix_organizer_key']) and $_SESSION['citrix_organizer_key'] != '')
		{
			$this->set_access_token($_SESSION['citrix_access_token']);
			$this->set_organizer_key($_SESSION['citrix_organizer_key']);
		}
	}
	
	/**
	* Sets citrix_api_key
	*/
	public function set_citrix_api_key($citrix_api_key) 
	{
		$this->citrix_api_key = $citrix_api_key;
	}
	
	/**
	* Gets citrix_api_key
	*/
	public function get_citrix_api_key() 
	{
		return $this->citrix_api_key;
	}
	
	/**
	* Sets current user id
	*/
	public function set_organizer_key($organizer_key) {
		$_SESSION['citrix_organizer_key'] = $organizer_key;
		$this->organizer_key = $organizer_key;
	}
	
	/**
	* Gets current user id
	*/
	public function get_organizer_key() 
	{
		if (array_key_exists('code', $_GET)) 
		{
			if(!$this->organizer_key)
			{
				$code = $_GET['code'];
				$access_token = $this->get_access_token($code);
				if ($access_token) 
				{
				  return $this->organizer_key;
				}
			}
		}

		if(!$this->organizer_key or !$this->access_token)
			return 0;
		
		$reponse = json_decode($this->make_request("https://api.citrixonline.com/G2W/rest/organizers/".$this->organizer_key."/upcomingWebinars?oauth_token=".$this->access_token), true);

		if(isset($reponse['int_err_code']) and $reponse['int_err_code'] != '')
		{
			$this->set_access_token('');
			$this->set_organizer_key('');				
			throw new Exception($reponse['int_err_code']);
		}
		
		return $this->organizer_key;
	}
	
	/**
	* Sets access token
	*/
	public function set_access_token($access_token) {
		$_SESSION['citrix_access_token'] = $access_token;
		$this->access_token = $access_token;
	}
	
	/**
	* Gets the access token for current user. If $_get['code']/?code={responseKey} is available and access_token not set then 
	*it will make request with code to get access token. 
	*
	* @return string access token
	*/
	public function get_access_token($code = null) {
		if($this->access_token)
			return $this->access_token;
				
		if($code)
		{
			$reponse = json_decode($this->make_request("https://api.citrixonline.com/oauth/access_token?grant_type=authorization_code&code={$code}&client_id=".$this->citrix_api_key), true);

			if(isset($reponse['int_err_code']) and $reponse['int_err_code'] != '')
			{
				$this->set_access_token('');
				$this->set_organizer_key('');				
				throw new Exception($reponse['int_err_code']);
			}
			$this->session_set($reponse);
			$this->set_access_token($_SESSION['citrix_access_token']);
			$this->set_organizer_key($_SESSION['citrix_organizer_key']);
			return $this->access_token;
		}
		
		$this->citrixonline_get_list_of_webinars(0);
		
		return false;
	}
	
	/**
	* Returns Authorization URL 
	*
	* @param string $redirect_url if empty it will add current url as redirect url
	*
	* @return string Authorization URL 
	*/
	function auth_citrixonline($redirect_url = '') 
    {
		if($redirect_url == '')
		{
			$redirect_url = $this->get_current_url();
		}
		if($this->citrix_api_key == '')
		{
			throw new Exception('Please set citrix_api_key.');
		}
		return 'https://api.citrixonline.com/oauth/authorize?client_id=' . $this->citrix_api_key . '&redirect_uri='. urlencode($redirect_url);
	}
	
	/**
	* Returns webinars of current user
	*
	* @param boolean $type greater than 0 will return historicalWebinars along with upcomingWebinars
	*
	* @return array webinars of current user
	*/	
	function citrixonline_get_list_of_webinars($type = 0) 
    {
		if(!$this->organizer_key or !$this->access_token)
			return 0;
			
		$return_array = array();
		
		$reponse = json_decode($this->make_request("https://api.citrixonline.com/G2W/rest/organizers/".$this->organizer_key."/upcomingWebinars?oauth_token=".$this->access_token), true);
		
		if(isset($reponse['int_err_code']) and $reponse['int_err_code'] != '')
		{
			$this->set_access_token('');
			$this->set_organizer_key('');				
			throw new Exception($reponse['int_err_code']);
		}
		
		$return_array['upcoming']['webinars'] = $reponse;
        $return_array['upcoming']['status'] = true;
		
		if($type>0)
		{
			$reponse = json_decode($this->make_request("https://api.citrixonline.com/G2W/rest/organizers/".$this->organizer_key."/historicalWebinars?oauth_token=".$this->access_token), true);
						
			if(isset($reponse['int_err_code']) and $reponse['int_err_code'] != '')
			{
				$this->set_access_token('');
				$this->set_organizer_key('');				
				throw new Exception($reponse['int_err_code']);
			}
			
			$return_array['historical']['webinars'] = $reponse;
			$return_array['historical']['status'] = true;
		}
			
        return $return_array;
	}
	
	/**
	* get all registrants for given webinar
	*
	* @param string $webinar_id webinar id
	*
	* @return array the registrants
	*/	
	function get_registrants_of_webinars($webinar_id=false) 
    {
		if($webinar_id)
		{
			if(!$this->organizer_key or !$this->access_token)
				return false;
				
			$return_array = array();
			
			$return_array = json_decode($this->make_request("https://api.citrixonline.com/G2W/rest/organizers/".$this->organizer_key."/webinars/{$webinar_id}/registrants?oauth_token=".$this->access_token), true);

			if(isset($reponse['int_err_code']) and $reponse['int_err_code'] != '')
			{
				$this->set_access_token('');
				$this->set_organizer_key('');				
				throw new Exception($reponse['int_err_code']);
			}
		}
			
        return $return_array;
	}
	
	
	/**
	* Creates a registrant for given webinar
	*
	* @param string $webinar_id webinar id
	* @param array $data The parameters to use for the POST Registrant it must xontain first_name, last_name, email
	* @param array $all_fields_chk The parameters to use for the checking the registration version view https://developer.citrixonline.com/api/gotowebinar-rest-api/apimethod/create-registrant
	*
	* @return array details about the registrant
	*/
    function citrixonline_create_registrant_of_webinar($webinar_id=false, $data = array(), $all_fields_chk = false) 
    {
		if($webinar_id and isset($data['first_name']) and isset($data['last_name']) and isset($data['email']))
		{
			$params = array();
			
			$fields = array(
				'firstName'=>$data['first_name'],
				'lastName'=>$data['last_name'],
				'email'=>$data['email'],
            );
			
			$params[CURLOPT_HTTPHEADER] = array('Accept: application/json', 'Content-Type: application/json', 'Authorization: OAuth oauth_token='.$this->access_token);
			$params[CURLOPT_POSTFIELDS] = json_encode($fields);
			
			$reponse = json_decode($this->make_request("https://api.citrixonline.com/G2W/rest/organizers/".$this->organizer_key."/webinars/{$webinar_id}/registrants", $params), true);
			if(isset($reponse['registrantKey']))
				return $reponse;
		}
		
		return false;			
	}
	
	/**
	* Returns the Current URL
	*
	* @return string The current URL
	*/
	protected function get_current_url() 
	{
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		  $protocol = 'https://';
		}
		else 
		{
		  $protocol = 'http://';
		}
		
		$currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$parts = parse_url($currentUrl);
				
		// use port if non default
		$port =
		  isset($parts['port']) &&
		  (($protocol === 'http://' && $parts['port'] !== 80) ||
		   ($protocol === 'https://' && $parts['port'] !== 443))
		  ? ':' . $parts['port'] : '';
		
		// rebuild
		return $protocol . $parts['host'] . $port . $parts['path'];
	}
	
	/**
	* Makes an HTTP request. This method can be overridden by subclasses if
	* developers want to do fancier things or use something other than curl to
	* make the request.
	*
	* @param string $url The URL to make the request to
	* @param array $params The parameters to use for the POST body
	*
	* @return string The response text
	*/	
	protected function make_request($url, $params = array()) 
	{
		$ch = curl_init();
		
		$opts = self::$CURL_OPTS;
		
		$opts[CURLOPT_URL] = $url;	
		
		if(!empty($params))
		{
			foreach($params as $key=>$value):
				$opts[$key] = $value;
			endforeach;
		}

		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);
		
		if ($result === false) 
		{
		  curl_close($ch);
		  throw new Exception(curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}
	
	/**
	* Print_r convenience function, which prints out <PRE> tags around
	* the output of given array. Similar to debug().
	*/	
	public function pr($var) 
	{
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
	
	/**
	* Store an array's values with array's key as key with prefix 'citrix_'.
	* 
	*/	
	protected function session_set($array) 
	{
		foreach($array as $key=>$value):
			$_SESSION['citrix_'.$key] = $value;
		endforeach;
	}
}
