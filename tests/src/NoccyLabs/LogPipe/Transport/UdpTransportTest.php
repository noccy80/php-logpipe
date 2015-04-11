<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class UdpTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "udp:127.0.0.1:8901";
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPortThrowsException()
    {
        $transport = new UdpTransport("0.0.0.0:99999");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testListenFailureThrowsException()
    {
        $transport = new UdpTransport("999.999.999.999:12345");
        $transport->listen();
    }

    public function testConnectFailureIsSilent()
    {
        $transport = new UdpTransport("999.999.999.999:12345");
        $transport->connect();
    }
}
