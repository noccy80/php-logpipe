<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Serializer\SerializerInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;
use NoccyLabs\LogPipe\Message\ConsoleMessage;
use NoccyLabs\LogPipe\Message\MonologMessage;

require_once __DIR__."/ProtocolTestAbstract.php";

class PipeV1ProtocolTest extends ProtocolTestAbstract
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function getProtocol(SerializerInterface $serializer)
    {
        return new PipeV1Protocol($serializer);
    }
    
    /**
     * @dataProvider getSerializerData
     */
    public function testProtocolWithSerializer($serializer)
    {
        $ser = SerializerFactory::getSerializerForName($serializer);
        $proto = $this->getProtocol($ser);

        $message1 = new ConsoleMessage([ "message"=>"Hello World!" ]);
        $message2 = new MonologMessage([ "message"=>"Monolog message" ]);
        
        $data1 = $proto->pack($message1);
        $this->assertNotNull($data1);
        $data2 = $proto->pack($message2);
        $this->assertNotNull($data2);
        
        $data = $data1.$data2;
        
        $extr1 = $proto->unpack($data);
        $this->assertEquals($message1, $extr1);
        $this->assertNotNull($data);
        $extr2 = $proto->unpack($data);
        $this->assertEquals($message2, $extr2);
        $this->assertEmpty($data);
        $extr3 = $proto->unpack($data);
        $this->assertEmpty($extr3);
        
    }

    /**
     * @dataProvider getSerializerData
     */
    public function testHugeMessagePayloadWithSerializer($serializer)
    {
        $ser = SerializerFactory::getSerializerForName($serializer);
        $proto = $this->getProtocol($ser);

        $message = new ConsoleMessage([ "message"=>str_repeat("Hello",15000) ]);

        $data = $proto->pack($message);
        $this->assertNotNull($data);
        $extr = $proto->unpack($data);
        $this->assertEquals($message, $extr);
    }

    public function getSerializerData()
    {
        return [
            [ "php" ],
            [ "json" ],
            [ "msgpack" ],
        ];
    }
    
    /**
     * @dataProvider getSerializerData
     */
    public function testUnpackingBogusData($serializer)
    {
        $ser = SerializerFactory::getSerializerForName($serializer);
        $proto = $this->getProtocol($ser);
        $buffer = str_repeat("\0",10000);
        $unpack = $proto->unpack($buffer);
        $this->assertNull($unpack);
    }
}
