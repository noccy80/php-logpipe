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
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $info = $this->getApplication()->getPluginManager()->getManifests();

        $this->output->writeln("Available plugins:");

        foreach ($info as $name=>$manifest) {
            if ($manifest->isLoaded()) {
                if ($manifest->isDependency()) {
                    $check = "<fg=cyan>x</fg=cyan>";
                } else {
                    $check = "<options=bold;fg=cyan>x</options=bold;fg=cyan>";
                }
            } else {
                $check = "<options=bold;fg=black>-</options=bold;fg=black>";
            }
            $description = $manifest->getDescription();
            $this->output->writeln(sprintf("  [%s] <info>%-30s</info> <comment>%s</comment>", $check, $name, $description));
        }
    }

}
