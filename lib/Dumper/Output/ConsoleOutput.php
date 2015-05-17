<?php


namespace NoccyLabs\LogPipe\Dumper\Output;


use NoccyLabs\LogPipe\Dumper\Formatter\Formatter;
use NoccyLabs\LogPipe\Message\MessageInterface;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput as SymfonyConsoleOutput;

class ConsoleOutput implements OutputInterface
{
    protected $output;
    
    public function __construct(SymfonyOutputInterface $output=null)
    {
        $this->output = $output?:(new SymfonyConsoleOutput(SymfonyConsoleOutput::VERBOSITY_VERY_VERBOSE, true));
        $this->formatter = new Formatter([$this,"getMessageMarkup"]);
    }

    public function write(MessageInterface $message)
    {
        $formatted = $this->formatter->format($message);
        $this->output->writeln($formatted);
    }

    public function getMessageMarkup(MessageInterface $message)
    {
        $level = $message->getLevel();
        if ($level < 200) {
            return "<fg=green>";
        } elseif ($level < 300) {
            return "<fg=green;options=bold>";
        } elseif ($level < 400) {
            return "<fg=yellow;options=bold>";
        } elseif ($level < 500) {
            return "<fg=red;options=bold>";
        } elseif ($level < 550) {
            return "<fg=yellow;bg=red>";
        } elseif ($level < 600) {
            return "<fg=yellow;bg=red;options=bold>";
        } else {
            return "<fg=red;bg=yellow;options=bold>";
        }
    }
}