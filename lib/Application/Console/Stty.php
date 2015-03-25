<?php

namespace NoccyLabs\LogPipe\Application\Console;

class Stty
{
    protected $previous;

    public function __construct()
    {
        $this->previous = exec("stty --save");
    }

    public function reset()
    {
        echo exec("stty {$this->previous}");
    }

    public function __destruct()
    {
        $this->reset();
    }

    public function set($option)
    {
        echo exec("stty {$option}");
    }
}
