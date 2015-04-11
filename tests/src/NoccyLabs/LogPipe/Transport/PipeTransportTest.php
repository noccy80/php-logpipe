<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class PipeTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "pipe:/tmp/logpipe-phpunit.pipe";
    }

    /**
     * @expectedException \Exception
     */
    public function testListenFailureThrowsException()
    {
        $transport = new PipeTransport("/blargh");
        $transport->listen();
    }

    public function testConnectFailureIsSilent()
    {
        $transport = new PipeTransport("/blargh");
        $transport->connect();
    }
}
