<?php

namespace NoccyLabs\LogPipe\Plugin;

use Symfony\Component\Yaml\Yaml;
use NoccyLabs\LogPipe\Application\LogPipeApplication;

class PluginManager
{
    protected $manifests = [];
    
    protected $plugins = [];
    
    public function __construct(LogPipeApplication $app)
    {
        $this->app = $app;
    }
    
    public function scanDirectory($path)
    {
        if (!is_dir($path)) {
            return $this;
        }
        
        $iter = new \DirectoryIterator($path);
        foreach ($iter as $item) {
            if ($item->isDot() || (!$item->isDir())) {
                continue;
            }
            if (!file_exists($item->getPathname()."/plugin.yml")) {
                continue;
            }
            
            $this->readPlugin($item->getPathname()."/plugin.yml");
            
        }
        
        return $this;
    }
    
    public function readPlugin($manifest_file)
    {
        try {
            $manifest = new PluginManifest($manifest_file, $this->app);
            $manifest->read();
            $this->manifests[$manifest->getName()] = $manifest;
        } catch (\Exception $e) {
            error_log("Error: The plugin " . $manifest_file . " could not be loaded: " . $e->getMessage() . "\n");
            return;
        }
    }
    
    public function loadAll()
    {
        foreach (array_keys($this->manifests) as $name) {
            $this->loadPlugin($name);
        }
    }
    
    public function loadPlugin($name)
    {
        if (array_key_exists($name, $this->manifests)) {
            $this->plugins[$name] = $this->manifests[$name]->loadPlugin();
        }
        return $this->plugins[$name];
    }
    
    public function getInfo($only_loaded=false)
    {
        $info = [];
        foreach ($this->manifests as $name=>$manifest) {
            if (!(array_key_exists($name, $this->plugins) || $only_loaded)) {
                continue;
            }
            $info[$name] = $manifest->getDescription()." (v".$manifest->getVersion().")";
        }
        return $info;
    }
    
}
