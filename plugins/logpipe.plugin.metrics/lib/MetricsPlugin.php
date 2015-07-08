<?php

namespace LogPipe\Plugin\Metrics;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;

class MetricsPlugin extends Plugin
{
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $app = $this->getApplication();

        // Listen for the relevant events
        $events = $app->getEventDispatcher();
        $events->addListener("logpipe.message.pre_filter", [ $this, "onMessagePreFilter" ]);
        
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
        if ($message->hasTag("metrics") && $message->hasData()) {
            // handle the message
            $data = $message->getData();
            
            // Consuming the message ensures it doesn't end up in the log.
            $message->consume();
            // By stopping propagation, remaining event listeners will not be
            // called.
            $event->stopPropagation();
        }
    }
}