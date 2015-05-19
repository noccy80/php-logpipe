<?php

require_once __DIR__."/../vendor/autoload.php";

use NoccyLabs\LogPipe\Dumper\Dumper;
use NoccyLabs\LogPipe\Dumper\Output\StreamOutput;
use NoccyLabs\LogPipe\Dumper\Output\ConsoleOutput;
use NoccyLabs\LogPipe\Dumper\Filter\SquelchStatFilter;

$dumper = new Dumper();

// create transport using default endpoint and add it
$dumper->createTransport();

// Filter messages below level 200 and containing the word 'cheese'
$dumper->addExpressionFilter("message.level >= 200");
$dumper->addExpressionFilter("not (message.text matches '/Test/')");

// This filter keeps track of how many messages have been filtered
$squelched = new SquelchStatFilter();
// The callback is invoked when a message is dumped after one or more messages have been filtered
$squelched->setSquelchCallback(function ($num) {
    echo "{$num} messages filtered\n";
});
// Note that it is added with a high priority value, meaning it will be called last
$dumper->addFilter($squelched, 100);

// Write to a log file as well as to the console using ANSI
//$dumper->addOutput(new StreamOutput("logfile.txt"));
$dumper->addOutput(new ConsoleOutput());

while (true) {
    $dumper->updateTransports();
    usleep(10000);
}
