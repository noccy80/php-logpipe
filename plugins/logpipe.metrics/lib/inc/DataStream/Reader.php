<?php

namespace NoccyLabs\LogPipe\Metrics\DataStream;

class Reader
{
    protected $stream;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            $stream = fopen($stream, "r");
        }

        $this->stream = $stream;
    }

    public function read()
    {
        if (feof($this->stream)) {
            return false;
        }

        $record = fgets($this->stream,256000);
        if (($record) && ($record[0]=="@")) {
            list ($header, $data) = explode("=", substr($record,1), 2);
            list ($session, $key) = explode("#", $header, 2);

            $data_dec = json_decode($data);

            return (object)array("session"=>$session, "key"=>$key, "data"=>$data_dec);
        }

        return false;
    }
}
