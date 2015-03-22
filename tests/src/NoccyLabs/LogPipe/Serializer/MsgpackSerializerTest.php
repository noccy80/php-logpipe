<?php

namespace NoccyLabs\LogPipe\Serializer;

require_once __DIR__."/SerializerTestAbstract.php";

class MsgpackSerializerTest extends SerializerTestAbstract
{
    public function setup()
    {
        if (!function_exists('msgpack_pack')) {
            $this->markTestSkipped('msgpack extension not available');
        }
    }

    public function getSerializer()
    {
        return new MsgpackSerializer();
    }
}
