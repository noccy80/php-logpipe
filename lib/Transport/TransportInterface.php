<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Transport\Message\MessageInterface;

/**
 *
 *
 * Interface TransportInterface
 * @package NoccyLabs\LogPipe\Transport
 */
interface TransportInterface
{
    /**
     * Send a message over the transport. This requires that connect() has been called prior. It can not be used on
     * a transport after calling listen().
     *
     * @param $message
     * @return mixed
     */
    public function send(MessageInterface $message);

    /**
     * Receive a message from the transport. If nothing is available to read, NULL is returned. receive() can only
     * be used once listen() has been called.
     *
     * @param bool $blocking
     * @return mixed
     */
    public function receive($blocking=false);

    /**
     * Start listening for connections
     *
     * @return mixed
     */
    public function listen();

    /**
     * Connect to a listening transport
     *
     * @return mixed
     */
    public function connect();

}
