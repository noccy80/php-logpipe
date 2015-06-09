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

    public function testLoggingErrorsThroughHandler()
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

    public function testLoggingExceptionsThroughHandler()
    {
        $server = TransportFactory::create("tcp:127.0.0.1:8888");
        $server->listen();

        $ch = new ConsoleHandler("tcp:127.0.0.1:8888");
        $ch->setExceptionReporting(true);

        $ch->_onException(new \Exception());

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

        $message = $server->receive();
        $this->assertNull($message);
    }


    /**
     * @dataProvider getPsrLogData
     */
    public function testLoggingWithPsrLogLevels($level_name, $level_num, $data)
    {
        $server = TransportFactory::create("tcp:127.0.0.1:8888");
        $server->listen();

        $ch = new ConsoleHandler("tcp:127.0.0.1:8888");

        $ch->{$level_name}($data);
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
        $this->assertEquals($level_num, $message->getLevel());

        $ch->log($level_num, $data);
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
        $this->assertEquals($level_num, $message->getLevel());

        $message = $server->receive();
        $this->assertNull($message);
    }

    public function getPsrLogData()
    {
        return [
            [ "debug",      100, "Debug data" ],
            [ "info",       200, "Debug data" ],
            [ "notice",     300, "Debug data" ],
            [ "warning",    400, "Debug data" ],
            [ "error",      500, "Debug data" ],
            [ "critical",   550, "Debug data" ],
            [ "alert",      600, "Debug data" ],
            [ "emergency",  700, "Debug data" ],
        ];
    }
}
