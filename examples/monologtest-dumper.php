<?php

require_once __DIR__."/../vendor/autoload.php";

use NoccyLabs\LogPipe\Transport\TransportFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use NoccyLabs\LogPipe\Dumper\Output\DefaultDumper;

$transport = TransportFactory::create("udp:127.0.0.1:6999");
echo "Listening...";
$transport->listen();
echo "Ok\n";
$dumper = new DefaultDumper();

while (true) {
    $msg = $transport->receive();
    if ($msg) { $dumper->dump($msg); }
    usleep(10000);
}
