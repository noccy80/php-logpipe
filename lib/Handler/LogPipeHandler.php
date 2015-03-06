<?php

namespace NoccyLabs\LogPipe\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;

class LogPipeHandler extends AbstractProcessingHandler
{
    protected $transport_uri;
    protected $transport;
    protected $initialized;
    protected $client_id;

    public function __construct($transport, $level = Logger::DEBUG, $bubble = true)
    {
        $this->client_id = uniqid(gethostname().'/');
        $this->transport_uri = $transport;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->transport->send(array(
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted'],
            'time' => $record['datetime']->format('U'),
            'client_id' => $this->client_id
        ));
    }

    private function initialize()
    {
        if ($this->transport_uri instanceof TransportInterface) {
            $this->transport = $this->transport_uri;
        } elseif (strpos($this->transport_uri, ":") !== false) {
            $this->transport = TransportFactory::create($this->transport_uri);
        } else {
            throw new \InvalidArgumentException("Unable to initialize transport from ".trim(print_r($this->transport_uri,true)));
        }

        $this->transport->connect();

        $this->initialized = true;
    }
}
