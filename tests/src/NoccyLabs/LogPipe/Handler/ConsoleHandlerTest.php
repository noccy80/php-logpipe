<?php

namespace NoccyLabs\LogPipe\Handler;

use NoccyLabs\LogPipe\Transport\TransportFactory;

class ConsoleHandlerTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function testLoggingThroughMonolog()
    {
        $server = TransportFactory::create("tcp:127.0.0.1:8888");
        $server->listen();

        $ch = new ConsoleHandler("tcp:127.0.0.1:8888");
        $ch->setErrorReporting(true);

        @trigger_error("Hello World");

        $ticker = 0;
        while (!($message = $server->receive())) {
            $ticker++;
            if ($ticker > 100) {
                $this->fail("Did not receive message in time!");
                return;
            }
            usleep(1000);
        }
        $this->assertNotNull($message);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Message\\ConsoleMessage", $message);
        $this->assertContains("Hello World", $message->getText());

        $message = $server->receive();
        $this->assertNull($message);

    }
}
