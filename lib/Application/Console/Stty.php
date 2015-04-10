<?php

namespace NoccyLabs\LogPipe\Application\Console;

/**
 * Class Stty
 * @package NoccyLabs\LogPipe\Application\Console
 */
class Stty
{
    /**
     * @var string
     */
    protected $previous;

    /**
     *
     */
    public function __construct()
    {
        $this->previous = exec("stty --save");
    }

    /**
     *
     */
    public function reset()
    {
        echo exec("stty {$this->previous}");
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->reset();
    }

    /**
     * @param $option
     */
    public function set($option)
    {
        echo exec("stty {$option}");
    }
}
