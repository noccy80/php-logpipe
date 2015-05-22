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
     * {@inheritdoc}
     */
    public function isSupported()
    {
        return (is_callable("json_encode") && is_callable("json_decode"));
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(MessageInterface $message)
    {
        $raw = [ get_class($message), $message->getData() ];
        $data = @\json_encode($raw);
        if (!$data) {
            throw new SerializerException("Unable to serialize data");
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = (array)@\json_decode($data);
        if (!$data) {
            throw new SerializerException("Unable to unserialize data");
        }
        $class = $data[0];
        $inst = new $class();
        $inst->setData((array)$data[1]);
        return $inst;
    }
}
