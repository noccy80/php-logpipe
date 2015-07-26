<?php

namespace NoccyLabs\LogPipe\Application;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use NoccyLabs\LogPipe\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class LogPipeApplication
 * @package NoccyLabs\LogPipe\Application
 */
class LogPipeApplication extends Application
{
    protected $plugins;

    protected $event_dispatcher;
    
    protected $container;
    
    /**
     * Main console application entrypoint
     *
     * @throws \Exception
     */
    public static function main()
    {
        $output = new ConsoleOutput();
        $inst = new self(APP_NAME, APP_VERSION, $output);

        $inst->add(new Command\PluginsCommand());
        $inst->add(new Command\InstallCommand());
        $inst->add(new Command\DumpLogCommand());
        $inst->add(new Command\DumpLogCommand("dump"));
        $inst->add(new Command\DumpChannelsCommand());

        $inst->add(new Command\LogWriteCommand());
        $inst->add(new Command\LogWriteCommand("write"));
        $inst->add(new Command\LogTestCommand());
        $inst->add(new Command\LogTestCommand("test"));
        $inst->add(new Command\LogPassCommand());


        $inst->run(null, $output);
    }

    public function getPluginManager()
    {
        return $this->plugins;
    }
    
    private function initPlugins()
    {
        $plugins = new PluginManager($this);
        $plugins
            ->scanDirectory(getenv("HOME")."/.local/share/logpipe/plugins")
            ->scanDirectory(getenv("HOME")."/.logpipe/plugins")
            ->scanDirectory(getcwd()."/plugins")
            ->scanDirectory(__DIR__."/../../plugins")
            ;
            
        $this->plugins = $plugins;

        $pluginConfig = getenv("HOME")."/.logpipe/plugins.conf";
        if (file_exists($pluginConfig)) {
            $pluginsList = file($pluginConfig, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
            foreach ((array)$pluginsList as $pluginName) {
                try {
                    $plugins->loadPlugin($pluginName);
                } catch (\Exception $e) {
                    error_log("Warning: {$e->getMessage()}");
                }
            }
        }

        if (($pluginsEnv = getenv("LOGPIPE_PLUGINS"))) {
            $pluginsEnv = explode(",", $pluginsEnv);
            foreach ($pluginsEnv as $pluginName) {
                try {
                    $plugins->loadPlugin($pluginName);
                } catch (\Exception $e) {
                    error_log("Warning: {$e->getMessage()}");
                }
            }
        }
        
    }
        
    private function initEvents()
    {
        $this->event_dispatcher = new EventDispatcher();
    }
    
    private function initContainer()
    {
        $this->container = new ContainerBuilder();
    }

    public function __construct($app, $version, $output)
    {
        parent::__construct($app, $version);

        $this->initEvents();
        $this->initContainer();
        $this->initPlugins();

        $formatter = $output->getFormatter();
        $styles = [
            "command" => new OutputFormatterStyle("cyan", null, []),
            "arguments" => new OutputFormatterStyle("cyan", null, ["bold"]),
            "value" => new OutputFormatterStyle("cyan", null, ["underscore"]),
            "example" => new OutputFormatterStyle("cyan", null, []),
            "header" => new OutputFormatterStyle(null, null, ["bold"]),
            "subheader" => new OutputFormatterStyle(null, null, [ "underscore" ]),
        ];
        foreach ($styles as $name=>$style) {
            $formatter->setStyle($name, $style);
        }
    }

    public function getEventDispatcher()
    {
        return $this->event_dispatcher;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
}
