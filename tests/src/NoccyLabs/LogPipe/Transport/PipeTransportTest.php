<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class PipeTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "pipe:/tmp/logpipe-phpunit.pipe";
    }
}
