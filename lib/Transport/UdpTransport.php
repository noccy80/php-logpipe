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

    public function send(MessageInterface $message)
    {
        if (!$this->stream) { return; }
        try {
            $msg = serialize($message);
            $header = pack("vV", strlen($msg), crc32($msg));
            @fwrite($this->stream, $header.$msg);
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

        if (strlen($buffer) > 6) {
            $header = unpack("vsize/Vcrc32", substr($buffer, 0, 6));
            if ((strlen($buffer) < $header['size'] - 6)) {
                return NULL;
            }
            $data = substr($buffer, 6, $header['size']);
            $buffer = substr($buffer, $header['size']+6);
            if (crc32($data) != $header['crc32']) {
                $buffer = null;
                error_log("Warning: Message with invalid crc32 encountered.");
                return NULL;
            }

            $rcv = @unserialize($buffer . $data);
            return $rcv;
        }

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
