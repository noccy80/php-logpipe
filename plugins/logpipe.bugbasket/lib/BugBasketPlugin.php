<?php

namespace LogPipe\Plugin\BugBasket;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;

class BugBasketPlugin extends Plugin
{
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $app = $this->getApplication();

        // Set up an indicator in the status line
        //$status = $app->getStatusLine();
        //$status->addPanel(new BugBasketStatusPanel($this));
        
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

        

     
    }
}