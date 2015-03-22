<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Exception\SerializerException;

class MsgpackSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "serializer.msgpack";
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(MessageInterface $message)
    {
        $raw = [ get_class($message), $message->getData() ];
        try {
            $data = \msgpack_pack($raw);
        } catch (\Exception $e) {
            throw new SerializerException("Unable to serialize data", 0, $e);
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        try {
            $data = \msgpack_unpack($data);
        } catch (\Exception $e) {
            throw new SerializerException("Unable to serialize data", 0, $e);
        }
        $class = $data[0];
        $inst = new $class();
        $inst->setData($data[1]);
        return $inst;
    }
}
