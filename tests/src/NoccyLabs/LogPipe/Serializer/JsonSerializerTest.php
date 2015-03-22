<?php

namespace NoccyLabs\LogPipe\Serializer;

require_once __DIR__."/SerializerTestAbstract.php";

class JsonSerializerTest extends SerializerTestAbstract
{
    public function getSerializer()
    {
        return new JsonSerializer();
    }
}
