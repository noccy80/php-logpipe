<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Serializer\SerializerFactory;

class UdpTransport extends TransportAbstract
{
    protected $host;

    protected $port;

    protected $stream;

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

    public function send(MessageInterface $message)
    {
        if (!$this->stream) { return; }
        try {
            $data = $this->protocol->pack($message);
            @fwrite($this->stream, $data);
        } catch (\Exception $e) {
            // Do nothing with this message if serialization failed.
        }
    }

    public function receive($blocking=false)
    {
        static $buffer;

        stream_set_blocking($this->stream, $blocking);

        $read = fread($this->stream, 65535);

        $buffer .= $read;
        return $this->protocol->unpack($buffer);
    }

    public function listen()
    {
        if (($this->stream) && (is_resource($this->stream))) {
            fclose($this->stream);
            $this->stream = null;
        }

        $timeout = 1;
        $errno   = null;
        $errstr  = null;

        $this->stream = stream_socket_server(
            "udp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND
        );

        if ($errno) {
            error_log("Warning: Listen failed {$errno} {$errstr}");
        }
    }

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

        $this->stream = stream_socket_client(
            "udp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
    }

    public function close()
    {
    }
}
