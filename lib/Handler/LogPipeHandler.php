<?php

namespace NoccyLabs\LogPipe\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

use NoccyLabs\LogPipe\Message\MonologMessage;
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
        $this->setClientId(null);
        $this->transport_uri = $transport;
        parent::__construct($level, $bubble);
    }

    public function setClientId($client_id, $request_id=null)
    {
        if (!$client_id) {
            $client_id = (getenv("APP_ID") ? : (defined("APP_ID") ? APP_ID : gethostname()));
        }
        if (!$request_id) {
            $request_id = sprintf("%04x%04x", rand(0,0xFFFF), rand(0,0xFFFF));
        }
        $this->client_id = sprintf("%s:%s", $client_id, $request_id);
    }

    protected function write(array $record)
    {

        if (!$this->initialized) {
            $this->initialize();
        }

        $message = new MonologMessage($record, $this->client_id);
        $this->transport->send($message);
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
