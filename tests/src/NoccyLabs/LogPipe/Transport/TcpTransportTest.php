<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class TcpTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "tcp:127.0.0.1:8901";
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPortThrowsException()
    {
        $transport = new TcpTransport("0.0.0.0:99999");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testListenFailureThrowsException()
    {
        $transport = new TcpTransport("999.999.999.999:12345");
        $transport->listen();
    }

    public function testConnectFailureIsSilent()
    {
        $transport = new TcpTransport("999.999.999.999:12345");
        $transport->connect();
    }
}
