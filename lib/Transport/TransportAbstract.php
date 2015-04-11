<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;
use NoccyLabs\LogPipe\Protocol\PipeV1Protocol;
use NoccyLabs\LogPipe\Serializer\SerializerInterface;

/**
 * Class TransportAbstract
 * @package NoccyLabs\LogPipe\Transport
 */
abstract class TransportAbstract implements TransportInterface
{
    /**
     * @var
     */
    protected $host;

    /**
     * @var
     */
    protected $port;

    /**
     * @var
     */
    protected $stream;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var PipeV1Protocol
     */
    protected $protocol;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param $options
     * @throws \Exception
     * @throws \NoccyLabs\LogPipe\Exception\SerializerException
     */
    public function __construct($options)
    {
        $parsed = [];
        parse_str($options, $parsed);

        $this->options = (array)$parsed;

        $this->serializer = SerializerFactory::getSerializerForName($this->getOption('serializer', 'php'));

        $proto = $this->getOption('protocol', 1);
        switch ($proto) {
            case 1:
                $this->protocol   = new PipeV1Protocol();
                break;
            default:
                throw new \Exception("Invalid/unsupported protocol requested: {$proto}");
        }

    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function getOption($name, $default=null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

}
