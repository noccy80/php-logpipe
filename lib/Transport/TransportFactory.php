<?php

namespace NoccyLabs\LogPipe\Transport;

class TransportFactory
{
    public static function create($uri)
    {
        list ($type, $resource) = explode(":", $uri, 2);
        $type_class = "NoccyLabs\\LogPipe\\Transport\\" . ucwords($type) . "Transport";
        if (class_exists($type_class)) {
            return new $type_class($resource);
        }
    }
}
