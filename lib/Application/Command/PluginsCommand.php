<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->addOption("all", "a", InputOption::VALUE_NONE, "Show all plugins, not just the loaded plugins");
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $all = $this->input->getOption("all");
        $info = $this->getApplication()->getPluginManager()->getInfo($all);
        
        $this->output->writeln($all?"Available plugins:":"Loaded plugins:");
        
        foreach ($info as $name=>$description) {
            $this->output->writeln(" - <info>".$name."</info>: <comment>".$description."</comment>");
        }
    }

}
