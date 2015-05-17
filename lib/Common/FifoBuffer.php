<?php

namespace NoccyLabs\LogPipe\Common;

/**
 * Addressable and countable fifo-buffer
 *
 * @package NoccyLabs\LogPipe\Application\Buffer
 */
class FifoBuffer implements \Countable, \ArrayAccess
{
    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @param int $size
     */
    public function __construct($size=2000)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->buffer);
    }

    /**
     * @param mixed $index
     * @return mixed
     */
    public function offsetGet($index)
    {
        if ($index >= count($this->buffer)) {
            throw new \OutOfBoundsException();
        }
        return $this->buffer[$index];
    }

    /**
     * @param mixed $index
     * @param mixed $value
     */
    public function offsetSet($index, $value)
    {}

    /**
     * @param mixed $index
     */
    public function offsetUnset($index)
    {}

    /**
     * @param mixed $index
     * @return bool
     */
    public function offsetExists($index)
    {
        return ($index < count($this->buffer));
    }

    /**
     * @param $data
     */
    public function push($data)
    {
        array_push($this->buffer, $data);
        while (count($this->buffer) > $this->size) {
            array_shift($this->buffer);
        }
        $this->total++;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Return a slice of the buffer
     *
     * @param $start
     * @param $length
     * @return array
     */
    public function slice($start, $length)
    {
        return array_slice($this->buffer, $start, $length);
    }

    /**
     * Return a number of records from the beginning of the buffer
     *
     * @param $lines
     * @return array
     */
    public function head($lines)
    {
        return $this->slice(0, min($lines,count($this)));
    }

    /**
     * Return a number of records from the end of the buffer
     *
     * @param $lines
     * @return array
     */
    public function tail($lines)
    {
        $start = max(count($this) - $lines, 0);
        return $this->slice($start, $lines);
    }

    /**
     * Return records matching the specified regular expression
     *
     * @param $pattern
     * @return array
     */
    public function match($pattern)
    {
        $out = [];
        // TODO: This depends on getMessage() -- string casting might be preferable?
        foreach ($this->buffer as $item) {
            if (@preg_match($pattern, $item->getMessage())) {
                $out[] = $item;
            }
        }
        return $out;
    }

}
