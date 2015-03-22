<?php

namespace NoccyLabs\LogPipe\Dumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Message\MonologMessage;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDumper
{
    protected $output;

    protected $formatter;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->formatter = new Formatter();
        $this->formatter->setMessageStyle(array($this,"getRecordMarkup"));
    }

    public function dump(MessageInterface $message)
    {
        $client     = $message->getClientId();

        $output = $this->formatter->format($message);

        $this->output->writeln(
            sprintf("%s %s", $client, $output)
        );
    }

    public function getRecordMarkup($message)
    {
        $level      = $message->getLevel();

        if ($level < 200) {
            $style = "<fg=green>";
        } elseif ($level < 300) {
            $style = "<fg=green;options=bold>";
        } elseif ($level < 400) {
            $style = "<fg=yellow;options=bold>";
        } elseif ($level < 500) {
            $style = "<fg=red;options=bold>";
        } elseif ($level < 550) {
            $style = "<fg=yellow;bg=red>";
        } elseif ($level < 600) {
            $style = "<fg=yellow;bg=red;options=bold>";
        } else {
            $style = "<fg=red;bg=yellow;options=bold>";
        }

        return $style;

    }
}
