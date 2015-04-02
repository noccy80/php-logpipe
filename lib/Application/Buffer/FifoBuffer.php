<?php

namespace NoccyLabs\LogPipe\Application\Buffer;

class FifoBuffer implements \Countable, \ArrayAccess
{
    protected $buffer = [];

    protected $size;

    protected $total = 0;

    public function __construct($size=2000)
    {
        $this->size = $size;
    }

    public function count()
    {
        return count($this->buffer);
    }

    public function offsetGet($index)
    {
        if ($index >= count($this->buffer)) {
            throw new \OutOfBoundsException();
        }
        return $this->buffer[$index];
    }

    public function offsetSet($index, $value)
    {}

    public function offsetUnset($index)
    {}

    public function offsetExists($index)
    {
        return ($index < count($this->buffer));
    }

    public function push($data)
    {
        array_push($this->buffer, $data);
        while (count($this->buffer) > $this->size) {
            array_shift($this->buffer);
        }
        $this->total++;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function slice($start, $end)
    {
        return array_slice($this->buffer, $start, $end);
    }

    public function head($lines)
    {
        return $this->slice(0, min($lines,count($this)));
    }

    public function tail($lines)
    {
        $start = max(count($this) - $lines, 0);
        return $this->slice($start, $lines);
    }

    public function match($pattern)
    {
        $out = [];
        foreach ($this->buffer as $item) {
            if (@preg_match($pattern, $item->getMessage())) {
                $out[] = $item;
            }
        }
        return $out;
    }

}
