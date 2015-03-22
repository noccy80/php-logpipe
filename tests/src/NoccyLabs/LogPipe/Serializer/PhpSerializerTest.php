<?php

namespace NoccyLabs\LogPipe\Serializer;

require_once __DIR__."/SerializerTestAbstract.php";

class PhpSerializerTest extends SerializerTestAbstract
{
    public function getSerializer()
    {
        return new PhpSerializer();
    }
}
