<?php


namespace NoccyLabs\LogPipe\Transport;

use NoccyLabs\LogPipe\Message\MessageInterface;

class TcpTransport {

    protected $host;

    protected $port;

    protected $stream;

    protected $clients = [];

    protected $buffer = [];

    protected $messages = [];

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
        try {
            $msg = serialize($message);
            $header = pack("vV", strlen($msg), crc32($msg));
            @fwrite($this->stream, $header.$msg);
        } catch (\Exception $e) {
            // Do nothing if unable to serialize
        }
    }

    public function receive($blocking=false)
    {
        static $buffer;
        if (!is_array($buffer)) { $buffer = []; }

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
                        //echo "Closing {$sh}\n";
                        // remove the client from the list
                        $this->clients = array_diff($this->clients, array($stream));
                        // close the socket and resume
                        fclose($stream);
                        unset($this->buffer[$sh]);
                        break;
                    }
    
                    @$this->buffer[$sh] .= $read;
    
                    //printf("Buffer %s len=%d\n", $sh, strlen($this->buffer[$sh]));

                    while (strlen($this->buffer[$sh]) > 6) {
                        $header = unpack("vsize/Vcrc32", substr($this->buffer[$sh], 0, 6));
                        if ((strlen($this->buffer[$sh]) < $header['size'] - 6)) {
                            echo "Insufficient data\n";
                            break;
                        }
                        $data = substr($this->buffer[$sh], 6, $header['size']);
                        $this->buffer[$sh] = substr($this->buffer[$sh], $header['size']+6);
                        //printf("Popped data (len=%d) buffer remaining len=%d\n", strlen($data), strlen($this->buffer[$sh]));
                        if (crc32($data) != $header['crc32']) {
                            $this->buffer[$sh] = null;
                            error_log("Warning: Message with invalid crc32 encountered.");
                            break;
                        }
                        $rcv = @unserialize($data);
                        $this->messages[] = $rcv;
                    }
                }
            }
        }
        return (count($this->messages) > 0) ? array_shift($this->messages) : NULL;

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
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND|STREAM_SERVER_LISTEN
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

    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

}
