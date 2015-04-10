<?php

namespace NoccyLabs\LogPipe\Posix;

/**
 * Posix fifo wrapper
 *
 * @package NoccyLabs\LogPipe\Posix
 */
class Fifo
{
    /**
     * @var string The path to the fifo
     */
    protected $path;

    /**
     * @var resource The fifo stream
     */
    protected $fifo;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Create the fifo and open it as a a reader. This will be done non-blocking.
     *
     */
    public function create()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        posix_mkfifo($this->path, 0777);
        $this->fifo = fopen($this->path, "r+");
    }

    /**
     *
     * @return bool
     */
    public function open()
    {
        if (!file_exists($this->path)) {
            $this->fifo = null;
            return false;
        }
        $this->fifo = fopen($this->path, "w");
    }

    /**
     * Close the fifo, but don't destroy it.
     *
     */
    public function close()
    {
        if (is_resource($this->fifo)) {
            fclose($this->fifo);
        }
    }

    /**
     * Close and destroy the fifo
     *
     */
    public function destroy()
    {
        $this->close();
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    /**
     * @param int $bytes
     * @return null|string
     */
    public function read($bytes=1024)
    {
        if (!$this->fifo) {
            return null;
        }
        stream_set_blocking($this->fifo, false);
        $data = fread($this->fifo, $bytes);
        return $data;
    }

    /**
     * @param $data
     * @return bool
     */
    public function write($data)
    {
        if (!$this->fifo) {
            return false;
        }
        fwrite($this->fifo, $data);
    }
}
