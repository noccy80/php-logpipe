<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LogPassCommand extends AbstractCommand
{

    protected $cmdname;

    public function __construct($cmdname="log:pass")
    {
        $this->cmdname = $cmdname;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->cmdname);
        $this->setDescription("Pass through standard input to an endpoint");
        $this->addOption("channel", "c", InputOption::VALUE_REQUIRED, "Logger channel", "main");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint to write to", DEFAULT_ENDPOINT);
        $this->setHelp(self::HELP_TEXT);
    }


    protected function exec()
    {

        $endpoint = $this->input->getArgument("endpoint");
        $channel  = $this->input->getOption("channel");

        $logger = new Logger($channel);
        $logger->pushHandler(new LogPipeHandler($endpoint));

        while (!feof(STDIN)) {
            $read = fgets(STDIN);
            $logger->info(rtrim($read));
        }
    }

const HELP_TEXT = <<<EOT
Use this command to pass the output of another script or process through to a LogPipe endpoint.
This can be used to include logs from external processes when dumping to follow a process as it
is executed, or while debugging a server.

Execute in one shell:

    $ <comment>dmesg -w | logpipe log:pass</comment>

And in another:

    $ <comment>logpipe dump</comment>

EOT;

}
