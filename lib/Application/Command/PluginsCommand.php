<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class PluginsCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="plugins")
    {
        $this->cmdname = $cmdname;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->cmdname);
        $this->setDescription("Show plugins");
        
        $this->addOption("enable", null, InputOption::VALUE_REQUIRED, "Enable a plugin in ~/.logpipe/plugins.conf");
        $this->addOption("disable", null, InputOption::VALUE_REQUIRED, "Disable a plugin in ~/.logpipe/plugins.conf");
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $info = $this->getApplication()->getPluginManager()->getManifests();

        
        if (($enable = $this->input->getOption("enable"))) {
            $this->output->writeln("<info>Enabling plugin {$enable}...</info>");
            $this->enablePlugin($enable);
            return;
        }

        if (($disable = $this->input->getOption("disable"))) {
            $this->output->writeln("<info>Disabling plugin {$disable}...</info>");
            $this->disablePlugin($disable);
            return;
        }

        $table = new Table($this->output);
        $table->setStyle("compact");
        $table->setHeaders(["Name", "Version", "Description", "Status" ]);
        foreach ($info as $name=>$manifest) {
            if ($manifest->isLoaded()) {
                if ($manifest->isDependency()) {
                    $check = "[<fg=green>x</fg=green>] <options=bold;fg=green>Dependency</options=bold;fg=green>";
                } else {
                    $check = "[<fg=cyan;options=bold>x</fg=cyan;options=bold>] <options=bold;fg=cyan>Loaded</options=bold;fg=cyan>";
                }
            } else {
                $check = "[ ] <options=bold;fg=black>Inactive</options=bold;fg=black>";
            }
            $description = $manifest->getDescription();
            $version = $manifest->getVersion();
            $table->addRow([
                $name,
                $version,
                $description,
                $check,
            ]);
        }
        
        $table->render();

    }

    protected function enablePlugin($name)
    {
        $pluginsConfig = getenv("HOME")."/.logpipe/plugins.conf";
        if (!is_dir(dirname($pluginsConfig))) {
            mkdir(dirname($pluginsConfig));
        }
        if (file_exists($pluginsConfig)) {
            $current = file($pluginsConfig, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        } else {
            $current = [];
        }
        if (!in_array($name, $current)) {
            $current[] = $name;
        }
        file_put_contents($pluginsConfig, join("\n", $current));        
    }
    
    protected function disablePlugin($name)
    {
        $pluginsConfig = getenv("HOME")."/.logpipe/plugins.conf";
        if (!is_dir(dirname($pluginsConfig))) {
            return;
        }
        if (file_exists($pluginsConfig)) {
            $current = file($pluginsConfig, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        } else {
            return;
        }
        if (in_array($name, $current)) {
            $current = array_filter($current, function ($item) use ($name) {
                return $name!=$item;
            });
        }
        file_put_contents($pluginsConfig, join("\n", $current));        
    }
    
}
