<?php

abstract class INBOUND_ConfigFileReaderTrait
{
    /** @var string */
    private $fileName;

    /** @var array */
    private $referers = array();

    public function __construct ()
    {

    }

    private function init($fileName)
    {
        if (!file_exists($fileName)) {
            throw INBOUND_InvalidArgumentException::fileNotExists($fileName);
        }

        $this->fileName = $fileName;
    }

    abstract protected function parse($content);

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
}
