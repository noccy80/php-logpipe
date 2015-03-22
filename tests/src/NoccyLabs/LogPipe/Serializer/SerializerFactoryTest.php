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
}
