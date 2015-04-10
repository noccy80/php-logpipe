<?php


namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Class TcpTransport
 * @package NoccyLabs\LogPipe\Transport
 */
class TcpTransport extends TransportAbstract {

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

    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @var array
     */
    protected $messages = [];

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

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message)
    {
        // Bail if we don't have an open stream
        if (!$this->stream) {
            return;
        }
        try {
            $data = $this->protocol->pack($message);
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
        static $buffer;
        if (!is_array($buffer)) { $buffer = []; }

        // Bail out if we don't have an open stream
        if (!$this->stream) {
            return;
        }

        $read = [ $this->stream ];
        $read = array_merge($read, $this->clients);
        $write = [];
        $except = [];

        if (stream_select($read, $write, $except, 0)) {

            if (in_array($this->stream, $read)) {
                $client = stream_socket_accept($this->stream);
                $this->clients[] = $client;
            }

            foreach ($read as $stream) {
                if (in_array($stream, $this->clients)) {
                    $sh = (string)$stream;
                    stream_set_blocking($stream, false);
                    $read = fread($stream, 65535);

                    if ($read == null) {
                        // remove the client from the list
                        $this->clients = array_diff($this->clients, array($stream));
                        // close the socket and resume
                        fclose($stream);
                        unset($this->buffer[$sh]);
                        break;
                    }

                    if (!array_key_exists($sh, $this->buffer)) {
                        $this->buffer[$sh] = null;
                    }
                    $this->buffer[$sh] .= $read;

                    while (($msg = $this->protocol->unpack($this->buffer[$sh]))) {
                        $this->messages[] = $msg;
                    }
                }
            }
        }
        return (count($this->messages) > 0) ? array_shift($this->messages) : NULL;

    }

    /**
     *
     */
    public function listen()
    {
        if (($this->stream) && (is_resource($this->stream))) {
            fclose($this->stream);
            $this->stream = null;
        }

        $timeout = 1;
        $errno   = null;
        $errstr  = null;

        $this->stream = @stream_socket_server(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND|STREAM_SERVER_LISTEN
        );

        if ($errno) {
            error_log("Warning: Listen failed {$errno} {$errstr}");
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
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($errno) {
            // error_log(sprintf("%s (%d)", $errstr, $errno));
            $this->stream = NULL;
        }
    }

    /**
     *
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

}
