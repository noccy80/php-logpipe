<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TestCommand extends AbstractCommand {

    protected function configure()
    {
        $this->setName("test");
        $this->setDescription("Sends a few test events");
    }


    protected function exec()
    {

        $logger = new Logger("main");
        $logger->pushHandler(new LogPipeHandler("udp:127.0.0.1:6999"));
        $logger->pushHandler(new StreamHandler(STDOUT));

        foreach(["debug","info","notice","warning","error","critical","alert","emergency"] as $level) {
            $logger->{$level}("Test log event");
        }

    }

}