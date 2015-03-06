<?php

namespace NoccyLabs\LogPipe\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Dumper\DefaultDumper;

class DumpCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("dump");
        $this->setDescription("Start listening for log events");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", "udp:127.0.0.1:6999");
    }

    protected function exec()
    {
        $endpoint = $this->input->getArgument("endpoint");
        $transport = TransportFactory::create($endpoint);
        $transport->listen();

        $dumper = new DefaultDumper();

        while (true) {
            $msg = $transport->receive(true);
            if ($msg) { $dumper->dump($msg); }
            usleep(10000);
        }

    }
}
