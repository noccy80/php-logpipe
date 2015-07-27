<?php

namespace LogPipe\Plugin\Relay;

use NoccyLabs\LogPipe\Application\Command\AbstractCommand;
use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Posix\SignalListener;

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
        $this->addOption("endpoint", "e", InputOption::VALUE_REQUIRED, "The endpoint or pipe to dump", DEFAULT_ENDPOINT);
        $this->addArgument("listeners", InputArgument::IS_ARRAY, "Destination listener endpoints");
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $destinations = $this->input->getArgument("listeners");
        if (count($destinations) == 0) {
            $this->output->writeln("<error>You need to specify at least one listener!</error>");
            return(1);
        }

        $this->output->writeln("Destination endpoints: <comment>" . join("</comment>, <comment>",$destinations) . "</comment>");

        $destinationEndpoints = [];
        foreach ($destinations as $destination) {
            $endpoint = TransportFactory::create($destination);
            $endpoint->connect();
            $destinationEndpoints[] = $endpoint;
        }
        
        $sourceEndpoint = TransportFactory::create($this->input->getOption("endpoint"));
        $sourceEndpoint->listen();
        
        $signal = new SignalListener(SIGINT);
        
        $spin = "/-\\|";
        $spix = 0;
        $count = 0;
        
        $this->output->writeln("<info>Starting relay...</info>");
        
        declare(ticks=5);
        while (true) {
            do {
                usleep(1000);
                if ($signal()) {
                    break(2);
                }
            } while (!($message = $sourceEndpoint->receive(false)));

            if ($message) {            
                foreach ($destinationEndpoints as $endpoint) {
                    $endpoint->send($message);
                }
            }
        }

        $this->output->writeln("\n<info>Shutting down...</info>");
        
    }

}
