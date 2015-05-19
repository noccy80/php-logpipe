<?php


namespace NoccyLabs\LogPipe\Dumper\Filter;


use NoccyLabs\LogPipe\Message\MessageInterface;

class SquelchStatFilter implements FilterInterface {

    protected $squelched = 0;

    protected $callback;

    public function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function filterMessage(MessageInterface $message, $filtered)
    {
        if ($filtered) {
            $this->squelched++;
        } else {
            if ($this->squelched > 0) {
                call_user_func($this->callback, $this->squelched);
                $this->squelched = 0;
            }
        }
        return !$filtered;
    }

    public function getSquelchedCount()
    {
        return $this->quelched;
    }

    public function setSquelchCallback(callable $callback)
    {
        $this->callback = $callback;
    }

}