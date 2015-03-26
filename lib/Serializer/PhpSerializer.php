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
        $data = @serialize($message);
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
        $data = @unserialize($data);
        if (!$data) {
            throw new SerializerException("Unable to unserialize data");
        }
        return $data;
    }
}
