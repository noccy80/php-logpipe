<?php

namespace NoccyLabs\LogPipe\Application\Command;

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
        $this->setHelp(self::HELP);
    }

    protected function exec()
    {
        $endpoint = $this->input->getArgument("endpoint");
        $transport = TransportFactory::create($endpoint);
        $transport->listen();

        $level = $this->input->getOption("level");
        $level_map = array(
            "debug"     => 100,
            "info"      => 200,
            "notice"    => 250,
            "warning"   => 300,
            "error"     => 400,
            "critical"  => 500,
            "alert"     => 550,
            "emergency" => 600,
        );
        if (!is_numeric($level)) {
            $level = strtolower($level);
            if (!array_key_exists($level, $level_map)) {
                $this->output->writeln("<error>Invalid log level {$level}. Expected numeric 0-600 or one of ".join(", ", array_keys($level_map))."</error>");
                return(1);
            }
            $level = $level_map[$level];
        }

        $dumper = new ConsoleDumper($this->output, $level);

        $squelched = 0;
        while (true) {
            $msg = $transport->receive(true);
            if ($msg) {
                if ($msg['level'] >= $level) {
                    if ($squelched > 0) {
                        $this->output->writeln("<fg=black;bg=yellow> {$squelched} messages squelched </fg=black;bg=yellow>");
                        $squelched = 0;
                    }
                    $dumper->dump($msg);
                } else {
                    $squelched++;
                }
            }
            usleep(10000);
        }

    }

    const HELP = <<<TEXT
This command will create a listener on the specified endpoint and start dumping events as
they arrive. The default endpoint is <comment>udp:127.0.0.1:6999</comment> but it can be overridden on
the command line.

To listen for log messages over UDP on all interfaces on port 12345:

    $ <info>logpipe dump udp:0.0.0.0:12345</info>

To restrict the level of messages being displayed, use the <comment>--level</comment> option:

    $ <info>logpipe dump --level info

TEXT;
}
