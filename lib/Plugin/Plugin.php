<?php

namespace NoccyLabs\LogPipe\Plugin;

use NoccyLabs\LogPipe\Application\LogPipeApplication;

abstract class Plugin implements PluginInterface
{
    protected $app;
    
    public function setApplication(LogPipeApplication $app)
    {
        $this->app = $app;
    }
    
    public function getApplication()
    {
        return $this->app;
    }
    
}
