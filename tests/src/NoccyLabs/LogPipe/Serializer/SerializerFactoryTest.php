<?php

namespace NoccyLabs\LogPipe\Serializer;

class SerializerFactoryTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function testPhpSerializerBeingAvailable()
    {
        $serializer = SerializerFactory::getSerializerForTag("php");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }

    public function testJsonSerializerBeingAvailable()
    {
        $serializer = SerializerFactory::getSerializerForTag("json");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }

    public function testMsgpackSerializerBeingAvailable()
    {
        $serializer = SerializerFactory::getSerializerForTag("msgpack");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }
}
