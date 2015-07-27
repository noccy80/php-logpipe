<?php

namespace LogPipe\Plugin\TermTitle;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;

class TermTitlePlugin extends Plugin
{
    protected $counter = 0;
    
    protected $stats;
    
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $this->stats = $this->getContainer()->get("plugin.corestats.stats");
        $this->addEventListener(DumperEvent::IDLE_REFRESH, [ $this, "onRefresh" ]);
    }
    
    public function onRefresh(DumperEvent $event)
    {
        $count = $this->stats->getNumDisplayedMessages();
        if ($this->counter == $count) {
            return;
        }
        $this->counter = $count;
        echo "\e]0;LogPipe [{$count}]\x07";
    }
}
