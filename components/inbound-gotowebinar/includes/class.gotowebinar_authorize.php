<?php
class LP_CitrixAPI {

	public $_organizerKey;
	public $_accessToken;

	public function __construct ($_accessToken = null, $_organizerKey = null) {
		$this->_accessToken = $_accessToken;
		$this->_organizerKey = $_organizerKey;
	}

	public function getOAuthToken ($_apiKey = null, $_callbackUrl = null, $authorize=null) {
		if ($authorize == 1) {
			global $wp_session;
			//echo $_callbackUrl;exit;
			$_callbackUrl = urlencode($_callbackUrl);
			set_transient('lp_gotowebinar_oauth' , true , 60 * 60 * 1);
               ?>
               <a href='<?php echo 'https://api.citrixonline.com/oauth/authorize?client_id='. $_apiKey .'&redirect_uri=' . $_callbackUrl; ?>'>Click Here to Authorize!</a>

               <?php 
				//echo $_SESSION['gotowebinar_oauth'];exit;
            exit();
		}

		if ($authorize == 2) {
			$url = 'https://api.citrixonline.com/oauth/access_token?grant_type=authorization_code&code='. $_GET['code'] .'&client_id='. $_apiKey;
			return $this->makeApiRequest($url);
		}
	}

	/**
	 * @name getAttendeesByOrganizer
	 * @desc GoToMeeting API
	 */

	public function getAttendeesByOrganizer () {
		$url  = 'https://api.citrixonline.com/G2M/rest/organizers/'. $this->_organizerKey .'/attendees';
		$url .= '?startDate='. date('c');
		$url .= '?endDate='. date('c', strtotime("-7 Days"));

		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getFutureMeetings
	 * @desc GoToMeeting API
	 */

	public function getFutureMeetings () {
		$url  = 'https://api.citrixonline.com/G2M/rest/meetings?scheduled=true';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getUpcomingWebinars
	 * @desc GoToWebinar API
	 */
	public function getUpcomingWebinars () {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/upcomingWebinars';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getUpcomingWebinars
	 * @desc GoToWebinar API
	 */
	public function getPastWebinars () {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/historicalWebinars';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	/**
	 * @name getWebinarAttendees
	 * @desc GoToWebinar API
	 */

	public function getWebinarAttendees ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey .'/attendees';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	public function getWebinarRegistrants ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey .'/registrants';
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}

	public function getWebinar ($webinarKey) {
		$url  = 'https://api.citrixonline.com/G2W/rest/organizers/'. $this->_organizerKey .'/webinars/'. $webinarKey;
		return $this->makeApiRequest($url, 'GET', array(), $this->getJsonHeaders());
	}
	/**
	 * @param String $url
	 * @param String $requestType
	 * @param Array $postData
	 * @param Array $headers
	 */

	public function makeApiRequest ($url = null, $requestType = 'GET', $postData = array(), $headers = array()) {

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    if ($requestType == 'POST') {
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	    }

	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    $data = curl_exec($ch);

	    $validResponseCodes = array(200, 201, 409);
	    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

	    if (curl_errno($ch)) {
	    	curl_close($ch);
	        return curl_error($ch);
	    }
	    elseif (!in_array($responseCode, $validResponseCodes)) {
	        if ($this->isJson($data)) {
	            $data = json_decode($data);
	        }
	    }

	    curl_close($ch);
	    return $data;
	}

	public function getJsonHeaders () {
		return array(
			"HTTP/1.1",
			"Content-type: application/json",
			"Accept: application/json",
			"Authorization: OAuth oauth_token=". $this->_accessToken
		);
	}

	public function isJson ($string) {
	    $isJson = 0;
	    $decodedString = json_decode($string);

	    if (is_array($decodedString) || is_object($decodedString)) {
	        $isJson = 1;
	    }

	    return $isJson;
	}
}
