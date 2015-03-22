<?php


namespace NoccyLabs\LogPipe\Transport;


use NoccyLabs\LogPipe\Transport\TransportInterface;

class PipeTransport implements TransportInterface
{
    public function __construct($params)
    {
        echo "New pipe on {$params}\n";
    }

    /**
     * Send a message over the transport. This requires that connect() has been called prior. It can not be used on
     * a transport after calling listen().
     *
     * @param $message
     * @return mixed
     */
    public function send($message)
    {
        // TODO: Implement send() method.
    }

    /**
     * Receive a message from the transport. If nothing is available to read, NULL is returned. receive() can only
     * be used once listen() has been called.
     *
     * @param bool $blocking
     * @return mixed
     */
    public function receive($blocking = false)
    {
        // TODO: Implement receive() method.
    }

    /**
     * Start listening for connections
     *
     * @return mixed
     */
    public function listen()
    {
        // TODO: Implement listen() method.
    }

    /**
     * Connect to a listening transport
     *
     * @return mixed
     */
    public function connect()
    {
        // TODO: Implement connect() method.
    }

    public function close()
    {
    }

}
