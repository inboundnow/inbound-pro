<?php

class ZapierMarketplace{

    protected $appSecret;
    protected $marketplaceSignature;
    protected $marketplaceCaller;
    protected $marketplaceUserId;
    protected $marketplaceUserEmail;
    protected $marketplaceUserFirstName;
    protected $marketplaceUserLastName;
    protected $marketplaceUserGlobal;
    protected $marketplacePortalId;
    protected $marketplaceAppName;
    protected $marketplaceAppCallbackURL;
    protected $marketplaceAppPageURL;
    protected $marketplaceAppCanvasURL;
    protected $marketplaceAccessToken;
    protected $marketplaceAccessExpires;
    protected $marketplaceRefreshToken;

    /**
    * Constructor.
    *
    * @param $request: The $_REQUEST array from the HTTP request
    * @param $appSecret: Your app secret key
    **/
    function __construct($request, $appSecret) {
        $this->appSecret = $appSecret;
        $this->marketplaceSignature = $request['zapier_marketplace_signature'];
        $this->marketplaceCaller = $request['zapier_marketplace_caller'];
        $this->marketplaceUserId = $request['zapier_marketplace_user_id'];
        $this->marketplaceUserEmail = $request['zapier_marketplace_user_email'];
        $this->marketplaceUserFirstName = $request['zapier_marketplace_user_first_name'];
        $this->marketplaceUserLastName = $request['zapier_marketplace_user_last_name'];
        $this->marketplaceUserGlobal = $request['zapier_marketplace_user_global'];
        $this->marketplacePortalId = $request['zapier_marketplace_portal_id'];
        $this->marketplaceAppName = $request['zapier_marketplace_app_name'];
        $this->marketplaceAppCallbackURL = $request['zapier_marketplace_app_callbackUrl'];
        $this->marketplaceAppPageURL = $request['zapier_marketplace_app_pageUrl'];
        $this->marketplaceAppCanvasURL = $request['zapier_marketplace_canvasUrl'];
        $this->marketplaceAccessToken = $request['zapier_marketplace_accessToken'];
        $this->marketplaceAccessExpires = $request['zapier_marketplace_accessExpires'];
        $this->marketplaceRefreshToken = $request['zapier_marketplace_refreshToken'];
    }

    /**
    * Verifies that request is from Zapier Marketplace
    *
    * @returns boolean true if request is verfied, false if not verified
    **/
    public function verifyRequest() {
        return $this->parseSignedRequest($this->marketplaceSignature);
    }

    /**
    * @returns String value of zapier_marketplace_caller
    **/
    public function getCaller() {
        return $this->marketplaceCaller;
    }

    /**
    * @returns String value of zapier_marketplace_user_id
    **/
    public function getUserId() {
        return $this->marketplaceUserId;
    }

    /**
    * @returns String value of zapier_marketplace_user_email
    **/
    public function getUserEmail() {
        return $this->marketplaceUserEmail;
    }

    /**
    * @returns String value of zapier_marketplace_user_first_name
    **/
    public function getUserFirstName() {
        return $this->marketplaceUserFirstName;
    }

    /**
    * @returns String value of zapier_marketplace_user_last_name
    **/
    public function getUserLastName() {
        return $this->marketplaceUserLastName;
    }

    /**
    * @returns Boolean value of zapier_marketplace_user_global
    **/
    public function getUserGlobal() {
        if ($this->marketplaceUserGlobal == 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
    * @returns String value of zapier_marketplace_portal_id
    **/
    public function getPortalId() {
        return $this->marketplacePortalId;
    }

    /**
    * @returns String value of zapier_marketplace_app_name
    **/
    public function getAppName() {
        return $this->marketplaceAppName;
    }

    /**
    * @returns String value of zapier_marketplace_app_callbackUrl
    **/
    public function getAppCallbackURL() {
        return $this->marketplaceAppCallbackURL;
    }

    /**
    * @returns String value of zapier_marketplace_app_pageUrl
    **/
    public function getAppPageURL() {
        return $this->marketplaceAppPageURL;
    }

    /**
    * @returns String value of zapier_marketplace_app_canvasUrl
    **/
    public function getAppCanvasURL() {
        return $this->marketplaceAppCanvasURL;
    }

    /**
    * @returns String value of zapier_marketplace_accessToken
    **/
    public function getAccessToken() {
        return $this->marketplaceAccessToken;
    }

    /**
    * @returns String value of zapier_marketplace_accessExpires
    **/
    public function getAccessExpires() {
        return $this->marketplaceAccessExpires;
    }

    /**
    * @returns String value of zapier_marketplace_refreshToken
    **/
    public function getRefreshToken() {
        return $this->marketplaceRefreshToken;
    }



    /**
    * Parses and decodes zapier_marketplace_signature to verify
    * that request is from Zapier
    *
    * @param $marketplaceSignature: The encoded signature from $_REQUEST['zapier_marketplace_signature']
    *
    * @returns boolean true if request is verified, false if not verified
    **/
    protected function parseSignedRequest($marketplaceSignature) {
        list($encoded_sig, $payload) = explode('.', $marketplaceSignature, 2);

        // decode the data
        $sig = $this->base64UrlDecode($encoded_sig);
        $data = $this->base64UrlDecode($payload);

        // check sig
        $expected_sig = hash_hmac('sha1', $data,
                                  $this->appSecret, $raw = true);

        if ($sig != $expected_sig) {
            return false;
        }

        return true;
    }

    /**
    * Decodes base64 encoded data
    *
    * @param $input: base 64 encoded string
    *
    * @returns decoded string
    **/
    protected function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}