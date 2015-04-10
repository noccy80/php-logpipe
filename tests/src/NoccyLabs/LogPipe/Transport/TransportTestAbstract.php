<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MonologMessage;
use NoccyLabs\LogPipe\Message\ConsoleMessage;

abstract class TransportTestAbstract extends \PhpUnit_Framework_TestCase
{

    protected $server;

    protected $client;

    public function setup()
    {
        $endpoint = $this->getEndpoint();

        $this->server = TransportFactory::create($endpoint);
        $this->client = TransportFactory::create($endpoint);
    }

    public function teardown()
    {
        if ($this->server) {
            $this->server->close();
        }
        if ($this->client) {
            $this->client->close();
        }
    }

    abstract public function getEndpoint();

    /**
     * @expectedException \Exception
     */
    public function testSettingUpWithInvalidSerializer()
    {
        $endpoint = $this->getEndpoint();
        $ep = TransportFactory::create($endpoint.':serializer=bar');
    }

    /**
     * @expectedException \Exception
     */
    public function testSettingUpWithInvalidProtocol()
    {
        $endpoint = $this->getEndpoint();
        $ep = TransportFactory::create($endpoint.':protocol=42');
    }

    public function testThatEndpointsCouldBeCreated()
    {
        $this->assertNotNull($this->server);
        $this->assertNotNull($this->client);
    }

    public function testStartClientWithoutServerPresent()
    {
        $this->client->connect();
    }

    public function testWritingToClientWithoutServerPresent()
    {
        $this->client->connect();
        foreach ($this->getMessages() as $message) {
            $this->client->send($message[0]);
        }
    }

    public function testReopeningListenerAsListener()
    {
        $this->server->listen();
        $this->server->listen();
    }

    public function testListeningTwice()
    {
        $this->server->listen();
        $this->client->listen();
        $this->assertNull($this->server->receive());
        $this->assertNull($this->client->receive());
    }

    public function testReopeningListenerAsClient()
    {
        $this->server->listen();
        $this->server->connect();
    }

    public function testReopeningClientAsClient()
    {
        $this->client->connect();
        $this->client->connect();
    }

    public function testReopeningClientAsServer()
    {
        $this->client->connect();
        $this->client->listen();
    }

    /**
     * @dataProvider getMessages
     */
    public function testSendingMessages($message)
    {
        $this->server->listen();
        $this->client->connect();

        $this->client->send($message);
        $tick = 0;
        while (!($received = $this->server->receive())) {
            if ($tick++ > 100) {
                $this->fail("Didn't receive the message within expected time");
            }
            usleep(1000);
        }
        $this->assertEquals($message, $received);
    }

    public function getMessages()
    {
        $messages = [];

        $messages[] = [
            new MonologMessage(array (
                'message' => 'Oh my god!',
                'context' => array (),
                'level' => 600,
                'level_name' => 'EMERGENCY',
                'channel' => 'main',
                'datetime' => \DateTime::__set_state(array(
                    'date' => '2015-03-07 04:20:39',
                    'timezone_type' => 3,
                    'timezone' => 'Europe/Berlin',
                )),
                'extra' => array (),
                'formatted' => '[2015-03-07 04:20:39] main.EMERGENCY: Oh my god! [] []'
            )) ]
        ;

        $messages[] = [new ConsoleMessage([
            "_client_id" => "tester",
            "message" => "This is a message",
            "channel" => "php.ERROR",
            "level" => 500
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 500
        ])];

        return $messages;
    }
}
