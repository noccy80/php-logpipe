<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerInterface;

/**
 * Interface ProtocolInterface
 * @package NoccyLabs\LogPipe\Protocol
 */
interface ProtocolInterface
{
    /**
     * Unpack a frame into a MessageInterface instance
     *
     * @param $buffer
     * @param SerializerInterface
     * @return MessageInterface|null
     */
    public function unpack(&$buffer, SerializerInterface $serializer);

    /**
     * Pack the message into a frame.
     *
     * @param MessageInterface $message
     * @param SerializerInterface
     * @return mixed
     */
    public function pack(MessageInterface $message, SerializerInterface $serializer);

    /**
     * Return the protocol version handled by the implementation.
     *
     * @return mixed
     */
    public function getVersion();
}
