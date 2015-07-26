<?php

namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Application\InputHelper;
use NoccyLabs\LogPipe\Application\LogDumper\InteractiveLogDumper;
use NoccyLabs\LogPipe\Common\StringBuilder;
use NoccyLabs\LogPipe\Dumper\Formatter\Formatter;
use NoccyLabs\LogPipe\Dumper\Filter\MessageFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Dumper\Output\ConsoleDumper;
use NoccyLabs\LogPipe\Application\LogDumper\LogDumper;
use NoccyLabs\LogPipe\Metrics\MetricsLog;
use NoccyLabs\LogPipe\Dumper\Decoder\MetricsDecoder;

/**
 * Class DumpLogCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class DumpLogCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="dump:log")
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
        $this->setDescription("Listen for and start dumping incoming events");

        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", DEFAULT_ENDPOINT);

        $this->addOption("level",       "l", InputOption::VALUE_REQUIRED,   "Minimum level for a log event to be displayed", 100);
        $this->addOption("channels",    "c", InputOption::VALUE_REQUIRED,   "The channels to include (comma-separated)");
        $this->addOption("exclude",     "x", InputOption::VALUE_REQUIRED,   "The channels to exclude (comma-separated)");
        $this->addOption("no-squelch",  "s", InputOption::VALUE_NONE,       "Don't show the number of squelched messages");
        $this->addOption("config",      "C", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Configuration to pass on to the log dumper");
        $this->addOption("interactive", "i", InputOption::VALUE_NONE,       "Allow searching and executing commands while dumping");
        $this->addOption("metrics",     "m", InputOption::VALUE_REQUIRED,   "Capture metrics to the specified file for later processing");
        $this->addOption("timeout",     null,InputOption::VALUE_REQUIRED,   "Stop running after the specified number of seconds");
        $this->addOption("filter",      "f", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Filter expressions");

        //$this->addOption("output", "o", InputOption::VALUE_REQUIRED, "Write the complete log to file");
        //$this->addOption("tee", "t", InputOption::VALUE_REQUIRED, "Write the filtered log to file");

    }


    /**
     * {@inheritdoc}
     */
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

        $config = $this->input->getOption("config");
        $config_opts = [];
        foreach ($config as $config_str) {
            if (strpos($config_str,"=")===false) {
                $key=$config_str;
                $value=1;
            } else {
                list($key,$value) = explode("=",$config_str,2);
            }
            $config_opts[$key] = $value;
        }


        if ($this->input->getOption("interactive")) {
            $log_dumper = new InteractiveLogDumper($this->getApplication(), $config_opts);
        } else {
            $log_dumper = new LogDumper($this->getApplication(), $config_opts);
        }
        $log_dumper->setEventDispatcher($this->getApplication()->getEventDispatcher());
        $log_dumper->setTransport($transport);
        $log_dumper->setDumper($dumper);

        // Set up the metrics dumper to log data if -m is specified
        if (($metrics_file = $this->input->getOption("metrics"))) {
            $metrics_log = new MetricsLog($metrics_file, "w");
        } else {
            $metrics_log = null;
        }
        $metrics_decoder = new MetricsDecoder($metrics_log);
        $dumper->addDecoder($metrics_decoder);

        $log_dumper->setFilter($filter);
        $log_dumper->setOutput($this->output);
        $log_dumper->setShowSquelchInfo(!$this->input->getOption("no-squelch"));

        if (($timeout = $this->input->getOption("timeout"))) {
            $log_dumper->setTimeout($timeout);
        }

        $log_dumper->run();


        $this->output->writeln("\nExiting.");

    }

}
