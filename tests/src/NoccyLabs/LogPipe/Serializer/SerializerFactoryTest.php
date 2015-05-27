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
        $serializer = SerializerFactory::getSerializerForTag("P");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }

    public function testJsonSerializerBeingAvailable()
    {
        $serializer = SerializerFactory::getSerializerForTag("j");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }

    public function testMsgpackSerializerBeingAvailable()
    {
        $serializer = SerializerFactory::getSerializerForTag("m");
        $this->assertNotNull($serializer);
        $this->assertInstanceOf("NoccyLabs\\LogPipe\\Serializer\\SerializerInterface", $serializer);
    }

    /** @expectedException \NoccyLabs\LogPipe\Exception\SerializerException */
    public function testInvalidSerializerThrowingException()
    {
         $serializer = SerializerFactory::getSerializerForTag("_");
    }
}
