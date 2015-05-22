<?php

namespace NoccyLabs\LogPipe\Serializer;

use NoccyLabs\LogPipe\Message\MessageInterface;

interface SerializerInterface
{
    /**
     * Serialize a variable
     *
     * @param mixed $var The data to serialize
     * @return string THe serialized data
     */
    public function serialize(MessageInterface $message);

    /**
     * Unserialize a variable
     *
     * @param string $var The serialized data
     * @return mixed The data
     */
    public function unserialize($data);

    /**
     * Check if the serializer is supported on this system
     *
     * @return bool True if the serializer is usable
     */
    public function isSupported();
}
