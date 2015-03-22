<?php

namespace NoccyLabs\LogPipe\Message;

use NoccyLabs\LogPipe\Dumper\Formatter;

class ConsoleMessage implements MessageInterface {

    protected $message = [];

    public function __construct(array $message=null)
    {
        $this->message = $message;
    }

    public function getData()
    {
        return $this->message;
    }

    public function setData(array $data)
    {
        $this->message = $data;
    }

    public function getChannel()
    {
        return $this->message['channel'];
    }

    public function getLevel()
    {
        return $this->message['level'];
    }

    public function getMessage()
    {
        return $this->message['message'];
    }

    public function getClientId()
    {
        return $this->message["_client_id"];
    }

    public function format(Formatter $formatter)
    {
        return $formatter->format($this);
    }

}
