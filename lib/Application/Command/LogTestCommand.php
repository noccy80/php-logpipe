<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->setDescription("Sends a few test events to an endpoint");
        $this->addArgument("endpoint", InputArgument::OPTIONAL, "The endpoint or pipe to dump", DEFAULT_ENDPOINT);
        $this->addOption("test", "t", InputOption::VALUE_REQUIRED, "The test to run", "default");
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $test = $this->input->getOption("test");
        $endpoint = $this->input->getArgument("endpoint");

        switch ($test) {
            case 'default':
                $this->runDefaultTest($endpoint);
                break;
            case 'large':
                $this->runLargeMessageTest($endpoint);
                break;
            case 'stress':
                $this->runStressTest($endpoint);
                break;
            default:
                throw new \Exception("No such test {$test}");
        }
    }

    private function runDefaultTest($endpoint)
    {
        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler($endpoint));

        foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
            $logger->{$level}("This is a test message of level '{$level}' sent over the 'main' channel");
        }

        $logger = new Logger("alternate");
        $logger->pushHandler(new LogPipeHandler($endpoint));
        foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
            $logger->{$level}("This is a test message of level '{$level}' sent over the 'alternate' channel");
        }

        $logger->error(new \Exception("This is an exception"));

        $logger->info("!metric.log page.hit", [ "route"=>"homepage" ]);
        $logger->info("!metric.log data.written", [ "records"=>13, "failed"=>0, "duration"=>199.7, "size"=>445032 ]);
        $logger->info("!metric.log feature-use", [ "privacy"=>true, "notifications"=>["email"=>true, "mobile"=>true, "desktop"=>false ]]);
        $logger->info("!metric.item page.hit.simple homepage");

    }

    private function runLargeMessageTest($endpoint)
    {
        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler($endpoint));

        for ($n = 0; $n < 10; $n++) {
            $logger->info(str_repeat("Aa",10000));
        }
    }

    private function runStressTest($endpoint)
    {
        for ($wid = 0; $wid <5; $wid++) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                $this->doStressTest($endpoint);
                return;
            } elseif ($pid > 0) {
                $this->output->writeln("New test process {$wid} spawned with PID={$pid}");
            } elseif ($pid === false) {
                error_log("Error: Unable to fork, exiting!");
                return;
            }
        }
    }
    
    private function doStressTest($endpoint) {        

        $logger = new Logger("stress");
        $logger->pushHandler(new LogPipeHandler($endpoint));

        for ($n = 0; $n < 500; $n++) {
            foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
                $logger->{$level}("This is a test message of level '{$level}' sent over the 'main' channel");
                usleep(100);
            }
        }
        
        $this->output->writeln("Tester done sending 4000 messages");
    }

    const HELP_TEXT = <<<EOT
This command sends a bunch of different messages over the transport for testing.

EOT;

}
