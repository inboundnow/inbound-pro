<?php

/* Usage:

include_once(WP_CTA_URLPATH . '/shared/tracking/sources/Snowplow/RefererParser/INBOUND_Parser.php');
include_once(WP_CTA_URLPATH . '/shared/tracking/sources/Snowplow/RefererParser/INBOUND_Referer.php');
include_once(WP_CTA_URLPATH . '/shared/tracking/sources/Snowplow/RefererParser/INBOUND_Medium.php');

// intialized the parser class
$this->parser = new INBOUND_Parser();

$referer = $this->parser->parse($source);
    if ( $referer->isKnown() ) {
        return $referer->getMedium();
    } else {
        return 'referral';
    }
*/

/*

    Functions to use in plugin

    // $source ex = http://clean.dev/
    // $origin ex = http://glocal.dev/?utm_source=the_source&utm_medium=camp%20med&utm_term=Bought%20keyword&utm_content=Funny%20Text&utm_campaign=400kpromo

    // ORIGIN URL grabbed from first ever page view

    function check_lead_source ( $source, $origin_url = '' )
        {
            if ( $source )
            {
                $decoded_source = urldecode($source);

                if ( stristr($decoded_source, 'utm_medium=cpc') || stristr($decoded_source, 'utm_medium=ppc') || stristr($decoded_source, 'aclk') || stristr($decoded_source, 'gclid') )
                    return 'paid';

                if ( stristr($source, 'utm_') )
                {
                    $url = $source;
                    $url_parts = parse_url($url);
                    parse_str($url_parts['query'], $path_parts);

                    if ( isset($path_parts['adurl']) )
                        return 'paid';

                    if ( isset($path_parts['utm_medium']) )
                    {
                        if ( $path_parts['utm_medium'] == 'cpc' || $path_parts['utm_medium'] == 'ppc' )
                            return 'paid';

                        if ( $path_parts['utm_medium'] == 'social' )
                            return 'social';

                        if ( $path_parts['utm_medium'] == 'email' )
                            return 'email';
                    }

                    if ( isset($path_parts['utm_source']) )
                    {
                        if ( stristr($path_parts['utm_source'], 'email') )
                            return 'email';
                    }
                }

                $referer = $this->parser->parse(
                     $source
                );

                if ( $referer->isKnown() )
                    return $referer->getMedium();
                else
                    return 'referral';
            }
            else
            {
                $decoded_origin_url = urldecode($origin_url);

                if ( stristr($decoded_origin_url, 'utm_medium=cpc') || stristr($decoded_origin_url, 'utm_medium=ppc') || stristr($decoded_origin_url, 'aclk') || stristr($decoded_origin_url, 'gclid') )
                    return 'paid';

                if ( stristr($decoded_origin_url, 'utm_') )
                {
                    $url = $decoded_origin_url;
                    $url_parts = parse_url($url);
                    parse_str($url_parts['query'], $path_parts);

                    if ( isset($path_parts['adurl']) )
                        return 'paid';

                    if ( isset($path_parts['utm_medium']) )
                    {
                        if ( $path_parts['utm_medium'] == 'cpc' || $path_parts['utm_medium'] == 'ppc' )
                            return 'paid';

                        if ( $path_parts['utm_medium'] == 'social' )
                            return 'social';

                        if ( $path_parts['utm_medium'] == 'email' )
                            return 'email';
                    }

                    if ( isset($path_parts['utm_source']) )
                    {
                        if ( stristr($path_parts['utm_source'], 'email') )
                            return 'email';
                    }
                }

                return 'direct';
            }
        }

        function print_readable_source ( $source )
        {
            switch ( $source )
            {
                case 'search' :
                    return 'Organic Search';
                break;

                case 'social' :
                    return 'Social Media';
                break;

                case 'email' :
                    return 'Email Marketing';
                break;

                case 'referral' :
                    return 'Referral';
                break;

                case 'paid' :
                    return 'Paid';
                break;

                case 'direct' :
                    return 'Direct';
                break;
            }
        }

 */

//echo 'hi';
include_once('Config/INBOUND_ConfigReaderInterface.php');
include_once('Config/INBOUND_JsonConfigReader.php');

class INBOUND_Parser
{
    /** @var ConfigReaderInterface */
    private $configReader;

    /**
     * @var string[]
     */
    private $internalHosts = array();

    public function __construct(INBOUND_ConfigReaderInterface $configReader = null, array $internalHosts = array() )
    {
        $this->configReader = $configReader ? $configReader : self::createDefaultConfigReader();
        $this->internalHosts = $internalHosts;
    }

    /**
     * Parse referer URL
     *
     * @param string $refererUrl
     * @param string $pageUrl
     * @return Referer
     */
    public function parse($refererUrl, $pageUrl = null)
    {
        $refererParts = $this->parseUrl($refererUrl);
        if (!$refererParts) {
            return INBOUND_Referer::createInvalid();
        }

        $pageUrlParts = $this->parseUrl($pageUrl);

        //print_r($refererParts);

        if ($pageUrlParts
            && $pageUrlParts['host'] === $refererParts['host']
            || in_array($refererParts['host'], $this->internalHosts)) {
            return INBOUND_Referer::createInternal();
        }

        $referer = $this->lookup($refererParts['host'], $refererParts['path']);

        if (!$referer) {
            return INBOUND_Referer::createUnknown();
        }

        $searchTerm = null;

        if (is_array($referer['parameters'])) {
            parse_str($refererParts['query'], $queryParts);

            //foreach ($queryParts as $key => $parameter) {
            $searchTerm = isset($queryParts['q']) ? $queryParts['q'] : $searchTerm;
            //}
        }

        return INBOUND_Referer::createKnown($referer['medium'], $referer['source'], $searchTerm);
    }

    private static function parseUrl($url)
    {
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        if (!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'))) {
            return null;
        }

        return array_merge(array('query' => null, 'path' => '/'), $parts);
    }

    private function lookup($host, $path)
    {
        $referer = $this->lookupPath($host, $path);

        if ($referer) {
            return $referer;
        }

        return $this->lookupHost($host);
    }

    private function lookupPath($host, $path)
    {
        $referer = $this->lookupHost($host, $path);

        if ($referer) {
            return $referer;
        }

        $path = substr($path, 0, strrpos($path, '/'));

        if (!$path) {
            return null;
        }

        return $this->lookupPath($host, $path);
    }

    private function lookupHost($host, $path = null)
    {
        do {
            $referer = $this->configReader->lookup($host . $path);
            $host = substr($host, strpos($host, '.') + 1);
        } while (!$referer && substr_count($host, '.') > 0);

        return $referer;
    }

    private static function createDefaultConfigReader()
    {
        //TODO FIX WITH GLOBAL SHARED CONSTANT
        return new INBOUND_JsonConfigReader( INBOUNDNOW_SHARED_PATH . 'assets/includes/referers.json');
    }
}
