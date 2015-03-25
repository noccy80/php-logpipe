<?php

namespace NoccyLabs\LogPipe\Posix;

class SignalListener
{
    protected $fired = false;

    protected $callback = NULL;

    public function __construct($signal, callable $callback=NULL)
    {
        $this->callback = $callback;
        pcntl_signal($signal, array($this, "_onSignalCallback"));
    }

    public function _onSignalCallback($signal)
    {
        $this->fired = true;
    }

    public function __invoke($peek=false)
    {
        $ret = $this->fired;
        if (!$peek) { $this->fired = false; }
        return $ret;
    }

    public function reset()
    {
        $this->fired = false;
    }
}
