<?php

namespace NoccyLabs\LogPipe\Protocol;

class ProtocolManager
{
    protected $protocols = [];

    public static function getInstance()
    {
        $inst = new self();

        return $inst;
    }

    public function __construct()
    {
        $this->registerDefaultProtocols();
    }

    protected function registerDefaultProtocols()
    {
        $this->registerProtocol(new PipeV1Protocol());
    }

    public function registerProtocol(ProtocolInterface $protocol)
    {
        $tag = $protocol->getVersion();
        $this->protocols[$tag] = $protocol;
    }
}
