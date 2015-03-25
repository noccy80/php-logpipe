<?php

namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Application\InputHelper;
use NoccyLabs\LogPipe\Dumper\Formatter;
use NoccyLabs\LogPipe\Filter\MessageFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Dumper\ConsoleDumper;
use NoccyLabs\LogPipe\Application\LogDumper\LogDumper;

class DumpLogCommand extends AbstractCommand
{
    protected $cmdname;

    public function __construct($cmdname="dump:log")
    {
        $this->cmdname = $cmdname;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->cmdname);
        $this->setDescription("Listen for and start dumping incoming events");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", "udp:127.0.0.1:6999");
        $this->addOption("level", "l", InputOption::VALUE_REQUIRED, "Minimum level for a log event to be displayed", 100);
        $this->addOption("channels", "c", InputOption::VALUE_REQUIRED, "The channels to include (comma-separated)");
        $this->addOption("exclude", "x", InputOption::VALUE_REQUIRED, "The channels to exclude (comma-separated)");
        $this->addOption("no-squelch", "s", InputOption::VALUE_NONE, "Don't show the number of squelched messages");
        $this->addOption("output", "o", InputOption::VALUE_REQUIRED, "Write the complete log to file");
        $this->addOption("tee", "t", InputOption::VALUE_REQUIRED, "Write the filtered log to file");
        $this->setHelp(self::HELP);
    }

    protected function exec()
    {
        $endpoint = $this->input->getArgument("endpoint");
        $transport = TransportFactory::create($endpoint);
        $transport->listen();

        $squelch_info = (!$this->input->getOption("no-squelch"));

        $filter = new MessageFilter();
        $filter->setIncludedChannels($this->input->getOption("channels"));
        $filter->setExcludedChannels($this->input->getOption("exclude"));
        $filter->setMinimumLevel($this->input->getOption("level"));

        $dumper = new ConsoleDumper($this->output);


        $log_dumper = new LogDumper();
        $log_dumper->setTransport($transport);
        $log_dumper->setDumper($dumper);
        $log_dumper->setFilter($filter);
        $log_dumper->setOutput($this->output);
        $log_dumper->run();


        $this->output->writeln("\nGot SIGINT, Exiting");

    }

    const HELP = <<<TEXT
This command will create a listener on the specified endpoint and start dumping events as
they arrive. The default endpoint is <info>udp:127.0.0.1:6999</info> but it can be overridden on
the command line.

To listen for log messages over UDP on all interfaces on port 12345:

    $ <comment>logpipe dump udp:0.0.0.0:12345</comment>

To restrict the level of messages being displayed, use the <info>--level</info> option:

    $ <comment>logpipe dump --level </comment>

Channels can be used for filtering as well. If the <info>--channels</info> option is specified, it will take
precedence, and only channels matching will be displayed disregarding <info>--exclude</info> if provided.

To dump only the channel monolog:

    $ <comment>logpipe dump --channels monolog</comment>

To dump EVERYTHING EXCEPT the channel monolog:

    $ <comment>logpipe dump --exclude monolog</comment>

The log of events can be dumped in full to a file with <info>--output</info> or filtered with <info>--tee</info>.

Additionally, the <info>--no-squelch</info> option is available to hide the notification about the number of
squelched messages.

TEXT;
}
