<?php

require_once __DIR__."/../vendor/autoload.php";

use NoccyLabs\LogPipe\Transport\TransportFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use NoccyLabs\LogPipe\Dumper\Output\DefaultDumper;

$transport = TransportFactory::create(DEFAULT_ENDPOINT);
$transport->listen();
$dumper = new DefaultDumper();

while (true) {
    $msg = $transport->receive();
    if ($msg) { $dumper->dump($msg); }
    usleep(10000);
}
