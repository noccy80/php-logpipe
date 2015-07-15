<?php

namespace LogPipe\Plugin\BugBasket;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Console\StatusPanel;

class BugBasketStatusPanel extends StatusPanel
{
    const STYLE_DEFAULT = "34";
    const STYLE_CAPTURED = "30;43";
    
    protected $plugin;
    
    public function __construct(BugBasketPlugin $plugin)
    {
        $this->plugin = $plugin;
    }
    
    public function onUpdate()
    {
        $counter = $this->plugin->getSessionBagCounter();
        if ($counter == 0) {
            $this->setStyle(self::STYLE_DEFAULT);
            $this->setText(null);
        } else {
            $this->setStyle(self::STYLE_CAPTURED);
            $this->setText("(\u26c1 {$counter})");
        }
    }
    
}
