<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;

class TransportFactoryTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function testCreatingTransportWithImplicitPipe()
    {
        $transport = TransportFactory::create("/tmp/foobar");
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Transport\\PipeTransport", $transport);
    }

    public function testCreatingTransportFromExplicitClass()
    {
        $transport = TransportFactory::create("NoccyLabs\\LogPipe\\Transport\\TestTransport:foo");
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Transport\\TestTransport", $transport);
        $this->assertEquals("foo", $transport->test);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreatingInvalidTransport()
    {
        $transport = TransportFactory::create("fork:baz");
    }
}

class TestTransport extends TransportAbstract
{
    public $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function send(MessageInterface $message)
    {
    }

    public function receive($blocking=false)
    {
    }

    public function listen()
    {
    }

    public function connect()
    {
    }

    public function close()
    {
    }
}
