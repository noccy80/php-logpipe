<?php

namespace LogPipe\Posix;

class Fifo
{
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function create()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        posix_mkfifo($this->path, 0777);
        $this->fifo = fopen($this->path, "r+");
    }

    public function open()
    {
        if (!file_exists($this->path)) {
            $this->fifo = null;
            return false;
        }
        $this->fifo = fopen($this->path, "w");
    }

    public function destroy()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function read($bytes=1024)
    {
        if (!$this->fifo) {
            return null;
        }
        stream_set_blocking($this->fifo, false);
        $data = fread($this->fifo, $bytes);
        return $data;
    }

    public function write($data)
    {
        if (!$this->fifo) {
            return false;
        }
        fwrite($this->fifo, $data);
    }
}
