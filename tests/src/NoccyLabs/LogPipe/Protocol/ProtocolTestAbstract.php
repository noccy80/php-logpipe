<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Serializer\SerializerInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;
use NoccyLabs\LogPipe\Message\ConsoleMessage;
use NoccyLabs\LogPipe\Message\MonologMessage;


abstract class ProtocolTestAbstract extends \PhpUnit_Framework_TestCase
{

    abstract public function getProtocol();


    public function testProtocolVersionIsValid()
    {
        $proto = $this->getProtocol();
        $version = $proto->getVersion();
        $this->assertGreaterThan(0, $version);
        $this->assertLessThan(64, $version);
    }

    /**
     * @dataProvider getSerializerData
     */
    public function testProtocolWithSerializer($serializer)
    {
        $ser = SerializerFactory::getSerializerForName($serializer);
        $proto = $this->getProtocol();

        $message1 = new ConsoleMessage([ "message"=>"Hello World!" ]);
        $message2 = new MonologMessage([ "message"=>"Monolog message" ]);
        
        $data1 = $proto->pack($message1, $ser);
        $this->assertNotNull($data1);
        $data2 = $proto->pack($message2, $ser);
        $this->assertNotNull($data2);
        
        $data = $data1.$data2;
        
        $extr1 = $proto->unpack($data, $ser);
        $this->assertEquals($message1, $extr1);
        $this->assertNotNull($data);
        $extr2 = $proto->unpack($data, $ser);
        $this->assertEquals($message2, $extr2);
        $this->assertEmpty($data);
        $extr3 = $proto->unpack($data, $ser);
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

        $data = $proto->pack($message, $ser);
        $this->assertNotNull($data);
        $extr = $proto->unpack($data, $ser);
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
        $unpack = $proto->unpack($buffer, $ser);
        $this->assertNull($unpack, $ser);
    }

    /**
     * @dataProvider getSerializerData
     */
    public function testUnpackingCorruptedData($serializer)
    {
        $ser = SerializerFactory::getSerializerForName($serializer);

        $proto = $this->getProtocol($ser);

        $message = new ConsoleMessage([ "message"=>str_repeat("Hello",15000) ]);

        $data = $proto->pack($message, $ser);
        $data[128]=0;
        $extr = $proto->unpack($data, $ser);
        $this->assertNull($extr);
    }
}
