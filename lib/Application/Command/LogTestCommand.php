<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;

class LogTestCommand extends AbstractCommand
{

    protected $cmdname;

    public function __construct($cmdname="log:test")
    {
        $this->cmdname = $cmdname;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->cmdname);
        $this->setDescription("Sends a few test events");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", DEFAULT_ENDPOINT);
    }


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

}
