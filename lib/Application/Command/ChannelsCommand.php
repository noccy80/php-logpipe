<?php

namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Application\InputHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Dumper\ConsoleDumper;

class ChannelsCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("channels");
        $this->setDescription("Dump all unique channel names from an endpoint");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", "udp:127.0.0.1:6999");
        $this->setHelp(self::HELP);
    }

    protected function exec()
    {
        $endpoint = $this->input->getArgument("endpoint");
        $transport = TransportFactory::create($endpoint);
        $transport->listen();

        $break = false;
        pcntl_signal(SIGINT, function () use (&$break) { $break = true; });
        declare(ticks=5);

        while (!$break) {
            $msg = $transport->receive();
            if ($msg) {
                $channel = $msg->getChannel();
                $this->infoFancy($channel);
            }
            usleep(10000);
        }

        $this->output->writeln("Ctrl-C");

    }

    protected function infoFancy($channel)
    {
        static $channels;

        if (!is_array($channels)) { $channels = []; }

        if (!array_key_exists($channel, $channels)) {
            $channels[$channel] = 1;
        } else {
            $channels[$channel]++;
        }

        $this->output->write("\e[2J\e[H");
        foreach ($channels as $channel=>$events) {
            $this->output->writeln(sprintf("%-40s %5d", $channel, $events));
        }
    }

    const HELP = <<<TEXT
This command will create a listener on the specified endpoint and dump all channels being used.
TEXT;
}
