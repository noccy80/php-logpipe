<?php

require_once __DIR__."/../vendor/autoload.php";

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger("main");
$logger->pushHandler(new LogPipeHandler("udp:127.0.0.1:6999"));
$logger->pushHandler(new StreamHandler(STDOUT));

$logger->debug("Hello World!");
$logger->info("This is an info message");
$logger->warn("And a warning");
// $logger->crit("This is a very long message: ".str_repeat("hello world ", 100));
$logger->emergency("Oh my god!");
