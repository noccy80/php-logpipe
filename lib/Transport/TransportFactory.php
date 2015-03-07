<?php

namespace NoccyLabs\LogPipe\Transport;

class TransportFactory
{
    public static function create($uri)
    {
        if (strpos($uri,":")===false) {
            $uri = "pipe:{$uri}";
        }

        list ($type, $resource) = explode(":", $uri, 2);

        // Scenario 1: full class, like 'My\Lib\FooTransport:foo.parameters'
        if (class_exists($type)) {
            return new $type($resource);
        }

        // Scenario 2: matching class, like 'udp:udp.parameters'
        $type_class = "NoccyLabs\\LogPipe\\Transport\\" . ucwords($type) . "Transport";
        if (class_exists($type_class)) {
            return new $type_class($resource);
        }

        throw new \InvalidArgumentException("Unable to create a transport from URI '{$uri}'");
    }
}
