<?php

namespace NoccyLabs\LogPipe\Metrics\DataStream;

class Writer
{
    protected $stream;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            $stream = fopen($stream, "w");
        }

        $this->stream = $stream;
    }

    public function write($session, $key, $data)
    {
        $data_ser = \json_encode($data);
        $data_len = strlen($data_ser);
        $record = "@{$session}#{$key}={$data_ser}\n";
        fwrite($this->stream, $record);
    }
}
