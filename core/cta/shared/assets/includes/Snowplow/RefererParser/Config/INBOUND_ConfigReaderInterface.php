<?php

include('INBOUND_ConfigFileReaderTrait.php');

abstract class INBOUND_ConfigReaderInterface extends INBOUND_ConfigFileReaderTrait
{
    /**
     * @param string $lookupString
     * @return array
     */
    public function lookup($lookupString)
    {

    }
}
