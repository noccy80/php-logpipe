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
        $this->server->listen();

        $this->client = TransportFactory::create($endpoint);
        $this->client->connect();
    }

    public function teardown()
    {
        $this->server->close();
        $this->client->close();
    }

    abstract public function getEndpoint();

    /**
     * @dataProvider getMessages
     */
    public function testSendingMessages($message)
    {
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
