<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class UdpTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "udp:127.0.0.1:8901";
    }
}
