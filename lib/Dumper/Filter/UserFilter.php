<?php


namespace NoccyLabs\LogPipe\Dumper\Filter;


use NoccyLabs\LogPipe\Message\MessageInterface;

class UserFilter implements FilterInterface
{
    protected $filter;

    public function __construct(callable $filter_func)
    {
        $this->filter = $filter_func;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMessage(MessageInterface $message, $filtered)
    {
        if (call_user_func($this->filter, $message)) {
            return $message;
        }
        return NULL;
    }

}