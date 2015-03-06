<?php

namespace NoccyLabs\LogPipe\Dumper;

class DefaultDumper
{
    public function dump(array $record)
    {
        $channel    = $record['channel'];
        $level      = $record['level'];
        $message    = $record['message'];
        $time       = $record['time'];
        $client     = $record['client_id'];

        if ($level < 300) {
            $style = "\e[32;1m";
        } elseif ($level < 400) {
            $style = "\e[33;1m";
        } else {
            $style = "\e[31;1m";
        }
        $nostyle = "\e[0m";
        $bold = "\e[1m";
        $nobold = "\e[21m";
        printf("%s {$style}%s{$nostyle}\n",
            $client, rtrim($message));
        //echo join(",", array_keys($record)) . "\n";
    }
}
