<?php

namespace LogPipe\Plugin\Dumper\ChannelUtil;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;

class ChannelUtilPlugin extends Plugin
{
    protected $channelsSeen = [];
    
    public function onLoad()
    {
        $this->addEventListener(MessageEvent::PRE_FILTER, [ $this, "onMessagePreFilter" ]);
        $this->addEventListener(DumperEvent::AFTER_BATCH, [ $this, "onAfterBatch" ]);
    }
    
    public function onMessagePreFilter(MessageEvent $event)
    {
        $message = $event->getMessage();
        $channel = $message->getChannel();
        if (!array_key_exists($channel, $this->channelsSeen)) {
            $this->channelsSeen[$channel] = 0;
        }
        $this->channelsSeen[$channel]++;
    }
    
    public function onAfterBatch(DumperEvent $event)
    {
        if (count($this->channelsSeen) == 0) {
            return;
        }

        echo "\e[s"; // save cursor position
        
        $maxl = max(array_map("strlen", array_keys($this->channelsSeen)));
        $cols = (int)exec("tput cols");
        $offs = $cols - $maxl - 7;
        $rowi = 0;
        
        foreach ($this->channelsSeen as $channel=>$count) {
            printf("\e[%d;%dH\e[36;44;1m %5d \e[37;21m%-{$maxl}s \e[0m",
                ++$rowi,
                $offs,
                $count,
                $channel
            );
        }
        
        echo "\e[u"; // restore cursor position
    }
}
