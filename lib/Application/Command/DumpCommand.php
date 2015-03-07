<?php

namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Application\InputHelper;
use NoccyLabs\LogPipe\Filter\MessageFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Dumper\ConsoleDumper;

class DumpCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("dump");
        $this->setDescription("Start listening for log events");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", "udp:127.0.0.1:6999");
        $this->addOption("level", "l", InputOption::VALUE_REQUIRED, "Minimum level for a log event to be displayed", 100);
        $this->addOption("channels", "c", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "The channels to include");
        $this->addOption("exclude", "e", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "The channels to exclude");
        $this->setHelp(self::HELP);
    }

    protected function filterMessage($message)
    {
        $include    = $this->input->getOption("channels");
        $exclude    = $this->input->getOption("exclude");

        $channel    = $message['channel'];


    }

    protected function exec()
    {
        $endpoint = $this->input->getArgument("endpoint");
        $transport = TransportFactory::create($endpoint);
        $transport->listen();


        $filter = new MessageFilter();
        $filter->setIncludedChannels((array)$this->input->getOption("channels"));
        $filter->setExcludedChannels((array)$this->input->getOption("exclude"));
        $filter->setMinimumLevel($this->input->getOption("level"));

        $dumper = new ConsoleDumper($this->output);


        $break = false;
        pcntl_signal(SIGINT, function () use (&$break) { $break = true; });
        declare(ticks=5);

        $squelched = 0;
        while (!$break) {
            $msg = $transport->receive();
            if ($msg) {
                if (($out = $filter->filterMessage($msg))) {
                    if ($squelched > 0) {
                        $this->output->writeln("\r<fg=black;bg=yellow> {$squelched}</fg=black;bg=yellow><fg=black;bg=yellow;options=bold> messages squelched </fg=black;bg=yellow;options=bold>");
                        $squelched = 0;
                    }
                    $dumper->dump($out);
                } else {
                    $squelched++;
                    $this->output->write("\r<fg=black;bg=<yellow></yellow> {$squelched} </fg=black;bg=yellow>");
                }
            }
            usleep(1000);
        }

        $this->output->writeln("\nExiting");

    }

    const HELP = <<<TEXT
This command will create a listener on the specified endpoint and start dumping events as
they arrive. The default endpoint is <comment>udp:127.0.0.1:6999</comment> but it can be overridden on
the command line.

To listen for log messages over UDP on all interfaces on port 12345:

    $ <info>logpipe dump udp:0.0.0.0:12345</info>

To restrict the level of messages being displayed, use the <comment>--level</comment> option:

    $ <info>logpipe dump --level </info>

Channels can be used for filtering as well. If the <comment>--channels</comment> option is specified, it will take
precedence, and only channels matching will be displayed disregarding <comment>--exclude</comment> if provided.

To dump only the channel monolog:
    $ <info>logpipe dump --channels monolog</info>

To dump EVERYTHING EXCEPT the channel monolog:
    $ <info>logpipe dump --exclude monolog</info>

TEXT;
}
