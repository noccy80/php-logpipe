<?php

namespace NoccyLabs\LogPipe\Plugin;

use Symfony\Component\Yaml\Yaml;
use NoccyLabs\LogPipe\Application\LogPipeApplication;

class PluginManifest
{
    
    protected $name;
    
    protected $description;
    
    protected $version;
    
    protected $authors = [];
    
    protected $autoload_files = [];
    
    protected $autoload_psr0 = [];
    
    protected $autoload_psr4 = [];
    
    protected $plugin;
    
    protected $app;
    
    protected $auto = true;
    
    protected $depends = [];
    
    protected $is_dependency = false;
    
    public function __construct($filename, LogPipeApplication $app)
    {
        $this->setManifestFile($filename);
        $this->setApplication($app);
    }
    
    public function setManifestFile($filename)
    {
        $this->filename = $filename;
        return $this;
    }
    
    public function setApplication(LogPipeApplication $app)
    {
        $this->app = $app;
    }
    
    public function getApplication()
    {
        return $this->app;
    }
    
    public function read()
    {
        
        $yaml = file_get_contents($this->filename);
        $conf = Yaml::parse($yaml);
        
        $param = function ($key) use ($conf) {
            if (!array_key_exists($key, $conf)) {
                return null;
            }
            return $conf[$key];
        };
        
        $root = dirname($this->filename);
        
        $this->name = $param("name");
        $this->description = $param("descr");
        $this->version = $param("version");
        $this->parseAuthors($param("authors"));
        $this->parseAutoloaders($param("autoload"), $root);
        $this->parsePlugin($param("plugin"));

    }
    
    protected function parseAuthors($authors)
    {
    }
    
    protected function parseAutoloaders($autoloaders, $root)
    {
        foreach ((array)$autoloaders as $type=>$autoloader) {
            switch ($type) {
                case 'psr-4':
                    $this->autoload_psr4 = array_map(function ($file) use ($root) {
                        return $root."/".$file;
                    }, (array)$autoloader);
                    break;
                case 'psr-0':
                    $this->autoload_psr0 = array_map(function ($file) use ($root) {
                        return $root."/".$file;
                    }, (array)$autoloader);
                    break;
                case 'file':
                    $this->autoload_files = array_map(function ($file) use ($root) {
                        return $root."/".$file;
                    }, (array)$autoloader);
                    break;
                default:
                    throw new \RuntimeException("Invalid plugin manifest autoloader type: {$type}, expected psr-4, psr-0 or file");
            }
        }
    }
    
    public function registerAutoloaders()
    {
        foreach ($this->autoload_files as $file) {
            require_once $file;
        }
        $loaders = [];
        foreach ($this->autoload_psr0 as $prefix=>$path) {
            $loaders["psr0:{$prefix}"] = $path;
        }
        foreach ($this->autoload_psr4 as $prefix=>$path) {
            $loaders["psr4:{$prefix}"] = $path;
        }
        $loader_func = function ($class) use ($loaders) {
            foreach ($loaders as $prefix => $path) {
                list($type, $prefix) = explode(":", $prefix, 2);
                if (strncmp($prefix, $class, strlen($prefix))===0) {
                    if ($type == "psr4") {
                        $class = substr($class, strlen($prefix));
                    }
                    $file = $path . trim(strtr($class,"\\","/"),"/").".php";
                    if (file_exists($file)) {
                        return require_once $file;
                    }
                }
            }
        };
        \spl_autoload_register($loader_func);
    }
    
    protected function parsePlugin($plugin)
    {
        $this->class_name = $plugin["class"];
        
        if (array_key_exists("auto", $plugin)) {
            $this->auto = (bool)$plugin["auto"];
        }
        
        if (array_key_exists("depends", $plugin)) {
            $this->depends = $plugin["depends"];
        }
    }
    
    public function isLoaded()
    {
        return !empty($this->plugin);
    }
    
    public function isDependency()
    {
        return $this->is_dependency;
    }
    
    public function setIsDependency($is_dependency)
    {
        $this->is_dependency = (bool)$is_dependency;
    }
    
    public function loadPlugin()
    {
        if ($this->plugin) {
            return $this->plugin;
        }

        $this->registerAutoloaders();
        
        $cn = $this->class_name;
        $ci = new $cn();
        $ci->setApplication($this->app);
        $ci->setManifest($this);
        $ci->onLoad();

        $this->plugin = $ci;
        
        return $ci;
        
    }
    
    public function getDependencies()
    {
        return $this->depends;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function getAutoEnable()
    {
        return $this->auto;
    }
}
