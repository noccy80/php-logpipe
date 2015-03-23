<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Exception\SerializerException;

class JsonSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "json";
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return "j";
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(MessageInterface $message)
    {
        $raw = [ get_class($message), $message->getData() ];
        try {
            $data = \json_encode($raw);
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
            $data = (array)\json_decode($data);
        } catch (\Exception $e) {
            throw new SerializerException("Unable to unserialize data", 0, $e);
        }
        $class = $data[0];
        $inst = new $class();
        $inst->setData((array)$data[1]);
        return $inst;
    }
}
