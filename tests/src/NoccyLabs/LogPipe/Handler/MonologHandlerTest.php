<?php

namespace NoccyLabs\LogPipe\Handler;

use NoccyLabs\LogPipe\Transport\TransportFactory;
use Monolog\Logger;

class MonologHandlerTest extends \PhpUnit_Framework_TestCase
{
    protected $server;

    public function setup()
    {
        $this->server = TransportFactory::create("tcp:127.0.0.1:8888");
        $this->server->listen();
    }

    public function teardown()
    {
        if ($this->server) {
            $this->server->close();
        }
    }

    public function testLoggingThroughMonolog()
    {

        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler("tcp:127.0.0.1:8888"));

        $logger->info("Hello World");
        $ticker = 0;
        while (!($message = $this->server->receive())) {
            $ticker++;
            if ($ticker > 100) {
                $this->fail("Did not receive message in time!");
                return;
            }
            usleep(1000);
        }
        $this->assertNotNull($message);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Message\\MonologMessage", $message);
        $this->assertEquals("Hello World", $message->getText());

        $message = $this->server->receive();
        $this->assertNull($message);

    }

    public function testExplicitlyPassingTransportToLogger()
    {
        $transport = TransportFactory::create("tcp:127.0.0.1:8888");

        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler($transport));

        $logger->info("Hello World");
        $ticker = 0;
        while (!($message = $this->server->receive())) {
            $ticker++;
            if ($ticker > 100) {
                $this->fail("Did not receive message in time!");
                return;
            }
            usleep(1000);
        }
        $this->assertNotNull($message);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Message\\MonologMessage", $message);
        $this->assertEquals("Hello World", $message->getText());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassingInvalidTransportToLogger()
    {
        $logger = new Logger("main");
        $handler = new LogPipeHandler("invalid");
        $logger->pushHandler($handler);
        $logger->info("test");
    }

}
