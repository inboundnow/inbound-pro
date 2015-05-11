<?php

class INBOUND_Referer
{
    /** @var string */
    private $medium;

    /** @var string */
    private $source;

    /** @var string|null */
    private $searchTerm;

    private function __construct()
    {}

    public static function createKnown($medium, $source, $searchTerm = null)
    {
        $referer = new self();
        $referer->medium = $medium;
        $referer->source = $source;
        $referer->searchTerm = $searchTerm;

        return $referer;
    }

    public static function createUnknown()
    {
        $referer = new self();
        $referer->medium = INBOUND_Medium::UNKNOWN;

        return $referer;
    }

    public static function createInternal()
    {
        $referer = new self();
        $referer->medium = INBOUND_Medium::INTERNAL;

        return $referer;
    }

    public static function createInvalid()
    {
        $referer = new self();
        $referer->medium = INBOUND_Medium::INVALID;

        return $referer;
    }

    /** @return boolean */
    public function isValid()
    {
        return $this->medium !== INBOUND_Medium::INVALID;
    }

    /** @return boolean */
    public function isKnown()
    {
        return !in_array($this->medium, array(INBOUND_Medium::UNKNOWN, INBOUND_Medium::INTERNAL, INBOUND_Medium::INVALID), true);
    }

    /** @return string */
    public function getMedium()
    {
        return $this->medium;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getSearchTerm()
    {
        return $this->searchTerm;
    }
}
