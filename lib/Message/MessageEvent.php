<?php

namespace NoccyLabs\LogPipe\Message;

use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event
{
    const PRE_FILTER = "message.pre_filter";
    
    const POST_FILTER = "message.post_filter";
    
    const SQUELCHED = "message.squelched";
    
    protected $message;
    
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
}