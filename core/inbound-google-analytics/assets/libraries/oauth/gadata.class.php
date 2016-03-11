<?php

class Inbound_GA_Gadata {
    public $errors = array();
    public $startDate;
    public $endDate;

    private $_accesstoken;
    private $_refreshtoken;

    function __construct($settings) {

        $this->_accesstoken =(isset($settings['analyticAccessToken'])) ? $settings['analyticAccessToken'] : '';
        $this->_refreshtoken =(isset($settings['refreshToken'])) ? $settings['refreshToken'] : '';
        $this->startDate = date("d-F-Y", strtotime("-31 day"));
        $this->endDate = date("d-F-Y", strtotime("-1 day"));
		
    }

    /**
     * calls api and gets the data as object
     */
    function callApi($url) {

		/* make initial call */
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $curlheader[0] = "Authorization: Bearer " . $this->_accesstoken;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
        $curl_response = curl_exec($curl);
		$responseObj = json_decode($curl_response);
		curl_close($curl);
					
		/* if initial call fails try to refresh the access token and try again */
		if (isset($responseObj->error->errors)) {

			self::refresh_token();
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$curlheader[0] = "Authorization: Bearer " . $this->_accesstoken;
			curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
			$curl_response = curl_exec($curl);
			$responseObj = json_decode($curl_response);
			curl_close($curl);
		}
		 
		/* if error message still exists show it else return the responseObj */
		if (isset($responseObj->error->errors)) {
			error_log(print_r($responseObj,true));
			var_dump($responseObj);
			exit;
		} else {
			return $responseObj;
		}
    }
	
	/**
	 *
	 */
	function refresh_token() {
		

		/* Attempt to refresh token */
		if ( !isset($this->_refreshtoken) || !$this->_refreshtoken ) {
			return;
		}
		
		global $google_oauth;
		

		/* Use refresh token to get new access token  */
		$tokens = $google_oauth->getOauth2Token( $this->_refreshtoken , true);

		/* if access token does not exist return null */
		if (!isset($tokens->access_token) || !$tokens->access_token) {
			var_dump($tokens);
			return null;
		}
		
		/* save new access token */
		$ga_settings = get_option('inbound_ga' , false);			
		$ga_settings['analyticAccessToken'] = $tokens->access_token;
		update_option( 'inbound_ga' , $ga_settings );
		$this->_accesstoken = $tokens->access_token;
		
	}

    //returns profile list as array
    function parseProfileList() {

        $profiles = array();
        $profilesUrl = "https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties/~all/profiles";

        $profilesObj = $this->callApi($profilesUrl);
        error_log(print_r($profilesObj, true));
        //handle error in api request
        if (isset($profilesObj->error)) {
            $profiles[0]["error"] = "Inbound_GA_Gadata->parseProfileList: " . $profilesObj->error;
        } else {
            foreach ($profilesObj->items as $profile) {
                $profiles[$profile->id] = array();
                $profiles[$profile->id]["name"] = $profile->websiteUrl;
                $profiles[$profile->id]["profileid"] = $profile->id;
                $profiles[$profile->id]["webPropertyId"] = $profile->webPropertyId;
            }
        }
        //unset profiles object, just good practice to free up memory
        unset($profilesObj);
        return $profiles;
    }

    //returns data as array
    function parseData($requestUrl) {
        $r = 0;
        $results = array();
        $requestUrl .= "&start-date=" . date("Y-m-d", strtotime($this->startDate)) . "&end-date=" . date("Y-m-d", strtotime($this->endDate));
        $dataObj = $this->callApi($requestUrl);

        //handle error in api request
        if (isset($dataObj->error)) {
            $results[0]["error"] = "Inbound_GA_Gadata->parseData: " . $dataObj->error;
        } else {
            foreach ($dataObj->rows as $row) {
                $results[$r] = array();
                $h = 0;
                foreach ($dataObj->columnHeaders as $columnHeader) {
                    //rewrite to strip after :
                    $results[$r][ltrim($columnHeader->name, "ga:")] = $row[$h];
                    $h++;
                }
                $r++;
            }
        }
        //unset data object, just good practice to free up memory
        unset($dataObj);
        return $results;
    }
}

?>