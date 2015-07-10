<?php

namespace NoccyLabs\LogPipe\Plugin;

use NoccyLabs\LogPipe\Application\LogPipeApplication;
use Symfony\Component\Console\Command\Command;

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
