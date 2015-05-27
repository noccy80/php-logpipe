<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Message\MonologMessage;
use NoccyLabs\LogPipe\Message\ConsoleMessage;
use NoccyLabs\LogPipe\Common\ArrayUtils;

abstract class SerializerTestAbstract extends \PhpUnit_Framework_TestCase
{

    abstract function getSerializer();

    /**
     * @dataProvider getMessages
     */
    public function testSerializerCanSerializeMessages($message)
    {
        $serializer = $this->getSerializer();
        $serialized = $serializer->serialize($message);
        $this->assertNotNull($serialized);
    }

    /**
     * @dataProvider getMessages
     */
    public function testSerializerCanUnserializeMessages($message)
    {
        $serializer = $this->getSerializer();
        $serialized = $serializer->serialize($message);
        $unserialized = $serializer->unserialize($serialized);
        $this->assertEquals($message, $unserialized);
    }

    /**
     * @expectedException NoccyLabs\LogPipe\Exception\SerializerException
     */
    public function testUnserializingInvalidDataThrowsException()
    {
        $this->getSerializer()->unserialize("invalid-data");
    }

    public function testSerializingInvalidDataThrowsExceptionOrRecovers()
    {
        $unserializable = new MonologMessage();
        $unserializable->setData(array(
            'testid',
            array(
                'message' => new UnserializableDummyClass()
            )
        ));
        try {
            $data = $this->getSerializer()->serialize($unserializable);
        } catch (\NoccyLabs\LogPipe\Exception\SerializerException $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertNotNull($data);
    }

    public function getMessages()
    {
        $messages = [];

        $messages[] = [new MonologMessage(array (
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
            ))];

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

class UnserializableDummyClass {
    private function __sleep() { throw new \Exception(); }
    private function __wakeup() {}
}
