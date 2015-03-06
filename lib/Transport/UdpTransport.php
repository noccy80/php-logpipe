<?php

namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;

class UdpTransport implements TransportInterface
{
    protected $host;

    protected $port;

    protected $stream;

    public function __construct($endpoint)
    {
        list($host, $port) = explode(":", $endpoint);

        if (($port<1) || ($port>65535)) {
            throw new \InvalidArgumentException("Port must be between 1 and 65535");
        }

        $this->host = $host;
        $this->port = $port;
    }

    public function send($message)
    {
        if (!$this->stream) { return; }
        $msg = serialize($message);
        @fwrite($this->stream, $msg);
    }

    public function receive($blocking=false)
    {
        stream_set_blocking($this->stream, $blocking);
        $msg = fread($this->stream, 8192);
        if (!$msg) { return NULL; }
        return unserialize($msg);
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
}
