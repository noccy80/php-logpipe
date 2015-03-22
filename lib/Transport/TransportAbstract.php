<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;

abstract class TransportAbstract implements TransportInterface
{
    protected $host;

    protected $port;

    protected $stream;

    protected $serializer;

    protected $options;

    public function __construct($options)
    {
        $parsed = [];
        parse_str($options, $parsed);

        $this->options = (array)$parsed;

        $this->serializer = SerializerFactory::getSerializerForTag($this->getOption('serializer', 'php'));
    }

    public function getOption($name, $default=null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function pack(MessageInterface $message)
    {
        try {
            $msg = $this->serializer->serialize($message);
            $header = pack("vV", strlen($msg), crc32($msg));
        } catch (\Exception $e) { }
        return $header.$msg;
    }

    public function unpack(&$buffer)
    {
        if (strlen($buffer) > 6) {
            $header = unpack("vsize/Vcrc32", substr($buffer, 0, 6));
            if ((strlen($buffer) < $header['size'] - 6)) {
                return NULL;
            }
            $data = substr($buffer, 6, $header['size']);
            $buffer = substr($buffer, $header['size']+6);
            if (crc32($data) != $header['crc32']) {
                $buffer = null;
                error_log("Warning: Message with invalid crc32 encountered.");
                return NULL;
            }

            $rcv = $this->serializer->unserialize($data);

            return $rcv;
        }
    }
}
