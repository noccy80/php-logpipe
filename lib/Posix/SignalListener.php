<?php

namespace NoccyLabs\LogPipe\Posix;

/**
 * Simple Posix signal listener. Requires declare(ticks=n) for the relevant code in order to trigger properly. Once
 * triggered, its status can be polled using the __invoke() magical method, or isFired() method.
 *
 *    $sigInt = new SignalListener(SIGINT);
 *    if ($sigint()) { // was fired }
 *
 * @package NoccyLabs\LogPipe\Posix
 */
class SignalListener
{
    /**
     * @var bool
     */
    protected $fired = false;

    /**
     * @var callable|null
     */
    protected $callback = NULL;

    /**
     * @param $signal
     * @param callable $callback
     */
    public function __construct($signal, callable $callback=NULL)
    {
        $this->callback = $callback;
        pcntl_signal($signal, array($this, "_onSignalCallback"));
    }

    /**
     * @param $signal
     */
    public function _onSignalCallback($signal)
    {
        $this->fired = true;
    }

    /**
     * @param bool $peek
     * @return bool
     */
    public function __invoke($peek=false)
    {
        $ret = $this->fired;
        if (!$peek) { $this->fired = false; }
        return $ret;
    }

    /**
     * Return a bool indicating whether the signal has been received. Call reset() to clear the flag.
     *
     * @return bool True if the signal was fired
     */
    public function isFired()
    {
        return $this->fired;
    }

    /**
     *
     */
    public function reset()
    {
        $this->fired = false;
    }
}
