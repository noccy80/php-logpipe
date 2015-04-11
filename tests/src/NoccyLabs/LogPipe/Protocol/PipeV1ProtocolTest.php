<?php

namespace NoccyLabs\LogPipe\Protocol;

require_once __DIR__."/ProtocolTestAbstract.php";

class PipeV1ProtocolTest extends ProtocolTestAbstract
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function getProtocol()
    {
        return new PipeV1Protocol();
    }
    
}
