<?php


namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Posix\Fifo;

/**
 * Class PipeTransport
 * @package NoccyLabs\LogPipe\Transport
 */
class PipeTransport extends TransportAbstract
{
    /**
     * @var Fifo
     */
    protected $fifo;

    /**
     * @var
     */
    protected $is_listener;

    /**
     * @param $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        list($path, $options) = explode(":", $params.':');

        $this->fifo = new Fifo($path);

        parent::__construct($options);
    }

    /**
     * Send a message over the transport. This requires that connect() has been called prior. It can not be used on
     * a transport after calling listen().
     *
     * @param $message
     * @return mixed
     */
    public function send(MessageInterface $message)
    {
        if (!$this->fifo) { return false; }
        try {
            $data = $this->protocol->pack($message);
            $this->fifo->write($data);
            return true;
        } catch (\Exception $e) {
            // Do nothing with this message if serialization failed.
        }
    }

    /**
     * Receive a message from the transport. If nothing is available to read, NULL is returned. receive() can only
     * be used once listen() has been called.
     *
     * @param bool $blocking
     * @return mixed
     */
    public function receive($blocking=false)
    {
        static $buffer;

        $read = $this->fifo->read(65535);

        $buffer .= $read;

        return $this->protocol->unpack($buffer);
    }


    /**
     * Start listening for connections
     *
     * @return mixed
     */
    public function listen()
    {
        $this->is_listener = true;
        $this->fifo->create();
    }

    /**
     * Connect to a listening transport
     *
     * @return mixed
     */
    public function connect()
    {
        $this->fifo->open();
    }

    /**
     *
     */
    public function close()
    {
        if ($this->is_listener) {
            $this->fifo->destroy();
        } else {
            $this->fifo->close();
        }
    }

}
