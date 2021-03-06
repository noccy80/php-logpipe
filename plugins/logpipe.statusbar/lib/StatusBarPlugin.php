<?php

namespace LogPipe\Plugin\StatusBar;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\Helper\Unicode;


class StatusBarPlugin extends Plugin
{

    protected $statusLine;

    protected $stats;

    public function onLoad()
    {
        $this->stats = $this->getContainer()->get("plugin.corestats.stats");
        
        $this->addEventListener(DumperEvent::BEFORE_BATCH,  [ $this, "onBeforeBatch" ]);
        $this->addEventListener(DumperEvent::AFTER_BATCH,   [ $this, "onAfterBatch" ]);
        $this->addEventListener(DumperEvent::DUMPING,       [ $this, "onDumping" ]);
        $this->addEventListener(DumperEvent::SUSPEND,       [ $this, "onSuspend" ]);
        $this->addEventListener(DumperEvent::TERMINATING,   [ $this, "onTerminating" ]);
        $this->addEventListener(DumperEvent::IDLE_REFRESH,  [ $this, "onIdleRefresh" ]);

        
        $this->statusLine = new StatusLine();
        $this->statusLine->setStyle("44;37");
        $this->statusLine
            ->addPanel([ $this, "getTotalPanel" ])
            ->addPanel([ $this, "getSquelchPanel" ])
            ->addPanel([ $this, "getDebugPanel" ])
            ->addPanel([ $this, "getInfoPanel" ])
            ;
    }

    public function onBeforeBatch(DumperEvent $event)
    {
        $this->clearStatusLine();
    }

    public function onAfterBatch(DumperEvent $event)
    {
        $this->drawStatusLine();
    }
    
    public function onDumping(DumperEvent $event)
    {
        $this->drawStatusLine();
    }

    public function onSuspend(DumperEvent $event)
    {
        $this->clearStatusLine();
    }
    
    public function onTerminating(DumperEvent $event)
    {
        $this->clearStatusLine();
    }

    public function onIdleRefresh(DumperEvent $event)
    {
        $this->statusLine->update();
    }
    
    protected function clearStatusLine()
    {
        $this->statusLine->erase();        
    }
    
    protected function drawStatusLine()
    {
        $this->statusLine->update();
    }

    public function getSquelchPanel()
    {
        return [ Unicode::char(0x26D5). " " . $this->stats->getNumFilteredMessages(), "30;43" ];
    }

    public function getTotalPanel()
    {
        return [ Unicode::char(0x27F3). " " . $this->stats->getNumReceivedMessages(), "32;1" ];
    }
        
    public function getDebugPanel()
    {
        $load = sys_getloadavg();
        $blobs = [
            Unicode::char(0x26A1) => sprintf("%.2f", $load[0]),
            Unicode::char(0x26c3) => sprintf("%.2fMiB", memory_get_usage(true)/1024/1024),
        ];
        $state = ($load[0]<0.7)?"42;37":"41;37";
        $text = [];
        foreach ($blobs as $k=>$v) {
            $text[] = sprintf("%s \e[1m%s\e[21m", $k, $v);
        }
        $text = join(" \e[34m/\e[37m ",$text);
        
        return [ $text, $state ];
    }
    
    public function getInfoPanel()
    {
        return null;
    }
    
}