<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Exception\SerializerException;

class BsonSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "bson";
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return "b";
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported()
    {
        return (is_callable("bson_encode") && is_callable("bson_decode"));
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(MessageInterface $message)
    {
        // No exception is thrown here.
        $raw = [ get_class($message), $message->getData() ];
        $data = @\bson_encode($raw);
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        try {
            $data = (array)@\bson_decode($data);
        } catch (\MongoException $e) {
            throw new SerializerException("Unable to unserialize data");
        }
        $class = $data[0];
        $inst = new $class();
        $inst->setData((array)$data[1]);
        return $inst;
    }
}
