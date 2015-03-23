<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Exception\SerializerException;

class SerializerFactory
{
    protected static $default_serializers = [
        "PhpSerializer",
        "JsonSerializer",
        "MsgpackSerializer",
    ];

    protected static $serializers = [];

    protected static function setup()
    {
        static $was_setup;
        if ($was_setup) { return; }

        foreach (self::$default_serializers as $serializer) {
            $serializer = "NoccyLabs\\LogPipe\\Serializer\\{$serializer}";
            $inst = new $serializer();
            $tag = $inst->getTag();
            self::$serializers[$tag] = $inst;
        }

        $was_setup = true;
    }

    public static function getSerializerForTag($tag)
    {
        self::setup();
        if (array_key_exists($tag, self::$serializers)) {
            return self::$serializers[$tag];
        }
        throw new SerializerException("No serializer registered for tag [{$tag}]");
    }

    public static function getSerializerForName($name)
    {
        self::setup();
        foreach (self::$serializers as $serializer) {
            if ($serializer->getName() == $name) {
                return $serializer;
            }
        }
        throw new SerializerException("No serializer registered for name [{$name}]");
    }
}
