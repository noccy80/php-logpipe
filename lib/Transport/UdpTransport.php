<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;
use NoccyLabs\LogPipe\Exception\TransportException;

/**
 * Class UdpTransport
 * @package NoccyLabs\LogPipe\Transport
 */
class UdpTransport extends TransportAbstract
{
    /**
     * @var
     */
    protected $host;

    /**
     * @var
     */
    protected $port;

    /**
     * @var
     */
    protected $stream;

    protected $socket;

    protected $buffers = [];

    /**
     * @param $endpoint
     * @throws \Exception
     */
    public function __construct($endpoint)
    {
        list($host, $port, $options) = explode(":", $endpoint.':');

        if (($port<1) || ($port>65535)) {
            throw new \InvalidArgumentException("Port must be between 1 and 65535");
        }

        $this->host = $host;
        $this->port = $port;

        parent::__construct($options);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message)
    {
        if (!$this->stream) { return; }
        try {
            $data = $this->protocol->pack($message, $this->serializer);
            @fwrite($this->stream, $data);
        } catch (\Exception $e) {
            // Do nothing with this message if serialization failed.
        }
    }

    /**
     * @param bool $blocking
     * @return mixed|null|void
     */
    public function receive($blocking=false)
    {
        if (!$this->socket) { return; }

        //stream_set_blocking($this->stream, $blocking);
        socket_set_nonblock($this->socket);

        //$read = fread($this->stream, 65535);
        $read = null;
        $ip = null;
        $port = null;
        $bytes = @socket_recvfrom($this->socket, $read, 8192, 0, $ip, $port);
        
        if (!$bytes) {
            return;
        }
        
        $key = "{$ip}:{$port}";
        if (!array_key_exists($key, $this->buffers)) {
            $this->buffers[$key] = $read;
            //trace("Created new buffer key={$key} size=".strlen($this->buffers[$key]));
        } else {
            $this->buffers[$key] .= $read;
            //trace("Wrote to buffer key={$key} size=".strlen($this->buffers[$key])." read=".$bytes);
        }
        return $this->protocol->unpack($this->buffers[$key], $this->serializer);
    }

    /**
     *
     */
    public function listen()
    {
        if ($this->socket) {
            @socket_close($this->socket);
            $this->socket = null;
        }

        $timeout = 1;

        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        @socket_bind($this->socket, $this->host, $this->port);
        //$this->stream = @stream_socket_server(
        //    "udp://{$this->host}:{$this->port}",
        //    $errno,
        //    $errstr,
        //    STREAM_SERVER_BIND
        //);

        $errno = socket_last_error($this->socket);
        $errstr = socket_strerror($errno);

        if ($errno || !$this->socket) {
            throw new TransportException("UdpTransport:listen failed {$errno} {$errstr}");
        }
    }

    /**
     *
     */
    public function connect()
    {
        if (($this->stream) && (is_resource($this->stream))) {
            fclose($this->stream);
            $this->stream = null;
        }

        $context = stream_context_create();
        $timeout = 1;
        $errno   = null;
        $errstr  = null;

        $this->stream = @stream_socket_client(
            "udp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
    }

    /**
     *
     */
    public function close()
    {
        if ($this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
        if (($this->stream) && (is_resource($this->stream))) {
            @fclose($this->stream);
            $this->stream = null;
        }
    }
}
