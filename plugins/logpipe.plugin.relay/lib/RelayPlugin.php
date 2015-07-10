<?php

namespace LogPipe\Plugin\Relay;

use NoccyLabs\LogPipe\Plugin\Plugin;
use NoccyLabs\LogPipe\Message\MessageEvent;

class RelayPlugin extends Plugin
{
    protected $relayCommand;
    
    /**
     * Called when the plugin is loaded
     *
     *
     */
    public function onLoad()
    {
        $this->relayCommand = new RelayCommand();
        $this->addCommand($this->relayCommand);
    }
    
}