<?php

namespace LogPipe\Plugin\BugBasket;

use NoccyLabs\LogPipe\Application\Command\AbstractCommand;
use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Dumper\Decoder\ExceptionDecoder;

class BugsCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("bugs");
        $this->setDescription("Show events from a bug stash");
        $this->addOption("clear", null, InputOption::VALUE_NONE, "Clear the bug stash without showing it first");
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $bugBasket = $this->getContainer()->get("plugin.bugbasket");
        
        if ($this->input->getOption("clear")) {
            $bugBasket->dropStash();
            return;
        }
        
        $bugStash = $bugBasket->getStash();
        
        $bugs = $bugStash->getAll();
        
        foreach ($bugs as $bug) {
            
            $this->output->writeln(
                sprintf("#%d. <info>%s</info> (at <comment>%s</comment>)", $bug['id'], $bug['type'], date(\DateTime::RFC822, $bug['timestamp']))
            );
            $message = unserialize($bug['message']);
            switch ($bug['type']) {
                case 'exception':
                    $exceptionDecoder = new ExceptionDecoder();
                    $message = $exceptionDecoder->decode($message);
            }
            
            $message = "    \e[33;1m".str_replace("\n", "\e[0m\n    \e[33;1m", $message)."\e[0m"."\n";
            
            echo $message."\n\n";
        }
        
    }

}
