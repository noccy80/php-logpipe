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
        
        ksort($this->manifests);
        
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
            $manifest = $this->manifests[$name];
            if ($manifest->getAutoEnable()) {
                $this->loadPlugin($name);
            }
        }
    }
    
    public function loadPlugin($name, $is_dependency=false)
    {
        if (array_key_exists($name, $this->plugins)) {
            return $this->plugins[$name];
        }

        if (array_key_exists($name, $this->manifests)) {
            $manifest = $this->manifests[$name];
            $depends = (array)$manifest->getDependencies();
            foreach ($depends as $dependency) {
                $this->loadPlugin($dependency, true);
            }
            $this->plugins[$name] = $manifest->loadPlugin();
        }
        $manifest->setIsDependency($is_dependency);
        return $this->plugins[$name];
    }
    
    public function getManifests()
    {
        return $this->manifests;
    }
    
}
