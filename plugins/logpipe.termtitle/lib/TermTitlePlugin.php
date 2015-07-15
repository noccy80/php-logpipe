<?php

namespace LogPipe\Plugin\TermTitle;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;

class TermTitlePlugin extends Plugin
{
    protected $counter = 0;
    
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $this->addEventListener(MessageEvent::PRE_FILTER, [ $this, "onMessage" ]);
    }
    
    public function onMessage(MessageEvent $message)
    {
        $this->counter++;
        echo "\e]0;LogPipe [{$this->counter}]\x07";
    }
}
