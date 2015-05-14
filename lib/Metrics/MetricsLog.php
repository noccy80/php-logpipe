<?php

namespace NoccyLabs\LogPipe\Metrics;

class MetricsLog
{
    protected $stream;

    public function __construct($file, $mode)
    {
        switch ($mode) {
            case 'w':
                $this->stream = new DataStream\Writer($file);
                break;
            case 'r':
                $this->stream = new DataStream\Reader($file);
                break;
            default:
                throw new \Exception("Invalid mode, should be 'r' or 'w'");
        }
    }

    public function log($session, $key, $data)
    {
        $this->stream->write($session, $key, $data);
    }

    public function read()
    {
        return $this->stream->read();
    }
}
