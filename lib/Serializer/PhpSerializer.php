<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Exception\SerializerException;
use NoccyLabs\LogPipe\Message\MessageInterface;

class PhpSerializer implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "php";
    }

    /**
     * {@inheritDoc}
     */
    public function getTag()
    {
        return "P";
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(MessageInterface $message)
    {
        try {
            $data = @serialize($message);
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
            $data = @unserialize($data);
        } catch (\Exception $e) {
            throw new SerializerException("Unable to serialize data", 0, $e);
        }
        return $data;
    }
}
