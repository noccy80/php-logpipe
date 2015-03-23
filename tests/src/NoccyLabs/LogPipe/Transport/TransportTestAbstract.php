<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MonologMessage;

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

        return $messages;
    }
}
