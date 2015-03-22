<?php

namespace NoccyLabs\LogPipe\Transport;

require_once __DIR__."/TransportTestAbstract.php";

class TcpTransportTest extends TransportTestAbstract
{

    public function getEndpoint()
    {
        return "tcp:127.0.0.1:8901";
    }
}
