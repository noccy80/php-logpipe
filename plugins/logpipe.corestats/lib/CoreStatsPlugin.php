<?php

namespace LogPipe\Plugin\CoreStats;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;

class CoreStatsPlugin extends Plugin
{

    protected $channelsSeen = [];

    protected $squelched = 0;
    
    protected $received = 0;
    
    public function onLoad()
    {
        $this->getContainer()->set("plugin.corestats.stats", $this);
        $this->addEventListener(MessageEvent::SQUELCHED,    [ $this, "onMessageSquelched" ]);
        $this->addEventListener(MessageEvent::PRE_FILTER,   [ $this, "onMessageReceived" ]);
    }
    

    public function onMessageSquelched(MessageEvent $event)
    {
        $this->squelched++;
    }
    
    public function onMessageReceived(MessageEvent $event)
    {
        $this->received++;
        $message = $event->getMessage();
        $channel = $message->getChannel();
        if (!array_key_exists($channel, $this->channelsSeen)) {
            $this->channelsSeen[$channel] = 0;
        }
        $this->channelsSeen[$channel]++;
    }
    
    public function getNumChannelsSeen()
    {
        return count($this->channelsSeen);
    }
    
    public function getChannelsSeen()
    {
        return $this->channelsSeen;
    }

    public function getNumReceivedMessages()
    {
        return $this->received;
    }
    
    public function getNumFilteredMessages()
    {
        return $this->squelched;
    }
    
    public function getNumDisplayedMessages()
    {
        return $this->received - $this->squelched;
    }
}
