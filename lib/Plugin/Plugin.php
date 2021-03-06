<?php

namespace NoccyLabs\LogPipe\Plugin;

use NoccyLabs\LogPipe\Application\LogPipeApplication;
use Symfony\Component\Console\Command\Command;

abstract class Plugin implements PluginInterface
{
    protected $app;
    
    protected $manifest;
    
    public function setApplication(LogPipeApplication $app)
    {
        $this->app = $app;
        return $this;
    }
    
    public function setManifest(PluginManifest $manifest)
    {
        $this->manifest = $manifest;
        return $this;
    }
    
    public function getManifest()
    {
        return $this->manifest;
    }
    
    public function getApplication()
    {
        return $this->app;
    }
    
    public function addEventListener($event, callable $listener)
    {
        return $this->app->getEventDispatcher()->addListener($event, $listener);
    }

    public function addCommand(Command $command)
    {
        return $this->app->add($command);
    }
    
    public function getContainer()
    {
        return $this->app->getContainer();
    }
}
