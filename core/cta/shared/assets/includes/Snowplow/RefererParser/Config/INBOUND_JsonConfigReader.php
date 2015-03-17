<?php

class INBOUND_JsonConfigReader extends INBOUND_ConfigReaderInterface
{
	/** @var string */
    private $fileName;

    /** @var array */
    private $referers = array();

    public function __construct ( $fileName )
    {
    	$this->fileName = $fileName;
    }

    private function init($fileName)
    {
        if (!file_exists($fileName)) {
            throw INBOUND_InvalidArgumentException::fileNotExists($fileName);
        }

        $this->fileName = $fileName;
    }

    private function read()
    {
        if ($this->referers) {
            return;
        }

        $hash = $this->parse(file_get_contents($this->fileName));

        foreach ($hash as $medium => $referers) {
            foreach ($referers as $source => $referer) {
                foreach ($referer['domains'] as $domain) {
                    $this->referers[$domain] = array(
                        'source'     => $source,
                        'medium'     => $medium,
                        'parameters' => isset($referer['parameters']) ? $referer['parameters'] : array(),
                    );
                }
            }
        }
    }

    public function lookup($lookupString)
    {
        $this->read();

        return isset($this->referers[$lookupString]) ? $this->referers[$lookupString] : null;
    }

    protected function parse($content)
    {
    	return json_decode($content, true);
    }
}