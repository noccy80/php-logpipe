<?php

namespace LogPipe\Plugin\Dumper\ChannelUtil;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;

class ChannelUtilPlugin extends Plugin
{
    
    protected $stats;
    
    public function onLoad()
    {
        $this->addEventListener(DumperEvent::AFTER_BATCH, [ $this, "onAfterBatch" ]);
        $this->stats = $this->getContainer()->get("plugin.corestats.stats");
    }
    
    public function onAfterBatch(DumperEvent $event)
    {
        if ($this->stats->getNumChannelsSeen() == 0) {
            return;
        }

        echo "\e[s"; // save cursor position
        
        $channelsSeen = $this->stats->getChannelsSeen();
        
        $maxl = max(array_map("strlen", array_keys($channelsSeen)));
        $cols = (int)exec("tput cols");
        $offs = $cols - $maxl - 7;
        $rowi = 0;
        
        foreach ($channelsSeen as $channel=>$count) {
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
