<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;
use NoccyLabs\LogPipe\Protocol\PipeV1Protocol;

abstract class TransportAbstract implements TransportInterface
{
    protected $host;

    protected $port;

    protected $stream;

    protected $options;

    protected $protocol;

    public function __construct($options)
    {
        $parsed = [];
        parse_str($options, $parsed);

        $this->options = (array)$parsed;

        $serializer = SerializerFactory::getSerializerForName($this->getOption('serializer', 'php'));

        $proto = $this->getOption('protocol', 1);
        switch ($proto) {
            case 1:
                $this->protocol   = new PipeV1Protocol($serializer);
                break;
            default:
                throw new \Exception("Invalid/unsupported protocol requested: {$proto}");
        }

    }

    public function getOption($name, $default=null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

}
