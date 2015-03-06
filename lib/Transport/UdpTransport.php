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
        $header = pack("vV", strlen($msg), crc32($msg));
        @fwrite($this->stream, $header.$msg);
    }

    public function receive($blocking=false)
    {
        stream_set_blocking($this->stream, $blocking);
        $msg = fread($this->stream, 65535);
        $header = unpack("vsize/Vcrc32", substr($msg, 0,6));
        $data = substr($msg,6);
        if ((strlen($data) != $header['size']) || (crc32($data) != $header['crc32'])) {
            error_log("Warning: Backet with bad crc or size encountered.");
            return NULL;
        }
        if (!$msg) {
            return NULL;
        }
        return unserialize($data);
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
