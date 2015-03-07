<?php

namespace NoccyLabs\LogPipe\Dumper;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDumper
{
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function dump(array $record)
    {
        $channel    = $record['channel'];
        $level      = $record['level'];
        $message    = $record['message'];
        $time       = $record['time'];
        $client     = $record['client_id'];

        if ($level < 200) {
            $style = "fg=green";
        } elseif ($level < 300) {
            $style = "fg=green;options=bold";
        } elseif ($level < 400) {
            $style = "fg=yellow;options=bold";
        } elseif ($level < 500) {
            $style = "fg=red;options=bold";
        } elseif ($level < 600) {
            $style = "fg=yellow;bg=red";
        } else {
            $style = "fg=red;bg=yellow;options=bold";
        }

        $this->output->writeln(
            sprintf("%s <{$style}>%s</{$style}>", $client, rtrim($message))
        );
    }
}
