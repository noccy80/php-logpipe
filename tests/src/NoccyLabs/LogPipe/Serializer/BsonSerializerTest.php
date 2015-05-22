<?php

namespace NoccyLabs\LogPipe\Serializer;

require_once __DIR__."/SerializerTestAbstract.php";

class BsonSerializerTest extends SerializerTestAbstract
{
    public function getSerializer()
    {
        return new BsonSerializer();
    }
}
