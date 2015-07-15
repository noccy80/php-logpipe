<?php

namespace LogPipe\Plugin\Relay;

use NoccyLabs\LogPipe\Application\Command\AbstractCommand;
use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class RelayCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("relay");
        $this->setDescription("Relay log data");
        $this->addArgument("destination", InputArgument::IS_ARRAY, "Destination endpoints");
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $destinations = $this->input->getArgument("destination");
        $this->output->writeln("Destination endpoints: <info>" . join(" ",$destinations) . "</info>");
    }

}
