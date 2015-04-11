<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class LogTestCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="log:test")
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
        $this->setDescription("Sends a few test events");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", DEFAULT_ENDPOINT);
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {

        $endpoint = $this->input->getArgument("endpoint");

        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler($endpoint));

        foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
            $logger->{$level}("Test log event");
        }

        $logger = new Logger("alternate");
        $logger->pushHandler(new LogPipeHandler($endpoint));

        foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
            $logger->{$level}("Test log event");
        }


    }

    const HELP_TEXT = <<<EOT
This command sends a bunch of different messages over the transport for testing.

EOT;

}
