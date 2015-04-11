<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class LogWriteCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class LogWriteCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="log:write")
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
        $this->setDescription("Write a log event to an endpoint");
        $this->addOption("channel", "c", InputOption::VALUE_REQUIRED, "Logger channel", "main");
        $this->addOption("debug", "D", InputOption::VALUE_NONE, "Use message level debug");
        $this->addOption("info", "I", InputOption::VALUE_NONE, "Use message level info");
        $this->addOption("warning", "W", InputOption::VALUE_NONE, "Use message level warning");
        $this->addOption("error", "E", InputOption::VALUE_NONE, "Use message level error");
        $this->addOption("critical", "C", InputOption::VALUE_NONE, "Use message level critical");
        $this->addOption("alert", "A", InputOption::VALUE_NONE, "Use message level alert");
        $this->addOption("emergency", "F", InputOption::VALUE_NONE, "Use message level emergency");
        $this->addArgument("message", InputArgument::REQUIRED, "The message to write");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint to write to", DEFAULT_ENDPOINT);
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {

        $endpoint = $this->input->getArgument("endpoint");
        $channel  = $this->input->getOption("channel");
        $message  = $this->input->getArgument("message");

        $logger = new Logger($channel);
        $logger->pushHandler(new LogPipeHandler($endpoint));

        if ($this->input->getOption("debug")) {
            $logger->debug($message);
        } elseif ($this->input->getOption("info")) {
            $logger->info($message);
        } elseif ($this->input->getOption("warning")) {
            $logger->warning($message);
        } elseif ($this->input->getOption("error")) {
            $logger->error($message);
        } else {
            $logger->info($message);
        }
    }

const HELP_TEXT = <<<EOT
This command writes a message to a transport. It can be used to generate log events.

To write an error message over the default transport using the channel "scripts":

    $ <comment>logpipe write --error --channel scripts "The file foobar could not be deleted"</comment>

EOT;

}
