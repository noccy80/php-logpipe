<?php

namespace LogPipe\Plugin\BugBasket;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;

class BugBasketPlugin extends Plugin
{
    /** @var BugStash */
    protected $stash;
    
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $this->getContainer()->set("plugin.bugbasket", $this);
        $this->getApplication()->add(new BugsCommand());
        $this->addEventListener("message.pre_filter", [ $this, "onMessagePreFilter" ]);
    }

    /**
     * Event handler for logpipe.message.pre_filter
     *
     * Will receive the message before it is filtered to allow the plugin to
     * extract any relevant information.
     *
     * @param MessageEvent $event The event
     */
    public function onMessagePreFilter(MessageEvent $event)
    {
        $message = $event->getMessage();

        if (preg_match("/exception/i", $message->getText())) {
            $this->getStash()->addToStash($message, "exception");
        }
     
    }
    
    public function getStash()
    {
        if (!$this->stash) {
            $this->stash = new BugStash(getcwd()."/bugstash.db");
        }
        return $this->stash;
    }
    
    public function dropStash()
    {
        $stashFile = getcwd()."/bugstash.db";
        if (file_exists($stashFile)) {
            unlink($stashFile);
        }
    }
}